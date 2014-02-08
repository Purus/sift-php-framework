<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds changelog from GIT commit log
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildChangelogTask extends sfCliBaseBuildTask
{

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->addOptions(
            array(
                new sfCliCommandOption('since', 's', sfCliCommandOption::PARAMETER_OPTIONAL, 'Since (date)'),
            )
        );

        $this->aliases = array();
        $this->namespace = '';
        $this->name = 'changelog';
        $this->briefDescription = 'Builds changelog from GIT commit log';

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [changelog|INFO] task builds changelog from commit log.

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        if (!isset($options['since'])) {
            $options['since'] = strtotime('-10 year');
        } else {
            $options['since'] = strtotime($options['since']);
        }

        $since = date('d/m/Y', $options['since']);
        $cmd = sprintf('git log --since=%s --no-merges --format="{%%at} [%%h]%%w(900,0,21) %%B"', $since);

        // where to save the changelog
        $changelog = $this->environment->get('project_root_dir') . '/CHANGELOG';

        $return = null;
        $output = array();

        $this->logSection($this->getFullName(), 'Generating changelog...');
        exec($cmd, $output, $return);

        if ($return) {
            throw new sfCliCommandException(sprintf('Error executing command "%s"', $cmd));
        }

        if (count($output)) {
            $content = array();
            foreach ($output as $line) {
                if (empty($line)) {
                    continue;
                }

                $line = preg_replace_callback('/{(\d+)}/', array($this, 'formatDate'), $line);
                $content[] = str_replace("\n", ' ', $line);
            }

            file_put_contents($changelog, join("\n", $content));
        }

        $this->logSection($this->getFullName(), 'Done.');
    }

    /**
     * Formats the date
     *
     * @param array $matches
     *
     * @return string
     */
    protected function formatDate($matches)
    {
        return date('d.m.Y', $matches[1]);
    }

}
