<?php
require_once dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new lime_test(3, new lime_output_color());

$REMOTE_ADDR = 'www.example.com';
$PUBLIC_KEY = '6Lf2DQEAAAAAALB9pGaVdcjMiv4CAuOVkfCSVvGh';
$PRIVATE_KEY = '6Lf2DQEAAAAAALnEL0iEogIxZNYMlG7pmNhwEXjk';

// __construct()
$t->diag('__construct()');
try
{
  new sfValidatorReCaptcha();
}
catch(RuntimeException $e)
{
  $t->pass('__construct() expects a "private_key" option');
}

// ->clean()
$t->diag('->clean()');
$v = new sfValidatorReCaptcha(array('private_key' => $PRIVATE_KEY, 'remote_addr' => $REMOTE_ADDR));
try
{
  $v->clean(array(
      'recaptcha_challenge_field' => null,
      'recaptcha_response_field' => null,
      'captcha' => null,
  ));
  $t->fail('->clean() throws a sfValidatorError when the captcha is invalid');
  $t->skip();
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError when the captcha is invalid');
  $t->is($e->getCode(), 'captcha', '->clean() throws a captcha code');
}
