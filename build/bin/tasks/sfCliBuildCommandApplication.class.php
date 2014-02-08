<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Build command application
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildCommandApplication extends sfCliRootCommandApplication
{

    /**
     * Loads all available tasks.
     *
     * Looks for tasks in the Sift core, the current project and all project plugins.
     *
     * @param sfProjectConfiguration $configuration The project configuration
     */
    public function loadTasks()
    {
        // core tasks
        $dirs = array($this->environment->get('build_task_dir'));

        $finder = sfFinder::type('file')->name('*Task.class.php');

        foreach ($finder->in($dirs) as $file) {
            $this->taskFiles[basename($file, '.class.php')] = $file;
        }

        // register local autoloader for tasks
        spl_autoload_register(array($this, 'autoloadTask'));

        // require tasks
        foreach ($this->taskFiles as $task => $file) {
            // forces autoloading of each task class
            class_exists($task, true);
        }

        // unregister local autoloader
        spl_autoload_unregister(array($this, 'autoloadTask'));
    }

    /**
     * Does not bind to any project.
     */
    public function bindToProject()
    {
    }

}
