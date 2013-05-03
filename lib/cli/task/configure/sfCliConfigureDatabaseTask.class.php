<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configures the database connection.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliConfigureDatabaseTask extends sfCliBaseTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('dsn', sfCliCommandArgument::REQUIRED, 'The database dsn'),
        new sfCliCommandArgument('username', sfCliCommandArgument::OPTIONAL, 'The database username', 'root'),
        new sfCliCommandArgument('password', sfCliCommandArgument::OPTIONAL, 'The database password'),
    ));

    $this->addOptions(array(
        new sfCliCommandOption('env', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The environment', 'all'),
        new sfCliCommandOption('name', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The connection name', 'default'),
        new sfCliCommandOption('class', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The database class name', 'sfDoctrineDatabase'),
        new sfCliCommandOption('app', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
    ));

    $this->namespace = 'configure';
    $this->name = 'database';

    $this->briefDescription = 'Configure database DSN';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [configure:database|INFO] task configures the database DSN
for a project:

  [{$scriptName} configure:database mysql:host=localhost;dbname=example root mYsEcret|INFO]

By default, the task change the configuration for all environment. If you want
to change the dsn for a specific environment, use the [env|COMMENT] option:

  [{$scriptName} configure:database --env=dev mysql:host=localhost;dbname=example_dev root mYsEcret|INFO]

To change the configuration for a specific application, use the [app|COMMENT] option:

  [{$scriptName} configure:database --app=frontend mysql:host=localhost;dbname=example root mYsEcret|INFO]

You can also specify the connection name and the database class name:

  [{$scriptName} configure:database --name=main --class=ProjectDatabase mysql:host=localhost;dbname=example root mYsEcret|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // update databases.yml
    if(null !== $options['app'])
    {
      $this->checkAppExists($options['app']);

      $file = $this->environment->get('sf_apps_dir') . '/' . $options['app'] . '/config/databases.yml';
    }
    else
    {
      $file = $this->environment->get('sf_config_dir') . '/databases.yml';
    }

    $this->logSection($this->getFullName(), 'Configuring database...');

    $tpl = '';
    if(!file_exists($file))
    {
      $tpl = file_get_contents($this->environment->get('sf_sift_data_dir').'/skeleton/project/config/databases.yml');
    }

    $config = file_exists($file) ? sfYaml::load($file) : array();

    $config[$options['env']][$options['name']] = array(
        'class' => $options['class'],
        'param' => array_merge(isset(
                        $config[$options['env']][$options['name']]['param']) ?
                        $config[$options['env']][$options['name']]['param'] : array(), array('dsn' => $arguments['dsn'],
            'username' => $arguments['username'],
            'password' => $arguments['password'])),
    );

    $contents = $tpl ? sprintf("%s\n%s", $tpl, sfYaml::dump($config, 4)) : sfYaml::dump($config, 4);

    file_put_contents($file, $contents);

    $this->logSection($this->getFullName(), 'Done.');
  }

}
