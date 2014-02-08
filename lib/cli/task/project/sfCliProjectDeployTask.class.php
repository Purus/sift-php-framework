<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Deploys a project to another server.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectDeployTask extends sfCliBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('server', sfCliCommandArgument::REQUIRED, 'The server name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('go', null, sfCliCommandOption::PARAMETER_NONE, 'Do the deployment'),
      new sfCliCommandOption('rsync-dir', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The directory where to look for rsync*.txt files', 'config'),
      new sfCliCommandOption('rsync-options', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'To options to pass to the rsync executable',
              '-a --no-o -O --compress --chmod=ugo=rwX --progress'
              // '-azC --force --delete --progress'
              ),
    ));

    $this->namespace = 'project';
    $this->name = 'deploy';
    $this->briefDescription = 'Deploys a project to another server';

    $scriptName = $this->environment->get('script_name');

    $rsyncExcludeFile = $this->environment->get('sf_config_dir') . '/rsync_exclude.txt';

    $rsyncExclude = file_exists($rsyncExcludeFile) ? trim(file_get_contents($rsyncExcludeFile)) : 'n/a (file does not exist)';

    $this->detailedDescription = <<<EOF
The [project:deploy|INFO] task deploys a project on a server:

  [{$scriptName} project:deploy production|INFO]

The server must be configured in [config/properties.ini|COMMENT]:

  [[production]
    host=www.heaven.com
    port=22
    user=jesus
    dir=/var/www/evangelium/
    type=rsync|INFO]

To automate the deployment, the task uses rsync over SSH.
You must configure SSH access with a key or configure the password
in [config/properties.ini|COMMENT].

By default, the task is in dry-mode. To do a real deployment, you
must pass the [--go|COMMENT] option:

  [{$scriptName} project:deploy --go production|INFO]

Files and directories configured in [config/rsync_exclude.txt|COMMENT] are
not deployed:

  [{$rsyncExclude}|INFO]

You can also create a [rsync.txt|COMMENT] and [rsync_include.txt|COMMENT] files.

If you need to customize the [rsync*.txt|COMMENT] files based on the server,
you can pass a [rsync-dir|COMMENT] option:

  [{$scriptName} project:deploy --go --rsync-dir=config/production production|INFO]

Last, you can specify the options passed to the rsync executable, using the
[rsync-options|INFO] option (defaults are [-azC --force --delete --progress|INFO]):

  [{$scriptName} project:deploy --go --rsync-options=-avz|INFO]
EOF;

  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $env = $arguments['server'];

    $ini = $this->environment->get('sf_config_dir').'/properties.ini';
    if (!file_exists($ini)) {
      throw new sfCliCommandException('You must create a config/properties.ini file');
    }

    $properties = parse_ini_file($ini, true);

    if (!isset($properties[$env])) {
      throw new sfCliCommandException(sprintf('You must define the configuration for server "%s" in config/properties.ini', $env));
    }

    $properties = $properties[$env];

    if (!isset($properties['host']) || empty($properties['host'])) {
      throw new sfCliCommandException('You must define a "host" entry.');
    }

    if (!isset($properties['dir']) || empty($properties['dir'])) {
      throw new sfCliCommandException('You must define a "dir" entry.');
    }

    $host = $properties['host'];
    $dir  = $properties['dir'];
    $user = isset($properties['user']) ? $properties['user'].'@' : '';

    if (substr($dir, -1) != '/') {
      $dir .= '/';
    }

    $ssh = 'ssh';

    if (isset($properties['port']) && !empty($properties['port'])) {
      $port = $properties['port'];
      $ssh = '"ssh -p'.$port.'"';
    }

    if (isset($properties['parameters'])) {
      $parameters = $properties['parameters'];
    } else {
      $parameters = $options['rsync-options'];
      if (file_exists($options['rsync-dir'].'/rsync_include.txt')) {
        $parameters .= sprintf(' --include-from=%s/rsync_include.txt', $options['rsync-dir']);
      }

      if (file_exists($options['rsync-dir'].'/rsync_exclude.txt')) {
        $parameters .= sprintf(' --exclude-from=%s/rsync_exclude.txt', $options['rsync-dir']);
      }

      if (file_exists($options['rsync-dir'].'/rsync.txt')) {
        $parameters .= sprintf(' --files-from=%s/rsync.txt', $options['rsync-dir']);
      }
    }

    if (isset($properties['src'])) {
      $src = $properties['src'];
    } else {
      $src = './';
    }

    $dryRun = $options['go'] ? '' : '--dry-run';
    $command = "rsync $dryRun $parameters -e $ssh $src $user$host:$dir";

    $this->logSection($this->getFullName(), sprintf('Executing %s', $command));

    exec($command, $output);
    foreach ($output as $line) {
      if (!empty($line)) {
        $this->log($line);
      }
    }

  }

}
