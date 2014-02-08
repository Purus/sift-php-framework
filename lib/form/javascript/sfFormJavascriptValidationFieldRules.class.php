<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormJavascriptValidationFielsRules represents javascript validation rules for a form field
 *
 * @package Sift
 * @subpackage form_javascript
 */
class sfFormJavascriptValidationFieldRules implements ArrayAccess, sfIJsonSerializable
{
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
   * Array of rules
   *
   * @var array
   */
  protected $rules;

  /**
   * Constructor
   *
   * @param string $fieldName
   * @param string $formFieldName
   * @param array $rules
   */
  public function __construct($fieldName, $formFieldName, $rules)
  {
    $this->fieldName = $fieldName;
    $this->formFieldName = $formFieldName;
    $this->rules = (array) $rules;
  }

  /**
   * Is this rule required?
   *
   * @return boolean
   */
  public function isRequired()
  {
    return isset($this->rules['required']) && $this->rules['required'];
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
   * Return rules
   *
   * @return array
   */
  public function getRules()
  {
    return $this->rules;
  }

  /**
   * Set rules
   *
   * @param array $rules
   * @return sfFormJavascriptValidationRule
   */
  public function setRules($rules)
  {
    $this->rules = $rules;

    return $this;
  }

  public function offsetGet($name)
  {
    return $this->rules[$name];
  }

  public function offsetSet($name, $value)
  {
    return $this->rules[$name] = $value;
  }

  public function offsetExists($name)
  {
    return array_key_exists($name, $this->rules);
  }

  public function offsetUnset($name)
  {
    unset($this->rules[$name]);
  }

  public function jsonSerialize()
  {
    return $this->rules;
  }

}
