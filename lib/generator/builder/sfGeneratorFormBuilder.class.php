<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGeneratorFormBuilder can build forms based on the model definition
 *
 * @package    Sift
 * @subpackage form
 */
abstract class sfGeneratorFormBuilder extends sfConfigurable {

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(



    // date column options
    'date' => array(
      'widget' => array(
        'class' => 'sfWidgetFormDate',
        'options' => array(
          'rich' => true
        )
      ),
      'validator' => array(
        'class' => 'sfValidatorDate',
      )
    ),
    // timestamp column type options
    'timestamp' => array(
      'widget' => array(
        'class' => 'sfWidgetFormDateTime',
        'options' => array(
          'rich' => true
        )
      ),
      'validator' => array(
        'class' => 'sfValidatorDateTime',
      )
    ),
    'time' => array(
      'widget' => array(
        'class' => 'sfWidgetFormTime',
        'options' => array(
          'rich' => true
        )
      ),
      'validator' => array(
        'class' => 'sfValidatorTime',
      )
    ),

  );

  /**
   * Constructs the builder
   *
   * @param array $options Array of options
   *
   */
  public function __construct($options = array())
  {
    parent::__construct($options);
  }

  /**
   * Returns widget class, options, attributes and also validator class, options and messages for given $column
   *
   * @param sfGeneratorModelColumn $column Column
   * @param string $context Context (edit, list, filter, create, ...)
   * @return array
   * @throws RuntimeException
   */
  public function getWidgetAndValidator(sfGeneratorModelColumn $column, $context = 'edit')
  {
    if($column->isPartial())
    {
      return $this->getWidgetAndValidatorPartial($column, $context);
    }
    elseif($column->isComponent())
    {
      return $this->getWidgetAndValidatorComponent($column, $context);
    }
    elseif($column->isForeignKey())
    {
      return $this->getWidgetAndValidatorForeignKey($column, $context);
    }
    // we have real column
    elseif($column->isReal())
    {
      switch($column->getType())
      {
        case 'boolean':
          return $this->getWidgetAndValidatorBoolean($column, $context);
          break;

        case 'string':
          return $this->getWidgetAndValidatorString($column, $context);
          break;

        case 'integer':
          return $this->getWidgetAndValidatorInteger($column, $context);
          break;

        case 'date':
          return $this->getWidgetAndValidatorDate($column, $context);
          break;

        case 'timestamp':
          return $this->getWidgetAndValidatorTimestamp($column, $context);
          break;

        case 'time':
          return $this->getWidgetAndValidatorTime($column, $context);
          break;

        case 'enum':
          return $this->getWidgetAndValidatorEnum($column, $context);
          break;

        case 'float':
          return $this->getWidgetAndValidatorFloat($column, $context);
          break;

        case 'double':
          return $this->getWidgetAndValidatorDouble($column, $context);
          break;

        case 'decimal':
          return $this->getWidgetAndValidatorDecimal($column, $context);
          break;

        case 'clob':
          return $this->getWidgetAndValidatorClob($column, $context);
          break;

        case 'blob':
          return $this->getWidgetAndValidatorBlob($column, $context);
          break;

        case 'object':
          return $this->getWidgetAndValidatorObject($column, $context);
          break;

        case 'array':
          return $this->getWidgetAndValidatorArray($column, $context);
          break;

        case 'gzip':
          return $this->getWidgetAndValidatorGzip($column, $context);
          break;

        default:
          throw new Exception(sprintf('Not implemented for "%s"', $column->getType()));
          break;
      }
    }

    // FIXME: return widget for string type?
    throw new RuntimeException('Non handled type of column.');
  }

