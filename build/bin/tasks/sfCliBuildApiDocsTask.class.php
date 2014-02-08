<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds API documentation
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildApiDocsTask extends sfCliBaseBuildTask
{

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->aliases = array();
        $this->namespace = '';
        $this->name = 'api-docs';
        $this->briefDescription = 'Builds API documentation';

        $this->detailedDescription
            = <<<EOF
The [api-docs|INFO] task builds API documentation using ApiGen

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection($this->getFullName(), 'Generating Sift API docs');

        $this->build();

        $this->logSection($this->getFullName(), 'Done.');
    }

    protected function build()
    {
        $targetDir = realpath($this->environment->get('project_root_dir') . '/../sift_docs.git/api');
        $libDir = $this->environment->get('sf_sift_lib_dir');
        $excludeDir = realpath($this->environment->get('sf_sift_lib_dir') . '/vendor') . '/*';

        $title = 'API docs ~ Sift PHP framework';
        passthru(
            sprintf(
                'apigen --source %s --exclude %s --title %s --destination %s',
                escapeshellarg($libDir),
                escapeshellarg($excludeDir),
                escapeshellarg($title),
                escapeshellarg($targetDir)
            )
        );
    }

}
