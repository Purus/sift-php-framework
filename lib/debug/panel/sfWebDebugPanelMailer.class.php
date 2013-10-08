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
  public function __construct(sfWebDebug $webDebug)
  {
    parent::__construct($webDebug);

    $webDebug->getEventDispatcher()->connect('mailer.configure', array(
      $this, 'listenMailConfigureEvent'
    ));
  }

  public function listenMailConfigureEvent(sfEvent $event)
  {
    $this->mailer = $event['mailer'];
  }

  public function getTitle()
  {
    if($this->mailer && ($logger = $this->mailer->getLogger()) && ($count = $logger->countMessages()))
    {
      return sprintf('%s %s', $count, $count > 1 ? 'emails' : 'email');
    }
    return '';
  }

  public function getIcon()
  {
  }

  public function getPanelTitle()
  {
    return 'Emails';
  }

  public function getPanelContent()
  {
    if(!$this->mailer)
    {
      return false;
    }

    $logger = $this->mailer->getLogger();

    if(!$logger || !$messages = $logger->getMessages())
    {
      return false;
    }

    $html = array();

    $html[] = '<h2>Configuration</h2>';
    $html[] = '<ul>';
    $html[] = '<li>';
    $html[] = sprintf('Realtime transport: %s', get_class($this->mailer->getRealtimeTransport()));
    $html[] = '</li>';

    $html[] = '<li>';
    // are we using spool?
    try
    {
      $spool  = $this->mailer->getSpool();
      $html[] = sprintf('Spool: %s', get_class($spool));
    }
    catch(LogicException $e)
    {
      $html[] ='Spool: no';
    }

    $html[] = '</li>';

    $html[] = '<li>';
    $html[] = sprintf('Deliver: %s', $this->mailer->getOption('deliver') ? 'yes' : 'no');

    $html[] = '</li>';
    $html[] = '</ul>';

    // email sent
    $html[] = '<h2>Email sent</h2>';
    foreach ($messages as $message)
    {
      $html[] = $this->renderMessageInformation($message);
    }

    return implode("\n", $html);
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
