<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds autoloading information
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildCoreAutoloaderTask extends sfCliBaseBuildTask
{

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->aliases = array();
        $this->namespace = '';
        $this->name = 'autoloader';
        $this->briefDescription = 'Builds core autoloading information (class to file map)';

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [autoloader|INFO] task builds core autoloading information contained in sfCoreAutoloadClass

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        sfCoreAutoload::make();
        $this->logSection($this->getFullName(), 'Done.');
    }

}
