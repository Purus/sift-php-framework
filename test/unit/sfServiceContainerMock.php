<?php

$serviceContainer = new sfServiceContainer();
$serviceContainer->set('storage', new sfNoStorage());
$serviceContainer->set('request', new sfWebRequest());
$serviceContainer->set('event_dispatcher', new sfEventDispatcher());
