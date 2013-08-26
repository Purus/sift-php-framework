<?php

class mailerActions extends myActions
{
  /**
   * Congratulations page for creating an application
   *
   */
  public function executeIndex()
  {
    $mailer = $this->getMailer();

    $message = $mailer->getNewMessage('my test subject', 'This is a test email');

    $message->setTo('foo@localhost')
            ->setFrom('website@localhost')
            ->addPart('<strong>This is an html version</strong>', 'text/html')
            ->addPart('<strong>This is another html version</strong>', 'text/html');

    $mailer->send($message);

    return $this->renderText('The email has been sent');
  }
}
