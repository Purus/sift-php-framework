<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfBitwise.class.php');

$t = new lime_test(6, new lime_output_color());

define('MY_CONST1', 1);
define('MY_CONST2', 2);
define('MY_CONST3', 4);
define('MY_CONST4', 8);

$flag = sfBitwise::createFlag(MY_CONST1);

$t->is($flag, 1, '->createFlag() creates bit flag');

$flag = sfBitwise::unsetFlag($flag, MY_CONST2);

$t->is($flag, 1, '->unsetFlag() unsets flag');


$flag = sfBitwise::setFlag($flag, MY_CONST2);

$t->is($flag, 3, '->setFlag() sets flag');

$t->is(sfBitwise::isFlagSet($flag, MY_CONST3), false, '->isFlagSet() checks if flag is set');

$t->is(sfBitwise::isFlagSet($flag, MY_CONST1), true, '->isFlagSet() checks if flag is set');

$t->is(sfBitwise::setFlag($flag, MY_CONST4), 11, '->isFlagSet() checks if flag is set');

