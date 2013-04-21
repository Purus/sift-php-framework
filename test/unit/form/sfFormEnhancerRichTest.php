<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
$t = new lime_test(5);

class BaseForm extends myForm {

  public function configure()
  {
    $this->setWidget('date', new sfWidgetFormDate(array('culture' => 'cs_CZ')));
    $this->setValidator('data', new sfValidatorDate());
  }
}

class sfFormEnhancerTest extends sfFormEnhancerRich
{
}

// load base yaml

$yaml = sfYaml::load($sf_sift_data_dir . '/config/forms.yml');

$enhancer = new sfFormEnhancerTest($yaml);

$t->diag('->convertDateFormat');

// convert date formats
$dateFormats = array(
  'd.M.yyyy' => 'd.m.yy',
);

foreach($dateFormats as $format => $expected)
{
  $t->is($enhancer->convertDateFormat($format), $expected, 'format is converted to jquery ui date format successfully');
}

sfConfig::add(array(
  'sf_culture' => 'cs_CZ'
));

// ->enhance()
$t->diag('->enhance()');
$form = new BaseForm();

$enhancer->enhance($form);
$t->like($form['date']->render(), '/class="date" data-datepicker-options="{/', '->enhance() makes the form widgets rich');

$t->diag('sfWidgetFormInteger');

$widget = new sfWidgetFormInteger();
$enhancer->enhanceWidget($widget, new sfValidatorInteger(array('min' => 10, 'max' => 15)));
$t->like($widget->render('foo'), '/class="integer" data-spinner-options="{/', '->enhanceWidget() makes the widget rich');

$widget = new sfWidgetFormNumber();
$enhancer->enhanceWidget($widget, new sfValidatorNumber(array('min' => 10, 'max' => 15)));
$t->like($widget->render('foo'), '/class="number" data-spinner-options="{/', '->enhanceWidget() makes the widget rich');

$t->diag('sfWidgetFormDate');
$widget = new sfWidgetFormDate(array('culture' => 'cs_CZ'));
$enhancer->enhanceWidget($widget);
$t->like($widget->render('foo'), '/class="date" data-datepicker-options="{/', '->enhanceWidget() makes the widget rich');
