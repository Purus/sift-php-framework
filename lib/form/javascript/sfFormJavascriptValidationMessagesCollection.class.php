<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormJavascriptValidationMessagesCollection represents a collection of javascript validation messages
 *
 * @package Sift
 * @subpackage form_javascript
 */
class sfFormJavascriptValidationMessagesCollection extends sfCollection implements sfIJsonSerializable {

  /**
   * Allowed type for this collection
   *
   * @var string
   */
  protected $itemType = 'sfFormJavascriptValidationFieldMessages';

  /**
   * Return data which should be serialized to JSON
   *
   * @return array
   */
  public function jsonSerialize()
  {
    $data = array();
    foreach($this as $message)
    {
      $serializableMessages = array();
      foreach($message->getMessages() as $name => $singleMessage)
      {
        $serializableMessages[$name] = $singleMessage->__toString();
      }
      $data[$message->getFormFieldName()] = $serializableMessages;
    }
    return $data;
  }

}