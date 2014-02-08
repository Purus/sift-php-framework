<?php

/**
 * ##PLUGIN_NAME##
 *
 * @package     ##PLUGIN_NAME##
 * @subpackage  plugin
 * @author      ##AUTHOR_NAME##
 */
class ##PLUGIN_NAME## extends sfPlugin
{
    /**
     * @see sfPlugin
     */
    public function initialize()
    {
        // application or project reference
        $app = $this->getParent();
        // get dispatcher
        $dispatcher = $app->getEventDispatcher();
        $dispatcher->connect(
            'context.load_factories',
            array(
                $this,
                'listenToLoadFactoriesEvent'
            )
        );
    }

    /**
     * Listen to context.load_factories event
     *
     * @param sfEvent $event
     */
    public function listenToLoadFactoriesEvent(sfEvent $event)
    {
    }

}
