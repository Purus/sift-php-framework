<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Fixes directory permissions.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectPermissionsTask extends sfCliBaseTask
{
  protected
    $current = null,
    $failed  = array();

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->namespace = 'project';
    $this->name = 'permissions';
    $this->briefDescription = 'Fixes directory permissions';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [project:permissions|INFO] task fixes directory permissions:

  [{$scriptName} project:permissions|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection($this->getFullName(), 'Fixing permissions...');
    
    $uploadDir = $this->environment->get('sf_upload_dir');
    
    if(is_dir($uploadDir))
    {
      $this->chmod($uploadDir, 0777);
    }

    $persistentUpload = $this->environment->get('sf_data_dir') . '/persistent_upload';
    if(is_dir($persistentUpload))
    {
      $this->chmod($uploadDir, 0777);
    }  
    

    $this->chmod($this->environment->get('sf_log_dir'), 0777);
    $this->chmod($this->environment->get('sf_root_dir').'/sift', 0777);

    $dirs = array(
      $this->environment->get('sf_root_cache_dir'),
      $this->environment->get('sf_log_dir'),
      $this->environment->get('sf_upload_dir'),      
    );
    
    $webCacheDir = $this->environment->get('sf_web_dir').'/cache';
    
    if(is_dir($webCacheDir))
    {
      $this->chmod($webCacheDir, 0777);
      $dirs[] = $webCacheDir;
    }

    $dirFinder = sfFinder::type('dir');
    $fileFinder = sfFinder::type('file');

    foreach($dirs as $dir)
    {
      $this->chmod($dirFinder->in($dir), 0777);
      $this->chmod($fileFinder->in($dir), 0666);
    }

    // note those files that failed
    if (count($this->failed))
    {
      $this->logBlock(array_merge(
        array('Permissions on the following file(s) could not be fixed:', ''),
        array_map(create_function('$f', 'return \' - \'.sfDebug::shortenFilePath($f);'), $this->failed)
      ), 'ERROR_LARGE');
      
      $this->logSection($this->getFullName(), 'Done. but with errors.');
    }
    else
    {
      $this->logSection($this->getFullName(), 'Done.');
    }
    
  }

  /**
   * Chmod and capture any failures.
   * 
   * @param string  $file
   * @param integer $mode
   * @param integer $umask
   * 
   * @see sfFilesystem
   */
  protected function chmod($file, $mode, $umask = 0000)
  {
    if (is_array($file))
    {
      foreach ($file as $f)
      {
        $this->chmod($f, $mode, $umask);
      }
    }
    else
    {
      set_error_handler(array($this, 'handleError'));

      $this->current = $file;
      $this->getFilesystem()->chmod($file, $mode, $umask);
      $this->current = null;

      restore_error_handler();
    }
  }

  /**
   * Captures those chmod commands that fail.
   * 
   * @see http://www.php.net/set_error_handler
   */
  public function handleError($no, $string, $file, $line, $context)
  {
    $this->failed[] = $this->current;
  }
}