  /**
   * Returns widget and validator for string column type
   *
   * @param sfGeneratorModelColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorString(sfGeneratorModelColumn $column, $context)
  {
    $widgetClass = $this->getOption('string.widget.class', 'sfWidgetFormInputText');
    $widgetOptions = $this->getOption('string.widget.options', array());
    $widgetAttributes = $this->getOption('string.widget.attributes', array());

    $validatorClass = $this->getOption('string.validator.class', 'sfValidatorString');
    $validatorOptions = $this->getOption('string.validator.options', array());
    $validatorMessages = $this->getOption('string.validator.messages', array());

    $length = $column->getLength();

    switch($context)
    {
      // edit and create
      case 'edit':
      case 'create':

      default:

        if($length > 255)
        {
          $widgetClass = 'sfWidgetFormTextarea';
        }

        // we have it fixed to some length
        if($length && $column->isFixedLength())
        {
          $validatorOptions['min_length'] = $length;
          $validatorOptions['max_length'] = $length;
        }
        elseif($minLength = $column->getMinLength())
        {
          $validatorOptions['min_length'] = $minLength;
        }
        elseif($maxLength = $column->getMaxLength())
        {
          $validatorOptions['max_length'] = $maxLength;
        }
        elseif($length && $length < 1000)
        {
          $validatorOptions['max_length'] = $length;
        }

        // validator
        if($column->isEmail())
        {
          $validatorClass = 'Email';
        }
        elseif($column->isRegularExpression() && ($regexp = $column->getRegularExpression()))
        {
          $validatorOptions['pattern'] = $regexp;
          $validatorClass = 'Regex';
        }

        break;

      case 'filter':
        $widgetClass = 'sfWidgetFormInputText';
        $validatorClass = 'sfValidatorString';
        break;

      // list context, no input
      case 'list':
        $widgetClass = 'sfWidgetFormNoInput';
        $validatorClass = 'sfValidatorPass';
        break;
    }

    return array(
      $widgetClass, $widgetOptions, $widgetAttributes,
      $validatorClass, $validatorOptions, $validatorMessages
    );
  }


  /**
   * Returns widget and validator for boolean type
   *
   *
   */
  protected function getWidgetAndValidatorBoolean(sfGeneratorModelColumn $column, $context)
  {
    $widgetOptions = $widgetAttributes =
      $validatorOptions = $validatorMessages = array();

    switch($context)
    {
      // filter context
      case 'filter':
        $widgetSubclass = 'Choice';
        $validatorSubclass = 'Choice';
        $widgetOptions['choices'] = array('' => 'yes or no', 1 => 'yes', 0 => 'no');
        $validatorOptions['choices'] = array_keys($widgetOptions['choices']);
        break;

      default:
        $widgetSubclass = 'InputCheckbox';
        $validatorSubclass = 'Boolean';
        break;
    }

    return array(
      sprintf('sfWidgetForm%s', $widgetSubclass),
      $widgetOptions,
      $widgetAttributes,
      sprintf('sfValidator%s', $validatorSubclass),
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorInteger(sfGeneratorModelColumn $column, $context)
  {
    $widgetSubclass = 'InputText';
    $widgetOptions = $widgetAttributes =
      $validatorOptions = $validatorMessages = array();
    $validatorSubclass = 'Integer';

    // FIXME: what about min and max?
    return array(
      sprintf('sfWidgetForm%s', $widgetSubclass),
      $widgetOptions,
      $widgetAttributes,
      sprintf('sfValidator%s', $validatorSubclass),
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorFloat(sfGeneratorModelColumn $column, $context)
  {
    return $this->getWidgetAndValidatorNumber($column, $context);
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorDecimal(sfGeneratorModelColumn $column, $context)
  {
    return $this->getWidgetAndValidatorNumber($column, $context);
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorDouble(sfGeneratorModelColumn $column, $context)
  {
    return $this->getWidgetAndValidatorNumber($column, $context);
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorDate(sfGeneratorModelColumn $column, $context)
  {
    $widgetClass = $this->getOption('date.widget.class', 'sfWidgetFormDate');
    $widgetOptions = $this->getOption('date.widget.options', array());
    $widgetAttributes = $this->getOption('date.widget.attributes', array());

    $validatorClass = $this->getOption('date.validator.class', 'sfValidatorDate');
    $validatorOptions = $this->getOption('date.validator.options', array());
    $validatorMessages = $this->getOption('date.validator.messages', array());

    // manage contexts
    switch($context)
    {
      case 'edit':
      case 'create':

        break;

      // in filter context, we want to display
      // from: [] to: [] filters
      case 'filter':

        $validatorClass = 'sfValidatorDateRange';

        $widgetOptions = $validatorOptions = array(
          'from_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
          'to_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
        );

        // we will use filter widget
        $widgetClass = 'sfWidgetFormDateFilter';

        break;
    }

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorTimestamp(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('timestamp.widget.class', 'sfWidgetFormDateTime');
    $widgetOptions = $this->getOption('timestamp.widget.options', array());
    $widgetAttributes = $this->getOption('timestamp.widget.attributes', array());

    $validatorClass = $this->getOption('timestamp.validator.class', 'sfValidatorDateTime');
    $validatorOptions = $this->getOption('timestamp.validator.options', array());
    $validatorMessages = $this->getOption('timestamp.validator.messages', array());

    // manage contexts
    switch($context)
    {
      case 'edit':
      case 'create':

        break;

      // in filter context, we want to display
      // from: [] to: [] filters
      case 'filter':

        $validatorClass = 'sfValidatorDateTimeRange';

        $widgetOptions = $validatorOptions = array(
          'from_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
          'to_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
          'from_time' => new sfPhpExpression(sprintf('new %s(%s, %s)', $this->getOption('time.widget.class', 'sfWidgetFormTime'), $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
          'to_time' => new sfPhpExpression(sprintf('new %s(%s, %s)', $this->getOption('time.widget.class', 'sfWidgetFormTime'), $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
        );

        // we will use filter widget
        // FIXME: this validator does not exist!
        $widgetClass = 'sfWidgetFormDateTimeFilter';

        break;
    }

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorTime(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('time.widget.class', 'sfWidgetFormTime');
    $widgetOptions = $this->getOption('time.widget.options', array());
    $widgetAttributes = $this->getOption('time.widget.attributes', array());

    $validatorClass = $this->getOption('time.validator.class', 'sfValidatorTime');
    $validatorOptions = $this->getOption('time.validator.options', array());
    $validatorMessages = $this->getOption('time.validator.messages', array());

    // manage contexts
    switch($context)
    {
      case 'edit':
      case 'create':

        break;

      // in filter context, we want to display
      // from: [] to: [] filters
      case 'filter':

        $validatorClass = 'sfValidatorTimeRange';
        // we will use filter widget
        $widgetClass = 'sfWidgetFormTime';

        break;
    }

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorEnum(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('enum.widget.class', 'sfWidgetFormChoice');
    $widgetOptions = $this->getOption('enum.widget.options', array());
    $widgetAttributes = $this->getOption('enum.widget.attributes', array());

    $validatorClass = $this->getOption('enum.validator.class', 'sfValidatorChoice');
    $validatorOptions = $this->getOption('enum.validator.options', array());
    $validatorMessages = $this->getOption('enum.validator.messages', array());

    $validatorOptions['choices'] = $widgetOptions['choices'] = $column->getValues();

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for clob
   *
   */
  protected function getWidgetAndValidatorClob(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('clob.widget.class', 'sfWidgetFormTextarea');
    $widgetOptions = $this->getOption('clob.widget.options', array());
    $widgetAttributes = $this->getOption('clob.widget.attributes', array());
    $validatorClass = $this->getOption('clob.validator.class', 'sfValidatorString');
    $validatorOptions = $this->getOption('clob.validator.options', array());
    $validatorMessages = $this->getOption('clob.validator.messages', array());

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for clob
   *
   */
  protected function getWidgetAndValidatorBlob(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('blob.widget.class', 'sfWidgetFormNoInput');
    $widgetOptions = $this->getOption('blob.widget.options', array());
    $widgetAttributes = $this->getOption('blob.widget.attributes', array());
    $validatorClass = $this->getOption('blob.validator.class', 'sfValidatorPass');
    $validatorOptions = $this->getOption('blob.validator.options', array());
    $validatorMessages = $this->getOption('blob.validator.messages', array());

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for clob
   *
   */
  protected function getWidgetAndValidatorObject(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('object.widget.class', 'sfWidgetFormNoInput');
    $widgetOptions = $this->getOption('object.widget.options', array());
    $widgetAttributes = $this->getOption('object.widget.attributes', array());
    $validatorClass = $this->getOption('object.validator.class', 'sfValidatorPass');
    $validatorOptions = $this->getOption('object.validator.options', array());
    $validatorMessages = $this->getOption('object.validator.messages', array());

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for clob
   *
   */
  protected function getWidgetAndValidatorArray(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('array.widget.class', 'sfWidgetFormNoInput');
    $widgetOptions = $this->getOption('array.widget.options', array());
    $widgetAttributes = $this->getOption('array.widget.attributes', array());
    $validatorClass = $this->getOption('array.validator.class', 'sfValidatorPass');
    $validatorOptions = $this->getOption('array.validator.options', array());
    $validatorMessages = $this->getOption('array.validator.messages', array());

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for clob
   *
   */
  protected function getWidgetAndValidatorGzip(sfGeneratorModelColumn $column, $context)
  {
    // return defaults
    $widgetClass = $this->getOption('gzip.widget.class', 'sfWidgetFormNoInput');
    $widgetOptions = $this->getOption('gzip.widget.options', array());
    $widgetAttributes = $this->getOption('gzip.widget.attributes', array());
    $validatorClass = $this->getOption('gzip.validator.class', 'sfValidatorPass');
    $validatorOptions = $this->getOption('gzip.validator.options', array());
    $validatorMessages = $this->getOption('gzip.validator.messages', array());

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $validatorClass,
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for integer type
   *
   *
   */
  protected function getWidgetAndValidatorNumber(sfGeneratorModelColumn $column, $context)
  {
    $widgetSubclass = 'InputText';
    $widgetOptions = $widgetAttributes =
      $validatorOptions = $validatorMessages = array();
    $validatorSubclass = 'Number';

    // data input mask HTML5 attribute
    // $widgetAttributes['data-input-mask'] = '9';
    // FIXME: what about min and max?
    return array(
      sprintf('sfWidgetForm%s', $widgetSubclass),
      $widgetOptions,
      $widgetAttributes,
      sprintf('sfValidator%s', $validatorSubclass),
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for string column type
   *
   */
  protected function getWidgetAndValidatorPartial(sfGeneratorModelColumn $column, $context)
  {
    $widgetSubclass = 'Partial';

    $widgetOptions = $widgetAttributes =
      $validatorOptions = $validatorMessages = array();

    $widgetOptions['partial'] = sprintf('%s/%s', $column->getGenerator()->getModuleName(), $column->getName());

    $validatorSubclass = 'Pass';

    return array(
      sprintf('sfWidgetForm%s', $widgetSubclass),
      $widgetOptions,
      $widgetAttributes,
      sprintf('sfValidator%s', $validatorSubclass),
      $validatorOptions,
      $validatorMessages
    );
  }

  /**
   * Returns widget and validator for string column type
   *
   */
  protected function getWidgetAndValidatorComponent(sfGeneratorModelColumn $column, $context)
  {
    $widgetSubclass = 'Component';

    $widgetOptions = $widgetAttributes =
      $validatorOptions = $validatorMessages = array();

    $widgetOptions['component'] = sprintf('%s/%s', $column->getGenerator()->getModuleName(), $column->getName());

    $validatorSubclass = 'Pass';

    return array(
      sprintf('sfWidgetForm%s', $widgetSubclass),
      $widgetOptions,
      $widgetAttributes,
      sprintf('sfValidator%s', $validatorSubclass),
      $validatorOptions,
      $validatorMessages
    );
  }

  abstract protected function getWidgetAndValidatorForeignKey(sfGeneratorModelColumn $column, $context);

  /**
   * Exports variable to string
   *
   * @param mixed $var
   * @param boolean $expressions Parse sfPhpExpression objects?
   * @return string
   */
  protected function varExport($var, $expressions = true)
  {
    return sfToolkit::varExport($var, $expressions);
  }

}
