<?php

require_once dirname(__FILE__) . '/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/fixtures/TestMailerTransport.class.php';
require_once dirname(__FILE__).'/fixtures/TestSpool.class.php';
require_once dirname(__FILE__).'/fixtures/TestMailMessage.class.php';

$t = new lime_test(27);

class myMailer extends sfMailer {

  protected function loadOptions()
  {
    return array();
  }
}

$dispatcher = new sfEventDispatcher();


// __construct()
$t->diag('__construct()');

// main transport
$mailer = new myMailer($dispatcher, array(
  'transports' => array(
      'default' => array(
        'class' => 'TestMailerTransport', 'param' => array('foo' => 'bar', 'bar' => 'foo')
  )),
));

$transports = $mailer->getTransport()->getTransports();

$t->is($transports['default']->getFoo(), 'bar', '__construct() passes the parameters to the main transport');

// spool
$mailer = new myMailer($dispatcher, array(
  'spool'  => array(
    'enabled' => true,
    'class' => 'TestSpool',
    'arguments' => array('TestMailMessage')
  ),
  'transport_type' => 'default',
  'transports' => array(
    'default' => array('class' => 'Swift_SmtpTransport', 'param' => array('username' => 'foo'))
  ),
));

$t->is($mailer->getRealTimeTransport()->getUsername(), 'foo', '__construct() passes the parameters to the main transport');

try
{
  $mailer = new myMailer($dispatcher, array('spool' => array('enabled' => true)));
  $t->fail('__construct() throws an InvalidArgumentException exception if the spool class option is not set when spool is enabled');
}
catch (sfConfigurationException $e)
{
  $t->pass('__construct() throws an InvalidArgumentException exception if the spool class option is not set when spool is enabled');
}

$mailer = new myMailer($dispatcher, array('spool' => array('enabled' => true, 'class' => 'TestSpool')));

$t->is(get_class($mailer->getTransport()), 'Swift_SpoolTransport', '__construct() recognizes the spool delivery strategy');
$t->is(get_class($mailer->getTransport()->getSpool()), 'TestSpool', '__construct() recognizes the spool delivery strategy');

// logging
$mailer = new myMailer($dispatcher, array('log' => array('enabled' => false)));
$t->is($mailer->getLogger(), null, '__construct() disables logging if the logging option is set to false');
$mailer = new myMailer($dispatcher, array('log' => array('enabled' => true)));

$t->isa_ok($mailer->getLogger(), 'sfMailerLoggerPlugin', '__construct() enables logging if the logging option is set to true');

// ->getNewMessage()
$t->diag('->getNewMessage()');
$mailer = new myMailer($dispatcher, array('deliver' => false));

$t->isa_ok($mailer->getNewMessage(), 'sfMailerMessage', '->getNewMessage() returns a sfMailerMessage instance');

$message = $mailer->getNewMessage('Subject', 'Body');
$t->is($message->getSubject(), 'Subject', '->getNewMessage() takes the subject as its first argument');
$t->is($message->getBody(), 'Body', '->getNewMessage() takes the body as its second argument');

// ->flushQueue()
$t->diag('->flushQueue()');
$mailer = new myMailer($dispatcher, array('deliver' => false));
$message = $mailer->getNewMessage('Subject', 'Body');
$message->setFrom('me@localhost')
        ->setTo('you@localhost');
$mailer->send($message);
try
{
  $mailer->flushQueue();
  $t->fail('->flushQueue() throws a LogicException exception if the spool is not enabled');
}
catch(LogicException $e)
{
  $t->pass('->flushQueue() throws a LogicException exception if the spool is not enabled');
}

$mailer = new myMailer($dispatcher, array(
  'spool' => array(
    'enabled' => true,
    'class' => 'TestSpool',
    'arguments'   => array('TestMailMessage')
  ),
  'transport_type' => 'default',
  'transports' => array(
    'default' => array(
      'class' => 'TestMailerTransport'
    )
  )
));

