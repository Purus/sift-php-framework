<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormJavascriptValidationMessage represents a message used for javascript validation
 *
 * @package Sift
 * @subpackage form_javascript
 */
class sfFormJavascriptValidationMessage implements sfIJsonSerializable {

  /**
   * Message
   *
   * @var string
   */
  public $message = '';

  /**
   * Parameters for translation
   *
   * @var array
   */
  public $parameters = array();

  /**
   * Constructs the object
   *
   * @param string $message Message
   * @param array $parameters Array of parameters for translation string
   */
  public function __construct($message, $parameters = array())
  {
    $this->message = (string)$message;
    $this->parameters = $parameters;
  }

  /**
   * Sets the message
   *
   * @param string $message
   * @return sfFormJavascriptValidationMessage
   */
  public function setMessage($message)
  {
    $this->message = $message;
    return $this;
  }

  /**
   * Returns message
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Converts the object to string
   *
   * @return string
   */
  public function __toString()
  {
    return strtr($this->message, $this->parameters);
  }

  /**
   * Serialize to JSON
   *
   * @return string
   */
  public function jsonSerialize()
  {
    return $this->__toString();
  }

  /**
   * Returns an array of parameters
   *
   * @return array
   */
  public function getParameters()
  {
    return $this->parameters;
  }

  /**
   * Has any parameters?
   *
   * @return boolean
   */
  public function hasParameters()
  {
    return (count($this->parameters) > 0);
  }

  /**
   * Sets translation parameters
   *
   * @param array $parameters Array of translation parameters
   * @return sfFormJavascriptValidationMessage
   */
  public function setParameters(array $parameters)
  {
    $this->parameters = $parameters;
    return $this;
  }

}
