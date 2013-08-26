<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ignores messages. Simply sends them to blackhole.
 *
 * @package Sift
 * @subpackage mailer
 */
class sfMailerBlackholePlugin implements Swift_Events_SendListener {

  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $evt->cancelBubble();

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfMailer} Delivery canceled. The email has been put to the blackhole.');
    }
  }

  /**
   * Invoked immediately after the Message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
  }

}