$transport = $mailer->getRealtimeTransport();
$spool = $mailer->getSpool();

$t->isa_ok($spool, 'TestSpool', 'Spool instance is ok');
$t->isa_ok($transport, 'TestMailerTransport', 'Transport instance is ok');

$message = $mailer->getNewMessage('Subject', 'Body');
$message->setFrom('me@localhost')
        ->setTo('you@localhost');
$mailer->send($message);

$t->is($spool->getQueuedCount(), 1, '->flushQueue() sends messages in the spool');
$t->is($transport->getSentCount(), 0, '->flushQueue() sends messages in the spool');

$mailer->flushQueue();

$t->is($spool->getQueuedCount(), 0, '->flushQueue() sends messages in the spool');
$t->is($transport->getSentCount(), 1, '->flushQueue() sends messages in the spool');

// ->sendNextImmediately()
$t->diag('->sendNextImmediately()');

$mailer = new myMailer($dispatcher, array(
  'spool' => array(
    'enabled' => true,
    'class' => 'TestSpool',
    'arguments' => array('TestMailMessage')
  ),
  'transport_type' => 'default',
  'transports' => array(
    'default' => array('class' => 'TestMailerTransport')
  )
));

$transport = $mailer->getRealtimeTransport();
$spool = $mailer->getSpool();

$t->is($mailer->sendNextImmediately(), $mailer, '->sendNextImmediately() implements a fluid interface');

$message = $mailer->getNewMessage('Subject', 'Body');
$message->setFrom('me@localhost')
        ->setTo('you@localhost');
$mailer->send($message);

$t->is($spool->getQueuedCount(), 0, '->sendNextImmediately() bypasses the spool');
$t->is($transport->getSentCount(), 1, '->sendNextImmediately() bypasses the spool');
$transport->reset();
$spool->reset();

$message = $mailer->getNewMessage('Subject', 'Body');
$message->setFrom('me@localhost')
        ->setTo('you@localhost');
$mailer->send($message);

$t->is($spool->getQueuedCount(), 1, '->sendNextImmediately() bypasses the spool but only for the very next message');
$t->is($transport->getSentCount(), 0, '->sendNextImmediately() bypasses the spool but only for the very next message');

class myMailerLoggerPlugin extends sfMailerLoggerPlugin {


}

// ->getLogger() ->setLogger()
$t->diag('->getLogger() ->setLogger()');
$mailer = new myMailer($dispatcher);

$mailer->setLogger($logger = new myMailerLoggerPlugin($dispatcher));
$t->ok($mailer->getLogger() === $logger, '->setLogger() sets the mailer logger');

// ->getRealtimeTransport() ->setRealtimeTransport()
$t->diag('->getRealtimeTransport() ->setRealtimeTransport()');
$mailer = new myMailer($dispatcher, array('delivery_strategy' => 'none'));
$mailer->setRealtimeTransport($transport = new TestMailerTransport());
$t->ok($mailer->getRealtimeTransport() === $transport, '->setRealtimeTransport() sets the mailer transport');

$t->diag('plugins');

$mailer = new sfMailer($dispatcher,
  array(
    'transport_type' => 'default',
    'transports' => array(
      'default' => array('class' => 'Swift_NullTransport', 'param' => array('username' => 'foo'))
    )
));

$message = $mailer->getNewMessage();
$message->setHtmlBody('<p>This is html body</p>', 'text/html');
$mailer->send($message);

$t->is($message->getPlaintextBody(), 'This is html body', 'The Html2text plugin set the plain text body');


$message = $mailer->getNewMessage();
$message->setPlainTextBody('This is plain text body')
        ->setHtmlBody('<strong>this is html body</strong>');

$mailer->send($message);

$t->is($message->getPlaintextBody(), 'This is plain text body', 'The Html2text plugin did not touched the plain text body');
$t->is($message->getHtmlBody(), '<strong>this is html body</strong>', 'The Html2text plugin did not trouched the plain text body');
