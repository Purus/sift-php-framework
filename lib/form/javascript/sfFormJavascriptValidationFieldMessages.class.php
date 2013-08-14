<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormJavascriptValidationMessage represents a collection of messages for given form field
 *
 * @package Sift
 * @subpackage form_javascript
 */
class sfFormJavascriptValidationFieldMessages implements ArrayAccess {

  /**
   * Field name (short version)
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Field name used in the form ie. formName[fieldName]
   *
   * @var string
   */
  protected $formFieldName;

  /**
   * Array of messages
   *
   * @var array
   */
  protected $messages = array();

  /**
   * Constructs the object
   *
   * @param string $fieldName
   * @param string $formField
   * @param array $messages Array of messages
   */
  public function __construct($fieldName, $formFieldName, $messages = null)
  {
    $this->fieldName = $fieldName;
    $this->formFieldName = $formFieldName;

    foreach($messages as $i => $message)
    {
      if(!$message instanceof sfFormJavascriptValidationMessage)
      {
        $messages[$i] = new sfFormJavascriptValidationMessage($message);
      }
    }

    $this->messages = $messages;
  }

  /**
   * Return field name
   *
   * @return string
   */
  public function getFieldName()
  {
    return $this->fieldName;
  }

  /**
   * Return form field name
   *
   * @return string
   */
  public function getFormFieldName()
  {
    return $this->formFieldName;
  }

  /**
   * Return messages
   *
   * @return array
   */
  public function getMessages()
  {
    return $this->messages;
  }

  public function offsetGet($name)
  {
    return $this->messages[$name];
  }

  public function offsetSet($name, $value)
  {
    if(!$value instanceof sfFormJavascriptValidationMessage)
    {
      $value = new sfFormJavascriptValidationMessage($value);
    }

    return $this->messages[$name] = $value;
  }

  public function offsetExists($name)
  {
    return array_key_exists($name, $this->messages);
  }

  public function offsetUnset($name)
  {
    unset($this->messages[$name]);
  }

}
