<?php

/**
 * ##TASK_NAME##
 * 
 */
class ##TASK_CLASS_NAME## extends sfCliBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCliCommandArgument('my_arg', sfCliCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCliCommandOption('application', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The application name', true),
      new sfCliCommandOption('env', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCliCommandOption('connection', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The connection name', '##DEFAULT_CONNECTION##'),
      // add your own options here
    ));

    $this->namespace        = '##NAMESPACE##';
    $this->name             = '##NAME##';
    $this->briefDescription = '##BRIEF_DESCRIPTION##';
    
    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [##TASK_NAME##|INFO] task does things with database.
Call it with:

  [{$scriptName} ##TASK_NAME##|INFO] 
EOF;
  
  }
  
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection($this->getFullName(), 'Executing task...');
    
    // access the database
    $database = $this->getDatabase($options['connection']);
    $database->connect();
    
    $this->logSection($this->getFullName(), 'Connected to database');

    // shutdown
    $database->shutdown();
    
    $this->logSection($this->getFullName(), 'Done.');    
  }
  
}
