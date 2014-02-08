<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds javascript API documentation
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildJsApiDocsTask extends sfCliBaseBuildTask
{

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->aliases = array();
        $this->namespace = '';
        $this->name = 'js-docs';
        $this->briefDescription = 'Builds javascript API documentation';

        $this->detailedDescription
            = <<<EOF
The [js-docs|INFO] task builds API documentation

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection($this->getFullName(), 'Generating Sift javascript docs');

        $this->build();

        $this->logSection($this->getFullName(), 'Done.');
    }

    protected function build()
    {
        $targetDir = realpath($this->environment->get('project_root_dir') . '/../sift_docs.git/js_api');

        // which directories should be documented?
        $dirs = array();
        $dirs[] = realpath($this->environment->get('sf_sift_data_dir') . '/web/sf/js/core');
        $dirs[] = realpath($this->environment->get('sf_sift_data_dir') . '/web/sf/js/file_uploader');
        $dirs[] = realpath($this->environment->get('sf_sift_data_dir') . '/web/sf/js/dual_list');

        $exclude = array();

        // exclude minified scripts
        $exclude[] = 'min.js';
        $exclude[] = 'globalize.js';
        $exclude[] = 'jquery.fileupload';

        // THIS IS JSDOC3 version, which does not work ok for Sift!
        //$jsDocCmd = "C:/DOCUME~1/michal/DATAAP~1/npm/node_modules/jsdoc/jsdoc.cmd";
        //$cmd       = (sprintf('%s -d %s %s',
        //                $jsDocCmd,
        //                escapeshellarg($targetDir),
        //                $libDir));
        //
        //passthru($cmd);

        $jsDocDir = 'D:/data/tools/jsdoc-toolkit/';
        $templateDir = $jsDocDir . '/templates/JSDoc-Bootstrap-Theme-master';

        $excludes = array();
        foreach ($exclude as $x) {
            $excludes[] = sprintf('--exclude="%s"', $x);
        }

        $cmd = sprintf(
            'java -jar %s %s/app/run.js %s -a -t=%s -d=%s -r=2 %s',
            $jsDocDir . '/jsrun.jar',
            $jsDocDir,
            join(' ', $dirs),
            $templateDir,
            $targetDir,
            join(' ', $excludes)
        );

        passthru($cmd);
    }

}
