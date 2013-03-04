<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(105, new lime_output_color());

class myGeneratorFormBuilder extends sfGeneratorFormBuilder
{

  protected $defaultOptions = array(
    'foreign_key' => array(
      'widget' => array(
        'class' => '_FOREIGN_'
      ),
      'validator' => array(
        'class' => '_FOREIGN_'
      )
    )
  );

  protected function getWidgetAndValidatorForeignKey(sfIGeneratorColumn $column, $context, $widgetOptions = array())
  {
    return array(
      $this->getOption('foreign_key.widget.class'),
      array(),
      array(),
      $this->getOption('foreign_key.validator.class'),
      array(),
      array()
    );
  }

}

$formBuilder = new myGeneratorFormBuilder();

class myGenerator implements sfIGenerator {

  protected $moduleName;

  public function generate($params = array())
  {
  }

  public function setModuleName($moduleName)
  {
    $this->moduleName = $moduleName;
  }

  public function getModuleName()
  {
    return $this->moduleName;
  }
}

$generator = new myGenerator();

$generator->setModuleName('myModule');

class myGeneratorModelColumnString extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    parent::__construct($generator, $name, $options, $flags);
    $this->isReal = true;
  }
  public function getType()
  {
    return 'string';
  }
}

class myGeneratorModelColumnStringLong extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    parent::__construct($generator, $name, $options, $flags);
    $this->isReal = true;
  }
  public function getType()
  {
    return 'string';
  }
  public function getLength()
  {
    return 1000;
  }
}

class myGeneratorModelColumnStringFixed extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    parent::__construct($generator, $name, $options, $flags);
    $this->isReal = true;
  }
  public function getType()
  {
    return 'string';
  }
  public function getLength()
  {
    return 2;
  }
  public function isFixedLength()
  {
    return true;
  }
}

class myGeneratorModelColumnStringText extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    parent::__construct($generator, $name, $options, $flags);
    $this->isReal = true;
  }
  public function getType()
  {
    return 'string';
  }
  public function getLength()
  {
    return 1000;
  }
}

class myGeneratorModelColumnPartial extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    $flags = array(
      '_'
    );
    parent::__construct($generator, $name, $options, $flags);
  }
}

class myGeneratorModelColumnComponent extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    $flags = array(
      '~'
    );
    parent::__construct($generator, $name, $options, $flags);
  }
}

class myGeneratorModelColumnBoolean extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    parent::__construct($generator, $name, $options, $flags);

  }

  public function isReal()
  {
    return true;
  }

  public function getType()
  {
    return 'boolean';
  }

}

class myGeneratorModelColumnInteger extends sfGeneratorColumn {

  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    parent::__construct($generator, $name, $options, $flags);

  }

  public function isReal()
  {
    return true;
  }

  public function getType()
  {
    return 'integer';
  }

  public function getLength()
  {
    return 4;
  }

}

class myGeneratorModelColumnFloat extends sfGeneratorColumn {

  public function isReal()
  {
    return true;
  }

  public function getType()
  {
    return 'float';
  }

  public function getLength()
  {
    return 4;
  }

}

class myGeneratorModelColumnDecimal extends myGeneratorModelColumnFloat {

  public function getType()
  {
    return 'decimal';
  }
}

class myGeneratorModelColumnDouble extends myGeneratorModelColumnFloat {

  public function getType()
  {
    return 'double';
  }
}

class myGeneratorModelColumnDate extends myGeneratorModelColumnString {

  public function getType()
  {
    return 'date';
  }
}

class myGeneratorModelColumnTimestamp extends myGeneratorModelColumnDate {

  public function getType()
  {
    return 'timestamp';
  }
}

class myGeneratorModelColumnTime extends myGeneratorModelColumnDate {

  public function getType()
  {
    return 'time';
  }
}

class myGeneratorModelColumnEnum extends myGeneratorModelColumnString {

  public function getType()
  {
    return 'enum';
  }

  public function getValues()
  {
    return array(
        'failed', 'success'
    );
  }

}

class myGeneratorModelColumnClob extends myGeneratorModelColumnString {
  public function getType()
  {
    return 'clob';
  }
}

class myGeneratorModelColumnBlob extends myGeneratorModelColumnString {
  public function getType()
  {
    return 'blob';
  }
}

class myGeneratorModelColumnObject extends myGeneratorModelColumnString {
  public function getType()
  {
    return 'object';
  }
}

class myGeneratorModelColumnArray extends myGeneratorModelColumnString {
  public function getType()
  {
    return 'array';
  }
}

class myGeneratorModelColumnGzip extends myGeneratorModelColumnString {
  public function getType()
  {
    return 'gzip';
  }
}

