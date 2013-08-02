<?php
/*
 * This file is part of the Sift PHP framework
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfMailerLogger class
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailerLogger extends Swift_Plugins_MessageLogger {

  /**
   * Invoked immediately before the Message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    parent::beforeSendPerformed($evt);

    $message = clone $evt->getMessage();
    
    $to = null === $message->getTo() ? '' : implode(', ', array_keys($message->getTo()));

    $this->log(sprintf('Sending email "%s" to "%s"', $message->getSubject(), $to));
  }

  /**
   * Add a log entry.
   * 
   * @param string $entry
   */
  public function log($entry)
  {
    if(!sfConfig::get('sf_logging_enabled'))
    {
      return;
    }
    sfLogger::getInstance()->info(sprintf('{sfMailer} %s', $entry));
  }

  /**
   * Invoked immediately after the Message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    parent::sendPerformed($evt);
    
    $result = $evt->getResult();

    switch($result)
    {
      case Swift_Events_SendEvent::RESULT_FAILED:
        $failedRecipients = $evt->getFailedRecipients();
        $entry = sprintf('Sending failed. Failed recipients: "%s"', join(', ', $failedRecipients));
      break;

      case Swift_Events_SendEvent::RESULT_PENDING:
        $entry = 'Sending has yet to occur';
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
  
}
