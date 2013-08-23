<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfCoreMock.class.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfServiceContainerMock.php');

$t = new lime_test(33, new lime_output_color());

$_SERVER['session_id'] = 'test';
sfConfig::set('sf_test_cache_dir', sfToolkit::getTmpDir());

$context = new sfContext();
$request = new sfWebRequest();

$sessionPath = sys_get_temp_dir() . '/sessions_' . rand(11111, 99999);
$storage = new sfSessionTestStorage(array('session_path' => $sessionPath));

$serviceContainer->set('storage', $storage);

$context->request = $request;

$storage->clear();

$context->storage = $storage;

$user = new sfUser($serviceContainer);

$context->user = $user;

// ->initialize()
$t->diag('->initialize()');
$t->is($user->getCulture(), 'en', '->initialize() sets the culture to "en" by default');

sfConfig::set('sf_i18n_default_culture', 'de');
$user->setCulture(null);
user_flush($context);

$t->is($user->getCulture(), 'de', '->initialize() sets the culture to the value of sf_i18n_default_culture if available');

sfConfig::set('sf_i18n_default_culture', 'fr');
user_flush($context);
$t->is($user->getCulture(), 'de', '->initialize() reads the culture from the session data if available');

$userBis = new sfUser($serviceContainer);

$t->is($userBis->getCulture(), 'de', '->intialize() serializes the culture to the session data');

// ->setCulture() ->getCulture()
$t->diag('->setCulture() ->getCulture()');
$user->setCulture('fr');
$t->is($user->getCulture(), 'fr', '->setCulture() changes the current user culture');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($user, 'parameter');

// attribute holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($user, 'attribute');

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);

$dispatcherTest->launchTests($serviceContainer->get('event_dispatcher'), $user, 'user');

$storage->clear();

function user_flush($context)
{
  $context->getUser()->shutdown();
  $context->getStorage()->shutdown();
}
