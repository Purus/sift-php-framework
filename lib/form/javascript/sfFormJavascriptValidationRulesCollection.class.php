<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormJavascriptValidationRulesCollection represents a collection of javascript validation rules
 *
 * @package Sift
 * @subpackage form_javascript
 */
class sfFormJavascriptValidationRulesCollection extends sfCollection implements sfIJsonSerializable
{
  protected $itemType = 'sfFormJavascriptValidationFieldRules';

  /**
   * Offset get the rule with the given $name.
   *
   * @param string $name
   * @return sfFormJavascriptValidationRule|null
   */
  public function offsetGet($name)
  {
    foreach ($this as $rule) {
      if ($rule->getFieldName() == $name) {
        return $rule;
      }
    }
  }

  /**
   * Return data which should be serialized to JSON
   *
   * @return array
   */
  public function jsonSerialize()
  {
    $data = array();
    foreach ($this as $rule) {
      $data[$rule->getFormFieldName()] = $rule->getRules();
    }

    return $data;
  }

}
