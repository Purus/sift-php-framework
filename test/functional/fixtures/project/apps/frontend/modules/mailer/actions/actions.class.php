<?php

class mailerActions extends myActions
{
  public function executeIndex()
  {
    $mailer = $this->getMailer();

    $message = $mailer->getNewMessage('my test subject', 'This is a test email');

    $message->setTo('foo@localhost')
            ->setFrom('website@localhost')
            ->setHtmlBody('<strong>This is an html version</strong>');

    $message->attachFromPath(sfConfig::get('sf_data_dir') . '/email/files/foo.pdf', 'foo.pdf');

    $mailer->send($message);

    return $this->renderText('The email has been sent');
  }

  public function executeConvert()
  {
    $mailer = $this->getMailer();

    $message = $mailer->getNewMessage('my test subject');

    // plain text should be created from the html part
    $message->setTo('foo@localhost')
            ->setFrom('website@localhost')
            ->setHtmlBody('<strong>This is an html version</strong>');

    $mailer->send($message);

    return $this->renderText('The email has been sent');
  }

  public function executePartial()
  {
    $mailer = $this->getMailer();
    $message = $mailer->getNewMessage('my test subject');
    $message->setBodyFromPartial('mailer/plain', array(
      'name' => 'Foobar'
    ));
    $message->setBodyFromPartial('mailer/html', array(
      'name' => 'Foobar'
    ), 'html');

    $mailer->send($message);

    return $this->renderText('The email has been sent');
  }

}
