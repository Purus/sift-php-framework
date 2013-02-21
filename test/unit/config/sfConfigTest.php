<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(13, new lime_output_color());

// ::get() ::set()
$t->diag('::get() ::set()');
sfConfig::clear();

sfConfig::set('foo', 'bar');
$t->is(sfConfig::get('foo'), 'bar', '::get() returns the value of key config');
$t->is(sfConfig::get('foo1', 'default_value'), 'default_value', '::get() takes a default value as its second argument');

// ::has()
$t->diag('::has()');
sfConfig::clear();
$t->is(sfConfig::has('foo'), false, '::has() returns false if the key config does not exist');
sfConfig::set('foo', 'bar');
$t->is(sfConfig::has('foo'), true, '::has() returns true if the key config exists');

// ::add()
$t->diag('::add()');
sfConfig::clear();

sfConfig::set('foo', 'bar');
sfConfig::set('foo1', 'foo1');
sfConfig::add(array('foo' => 'foo', 'bar' => 'bar'));

$t->is(sfConfig::get('foo'), 'foo', '::add() adds an array of config parameters');
$t->is(sfConfig::get('bar'), 'bar', '::add() adds an array of config parameters');
$t->is(sfConfig::get('foo1'), 'foo1', '::add() adds an array of config parameters');

// ::getAll()
$t->diag('::getAll()');
sfConfig::clear();
sfConfig::set('foo', 'bar');
sfConfig::set('foo1', 'foo1');

$t->is(sfConfig::getAll(), array('foo' => 'bar', 'foo1' => 'foo1'), '::getAll() returns all config parameters');

// ::clear()
$t->diag('::clear()');
sfConfig::clear();
$t->is(sfConfig::get('foo1'), null, '::clear() removes all config parameters');

// dot notation

sfConfig::add(array('mailer' => array('class' => 'myMailer', 'params' => array())));
$t->is(sfConfig::get('mailer.class'), 'myMailer', '::get() set works ok for dot notation');
$t->isa_ok(sfConfig::get('mailer.params'), 'array', '::get() set works ok for dot notation');

sfConfig::set('mailer.class', 'myAdvancedMailer');
$t->is(sfConfig::get('mailer.class'), 'myAdvancedMailer', '::set() set works ok for dot notation');

sfConfig::add(array('mailer.class' => 'myMailer'));
$t->is(sfConfig::get('mailer.class'), 'myMailer', '::set() set works ok for dot notation');
