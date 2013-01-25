<?php

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(1);

class DummyTestTask extends sfCliTask
{
  public function __construct()
  {
    // lazy constructor
    parent::__construct(new sfCliTaskEnvironment(), new sfEventDispatcher(), new sfCliFormatter(), new sfConsoleLogger());
  }
  
  protected function configure()
  {
    $this->namespace        = 'ull_time';
    $this->name             = 'create-time-periods';
    $this->briefDescription = 'Create time periods';
    $this->detailedDescription = <<<EOF
    The [{$this->name} task|INFO] automatically creates time periods for the ullTime module
    foreach month for the given startdate and enddate.

    Call it with:

    [php sift {$this->namespace}:{$this->name}|INFO]
EOF;

    $this->addArgument('application', sfCliCommandArgument::OPTIONAL,
      'The application name', 'front');
    $this->addArgument('env', sfCliCommandArgument::OPTIONAL,
      'The environment', 'cli');
    
    $this->addOption('start-date', null, sfCliCommandOption::PARAMETER_OPTIONAL, 
      'Start month. Default is the current month. Format: YYYY-MM', date('Y-m'));
    $this->addOption('end-date', null, sfCliCommandOption::PARAMETER_REQUIRED, 
      'End date, Format: YYYY-MM');
    $this->addOption('languages', null, sfCliCommandOption::PARAMETER_OPTIONAL, 
      'Comma separated list of languages. Default: "en,de"', 'en,de');
  }

  protected function execute($arguments = array(), $options = array())
  {
  }
  
}

$task = new DummyTestTask();
$xml = $task->asXml();
$t->like($xml, '/<\?xml/', 'Converting to xml works ok');


