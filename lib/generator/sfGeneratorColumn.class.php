<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for all model columns
 *
 * @package    Sift
 * @subpackage generator
 */
abstract class sfGeneratorColumn extends sfConfigurable implements sfIGeneratorColumn {

  /**
   * String type
   */
  const TYPE_STRING = 'string';

  /**
   * String type
   */
  const TYPE_INTEGER = 'integer';

  /**
   * Boolean type
   */
  const TYPE_BOOLEAN = 'boolean';

  /**
   * Date type
   */
  const TYPE_DATE = 'date';

  /**
   * Time type
   */
  const TYPE_TIME = 'time';

  /**
   * Timestamp type
   */
  const TYPE_TIMESTAMP = 'timestamp';

  /**
   * Enum type
   */
  const TYPE_ENUM = 'enum';

  /**
   * Float type
   */
  const TYPE_FLOAT = 'float';

  /**
   * Decimal type
   */
  const TYPE_DECIMAL = 'decimal';

  /**
   * Double type
   */
  const TYPE_DOUBLE = 'double';

  /**
   * Clob type
   */
  const TYPE_CLOB = 'clob';

  /**
   * Blob type
   */
  const TYPE_BLOB = 'blob';

  /**
   * Object type
   */
  const TYPE_OBJECT = 'object';

  /**
   * Array type
   */
  const TYPE_ARRAY = 'array';

  /**
   * Gzip type
   */
  const TYPE_GZIP = 'gzip';

  /**
   * Bit type
   */
  const TYPE_BIT = 'bit';

  /**
   * Partial type
   */
  const TYPE_PARTIAL = 'partial';

  /**
   * Component type
   */
  const TYPE_COMPONENT = 'component';

  /**
   * Column name
   *
   * @var string
   */
  protected $name;

  /**
   * Column help
   *
   * @var string
   */
  protected $help;

  /**
   * Column flags
   *
   * @var array
   */
  protected $flags = array();

  /**
   * Is the column real column?
   *
   * @var boolean
   */
  protected $isReal = false;

  /**
   * Is primary key?
   *
   * @var boolean
   */
  protected $isPrimaryKey = false;

  /**
   * Is foreign key?
   *
   * @var boolean
   */
  protected $isForeignKey = false;

  /**
   * Foreign class name
   *
   * @var string
   */
  protected $foreignClassName;

  /**
   * Is relation alias?
   *
   * @var boolean
   */
  protected $isRelationAlias = false;

  /**
   * Renderer callback
   *
   * @var mixed
   */
  protected $renderer;

  /**
   * Array of renderer arguments
   *
   * @var array
   */
  protected $rendererArguments = array();

  /**
   * sfGenerator holder
   *
   * @var sfIGenerator
   */
  protected $generator;

  /**
   * Contructs the column
   *
   * @param string $name
   * @param array $flags
   */
  public function __construct(sfIGenerator $generator, $name, $options = array(), $flags = array())
  {
    $this->generator = $generator;
    $this->name = $name;

    $this->setFlags($flags);

    parent::__construct($options);
  }

  /**
   * Setups the column
   */
  public function setup()
  {
  }

  /**
   * Returns generator instance
   *
   * @return sfIGenerator
   */
  public function getGenerator()
  {
    return $this->generator;
  }

  /**
   * Sets generator
   *
   * @param sfIGenerator $generator
   */
  public function setGenerator(sfIGenerator $generator)
  {
    $this->generator = $generator;
  }

  /**
   * Returns name of the column
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns display name.
   *
   * @return string
   */
  public function getDisplayName()
  {
    if($name = $this->getOption('name'))
    {
      return $name;
    }
    return str_replace('_', ' ', ucfirst($this->name));
  }

  /**
   * Returns help for this column
   *
   * @return string
   */
  public function getHelp()
  {
    return $this->getOption('help');
  }

  /**
   * Returns true if the column maps a database column.
   *
   * @return boolean true if the column maps a database column, false otherwise
   */
  public function isReal()
  {
    return $this->isReal;
  }

  /**
   * Returns true if the column is a primary key.
   *
   * @return boolean true if the column is a primary key
   */
  public function isPrimaryKey()
  {
    return $this->isPrimaryKey;
  }

  /**
   * Returns true if this column is a foreign key and false if it is not
   *
   * @return boolean
   */
  public function isForeignKey()
  {
    return $this->isForeignKey;
  }

  /**
   * Returns true if this column is a relation alias
   *
   * @return boolean
   */
  public function isRelationAlias()
  {
    return $this->isRelationAlias;
  }

  /**
   * Returns true if the column is a partial.
   *
   * @return boolean true if the column is a partial, false otherwise
   */
  public function isPartial()
  {
    return in_array('_', $this->flags) ? true : false;
  }

  /**
   * Returns true if the column is a component.
   *
   * @return boolean true if the column is a component, false otherwise
   */
  public function isComponent()
  {
    return in_array('~', $this->flags) ? true : false;
  }

  /**
   * Returns true if the column has a link.
   *
   * @return boolean true if the column has a link, false otherwise
   */
  public function isLink()
  {
    return (in_array('=', $this->flags) || $this->isPrimaryKey()) ? true : false;
  }

  /**
   * Sets flags
   *
   * @param array|string $flags
   */
  public function setFlags($flags)
  {
    if(!is_array($flags))
    {
      $flags = array($flags);
    }

    $this->flags = $flags;
  }

