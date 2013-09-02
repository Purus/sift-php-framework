<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Notifies the application via events about a message which is being send and sent message.
 * Dispatches events:
 *
 * mailer.message.before_send
 * mailer.message.after_send
 *
 * Listenes can modify the message on the fly. Sending can be cancelled:
 *
 * $event['event']->cancelBubble()
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailerNotificationPlugin extends sfMailerPlugin {

  /**
   * Invoked before the message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $this->dispatcher->notify(new sfEvent('mailer.message.before_send', array(
        'event' => $evt,
        'message' => $evt->getMessage()
    )));
  }

  /**
   * Invoked immediately after the message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $this->dispatcher->notify(new sfEvent('mailer.message.after_send', array(
      'event' => $evt,
      'message' => $evt->getMessage()
    )));
  }

}
