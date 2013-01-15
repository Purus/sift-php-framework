<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormJavascriptFixedValidationMessage class
 *
 * @package    Sift
 * @subpackage form
 * @author     Mishal.cz <mishal at mishal dot cz>
 */
class sfFormJavascriptFixedValidationMessage {

  public $message = '';
  public $parameters = array();

  /**
   * Constructs the object
   * 
   * @param string $message Message
   * @param string $javascript Javascript code
   * @param array $parameters Array of parameters for translation string
   */
  public function __construct($message, $parameters = array())
  {
    $this->message = $message;
    $this->parameters = $parameters;
  }
  
  /**
   * Sets the message
   * 
   * @param string $message
   * @return sfFormJavascriptFixedValidationMessage
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
   * Returns an array of parameters
   * 
   * @return array
   */
  public function getParameters()
  {
    return $this->parameters;
  }
  
  /**
   * Sets translation parameters
   * 
   * @param array $parameters Array of translation parameters
   * @return sfFormJavascriptFixedValidationMessage
   */
  public function setParameters(array $parameters)
  {
    $this->parameters = $parameters;
    return $this;
  }
  
}
