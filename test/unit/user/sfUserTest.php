<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfCoreMock.class.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfServiceContainerMock.php');

$t = new lime_test(32, new lime_output_color());

$_SERVER['session_id'] = 'test';

$sessionPath = sys_get_temp_dir() . '/sessions_' . rand(11111, 99999);
$storage = new sfSessionTestStorage(array('session_path' => $sessionPath));

$serviceContainer->set('storage', $storage);

$user = new sfUser($serviceContainer);

$t->is($user->getCulture(), 'en', '->setup() sets the culture to "en" by default');

$user = new sfUser($serviceContainer, array('default_culture' => 'de'));

$t->is($user->getCulture(), 'de', '->setup() sets the culture to the value from storage');

user_flush($storage, $user);

$t->is($user->getCulture(), 'de', '->setup() reads the culture from the session data if available');

$userBis = new sfUser($serviceContainer);

$t->is($userBis->getCulture(), 'de', '->setup() serializes the culture to the session data');

// ->setCulture() ->getCulture()
$t->diag('->setCulture() ->getCulture()');
$user->setCulture('fr');
$t->is($user->getCulture(), 'fr', '->setCulture() changes the current user culture');

// ->setFlash() ->getFlash() ->hasFlash()
$t->diag('->setFlash() ->getFlash() ->hasFlash()');
$user = new sfUser($serviceContainer);

$user->setFlash('foo', 'bar');

$t->is((string)$user->getFlash('foo'), 'bar', '->setFlash() sets a flash variable');
$t->is($user->hasFlash('foo'), true, '->hasFlash() returns true if the flash variable exists');
user_flush($storage, $user);

$userBis = new sfUser($serviceContainer, array('use_flash' => true));
$t->is((string)$userBis->getFlash('foo'), 'bar', '->getFlash() returns a flash previously set');
$t->is($userBis->hasFlash('foo'), true, '->hasFlash() returns true if the flash variable exists');
user_flush($storage, $user);

$userBis = new sfUser($serviceContainer, array('use_flash' => true));
$t->is($userBis->getFlash('foo'), null, 'Flashes are automatically removed after the next request');
$t->is($userBis->hasFlash('foo'), false, '->hasFlash() returns true if the flash variable exists');

// array access for user attributes
$user->setAttribute('foo', 'foo');

$t->diag('Array access for user attributes');
$t->is(isset($user['foo']), true, '->offsetExists() returns true if user attribute exists');
$t->is(isset($user['foo2']), false, '->offsetExists() returns false if user attribute does not exist');
$t->is($user['foo3'], false, '->offsetGet() returns false if attribute does not exist');
$t->is($user['foo'], 'foo', '->offsetGet() returns attribute by name');

$user['foo2'] = 'foo2';
$t->is($user['foo2'], 'foo2', '->offsetSet() sets attribute by name');

unset($user['foo2']);
$t->is(isset($user['foo2']), false, '->offsetUnset() unsets attribute by name');

$user = new sfUser($serviceContainer);

// attribute holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($user, 'attribute');

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);

$dispatcherTest->launchTests($serviceContainer->get('event_dispatcher'), $user, 'user');

function user_flush($storage, $user)
{
  $user->shutdown();
  $storage->shutdown();

  $storage->setup();
  $user->setup();
}
