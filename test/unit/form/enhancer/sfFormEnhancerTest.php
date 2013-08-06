<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');
$t = new lime_test(7);

class BaseForm extends myForm {

  public function configure()
  {
    $this->setWidget('body', new sfWidgetFormTextarea());
    $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
  }
}

class sfFormEnhancerTest extends sfFormEnhancer
{
}

class sfFormExtraEnhancerTest extends sfFormEnhancer
{
  public function enhanceWidget(sfWidget $widget, sfValidatorBase $validator = null)
  {
    switch(get_class($widget))
    {
      case 'sfWidgetFormInputText';
        $widget->setAttribute('class', 'datetimepicker');
        $widget->setAttribute('data-datetimepicker-options', json_encode(array(
          'format' => $this->convertFormat($widget->getOption('format')),
          'showWeek' => true
        )));
        break;
    }

    parent::enhanceWidget($widget);
  }

  protected function convertFormat($format)
  {
    return sprintf("converted_%s", $format);
  }

}

class CommentForm extends BaseForm
{
  public function configure()
  {
    parent::configure();
  }
}

class myTimeForm extends sfForm {

  public function configure()
  {
    $this->setWidget('time', new sfWidgetFormInputText(array()));
  }

}

$options = sfYaml::load(dirname(__FILE__).'/fixtures/forms.yml');
$enhancer = new sfFormEnhancerTest($options);

// ->enhance()
$t->diag('->enhance()');

$form = new CommentForm();
$form->bind(array('body' => '+1'));

$enhancer->enhance($form);

$t->like($form['body']->renderLabel(), '/Please enter your comment/', '->enhance() enhances labels');
$t->like($form['body']->render(), '/class="(.*)? comment/', '->enhance() enhances widgets');
$t->like($form['body']->renderError(), '/You haven\'t written enough/', '->enhance() enhances error messages');

$form = new CommentForm();
$form->bind();
$enhancer->enhance($form);
$t->like($form['body']->renderError(), '/A base required message/', '->enhance() considers inheritance');

class SpecialCommentForm extends CommentForm { }
$form = new SpecialCommentForm();
$form->bind();
$enhancer->enhance($form);
$t->like($form['body']->renderLabel(), '/Please enter your comment/', '->enhance() applies parent config');

$form = new BaseForm();
$form->embedForm('comment', new CommentForm());
$form->bind();
$enhancer->enhance($form);
$t->like($form['comment']['body']->renderLabel(), '/Please enter your comment/', '->enhance() enhances embedded forms');


// construct advanced enhancer
$enhancer = new sfFormExtraEnhancerTest($options);

$form = new myTimeForm();
$enhancer->enhance($form);
$t->like($form['time']->render(), '/class="datetimepicker" data-datetimepicker-options="{/', '->enhance() makes the widget rich');
