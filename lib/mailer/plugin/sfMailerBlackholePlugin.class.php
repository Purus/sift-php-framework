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
class sfMailerBlackholePlugin extends sfMailerPlugin implements Swift_Events_TransportChangeListener {

  /**
   * Invoked just before a transport is started.
   *
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfMailer} Delivery canceled. The email has been put to the blackhole.');
    }
    $evt->cancelBubble();
  }

  /**
   * Invoked just before a transport is stopped.
   *
   * @param Swift_Events_TransportChangeEvent $evt
   */
  public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt)
  {
    // does this even happen if we always cancel the starting of the transport?
    $evt->cancelBubble();
  }

  /**
   * Invoked immediately before the message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $evt->cancelBubble();
  }

  // The three events below are there to fulfill the interface contracts
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    throw new RuntimeException('Sending should never have been performed');
  }

  public function transportStarted(Swift_Events_TransportChangeEvent $evt)
  {
    throw new RuntimeException('Transport should never have started');
  }

  public function transportStopped(Swift_Events_TransportChangeEvent $evt)
  {
    throw new RuntimeException('Transport should never have stopped');
  }
}
