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
    // ------
    // Context wide settings
    // ------

    // edit context
    sfGenerator::CONTEXT_EDIT => array(
      sfGeneratorColumn::TYPE_STRING => array(),
      sfGeneratorColumn::TYPE_INTEGER => array(),
      sfGeneratorColumn::TYPE_FLOAT => array(),
      sfGeneratorColumn::TYPE_DOUBLE => array(),
      sfGeneratorColumn::TYPE_DECIMAL => array(),
      sfGeneratorColumn::TYPE_DATE => array(),
      sfGeneratorColumn::TYPE_TIME => array(),
      sfGeneratorColumn::TYPE_TIMESTAMP => array(),
      sfGeneratorColumn::TYPE_CLOB => array(),
      sfGeneratorColumn::TYPE_BLOB => array(),
      sfGeneratorColumn::TYPE_OBJECT => array(),
      sfGeneratorColumn::TYPE_ARRAY => array(),
      sfGeneratorColumn::TYPE_GZIP => array(),
      sfGeneratorColumn::TYPE_BIT => array(),
    ),

    // global wide settings
    //
    // date column options
    sfGeneratorColumn::TYPE_DATE => array(
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
    sfGeneratorColumn::TYPE_TIMESTAMP => array(
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
    sfGeneratorColumn::TYPE_TIME => array(
      'widget' => array(
        'class' => 'sfWidgetFormTime',
        'options' => array(
          'rich' => true
        )
      ),
      'validator' => array(
        'class' => 'sfValidatorTime',
      )
    )
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
   * @param sfIGeneratorColumn $column Column
   * @param string $context Context (edit, list, filter, create, ...)
   * @return array
   * @throws RuntimeException If column cannot be handled. (Unknown type)
   */
  public function getWidgetAndValidator(sfIGeneratorColumn $column, $context = 'edit')
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
        case sfGeneratorColumn::TYPE_BOOLEAN:
          return $this->getWidgetAndValidatorBoolean($column, $context);
          break;

        case sfGeneratorColumn::TYPE_STRING:
          return $this->getWidgetAndValidatorString($column, $context);
          break;

        case sfGeneratorColumn::TYPE_INTEGER:
          return $this->getWidgetAndValidatorInteger($column, $context);
          break;

        case sfGeneratorColumn::TYPE_DATE:
          return $this->getWidgetAndValidatorDate($column, $context);
          break;

        case sfGeneratorColumn::TYPE_TIMESTAMP:
          return $this->getWidgetAndValidatorTimestamp($column, $context);
          break;

        case sfGeneratorColumn::TYPE_TIME:
          return $this->getWidgetAndValidatorTime($column, $context);
          break;

        case sfGeneratorColumn::TYPE_ENUM:
          return $this->getWidgetAndValidatorEnum($column, $context);
          break;

        case sfGeneratorColumn::TYPE_FLOAT:
          return $this->getWidgetAndValidatorFloat($column, $context);
          break;

        case sfGeneratorColumn::TYPE_DOUBLE:
          return $this->getWidgetAndValidatorDouble($column, $context);
          break;

        case sfGeneratorColumn::TYPE_DECIMAL:
          return $this->getWidgetAndValidatorDecimal($column, $context);
          break;

        case sfGeneratorColumn::TYPE_CLOB:
          return $this->getWidgetAndValidatorClob($column, $context);
          break;

        case sfGeneratorColumn::TYPE_BLOB:
          return $this->getWidgetAndValidatorBlob($column, $context);
          break;

        case sfGeneratorColumn::TYPE_OBJECT:
          return $this->getWidgetAndValidatorObject($column, $context);
          break;

        case sfGeneratorColumn::TYPE_ARRAY:
          return $this->getWidgetAndValidatorArray($column, $context);
          break;

        case sfGeneratorColumn::TYPE_GZIP:
          return $this->getWidgetAndValidatorGzip($column, $context);
          break;

        case sfGeneratorColumn::TYPE_BIT:
          return $this->getWidgetAndValidatorBit($column, $context);
          break;

        default:
          throw new RuntimeException(sprintf('Not implemented for "%s"', $column->getType()));
          break;
      }
    }

    // FIXME: return widget for string type?
    throw new RuntimeException('Non handled type of column.');
  }

  /**
   * Returns widget and validator for string column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorString(sfIGeneratorColumn $column, $context)
  {
    $widgetClass = $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_STRING, $context, 'sfWidgetFormInputText');
    $widgetOptions = $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_STRING, $context, array());
    $widgetAttributes = $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_STRING, $context, array());
    $validatorClass = $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_STRING, $context, 'sfValidatorString');
    $validatorOptions = $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_STRING, $context, array());
    $validatorMessages = $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_STRING, $context, array());

    $length = $column->getLength();

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
    elseif($column->isRegularExpression() &&
      ($regexp = $column->getRegularExpression()))
    {
      $validatorOptions['pattern'] = $regexp;
      $validatorClass = 'Regex';
    }

    return array(
      $widgetClass, $widgetOptions, $widgetAttributes,
      $validatorClass, $validatorOptions, $validatorMessages
    );
  }

  /**
   * Returns widget and validator for boolean column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorBoolean(sfIGeneratorColumn $column, $context)
  {
    switch($context)
    {
      case sfGenerator::CONTEXT_FILTER:
        // return defaults but without fallback to column type!
        return array(
          $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_BOOLEAN, $context, 'sfWidgetFormChoice', false),
          array_merge($this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_BOOLEAN, $context, array(), false),
            array(
              'choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no')
            )),
          $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_BOOLEAN, $context, array(), false),
          $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_BOOLEAN, $context, 'sfValidatorInteger', false),
          array_merge($this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_BOOLEAN, $context, array(), false),
            array(
              'choices' => array('', 0, 1)
            )),
          $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_BOOLEAN, $context, array(), false)
        );
      break;

      // default context, search context wide settings
      default:
        return array(
          $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_BOOLEAN, $context, 'sfWidgetFormInputCheckbox'),
          $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_BOOLEAN, $context, array()),
          $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_BOOLEAN, $context, array()),
          $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_BOOLEAN, $context, 'sfValidatorBoolean'),
          $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_BOOLEAN, $context, array()),
          $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_BOOLEAN, $context, array())
        );
        break;
    }
  }

  /**
   * Returns widget and validator for integer column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorInteger(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_INTEGER, $context, 'sfWidgetFormInputText'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_INTEGER, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_INTEGER, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_INTEGER, $context, 'sfValidatorInteger'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_INTEGER, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_INTEGER, $context, array())
    );
  }

  /**
   * Returns widget and validator for float column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorFloat(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_FLOAT, $context, 'sfWidgetFormInputText'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_FLOAT, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_FLOAT, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_FLOAT, $context, 'sfValidatorNumber'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_FLOAT, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_FLOAT, $context, array())
    );
  }

  /**
   * Returns widget and validator for decimal type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorDecimal(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_DECIMAL, $context, 'sfWidgetFormInputText'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_DECIMAL, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_DECIMAL, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_DECIMAL, $context, 'sfValidatorNumber'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_DECIMAL, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_DECIMAL, $context, array())
    );
  }

  /**
   * Returns widget and validator for double type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorDouble(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_DOUBLE, $context, 'sfWidgetFormInputText'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_DOUBLE, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_DOUBLE, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_DOUBLE, $context, 'sfValidatorNumber'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_DOUBLE, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_DOUBLE, $context, array())
    );
  }

  /**
   * Returns widget and validator for date type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorDate(sfIGeneratorColumn $column, $context)
  {
    $widgetClass = $this->getOption(sprintf('%s.widget.class',  sfGeneratorColumn::TYPE_DATE), 'sfWidgetFormDate');
    $widgetOptions = $this->getOption(sprintf('%s.widget.options',  sfGeneratorColumn::TYPE_DATE), array());
    $widgetAttributes = $this->getOption(sprintf('%s.widget.attributes',  sfGeneratorColumn::TYPE_DATE), array());

    switch($context)
    {
      // filter context
      case sfGenerator::CONTEXT_FILTER:
        // return defaults but without fallback to column type!
        return array(
          $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_DATE, $context, 'sfWidgetFormDateFilter', false),
          array_merge($this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_DATE, $context, array(), false),
            array(
              'from_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
              'to_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
            )),
          $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_DATE, $context, array(), false),
          $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_DATE, $context, 'sfValidatorDateRange', false),
          $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_DATE, $context, array(), false),
          $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_DATE, $context, array(), false)
        );
      break;

      // default context,
      // search context wide settings
      default:
        return array(
          $widgetClass,
          $widgetOptions,
          $widgetAttributes,
          $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_DATE, $context, 'sfValidatorDate'),
          $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_DATE, $context, array()),
          $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_DATE, $context, array())
        );
        break;
    }
  }

  /**
   * Returns widget and validator for timestamp type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorTimestamp(sfIGeneratorColumn $column, $context)
  {
    $widgetClass = $this->getOption(sprintf('%s.widget.class',  sfGeneratorColumn::TYPE_TIMESTAMP), 'sfWidgetFormDateTime');
    $widgetOptions = $this->getOption(sprintf('%s.widget.options',  sfGeneratorColumn::TYPE_TIMESTAMP), array());
    $widgetAttributes = $this->getOption(sprintf('%s.widget.attributes',  sfGeneratorColumn::TYPE_TIMESTAMP), array());

    // manage contexts
    switch($context)
    {
      // in filter context, we want to display
      // from: [] to: [] filters
      case sfGenerator::CONTEXT_FILTER:
        return array(
          $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_TIMESTAMP, $context, 'sfWidgetFormDateTimeFilter', false),
          array_merge($this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_TIMESTAMP, $context, array(), false),
            array(
              'from_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
              'to_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
              'from_time' => new sfPhpExpression(sprintf('new %s(%s, %s)', $this->getOption('time.widget.class', 'sfWidgetFormDateTime'), $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
              'to_time' => new sfPhpExpression(sprintf('new %s(%s, %s)', $this->getOption('time.widget.class', 'sfWidgetFormDateTime'), $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
            )),
          $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_TIMESTAMP, $context, array(), false),
          $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_TIMESTAMP, $context, 'sfValidatorDateTimeRange', false),
          $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_TIMESTAMP, $context, array(), false),
          $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_TIMESTAMP, $context, array(), false)
        );
        break;
    }

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_TIMESTAMP, $context, 'sfValidatorDateTime'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_TIMESTAMP, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_TIMESTAMP, $context, array())
    );
  }

  /**
   * Returns widget and validator for time type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context Generator context (edit, list...)
   * @return array
   */
  protected function getWidgetAndValidatorTime(sfIGeneratorColumn $column, $context)
  {
    $widgetClass = $this->getOption(sprintf('%s.widget.class',  sfGeneratorColumn::TYPE_TIME), 'sfWidgetFormTime');
    $widgetOptions = $this->getOption(sprintf('%s.widget.options',  sfGeneratorColumn::TYPE_TIME), array());
    $widgetAttributes = $this->getOption(sprintf('%s.widget.attributes',  sfGeneratorColumn::TYPE_TIME), array());

    // manage contexts
    switch($context)
    {
      // in filter context, we want to display
      // from: [] to: [] filters
      case sfGenerator::CONTEXT_FILTER:
        return array(
          $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_TIMEP, $context, 'sfWidgetFormTimeFilter', false),
          array_merge($this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_TIME, $context, array(), false),
            array(
              'from_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
              'to_date' => new sfPhpExpression(sprintf('new %s(%s, %s)', $widgetClass, $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
              'from_time' => new sfPhpExpression(sprintf('new %s(%s, %s)', $this->getOption('time.widget.class', 'sfWidgetFormTime'), $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
              'to_time' => new sfPhpExpression(sprintf('new %s(%s, %s)', $this->getOption('time.widget.class', 'sfWidgetFormTime'), $this->varExport($widgetOptions), $this->varExport($widgetAttributes))),
            )),
          $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_TIME, $context, array(), false),
          $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_TIME, $context, 'sfValidatorTimeRange', false),
          $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_TIME, $context, array(), false),
          $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_TIME, $context, array(), false)
        );
        break;
    }

    return array(
      $widgetClass,
      $widgetOptions,
      $widgetAttributes,
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_TIME, $context, 'sfValidatorTime'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_TIME, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_TIME, $context, array())
    );
  }

  /**
   * Returns widget and validator for enum column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorEnum(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_ENUM, $context, 'sfWidgetFormChoice'),
      array_merge($this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_ENUM, $context, array()),
        array(
          'choices' => $column->getValues()
        )
      ),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_ENUM, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_ENUM, $context, 'sfValidatorChoice'),
      array_merge($this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_ENUM, $context, array()),
        array(
          'choices' => $column->getValues()
        )
      ),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_ENUM, $context, array())
    );
  }

  /**
   * Returns widget and validator for clob column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorClob(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_CLOB, $context, 'sfWidgetFormTextarea'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_CLOB, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_CLOB, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_CLOB, $context, 'sfValidatorString'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_CLOB, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_CLOB, $context, array())
    );
  }

  /**
   * Returns widget and validator for blob column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorBlob(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_BLOB, $context, 'sfWidgetFormNoInput'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_BLOB, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_BLOB, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_BLOB, $context, 'sfValidatorPass'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_BLOB, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_BLOB, $context, array())
    );
  }

  /**
   * Returns widget and validator for partial column type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorPartial(sfIGeneratorColumn $column, $context)
  {
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_PARTIAL, $context, 'sfWidgetFormPartial'),
      array_merge($this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_PARTIAL, $context, array()),
        array(
          'partial' => sprintf('%s/%s', $column->getGenerator()->getModuleName(), $column->getName())
        )),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_PARTIAL, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_PARTIAL, $context, 'sfValidatorPass'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_PARTIAL, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_PARTIAL, $context, array())
    );
  }

  /**
   * Returns widget and validator for component type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorComponent(sfIGeneratorColumn $column, $context)
  {
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_COMPONENT, $context, 'sfWidgetFormComponent'),
      array_merge($this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_COMPONENT, $context, array(
      )), array(
        'component' => sprintf('%s/%s', $column->getGenerator()->getModuleName(), $column->getName())
      )),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_COMPONENT, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_COMPONENT, $context, 'sfValidatorPass'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_COMPONENT, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_COMPONENT, $context, array())
    );
  }

  /**
   * Returns widget and validator for object type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorObject(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_OBJECT, $context, 'sfWidgetFormNoInput'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_OBJECT, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_OBJECT, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_OBJECT, $context, 'sfValidatorPass'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_OBJECT, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_OBJECT, $context, array())
    );
  }

  /**
   * Returns widget and validator for array type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorArray(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_ARRAY, $context, 'sfWidgetFormNoInput'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_ARRAY, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_ARRAY, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_ARRAY, $context, 'sfValidatorPass'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_ARRAY, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_ARRAY, $context, array())
    );
  }

  /**
   * Returns widget and validator for gzip type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorGzip(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_GZIP, $context, 'sfWidgetFormNoInput'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_GZIP, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_GZIP, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_GZIP, $context, 'sfValidatorPass'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_GZIP, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_GZIP, $context, array())
    );
  }

  /**
   * Returns widget and validator for bit type of column
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  protected function getWidgetAndValidatorBit(sfIGeneratorColumn $column, $context)
  {
    // return defaults
    return array(
      $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_BIT, $context, 'sfWidgetFormNoInput'),
      $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_BIT, $context, array()),
      $this->getOptionFor('widget.attributes', sfGeneratorColumn::TYPE_BIT, $context, array()),
      $this->getOptionFor('validator.class', sfGeneratorColumn::TYPE_BIT, $context, 'sfValidatorPass'),
      $this->getOptionFor('validator.options', sfGeneratorColumn::TYPE_BIT, $context, array()),
      $this->getOptionFor('validator.messages', sfGeneratorColumn::TYPE_BIT, $context, array())
    );
  }

  /**
   * Returns widget and validator for foreign key type
   *
   * @param sfIGeneratorColumn $column
   * @param string $context
   * @return array
   */
  abstract protected function getWidgetAndValidatorForeignKey(sfIGeneratorColumn $column, $context);

  /**
   * Returns option for given $key. Searches in context wide settings and than in widget type specific
   * setting.
   *
   * <code>
   * $widgetClass = $this->getOptionFor('widget.class', sfGeneratorColumn::TYPE_DATE, sfGenerator::CONTEXT_EDIT, 'sfWidgetFormDate');
   *
   * // Will search in $defaultOptions array as follows:
   * // context -> edit -> date -> widget -> class
   * // If nothing is found, returns default value
   *
   * $widgetOptions = $this->getOptionFor('widget.options', sfGeneratorColumn::TYPE_DATE, sfGenerator::CONTEXT_EDIT, array());
   *
   * </code>
   *
   * @param string $key Key like widget.class
   * @param string $columnType sfGeneratorColumn::TYPE_* constant
   * @param string $context Context
   * @param mixed $default Default value nor global nor context wide setting was not found
   * @param boolean $fallback Fallback to column type setting? Ie search only in context wide setting?
   * @return mixed
   */
  protected function getOptionFor($key, $columnType, $context, $default, $fallback = true)
  {
    if($fallback)
    {
      return $this->getOption(sprintf('%s.%s.%s', $context, $columnType, $key),
             $this->getOption(sprintf('%s.%s', $columnType, $key), $default));
    }

    // no search in column type setting, just in context wide setting
    return $this->getOption(sprintf('%s.%s.%s', $context, $columnType, $key), $default);
  }

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
