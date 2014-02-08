<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelMailer adds a panel to the web debug toolbar with sent emails.
 *
 * @package    Sift
 * @subpackage debug
 */
class sfWebDebugPanelMailer extends sfWebDebugPanel
{
  protected $mailer = null;

  /**
   * Constructor.
   *
   * @param sfWebDebug $webDebug The web debug toolbar instance
   */
  public function __construct(sfWebDebug $webDebug, $options = array())
  {
    parent::__construct($webDebug, $options);
    $this->webDebug->getEventDispatcher()->connect('mailer.configure', array(
      $this, 'listenMailConfigureEvent'
    ));
  }

  public function listenMailConfigureEvent(sfEvent $event)
  {
    $this->mailer = $event['mailer'];
  }

  public function getTitle()
  {
    if ($this->mailer && ($logger = $this->mailer->getLogger()) && ($count = $logger->countMessages())) {
      return sprintf('%s %s', $count, $count > 1 ? 'emails' : 'email');
    }

    return '';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getIcon()
  {
    return sfWebDebugIcon::get('email');
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
    return 'Emails';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelContent()
  {
    if (!$this->mailer) {
      return false;
    }

    $logger = $this->mailer->getLogger();

    if (!$logger || !$logger->countMessages()) {
      return false;
    }

    // detect spool
    try {
      $spool = get_class($this->mailer->getSpool());
    } catch (LogicException $e) {
      $spool = false;
    }

    // prepare messages
    $messages = array();
    foreach ($logger->getMessages() as $message) {
      $content = $message->toString();
      $subject = $message->getSubject();
      // convert charset if not the same as message
      if (strtolower($message->getCharset()) !== strtolower(sfConfig::get('sf_charset'))) {
        $content = iconv($message->getCharset(), sfConfig::get('sf_charset'), $content);
        $subject = iconv($message->getCharset(), sfConfig::get('sf_charset'), $subject);
      }

      $messages[] = array(
        'to' => null === $message->getTo() ? '' : implode(', ', array_keys($message->getTo())),
        'bcc' => null === $message->getBcc() ? '' : implode(', ', array_keys($message->getBcc())),
        'subject' => $subject,
        'content' => $content,
        'charset' => $message->getCharset()
      );
    }

    return $this->webDebug->render($this->getOption('template_dir').'/panel/mailer.php', array(
      'realtime_transport' => get_class($this->mailer->getRealtimeTransport()),
      'spool' => $spool,
      'deliver' => $this->mailer->getOption('deliver'),
      'messages' => $messages
    ));
  }

  protected function renderMessageInformation(Swift_Message $message)
  {
    static $i = 0;

    $i++;

    $to = null === $message->getTo() ? '' : implode(', ', array_keys($message->getTo()));

    $html = array();
    $html[] = sprintf('<h3>%s (to: %s) %s</h3>', $message->getSubject(), $to, $this->getToggler('sf-web-debug-mail-'.$i));
    $html[] = '<div id="sf-web-debug-mail-'.$i.'" style="display:'.(1 == $i ? 'block' : 'none').'">';
    $html[] = '<pre>'.htmlentities($message->toString(), ENT_QUOTES, $message->getCharset()).'</pre>';
    $html[] = '</div>';

    return implode("\n", $html);
  }

}
