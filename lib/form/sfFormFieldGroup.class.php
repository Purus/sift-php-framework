<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormFieldGroup class is used for grouping form fields into groups aka
 * fieldsets
 *
 * @package    Sift
 * @subpackage form
 */
class sfFormFieldGroup implements ArrayAccess {

  protected $form,
            $label,
            $fields = array();

  protected $priority = 1;

  /**
   * Constructs form groups which has $label and consists of $fields
   *
   * @param sfForm $form
   * @param string $label
   * @param array $fields
   * @param integer $priority
   */
  public function __construct(sfForm $form, $label, array $fields, $priority = 1)
  {
    $this->form = $form;
    $this->label = $label;
    $this->fields = $fields;
    $this->priority = (int)$priority;
  }

  public function getFields()
  {
    $return = array();
    foreach($this->fields as $fieldName)
    {
      if(!isset($this->form[$fieldName]))
      {
        continue;
      }
      $return[$fieldName] = $this->form[$fieldName];
    }
    return $return;
  }

  /**
   * Sets label aka title
   *
   * @param string $label
   * @return sfFormFieldGroup
   */
  public function setLabel($label)
  {
    $this->label = $label;
    return $this;
  }

  public function getLabel()
  {
    return $this->label;
  }

  public function setPriority($priority)
  {
    $this->priority = (int)$prority;
    return $this;
  }

  public function getPriority()
  {
    return $this->priority;
  }

  /**
   * Returns true if the bound field exists (implements the ArrayAccess interface).
   *
   * @param string $name The name of the bound field
   *
   * @return Boolean true if the widget exists, false otherwise
   */
  public function offsetExists($name)
  {
    return isset($this->fields[$name]);
  }

  /**
   * Throws an exception saying that values cannot be set (implements the ArrayAccess interface).
   *
   * @param string $offset (ignored)
   * @param string $value (ignored)
   *
   * @throws LogicException
   */
  public function offsetSet($offset, $value)
  {
    throw new LogicException('Cannot update form fields (read-only).');
  }

  /**
   * Throws an exception saying that values cannot be unset (implements the ArrayAccess interface).
   *
   * @param string $offset (ignored)
   *
   * @throws LogicException
   */
  public function offsetUnset($offset)
  {
    unset($this->fields[$offset]);
  }

 /**
   * Returns the form field associated with the name (implements the ArrayAccess interface).
   *
   * @param string $name The offset of the value to get
   *
   * @return sfFormField A form field instance
   */
  public function offsetGet($name)
  {
    return $this->fields[$name];
  }

  /**
   * Returns a string representation of the form.
   *
   * @return string A string representation of the form
   *
   * @see render()
   */
  public function __toString()
  {
    try
    {
      return $this->render();
    }
    catch (Exception $e)
    {
      sfForm::setToStringException($e);

      // we return a simple Exception message in case the form framework is used out of symfony.
      return 'Exception: '.$e->getMessage();
    }
  }

  /**
   * Renders the groups widgets associated with this group.
   *
   * @return string The rendered group
   */
  public function render()
  {
    $formatter = $this->form->getWidgetSchema()->getFormFormatter();

    $rows = array();

    foreach($this->getFields() as $field)
    {
      $rows[] = $field->renderRow();
    }

    if($formatter)
    {
      return strtr($formatter->getFieldGroupFormat(), array(
          '%group_name%' => $this->__($this->getLabel()),
          '%field_rows%' => join("\n", $rows)
      ));
    }

    return join("\n", $rows);
  }

  /**
   * Translates the text
   *
   * @param string $str Text to be translated
   * @param array $params Translation arguments
   * @return string
   */
  public function __($str, $params = array())
  {
    return $this->form->__($str, $params);
  }

}
