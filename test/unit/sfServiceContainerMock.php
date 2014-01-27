<?php

$serviceContainer = new sfServiceContainer(new sfNoCache());
$dispatcher = new sfEventDispatcher();
$serviceContainer->set('storage', new sfNoStorage());
$serviceContainer->set('request', new sfWebRequest($dispatcher));
$serviceContainer->set('event_dispatcher', $dispatcher);
