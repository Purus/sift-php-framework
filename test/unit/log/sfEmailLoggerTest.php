<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3);

class myEmailLogger extends sfEmailLogger {

  public $testEmail,
         $testSubject,
         $testBody,
         $testHeaders;

  protected function sendEmail($email, $subject, $body, $headers)
  {
    $this->testEmail = $email;
    $this->testSubject = $subject;
    $this->testBody = $body;
    $this->testHeaders = $headers;
  }

  public function getMailBody()
  {
    return 'LOG';
  }

}

try
{
  $logger = new myEmailLogger();
  $t->fail('constructor options must contain required parameters');
}
catch(Exception $e)
{
  $t->pass('constructor options parameters must contain required parameters');
}

// ->log()
$t->diag('->log()');
$logger = new myEmailLogger(array(
                                  'template' => __FILE__,
                                  'recipients' => array('email@domain.com'),
                                  'sender_email' => 'email@domain.com'));

$logger->log('foo', sfILogger::EMERGENCY);

$logger->shutdown();

$t->isa_ok($logger->testSubject, 'string', 'Subject is set');
$t->is($logger->testBody, 'LOG', 'Log is present in the mail body');