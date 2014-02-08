<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds web debugger files
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildErrorsTask extends sfCliBaseBuildTask
{

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->aliases = array();
        $this->namespace = '';
        $this->name = 'errors';
        $this->briefDescription = 'Builds production error pages';

        $this->detailedDescription
            = <<<EOF
The [errors|INFO] task builds production error pages

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->logSection($this->getFullName(), 'Generating error pages.');
        $this->build();
        $this->logSection($this->getFullName(), 'Done.');
    }

    protected function build()
    {
        $filesystem = $this->getFilesystem();
        $targetDir = realpath($this->environment->get('sf_sift_data_dir') . '/errors');
        $sourceDir = realpath($this->environment->get('build_data_dir') . '/errors');

        $tmp = sys_get_temp_dir() . '/errors';

        if (is_dir($tmp)) {
            sfToolkit::clearDirectory($tmp);
            rmdir($tmp);
        }

        mkdir($tmp);

        // copy all to temp directory
        $filesystem->mirror($sourceDir, $tmp, sfFinder::type('any'));

        $compiler = new sfLessCompiler(new sfEventDispatcher(), array(
            'cache_dir'     => sys_get_temp_dir(),
            'web_cache_dir' => sys_get_temp_dir()
        ));

        $compiler->addImportDir($tmp);
        $compiler->addImportDir($tmp . '/less');

        $compiled = $compiler->compile(file_get_contents($tmp . '/error.less'));
        file_put_contents($tmp . '/error.min.css', $compiled);

        $filesystem->copy($tmp . '/error.min.css', $targetDir . '/css/error.min.css');

        sfToolkit::clearDirectory($tmp);
        rmdir($tmp);
    }

}
