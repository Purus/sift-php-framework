<?php

$serviceContainer = new sfServiceContainer();
$dispatcher = new sfEventDispatcher();
$serviceContainer->set('storage', new sfNoStorage());
$serviceContainer->set('request', new sfWebRequest($dispatcher));
$serviceContainer->set('event_dispatcher', $dispatcher);
