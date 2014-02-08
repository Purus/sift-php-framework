<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Flash message
 *
 * @package    Sift
 * @subpackage user
 */
class sfUserFlashMessage implements Serializable {

  /**
   * Success
   */
  const SUCCESS = 'success';

  /**
   * Error
   */
  const ERROR = 'error';

  /**
   * Notice
   */
  const NOTICE = 'notice';

  /**
   * Type of the message
   *
   * @var string
   */
  protected $type;

  /**
   * The message
   *
   * @var string
   */
  protected $message;

  /**
   * The application which set this message
   *
   * @var string
   */
  protected $application;

  /**
   * Constructor
   *
   * @param string $message The message
   * @param string $type The type of the message (success, info, help...)
   * @param string $application The application which set this message
   */
  public function __construct($message, $type = self::TYPE_INFO, $application = null)
  {
    $this->message = (string)$message;
    $this->type = (string)$type;
    $this->application = $application ? (string)$application : null;
  }

  /**
   * Return the message
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Sets the message
   *
   * @param string $message The message
   * @return sfUserFlashMessage
   */
  public function setMessage($message)
  {
    $this->message = (string)$message;
    return $this;
  }

  /**
   * Return the type of the message
   *
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Sets the type of the message (success, info, help...)
   *
   * @param string $type
   * @return sfUserFlashMessage
   */
  public function setType($type)
  {
    $this->type = (string)$type;
    return $this;
  }

  /**
   * Returns the application which set this message
   *
   * @return string
   */
  public function getApplication()
  {
    return $this->application;
  }

  /**
   * Sets the application
   *
   * @param string $application
   */
  public function setApplication($application)
  {
    $this->application = (string)$application;
  }

  /**
   * Serialized the message
   *
   * @return string
   */
  public function serialize()
  {
    return serialize(array($this->type, $this->message, $this->application));
  }

  /**
   * Unserializes the message
   *
   * @param string $serialized
   */
  public function unserialize($serialized)
  {
    list($this->type, $this->message, $this->application) = unserialize($serialized);
  }

  /**
   * Converts the message to string
   *
   * @return string
   */
  public function __toString()
  {
    return $this->message;
  }

}
