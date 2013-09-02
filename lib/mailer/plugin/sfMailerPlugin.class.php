<?php
/*
 * This file is part of the Sift PHP framework.
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
abstract class sfMailerPlugin extends sfConfigurable implements Swift_Events_SendListener {

  /**
   * Event dispatcher
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * Constructs the plugin
   *
   * @param sfEventDispatcher $dispatcher
   * @param array $options
   * @inject dispatcher
   */
  public function __construct(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->dispatcher = $dispatcher;
    parent::__construct($options);
  }

}