class myGeneratorModelColumnForeignKey extends myGeneratorModelColumnString {

  public function isForeignKey()
  {
    return true;
  }

  public function getType()
  {
    return 'integer';
  }
}

$widgets = array(
    'string' => array(
        'sfWidgetFormInputText',
        array(),
        array(),
        'sfValidatorString',
        array(),
        array()
    ),
    'stringLong' => array(
      'sfWidgetFormTextarea',
      array(),
      array(),
      'sfValidatorString',
      array(),
      array(),
   ),
    'stringFixed' => array(
      'sfWidgetFormInputText',
      array(),
      array(),
      'sfValidatorString',
      array(
        'min_length' => 2,
        'max_length' => 2,
      ),
      array(),
   ),
    'stringText' => array(
      'sfWidgetFormTextarea',
      array(),
      array(),
      'sfValidatorString',
      array(),
      array(),
   ),
    'boolean' => array(
      'sfWidgetFormInputCheckbox',
      array(
      ),
      array(),
      'sfValidatorBoolean',
      array(
      ),
      array(),
   ),
    'integer' => array(
      'sfWidgetFormInputText',
      array(
      ),
      array(),
      'sfValidatorInteger',
      array(
      ),
      array(),
   ),
    'float' => array(
      'sfWidgetFormInputText',
      array(
      ),
      array(),
      'sfValidatorNumber',
      array(
      ),
      array(),
   ),
    'decimal' => array(
      'sfWidgetFormInputText',
      array(
      ),
      array(),
      'sfValidatorNumber',
      array(
      ),
      array(),
   ),
    'double' => array(
      'sfWidgetFormInputText',
      array(
      ),
      array(),
      'sfValidatorNumber',
      array(
      ),
      array(),
   ),
    'date' => array(
      'sfWidgetFormDate',
      array(
        'rich' => true
      ),
      array(),
      'sfValidatorDate',
      array(
      ),
      array(),
   ),
    'timestamp' => array(
      'sfWidgetFormDateTime',
      array(
        'rich' => true
      ),
      array(),
      'sfValidatorDateTime',
      array(
      ),
      array(),
   ),
    'time' => array(
      'sfWidgetFormTime',
      array(
        'rich' => true
      ),
      array(),
      'sfValidatorTime',
      array(
      ),
      array(),
   ),
    'enum' => array(
      'sfWidgetFormChoice',
      array(
        'choices' => array('failed', 'success')
      ),
      array(),
      'sfValidatorChoice',
      array(
        'choices' => array('failed', 'success')
      ),
      array(),
    ),
    'clob' => array(
      'sfWidgetFormTextarea',
      array(),
      array(),
      'sfValidatorString',
      array(),
      array(),
    ),
    'blob' => array(
      'sfWidgetFormNoInput',
      array(),
      array(),
      'sfValidatorPass',
      array(),
      array(),
    ),
    'object' => array(
      'sfWidgetFormNoInput',
      array(),
      array(),
      'sfValidatorPass',
      array(),
      array(),
    ),
    'array' => array(
      'sfWidgetFormNoInput',
      array(),
      array(),
      'sfValidatorPass',
      array(),
      array(),
    ),
    'gzip' => array(
      'sfWidgetFormNoInput',
      array(),
      array(),
      'sfValidatorPass',
      array(),
      array(),
    ),
    'partial' => array(
      'sfWidgetFormPartial',
      array(
        'partial' => 'myModule/partial'
      ),
      array(
      ),
      'sfValidatorPass',
      array(),
      array(),
   ),
    'component' => array(
      'sfWidgetFormComponent',
      array(
        'component' => 'myModule/component'
      ),
      array(
      ),
      'sfValidatorPass',
      array(),
      array(),
   ),
    'foreignKey' => array(
      '_FOREIGN_',
      array(),
      array(),
      '_FOREIGN_',
      array(),
      array(),
   )
);

$options = array();

foreach($widgets as $widget => $expected)
{
  $class = sprintf('myGeneratorModelColumn%s', ucfirst($widget));

  $column = new $class($generator, $widget);

  $t->diag("column type: " . $column->getType());

  $t->isa_ok($formBuilder->getWidgetAndValidator($column, $options), 'array',
          'getWidgetAndValidator() returns array');

  $a = $formBuilder->getWidgetAndValidator($column, $options);

  $t->is(count($a), 6,
          'getWidgetAndValidator() returns array with 6 items in it');

  $t->is($a[0], $expected[0], sprintf('widget is %s', $expected[0]));
  $t->is($a[3], $expected[3], sprintf('validator is %s', $expected[3]));

  $t->is_deeply($a, $expected, 'Returned array of widget options, validator options and messages is ok');
}

