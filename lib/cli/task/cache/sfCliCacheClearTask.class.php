<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Clears the cache.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliCacheClearTask extends sfCliBaseTask
{
  protected $config = null;

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCliCommandOption('app', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCliCommandOption('env', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'The environment', null),
    ));

    $this->aliases = array('cc');
    $this->namespace = 'cache';
    $this->name = 'clear';
    $this->briefDescription = 'Clears the cache';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [cache:clear|INFO] task clears the cache.

By default, it removes the cache for all available types, all applications,
and all environments.

You can restrict by type, application, or environment:

For example, to clear the [front|COMMENT] application cache:

  [{$scriptName} cache:clear --app=frontend|INFO]

To clear the cache for the [prod|COMMENT] environment for the [frontend|COMMENT] application:

  [{$scriptName} cache:clear --app=frontend --env=prod|INFO]

To clear the cache for all [prod|COMMENT] environments:

  [{$scriptName} cache:clear --env=prod|INFO]

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $cacheDir = $this->environment->get('sf_root_cache_dir');

    if (!$cacheDir || !is_dir($cacheDir))
    {
      throw new sfException(sprintf('Cache directory "%s" does not exist.', $cacheDir));
    }

    // finder to find directories (1 level) in a directory
    // leave hidden files in the place -> discard(.*)
    $dirFinder = sfFinder::type('dir')->discard('.*')->maxDepth(0)->relative();

    // iterate through applications

    $apps = null === $options['app'] ? $dirFinder->in($this->environment->get('sf_apps_dir')) : array($options['app']);
    foreach($apps as $app)
    {
      $this->checkAppExists($app);

      if(!is_dir($cacheDir.'/'.$app))
      {
        continue;
      }

      // iterate through environments
      $envs = null === $options['env'] ? $dirFinder->in($cacheDir.'/'.$app) : array($options['env']);
      foreach($envs as $env)
      {
        if (!is_dir($cacheDir.'/'.$app.'/'.$env))
        {
          continue;
        }

        $this->logSection($this->getFullName(), sprintf('Clearing cache for "%s" app and "%s" env', $app, $env));
        $this->lock($app, $env);

        $event = $this->dispatcher->notifyUntil(new sfEvent('cli_task.cache.clear',
                array('app' => $app, 'env' => $env, 'task' => $this)));

        if(!$event->isProcessed())
        {
          $this->clearCache($cacheDir.'/'.$app.'/'.$env);
        }

        $this->unlock($app, $env);
      }
    }

    // clear global cache
    if(null === $options['app'])
    {
      $this->getFilesystem()->remove(sfFinder::type('any')
              ->discard('.*')->in($this->environment->get('sf_root_cache_dir')));
    }

    $this->logSection($this->getFullName(), 'Done.');
  }

  protected function clearCache($cacheDir)
  {
    $this->getFilesystem()->remove(sfFinder::type('file')->discard('.*')->in($cacheDir));
  }

  protected function lock($app, $env)
  {
    // create a lock file
    $this->getFilesystem()->touch($this->getLockFile($app, $env));
    // change mode so the web user can remove it if we die
    $this->getFilesystem()->chmod($this->getLockFile($app, $env), 0777);
  }

  protected function unlock($app, $env)
  {
    // release lock
    $this->getFilesystem()->remove($this->getLockFile($app, $env));
  }

  protected function getLockFile($app, $env)
  {
    return $this->environment->get('sf_data_dir').'/'.$app.'_'.$env.'-cli.lck';
  }

}
