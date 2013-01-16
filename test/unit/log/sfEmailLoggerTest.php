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
  
}

// ->initialize()
$t->diag('->initialize()');
try
{
  $logger = new myEmailLogger();
  $t->fail('->initialize() parameters must contain a "recipients" parameter');
}
catch (sfConfigurationException $e)
{
  $t->pass('->initialize() parameters must contain a "recipients" parameter');
}

// ->log()
$t->diag('->log()');
$logger = new myEmailLogger(array('recipients' => array('mishal@mishal.cz'), 
                                  'sender_email' => 'webmaster@mishal.cz'));
$logger->log('foo');

$logger->shutdown();

$t->isa_ok($logger->testSubject, 'string', 'Subject is set');
$t->like($logger->testBody, '/foo/', 'Log is present in the mail body');