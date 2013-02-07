<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new application.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliGenerateAppTask extends sfCliGeneratorBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('app', sfCliCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('escaping-strategy', null, sfCliCommandOption::PARAMETER_REQUIRED, 'Output escaping strategy', false),
      new sfCliCommandOption('csrf-secret', null, sfCliCommandOption::PARAMETER_REQUIRED, 'Secret to use for CSRF protection', true),
      new sfCliCommandOption('secured', null, sfCliCommandOption::PARAMETER_NONE, 'Is the app secured?', null),
    ));

    $this->namespace = 'generate';
    $this->name = 'app';

    $this->briefDescription = 'Generates a new application';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [generate:app|INFO] task creates the basic directory structure
for a new application in the current project:

  [{$scriptName} generate:app frontend|INFO]

This task also creates two front controller scripts in the
[web/|COMMENT] directory:

  [web/%application%.php|INFO]     for the production environment
  [web/%application%_dev.php|INFO] for the development environment

For the first application, the production environment script is named
[index.php|COMMENT].

If an application with the same name already exists,
it throws a [sfCliCommandException|COMMENT].

By default, the output escaping is enabled (to prevent XSS), and a random
secret is also generated to prevent CSRF.

You can disable output escaping by using the [escaping-strategy|COMMENT]
option:

  [{$scriptName} generate:app front --escaping-strategy=false|INFO]

You can enable session token in forms (to prevent CSRF) by defining
a secret with the [csrf-secret|COMMENT] option:

  [{$scriptName} generate:app front --csrf-secret=UniqueSecret|INFO]

You can customize the default skeleton used by the task by creating a
[%sf_data_dir%/skeleton/app|COMMENT] directory.
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['app'];

    // Validate the application name
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $app))
    {
      throw new sfCliCommandException(sprintf('The application name "%s" is invalid.', $app));
    }

    if($this->checkAppExists($app, false))
    {
      throw new sfCliCommandException(sprintf('The application "%s" already exists.', $app));
    }

    // project skeleton
    if(is_readable($this->environment->get('sf_data_dir').'/data/app'))
    {
      $skeletonDir = $this->environment->get('sf_data_dir').'/data/app';
    }
    else
    {
      $skeletonDir = $this->environment->get('sf_sift_data_dir').'/skeleton/app';
    }

    $this->logSection($this->getFullName(), 'Generating app...');
    
    $appDir = $this->environment->get('sf_apps_dir') . '/' . $app;

    // Create basic application structure
    // discard dot files
    $finder = sfFinder::type('any')->discard('.*');
    $this->getFilesystem()->mirror($skeletonDir.'/app', $appDir, $finder);

    $this->replaceTokens(array(
        $appDir        
    ), array('APP_NAME' => $app));
    
    // Create $app.php or index.php if it is our first app
    $indexName = 'index';
    $firstApp = !file_exists($this->environment->get('sf_web_dir').'/index.php');
    if (!$firstApp)
    {
      $indexName = $app;
    }

    if(true === $options['csrf-secret'])
    {
      $options['csrf-secret'] = $this->generateCsrfSecret();
    }

    // Set no_script_name value in settings.yml for production environment
    $finder = sfFinder::type('file')->name('settings.yml');
    
    $this->getFilesystem()->replaceTokens($finder->in($appDir.'/config'), '##', '##', array(
      'NO_SCRIPT_NAME'    => $firstApp ? 'true' : 'false',
      'CSRF_SECRET'       => $options['csrf-secret'],
      'ESCAPING_STRATEGY' => sfYamlInline::dump((boolean) sfYamlInline::parseScalar($options['escaping-strategy'])),
      // FIXME: make this configurable?
      'USE_DATABASE'      => 'true',
    ));

    $this->getFilesystem()->copy($skeletonDir.'/web/index.php', $this->environment->get('sf_web_dir').'/'.$indexName.'.php');
    $this->getFilesystem()->copy($skeletonDir.'/web/index.php', $this->environment->get('sf_web_dir').'/'.$app.'_dev.php');

    $this->getFilesystem()->replaceTokens($this->environment->get('sf_web_dir').'/'.$indexName.'.php', '##', '##', array(
      'APP_NAME'    => $app,
      'ENVIRONMENT' => 'prod',
      'IS_DEBUG'    => 'false',
      'IP_CHECK'    => '',
    ));

    $this->getFilesystem()->replaceTokens($this->environment->get('sf_web_dir').'/'.$app.'_dev.php', '##', '##', array(
      'APP_NAME'    => $app,
      'ENVIRONMENT' => 'dev',
      'IS_DEBUG'    => 'true',
      'IP_CHECK'    => '// this check prevents access to debug front controllers that are deployed by accident to production servers.'.PHP_EOL.
                       '// feel free to remove this, extend it or make something more sophisticated.'.PHP_EOL.
                       'if (!in_array(@$_SERVER[\'REMOTE_ADDR\'], array(\'127.0.0.1\', \'::1\')))'.PHP_EOL.
                       '{'.PHP_EOL.
                       '  //die(\'You are not allowed to access this file. Check \'.basename(__FILE__).\' for more information.\');'.PHP_EOL.
                       '}'.PHP_EOL,
    ));

    $className = sprintf('my%sApplication', sfInflector::camelize($app));
    
    $this->getFilesystem()->rename($appDir.'/lib/application.class.php', $appDir.'/lib/'.$className.'.class.php');
    $this->getFilesystem()->replaceTokens($appDir.'/lib/'.$className.'.class.php', '##', '##', 
            array('CLASS_NAME' => $className, 'PROJECT_NAME' => $this->getProjectProperty('name')));

    // security
    $finder = sfFinder::type('file')->name('security.yml');    
    $this->getFilesystem()->replaceTokens($finder->in($appDir.'/config'), '##', '##', array(
      'IS_SECURE' => $options['secured'] ? 'true' : 'false',
    ));
    
    // Create test dir
    $this->getFilesystem()->mkdirs($this->environment->get('sf_test_dir').'/functional/'.$app);
    
    $this->logSection($this->getFullName(), 'Done.');    

    // fix permissions
    $fixPerms = new sfCliProjectPermissionsTask($this->environment, $this->dispatcher, $this->formatter, $this->logger);
    $fixPerms->setCommandApplication($this->commandApplication);
    $fixPerms->run();
  }
  
  /**
   * Generates CSRF protection string
   * 
   * @return string
   */
  protected function generateCsrfSecret()
  {
    $words = explode(' ', str_replace(array(',', '.', ':', ';'), '', 
      'And the Lord said, Simon, Simon, behold, Satan hath desired to have you, that he may sift you as wheat: '.
      'But I have prayed for thee, that thy faith fail not: and when thou art converted, strengthen thy brethren. '.
      'His head and his hairs were white like wool, as white as snow; and his eyes were as a flame of fire '.
      'And his feet like unto fine brass, as if they burned in a furnace; and his voice as the sound of many waters. '.
      'And they said, Believe on the Lord Jesus Christ, and thou shalt be saved, and thy house.'
    ));
    
    shuffle($words);
    
    $word = substr(join('', $words), 0, 32);    
    $output = '';    
    for($l = 0, $len = strlen($word); $l < $len; $l++)
    {
      if(mt_rand(0, 1) == 1) 
      {
        $output .= strtoupper($word[$l]);
      }
      else
      {
        $output .= strtolower($word[$l]);
      }      
    }    
    return $output;
  }


}
