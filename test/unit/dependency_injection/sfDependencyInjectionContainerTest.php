<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');
require_once(dirname(__FILE__) . '/stubs/Book.php');

$t = new lime_test(6, new lime_output_color());

$container = sfDependencyInjectionContainer::getInstance('testing');
$container->getDependencies()->set('someValue', 'yellow');

$t->is($container->getDependencies()->get('someValue'), 'yellow', 'singleton works ok.');

$container = sfDependencyInjectionContainer::getInstance('newTest');

$t->is_deeply($container->getDependencies()->get('someValue'), null, 'getDependencies() returns null if using different singleton');

/* @var $book Book */
$book = sfDependencyInjectionContainer::create('Book');
$t->isa_ok($book, 'Book', 'create() creates an instance of Book class');

class sfContext {

  public function __construct($options = array())
  {
    
  }      
  
}

$context = new sfContext();

sfDependencyInjectionContainer::getInstance()->getDependencies()->set('context', $context);

class sfView {

  public $context;

  /**
   *
   * @param sfContext $context
   * @inject context
   */
  public function __construct(sfContext $context)
  {
    $this->context = $context;
  }
  
  public function initialize($moduleName, $actionName, $viewName = '')
  {    
  }

}

class myPHPView extends sfView {}

$view = sfDependencyInjectionContainer::create('sfView');
$view->initialize('foo', 'bar');
$t->isa_ok($view, 'sfView');

$view = sfDependencyInjectionContainer::create('sfView', array($context));

$t->isa_ok($view->context, 'sfContext', 'create() works with arguments');

$view = sfDependencyInjectionContainer::create('myPHPView');
$t->isa_ok($view->context, 'sfContext', 'create() works with arguments');