  /**
   * Returns an array of flags
   *
   * @return array
   */
  public function getFlags()
  {
    return $this->flags;
  }

  /**
   * Sets the list renderer for the field.
   *
   * @param mixed $renderer A PHP callable
   */
  public function setRenderer($renderer)
  {
    $this->renderer = $renderer;
  }

  /**
   * Gets the list renderer for the field.
   *
   * @return mixed A PHP callable
   */
  public function getRenderer()
  {
    return $this->renderer;
  }

  /**
   * Sets the list renderer arguments for the field.
   *
   * @param array $arguments An array of arguments to pass to the renderer
   */
  public function setRendererArguments(array $arguments)
  {
    $this->rendererArguments = $arguments;
  }

  /**
   * Gets the list renderer arguments for the field.
   *
   * @return array An array of arguments to pass to the renderer
   */
  public function getRendererArguments()
  {
    return $this->rendererArguments;
  }

  /**
   * Returns foreign class name
   *
   * @return string|null
   */
  public function getForeignClassName()
  {
    return $this->foreignClassName;
  }

  /**
   * Is column sortable? In other words: Can be results sorted by this column?
   *
   * @return boolean
   */
  public function isSortable()
  {
    return $this->isReal() || $this->isForeignKey();
  }

  /**
   * Returns an array of credentials for this column
   *
   * @return array
   */
  public function getCredentials()
  {
    return $this->getOption('credentials', array());
  }

  /**
   * Returns css class for this column
   *
   * @return string
   */
  public function getCssClass()
  {
    return strtolower($this->getType());
  }

  /**
   * Returns colum name without the flag ('=', '_', '~')
   *
   * @param $column
   * @return array ($column, $flag)
   */
  static public function splitColumnWithFlag($column)
  {
    $flags = array();
    while(in_array($column[0], array('=', '_', '~')))
    {
      $flags[] = $column[0];
      $column = substr($column, 1);
    }
    return array($column, $flags);
  }

  /**
   * Renders itself in given context
   *
   * @param string $context
   */
  public function render($context)
  {
    switch($context)
    {
      case 'list':
      default:
          return $this->renderInListContext();
      break;
    }

    throw new LogicException('Not implemented yet');
  }

  /**
   * Renders this column in "list" context
   *
   * @return string
   */
  public function renderInListContext()
  {
    $html = $this->generator->getColumnGetter($this, true);

    if($renderer = $this->getRenderer())
    {
      $html = sprintf("$html ? call_user_func_array(%s, array_merge(array(%s), %s)) : '&nbsp;'", $this->generator->asPhp($renderer), $html, $this->generator->asPhp($this->getRendererArguments()));
    }
    elseif($this->isComponent())
    {
      return sprintf("get_component('%s', '%s', array('type' => 'list', '%s' => &\$%s))", $this->generator->getModuleName(), $this->getName(), $this->generator->getSingularName(), $this->generator->getSingularName());
    }
    elseif($this->isPartial())
    {
      return sprintf("get_partial('%s/%s', array('type' => 'list', '%s' => &\$%s))", $this->generator->getModuleName(), $this->getName(), $this->generator->getSingularName(), $this->generator->getSingularName());
    }
    elseif('date' == $this->getType())
    {
      $html = sprintf("false !== strtotime($html) ? format_date(%s, \"%s\") : '&nbsp;'", $html, $this->getOption('date_format', 'f'));
    }
    elseif('boolean' == $this->getType())
    {
      $html = sprintf("get_partial('%s/list_column_boolean', array('value' => %s, '%s' => &\$%s))", $this->generator->getModuleName(), $html, $this->generator->getSingularName(), $this->generator->getSingularName());
    }

    if($this->isLink())
    {
      $html = sprintf("link_to(%s, '%s', \$%s)", $html, $this->generator->getRouteForAction('edit'), $this->generator->getSingularName());
    }

    return $html;
  }

  /**
   * Is this column "not null"?
   *
   * @return boolean
   */
  public function isNotNull()
  {
  }

  /**
   * Is this column "null" ?
   *
   * @return boolean
   */
  public function isNull()
  {
  }

  /**
   * Returns type of the column
   *
   */
  public function getType()
  {
  }

  /**
   * Returns size of the column
   *
   */
  public function getSize()
  {
  }

  /**
   *
   * @see getSize();
   */
  public function getLength()
  {
    return $this->getSize();
  }

  public function getMinLength()
  {
  }

  public function getMaxLength()
  {
  }

  public function isFixedLength()
  {
  }

  /**
   * Is this column IP adress?
   *
   * @return boolean
   */
  public function isIpAddress()
  {
    return $this->isReal() && $this->getType == self::TYPE_INTEGER
            && in_array($this->getName(), array('ip', 'ip_forwarded_for'));
  }

  /**
   * Is column culture column?
   *
   * @return boolean
   */
  public function isCulture()
  {
    return false;
  }

  public function isRegularExpression()
  {
    return false;
  }

  public function getRegularExpression()
  {
    return false;
  }

  public function isEmail()
  {
    return false;
  }

  /**
   * Returns array of possible values. Used when column is enum type.
   *
   * @return array
   */
  public function getValues()
  {
    return array();
  }

}
