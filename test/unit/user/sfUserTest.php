<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfCoreMock.class.php');


class sfContext
{
  public static $instance;
  
  public    
    $storage  = null,
    $user     = null,
    $request  = null,
    $dispatcher;

  public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfContext();
      self::$instance->dispatcher = new sfEventDispatcher();
    }

    return self::$instance;
  }

  public function getRequest()
  {
    return $this->request;
  }

  public function getUser()
  {
    return $this->user;
  }

  public function getStorage()
  {
    return $this->storage;
  }

  public function getEventDispatcher()
  {
    return self::getInstance()->dispatcher;
  }

}

$t = new lime_test(33, new lime_output_color());

$_SERVER['session_id'] = 'test';
sfConfig::set('sf_test_cache_dir', sfToolkit::getTmpDir());

$context = new sfContext();

$request = new sfWebRequest();
$request->initialize($context);
$context->request = $request;

$storage = sfStorage::newInstance('sfSessionTestStorage');
$storage->initialize($context);
$storage->clear();
$context->storage = $storage;

$user = new sfUser();
$user->initialize($context);
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

$userBis = new sfUser();
$userBis->initialize($context);
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

$dispatcherTest->launchTests(sfCore::getEventDispatcher(), $user, 'user');

$storage->clear();

function user_flush($context)
{
  $context->getUser()->shutdown();
  $context->getUser()->initialize($context);
  $context->getStorage()->shutdown();
  $context->getStorage()->initialize($context);
}
