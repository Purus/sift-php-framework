<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new project.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliGenerateProjectTask extends sfCliGeneratorBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function doRun(sfCliCommandManager $commandManager, $options)
  {
    $this->process($commandManager, $options);

    return $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());
  }

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('name', sfCliCommandArgument::REQUIRED, 'The project name'),
      new sfCliCommandArgument('author', sfCliCommandArgument::OPTIONAL, 'The project author', 'Your name here'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('no-git-stuff', null, sfCliCommandOption::PARAMETER_NONE, 'Do not add git specific files to the project directory?', null)
    ));
    
    $this->namespace = 'generate';
    $this->name = 'project';

    $this->briefDescription = 'Generates a new project';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [generate:project|INFO] task creates the basic directory structure
for a new project in the current directory:

  [{$scriptName} generate:project blog|INFO]

If the current directory already contains a Sift project,
it throws a [sfCliCommandException|COMMENT].

You can optionally include a second [author|COMMENT] argument to specify what name to
use as author when framework generates new modules and other generated stuff:

  [{$scriptName} generate:project blog "Jack Doe"|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    try 
    {      
      $this->checkProjectExists();
      throw new sfCliCommandArgumentsException('Sift project already exits in current directory');
    }
    catch(sfCliCommandException $e)
    {
      throw $e;
    }
    catch(sfException $e)
    {
    }
    
    $this->arguments = $arguments;
    $this->options = $options;
    
    $this->logSection($this->getFullName(), 'Creating project...');
    
    // create basic project structure
    $this->installDir($this->environment->get('sf_sift_data_dir').'/skeleton/project');

    if(isset($options['nogitstuff']))
    {
      $files = sfFinder::type('file')->name('.gitignore')->level(0)->in($this->environment->get('sf_root_dir'));
      $this->getFilesystem()->remove($files);   
    }  
    
    $this->tokens = array(
      'SF_SIFT_LIB_DIR'   => $this->environment->get('sf_sift_lib_dir'),  
      'SF_SIFT_DATA_DIR'  => $this->environment->get('sf_sift_data_dir'),  
      'PROJECT_NAME'      => $this->arguments['name'],
      'AUTHOR_NAME'       => $this->arguments['author'],
      'PROJECT_DIR'       => $this->environment->get('sf_root_dir'),
      'IS_SECURE'         => isset($options['secured']) ? 'true' : 'false'
    );

    $this->replaceTokens();

    // project is generated, we have to bind to project
    $this->commandApplication->bindToProject();
    
    try 
    {
      // generate new crypt key for the project
      $generateCryptKey = new sfCliGenerateCryptKeyTask($this->environment, 
                                                        $this->dispatcher, 
                                                        $this->formatter, 
                                                        $this->logger);
      $generateCryptKey->setCommandApplication($this->commandApplication);
      $generateCryptKey->run();
    }
    catch(RuntimeException $e) // openssl not installed
    {
      $this->logSection($this->getFullName(), 'Generating of crypt file failed. Please install openssl!');
    }
    catch(sfException $e) // error generating crypt key
    {
      $this->logSection($this->getFullName(), 'Generating of crypt file failed. Regenerate it again.');
    }
    
    // fix permission for common directories
    $fixPerms = new sfCliProjectPermissionsTask($this->environment, 
                                                $this->dispatcher, 
                                                $this->formatter, 
                                                $this->logger);
    $fixPerms->setCommandApplication($this->commandApplication);
    $fixPerms->run();

    // replace tokens
    // $this->replaceTokens();
    
    $this->logSection($this->getFullName(), 'Done.');    
  }

}
