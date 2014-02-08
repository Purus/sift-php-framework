<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Disables an application in a given environment.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectDisableTask extends sfCliBaseTask
{
    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->addArguments(
            array(
                new sfCliCommandArgument('env', sfCliCommandArgument::REQUIRED, 'The environment name'),
                new sfCliCommandArgument('app',
                    sfCliCommandArgument::OPTIONAL | sfCliCommandArgument::IS_ARRAY, 'The application name'),
            )
        );

        $this->namespace = 'project';
        $this->name = 'disable';
        $this->briefDescription = 'Disables an application in a given environment';

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [project:disable|INFO] task disables an environment:

  [{$scriptName} project:disable prod|INFO]

You can also specify individual applications to be disabled in that
environment:

  [{$scriptName} project:disable prod front admin|INFO]
EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $applications = count($arguments['app'])
            ? $arguments['app']
            :
            sfFinder::type('dir')->relative()->maxDepth(0)->in($this->environment->get('sf_apps_dir'));

        $env = $arguments['env'];

        foreach ($applications as $app) {
            $this->checkAppExists($app);
            $lockFile = $this->environment->get('sf_data_dir') . '/' . $app . '_' . $env . '.lck';
            if (file_exists($lockFile)) {
                $this->logSection($this->getFullName(), sprintf('%s [%s] is currently DISABLED', $app, $env));
            } else {
                $this->getFilesystem()->touch($lockFile);
                $this->logSection($this->getFullName(), sprintf('%s [%s] has been DISABLED', $app, $env));
            }
        }
    }
}
