<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfServiceContainerMock.php');

$t = new lime_test(10, new lime_output_color());

class myTextFilterRegistry extends sfTextFilterRegistry {

  protected function loadFilters()
  {
  }
}

$registry = new myTextFilterRegistry($serviceContainer, new sfConsoleLogger());

function my_filter(sfTextFilterContent $content)
{
  $content->setText((string)$content . '-FILTERED');
}

function my_next_filter(sfTextFilterContent $content)
{
  $content->setText((string)$content . '-AGAIN');
}

function my_canceling_filter(sfTextFilterContent $content)
{
  $content->setText((string)$content . '-CANCELED');
  // cancel bubbling to next filters!
  // I took over this!
  $content->cancelBubble(true);
}

function my_wildcard_filter(sfTextFilterContent $content)
{
  $content->setText((string)$content . '-WILDCARD');
}

class Foo implements sfITextFilter {

  protected $options = array();

  public function __construct($options = array())
  {
    $this->options = $options;
  }

  public function filter(sfTextFilterContent $content)
  {
    $content->setText($content->getText(). '-FOO-OBJECT-'.http_build_query($this->options));
  }

}

$t->diag('->register()');

$registry->register('content.description', 'my_filter');
$t->is(count($registry), 1, 'register() registered the text filter');

$t->diag('->unregister()');
$registry->unregister('content.description');

$t->is(count($registry), 0, 'unregister() unregistered the text filter');

$t->diag('->apply()');

$registry->register('content.description', 'my_filter');

$t->is($registry->apply('content.description', 'my content'), 'my content-FILTERED', 'apply applies the filter');

$registry->register('content.description', 'my_next_filter');

$t->is(count($registry), 2, 'register() registered the next text filter');

$t->is($registry->apply('content.description', 'my content'), 'my content-FILTERED-AGAIN', 'apply applies both registered filters');

$registry->register('content.description', 'my_canceling_filter', 100);

$t->is($registry->apply('content.description', 'my content'), 'my content-CANCELED', 'apply applies only the high priority filter which cancels the bubbling');

$t->diag('->getTags()');
$t->is($registry->getTags(), array('content.description'), 'getTags() returns an array of registered filters');

$t->diag('->apply() with wildcard');

$registry->clear()
         ->register('*.description', 'my_wildcard_filter');

$t->is($registry->apply('content.description', 'my super text content'), 'my super text content-WILDCARD', 'apply() applied the wildcard filter');

$registry->clear()
         ->register('content.description', 'my_filter')
         ->register('*.description', 'my_wildcard_filter');

$t->is($registry->apply('content.description', 'my super text content'), 'my super text content-FILTERED-WILDCARD', 'apply() applied the wildcard filter');

$registry->clear()
         ->register('*.description', 'my_wildcard_filter')
         ->register('content.description', 'my_filter')
         ->register('content.description', (array(
             'class' => 'Foo',
             'arguments' => array(array('allowed_tags' => 'foo', 'option2' => 'bar'))
         )));

$t->is($registry->apply('content.description', 'my super text content'), 'my super text content-WILDCARD-FILTERED-FOO-OBJECT-allowed_tags=foo&option2=bar', 'apply() applied the wildcard filter');
