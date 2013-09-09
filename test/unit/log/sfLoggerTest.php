<?php


require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(136, new lime_output_color());

class myLogger extends sfLoggerBase
{
  public $log = '';

  public function log($message, $level = sfILogger::INFO, array $context = array())
  {
    $this->log .= $this->formatMessage($message, $context);
  }

  public function shutdown()
  {
  }

}

$myLogger = new myLogger();

// ->getInstance()
$t->diag('->getInstance()');
$t->isa_ok(sfLogger::getInstance(), 'sfLogger', '::getInstance() returns a sfLogger instance');
$t->is(sfLogger::getInstance(), sfLogger::getInstance(), '::getInstance() is a singleton');

$logger = new sfLogger();

// ->getLoggers()
$t->diag('->getLoggers()');
$t->is($logger->getLoggers(), array(), '->getLoggers() returns an array of registered loggers');

// ->registerLogger()
$t->diag('->registerLogger()');
$logger->registerLogger($myLogger);
$t->is($logger->getLoggers(), array($myLogger), '->registerLogger() registers a new logger instance');

// ->setLogLevel() ->getLogLevel()
$t->diag('->setLogLevel() ->getLogLevel()');
$t->is($logger->getLogLevel(), sfLogger::EMERG, '->getLogLevel() gets the current log level');
$logger->setLogLevel(sfLogger::WARNING);
$t->is($logger->getLogLevel(), sfLogger::WARNING, '->setLogLevel() sets the log level');

// ->log()
$logger = new sfLogger();
$t->diag('->log()');
$logger->setLogLevel(sfLogger::DEBUG);
$logger->registerLogger($myLogger);
$logger->registerLogger($myLogger);
$logger->log('message');
$t->is($myLogger->log, 'messagemessage', '->log() calls all registered loggers');

// log level
$t->diag('log levels');
foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level)
{
  $levelConstant = 'sfILogger::'.strtoupper($level);

  foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel)
  {
    $logLevelConstant = 'sfILogger::'.strtoupper($logLevel);
    $logger->setLogLevel(constant($logLevelConstant));

    $myLogger->log = '';
    $logger->log('foo', constant($levelConstant));

    $t->is($myLogger->log, constant($logLevelConstant) >= constant($levelConstant), sprintf('->log() only logs if the level is >= to the defined log level (%s >= %s)', $logLevelConstant, $levelConstant));
  }
}

// shortcuts
$t->diag('log shortcuts');
foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level)
{
  $levelConstant = 'sfLogger::'.strtoupper($level);

  foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel)
  {
    $logger->setLogLevel(constant('sfLogger::'.strtoupper($logLevel)));

    $myLogger->log = '';
    $logger->log('foo', constant($levelConstant));
    $log1 = $myLogger->log;

    $myLogger->log = '';
    $logger->$level('foo');
    $log2 = $myLogger->log;

    $t->is($log1, $log2, sprintf('->%s($msg) is a shortcut for ->log($msg, %s)', $level, $levelConstant));
  }
}

$t->diag('context placeholders');

$myLogger->log = '';
$myLogger->emerg('Error {foo}', array('foo' => 'bar'));
$t->is($myLogger->log, 'Error bar', 'message is formatted');
