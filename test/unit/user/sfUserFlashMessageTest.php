<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

$flash = new sfUserFlashMessage('Successfully logged in', sfUserFlashMessage::SUCCESS);

$t->is($flash->getMessage(), 'Successfully logged in', 'getMessage() works');
$t->is($flash->getType(), 'success', 'getType() works');
$t->is($flash->getApplication(), '', 'getApplication() works');

$t->ok(unserialize(serialize($flash)) == $flash, 'serializing works ok');
