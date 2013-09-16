<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new module.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliGenerateControllerTask extends sfCliGeneratorBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application name'),
      new sfCliCommandArgument('env', sfCliCommandArgument::REQUIRED, 'The environment name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('debug', null, sfCliCommandOption::PARAMETER_NONE, 'Enable debug?'),
      new sfCliCommandOption('force', null, sfCliCommandOption::PARAMETER_NONE, 'Force the generation? Ovewrites existing controller.')
    ));

    $this->namespace = 'generate';
    $this->name = 'controller';

    $this->briefDescription = 'Generates a new controller';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [generate:controller|INFO] task creates new controller in /web directory
for an existing application in using given environment:

  [{$scriptName} generate:controller front staging|INFO]

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];
    $env = $arguments['env'];

    $debug = $options['debug'];

    $this->checkAppExists($app);

    $this->logSection($this->getFullName(), sprintf('Creating controller for "%s".', $app));

    $controller = $app.'_'.$env;

    $constants = array(
      'APP_NAME'        => $app,
      'CONTROLLER_NAME' => $controller,
      'ENVIRONMENT'     => $env,
      'IS_DEBUG'        => $debug ? 'true' : 'false',
      'IS_DEBUG_HUMAN'  => $debug ? 'yes' : 'no',
      'IP_CHECK'        => self::getIpCheckCode($debug)
    );

    $controller = $this->environment->get('sf_web_dir') . '/' . $controller . '.php';

    if(is_readable($controller))
    {
      if(!$options['force'])
      {
        throw new sfException(sprintf('Controller "%s" already exists', basename($controller)));
      }
      else
      {
        $this->getFilesystem()->remove($controller);
      }
    }

    if (is_readable($this->environment->get('sf_data_dir').'/skeleton/controller/controller.php'))
    {
      $skeleton= $this->environment->get('sf_data_dir').'/skeleton/controller/controller.php';
    }
    else
    {
      $skeleton = $this->environment->get('sf_sift_data_dir').'/skeleton/controller/controller.php';
    }

    $this->getFilesystem()->copy($skeleton, $controller);
    $this->getFilesystem()->replaceTokens($controller, '##', '##', $constants);

    $this->logSection($this->getFullName(), 'Done.');
  }

  /**
   * Returns the IP check code for the environment
   *
   * @param boolean $debug Debug?
   * @return string
   */
  public static function getIpCheckCode($debug)
  {
    $code = '';
    if($debug)
    {
      $code = PHP_EOL .
        '// this check prevents access to debug front controllers that are deployed by accident to production servers.'.PHP_EOL.
        '// feel free to remove this, extend it or make something more sophisticated.'.PHP_EOL.
        'if(!in_array(@$_SERVER[\'REMOTE_ADDR\'], array(\'127.0.0.1\', \'::1\')))'.PHP_EOL.
        '{'.PHP_EOL.
        '  die(\'You are not allowed to access this file. Check \'.basename(__FILE__).\' for more information.\');'.PHP_EOL.
        '}'.PHP_EOL;
    }
    return $code;
  }

}
