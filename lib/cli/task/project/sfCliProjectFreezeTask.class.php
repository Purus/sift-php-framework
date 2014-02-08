<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Freezes the project do current Sift release
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectFreezeTask extends sfCliBaseTask
{
    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->namespace = 'project';
        $this->name = 'freeze';
        $this->briefDescription = 'Freeze the project to current Sift release';

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [project:freeze|INFO] task freezes to current Sift release:

  [{$scriptName} project:freeze|INFO]
EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $lib_dir = $this->environment->get('sf_lib_dir');
        $data_dir = $this->environment->get('sf_data_dir');
        $web_dir = $this->environment->get('sf_web_dir');

        $sift_lib_dir = $this->environment->get('sf_sift_lib_dir');
        $sift_data_dir = $this->environment->get('sf_sift_data_dir');

        if (is_readable($lib_dir . '/sift')) {
            throw new sfException('You can only freeze when lib/sift is empty.');
        }

        if (is_readable($data_dir . '/sift')) {
            throw new sfException('You can only freeze when data/sift is empty.');
        }

        if (is_readable($web_dir . '/sf')) {
            throw new sfException('You can only freeze when web/sf is empty.');
        }

        $this->logSection(
            $this->getFullName(),
            sprintf(
                'Freezing project to %s %s',
                $this->commandApplication->getName(),
                $this->commandApplication->getVersion()
            )
        );

        // remove symlink
        if (is_link($web_dir . '/sf')) {
            $this->getFilesystem()->remove($web_dir . '/sf');
        }

        // create directories
        $this->getFilesystem()->mkdirs($lib_dir . '/sift');
        $this->getFilesystem()->mkdirs($data_dir . '/sift');
        $this->getFilesystem()->mkdirs($web_dir . '/sf');

        $this->getFilesystem()->mirror($sift_lib_dir, $lib_dir . '/sift', sfFinder::type('any'));
        $this->getFilesystem()->mirror($sift_data_dir, $data_dir . '/sift', sfFinder::type('any'));
        $this->getFilesystem()->mirror($sift_data_dir . '/web/sf', $web_dir . '/sf', sfFinder::type('any'));

        file_put_contents(
            $this->environment->get('sf_config_dir') . '/config.php.bak',
            sprintf(
                '%s#%s',
                $this->environment->get('sf_sift_lib_dir'),
                $this->environment->get('sf_sift_data_dir')
            )
        );

        //$this->getFilesystem()->copy($sift_data_dir.'/bin/sift',
        //        $this->environment->get('sf_root_dir') . '/sift');

        $this->changeFrameworkDirectories("dirname(__FILE__).'/../lib/sift'", "dirname(__FILE__).'/../data/sift'");

        $this->clearAllCache();

        $this->logSection($this->getFullName(), 'Done.');
    }

    protected function changeFrameworkDirectories($lib_dir, $data_dir)
    {
        $content = file_get_contents($this->environment->get('sf_config_dir') . '/config.php');
        $content = preg_replace("/^(\s*.sf_sift_lib_dir\s*=\s*).+?;/m", "$1$lib_dir;", $content);
        $content = preg_replace("/^(\s*.sf_sift_data_dir\s*=\s*).+?;/m", "$1$data_dir;", $content);
        file_put_contents($this->environment->get('sf_config_dir') . '/config.php', $content);
    }

    protected function clearAllCache()
    {
        $task = new sfCliCacheClearTask($this->environment,
            $this->dispatcher,
            $this->formatter,
            $this->logger);

        $task->setCommandApplication($this->commandApplication);
        $task->run();
    }

}
