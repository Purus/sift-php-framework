<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class is a plugin for Swift Mailer which converts a html body to a plaintext body
 *
 * sfMailerHtml2TextPlugin class sets plain text body if plain text part of
 * the email is missing. It uses sfHtml2Text class to convert HTML part to plain
 * text
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailerHtml2TextPlugin extends sfMailerPlugin
{
  /**
   * Invoked immediately before the message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $mail = $evt->getMessage();
    if($mail instanceof sfMailerMessage && !$mail->getPlainTextBody())
    {
      $body = $mail->getHtmlBody();
      if(!$body)
      {
        return;
      }
      try
      {
        $converted = sfHtml2Text::convert($body);
        $mail->setPlaintextBody($converted);
      }
      catch(Exception $e)
      {
      }
    }
  }

  /**
   * Invoked immediately after the message is sent.
   *
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
  }

}
