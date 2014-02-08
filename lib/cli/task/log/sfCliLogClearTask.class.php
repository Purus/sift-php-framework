<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Clears log files.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliLogClearTask extends sfCliBaseTask
{
    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->namespace = 'log';
        $this->name = 'clear';
        $this->briefDescription = 'Clears log files';

        $this->addOptions(
            array(
                new sfCliCommandOption('history', null, sfCliCommandOption::PARAMETER_NONE, 'Clear also history logs?', null),
            )
        );

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [log:clear|INFO] task clears all log files:

  [{$scriptName} log:clear|INFO]
EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection($this->getName(), 'Clearing logs...');

        $history = $options['history'];

        $finder = sfFinder::type('file');
        // clear also history?
        if (!$history) {
            $finder->maxdepth(0);
        }

        $logs = $finder->in($this->environment->get('sf_log_dir'));

        $this->getFilesystem()->remove($logs);

        $this->logSection($this->getName(), 'Done.');
    }

}
