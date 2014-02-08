<?php

// load application base, shared between apps in the project
require_once dirname(__FILE__) . '/../../../lib/myApplicationBase.class.php';

/**
 * @package    ##PROJECT_NAME##
 * @subpackage project
 */
class ##CLASS_NAME## extends myApplicationBase
{
//    /**
//     * Early stage application dimension initialization.
//     *
//     * Beware that this is called before the sfContext gets created,
//     * so there is no access to internal objects.
//     */
//    public function initCurrentDimension()
//    {
//        parent::initCurrentDimension();
//        // set dimension based on domain name
//        $culture = 'cs';
//        if (preg_match('/^en\./', @$_SERVER['SERVER_NAME'])) {
//            $culture = 'en';
//        }
//        // set dimension
//        $this->setCurrentDimension(array('culture' => $culture));
//    }
//
//    /**
//     * Initializes the application.
//     *
//     */
//    public function initialize()
//    {
//        parent::initialize();
//        // set base domain
//        $baseDomain = preg_replace('/^(www\.)/', '', @$_SERVER['SERVER_NAME']);
//        $this->setOption('sf_base_domain', $baseDomain);
//
//        // Connect to context created event with high priority
//        // $this->getEventDispatcher()->connect('context.instance_created', array($this, 'listenToContextCreatedEvent'), 100);
//
//        // Include custom configuration file
//        // include $this->getConfigCache()->checkConfig('config/custom.yml');
//    }
//
//    public function listenToContextCreatedEvent(sfEvent $event)
//    {
//        $context = $event['context'];
//        $dimension = $this->getCurrentDimension();
//        switch ($dimension['culture']) {
//            case 'en':
//                $context->getUser()->setCulture('en_GB');
//                break;
//        }
//    }

}
