<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs the message to the logger instance
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailerLoggerPlugin extends sfMailerPlugin
{
  /**
   * @var array
   */
  protected $messages = array();

  /**
   * Get the message list
   *
   * @return array
   */
  public function getMessages()
  {
    return $this->messages;
  }

  /**
   * Get the message count
   *
   * @return integer count
   */
  public function countMessages()
  {
    return count($this->messages);
  }

  /**
   * Empty the message list
   *
   */
  public function clear()
  {
    $this->messages = array();
  }

  /**
   * Invoked immediately before the message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = clone $evt->getMessage();
    $this->messages[] = $message;
    $to = null === $message->getTo() ? '' : implode(', ', array_keys($message->getTo()));
    $this->log(sprintf('Sending email "%s" to "%s"', $message->getSubject(), $to));
  }

  /**
   * Invoked immediately after the message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $result = $evt->getResult();
    switch ($result) {
      case Swift_Events_SendEvent::RESULT_FAILED:
        $failedRecipients = $evt->getFailedRecipients();
        $entry = sprintf('Sending failed. Failed recipients: "%s".', join(', ', $failedRecipients));
      break;

      case Swift_Events_SendEvent::RESULT_PENDING:
        $entry = 'Sending has yet to occur.';
      break;

      case Swift_Events_SendEvent::RESULT_SUCCESS:
        $entry = 'Sending was successfull.';
      break;

      case Swift_Events_SendEvent::RESULT_TENTATIVE:
        $entry = 'Sending worked, but there were some failures.';
      break;

      default:
        $entry = 'Unknown result status.';
      break;
    }
    // log this entry
    $this->log($entry);
  }

  /**
   * Add a log entry.
   *
   * @param string $entry
   */
  public function log($entry)
  {
    if (!sfConfig::get('sf_logging_enabled')) {
      return;
    }
    sfLogger::getInstance()->info(sprintf('{sfMailer} %s', $entry));
  }

}
