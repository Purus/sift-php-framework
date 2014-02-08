<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelEnvironment adds a panel to the web debug toolbar
 * with the current environment information like configuration, server information,
 * request variables...
 *
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelEnvironment extends sfWebDebugPanel
{
    /**
     * @see sfWebDebugPanel
     */
    public function getTitle()
    {
        return 'environment';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelTitle()
    {
        return 'Environment';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelContent()
    {
        $context = sfContext::getInstance();

        return $this->webDebug->render(
            $this->getOption('template_dir') . '/panel/environment.php',
            array(
                'app_name'    => sfConfig::get('sf_app'),
                'environment' => sfConfig::get('sf_environment'),
                'settings'    => sfDebug::removeObjects(sfDebug::settingsAsArray()),
                'globals'     => sfDebug::removeObjects(sfDebug::globalsAsArray()),
                'sift'        => sfDebug::frameworkInfoAsArray(),
                'php'         => sfDebug::phpInfoAsArray(),
                'plugins'     => sfDebug::pluginsInfoAsArray(),
                'request'     => sfDebug::requestAsArray($context->getRequest()),
                'response'    => sfDebug::responseAsArray($context->getResponse()),
                'user'        => sfDebug::userAsArray($context->getUser())
            )
        );
    }
}
