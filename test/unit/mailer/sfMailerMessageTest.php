<?php

require_once dirname(__FILE__) . '/../../bootstrap/unit.php';

// we need for dependency map settings
require_once $sf_sift_lib_dir . '/mailer/sfMailer.class.php';

$t = new lime_test(12);

$dir = dirname(__FILE__).'/fixtures/files';
$dispatcher = new sfEventDispatcher();

$message = new sfMailerMessage($dispatcher, 'Subject', 'Body');

$t->is($message->getSubject(), 'Subject', '__construct() takes the subject as second argument');
$t->is($message->getBody(), 'Body', '__construct() takes the body as third argument');
$t->is($message->getEncoding(), '8bit', '__construct() sets default encoding');

$t->diag('->getPlainTextBody() ->hasPlainTextBody() ->getHtmlBody() ->hasHtmlBody()');

$t->is_deeply($message->hasPlaintextBody(), true, 'hasPlainTextBody() returns true is there is a plain text body');
$t->is_deeply($message->getPlaintextBody(), 'Body', 'getPlainTextBody() returns the plain text body');

$message = new sfMailerMessage($dispatcher);

$t->is_deeply($message->hasPlaintextBody(), false, 'hasPlainTextBody() returns false is there is no plain text body');
$t->is_deeply($message->getPlaintextBody(), null, 'getPlainTextBody() returns null if there is no plain text body');

$message->setPlaintextBody('This is my message to you');
$t->is_deeply($message->hasPlaintextBody(), true, 'hasPlainTextBody() returns true is there is a plain text body');
$t->is_deeply($message->getPlaintextBody(), 'This is my message to you', 'getPlainTextBody() returns the body');

$message->setHtmlBody('<p>This is <strong>my message</strong> to you</p>');

$t->is_deeply($message->hasPlaintextBody(), true, 'hasPlainTextBody() returns true is there is a plain text body');
$t->is_deeply($message->getPlaintextBody(), 'This is my message to you', 'getPlainTextBody() returns the body');
$t->is_deeply($message->getHtmlBody(), '<p>This is <strong>my message</strong> to you</p>', 'getHtmlBody() returns the HTML body');

// file_put_contents($dir . '/message.eml', $message->toString());

$message = new sfMailerMessage($dispatcher);

$t->diag('->attachFromPath()');


$message->attachFromPath($dir . '/bible.jpg');

$message->attachData('this is an attachment', 'foo.txt', 'text/plain', sfMailerMessage::DISPOSITION_INLINE, 'Toto je popis');

function createAttachment()
{
  return 'Dynamically created';
}

function createAttachment2()
{
  return array('Dynamically created with content type', 'text/plain');
}

$message->attachData(new sfCallable('createAttachment'), 'foo1.txt');
$message->attachData(new sfCallable('createAttachment2'), 'foo2.txt');

// file_put_contents($dir . '/message2.eml', $message->toString());
