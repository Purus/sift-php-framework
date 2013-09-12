<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(4, new lime_output_color());

class myView extends sfPartialMailView
{

  function getMailVars()
  {
    return array();
  }
}

$context = new sfContext();

$t->diag('plain text message');

$view = new myView($context);
$view->setDecorator(false);
$view->setTemplate(dirname(__FILE__) . '/fixtures/plain_text.php');
$rendered = $view->render(array(
  'name' => 'User'
));

$expected = file_get_contents(dirname(__FILE__) . '/fixtures/plain_text.txt');
$t->is($rendered, $expected, '->render() replaces more new lines with single newline');


$view = new myView($context);
$view->setTemplate(dirname(__FILE__) . '/fixtures/plain_text.php');
$view->setDecoratorTemplate(dirname(__FILE__) . '/fixtures/plain_layout.php');
$rendered = $view->render(array(
  'name' => 'User'
));

$expected = file_get_contents(dirname(__FILE__) . '/fixtures/plain_text_with_layout.txt');
$t->is($rendered, $expected, '->render() replaces more new lines with single newline');

$t->diag('html message');

$view = new myView($context);
$view->setPartialVars(array(
  'sf_email_type' => 'html'
));

$view->setDecorator(false);
$view->setTemplate(dirname(__FILE__) . '/fixtures/html_text.php');
$rendered = $view->render(array(
  'name' => 'User'
));

$expected = file_get_contents(dirname(__FILE__) . '/fixtures/html_text.html');
$t->is($rendered, $expected, '->render() does not touch newlines if the template is HTML');

$view = new myView($context);
$view->setTemplate(dirname(__FILE__) . '/fixtures/html_text.php');
$view->setDecoratorTemplate(dirname(__FILE__) . '/fixtures/html_layout.php');
$rendered = $view->render(array(
  'name' => 'User'
));

$expected = file_get_contents(dirname(__FILE__) . '/fixtures/html_text_with_layout.html');
$t->is($rendered, $expected, '->render() replaces more new lines with single newline');