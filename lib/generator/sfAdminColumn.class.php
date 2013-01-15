<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Admin generator column.
 *
 * @package    Sift
 * @subpackage generator
 */
class sfAdminColumn
{
  protected
    $phpName    = '',
    $column     = null,
    $flags      = array();

  /**
   * Store the name of the related class for this column if it is
   * a foreign key
   *
   * @var string
   */
  protected $foreignClassName = null;


  protected $isForeignKey     = false;

  /**
   * Relation alias?
   *
   * @var boolean
   */
  protected $isRelationAlias  = false;

  /**
   * Store the name of the related class for this column if it is
   * a foreign key
   *
   * @var string
   */
  protected $isI18n           = false;

  /**
   * Is this column a relation and is the table tree?
   *
   * @var string
   */
  protected $isTree           = false;

  /**
   * Is many to many relationship?
   *
   * @var boolean
   */
  protected $isManyToMany      = false;

  /**
   * Is this column sortable?
   *
   * @var boolean
   */
  protected $isSortable        = false;

  public    $relationIsI18n       = false;
  
  /**
   * Doctrine_Table instance this column belongs to
   *
   * @var Doctrine_Table $table
   */
  protected $table = null;

  public function __construct($phpName, Doctrine_Table $table, $flags = array())
  {
    $this->phpName = $phpName;
    $this->table   = $table;

    if($this->table->hasColumn($phpName))
    {
      $this->column  = $table->getDefinitionOf($phpName);      
      if($this->table->hasTemplate('Doctrine_Template_Sortable'))
      {
        if($this->phpName == 'position')
        {
          $this->isSortable = true;
        }
      }      
    }
    // many to many relation detection
    elseif($this->table->hasRelation($phpName) && $phpName != 'Translation')
    {
      $relation = $this->table->getRelation($phpName);

      $this->foreignClassName       = $relation->getClass();
      $this->isRelationAlias        = true;
      $this->isForeignKey           = true;
      // $this->phpName                = $relation['local'];
        
      $this->isManyToMany           = $relation->getType() === Doctrine_Relation::MANY && isset($relation['refTable']);
      $this->isTree                 = $relation->getTable()->getOption('treeImpl') == 'NestedSet';
      $this->relationIsI18n         = $relation->getTable()->hasColumn('lang') || $relation->getTable()->hasColumn('culture') ? true : false;
    }
    elseif($this->table->hasRelation('Translation'))
    {
      $i18nTable = $this->table->getRelation('Translation')->getTable();
      if($i18nTable->hasColumn($phpName))
      {
        $this->column  = $i18nTable->getDefinitionOf($phpName);
        $this->isI18n  = true;
      }
    }    

    // detect foreign key
    foreach($this->table->getRelations() as $name => $relation)
    {
      $local = (array) $relation['local'];
      $local = array_map('strtolower', $local);
      if(in_array(strtolower($this->phpName), $local))
      {
        $this->foreignClassName = $relation['class'];
        $this->isForeignKey     = true;
      }
    }
    
    $this->flags   = (array) $flags;
    
  }

  /**
   * Returns true if the column maps a database column.
   *
   * @return boolean true if the column maps a database column, false otherwise
   */
  public function isReal()
  {
    return $this->column ? true : false;
  }

  /**
   * Get the name of the column
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->table->getColumnName($this->phpName);
  }

  public function getTable()
  {
    return $this->table;
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
   * Gets the php name of the column.
   *
   * @return string The php name
   */
  public function getPhpName()
  {
    return $this->phpName;
  }

  /**
   * Get the Doctrine type of the column
   *
   * @return void
   */
  public function getType()
  {
    return isset($this->column['type']) ? $this->column['type'] : null;
  }

  /**
   * Returns size of the column
   * @return <type> integer
   */
  function getSize()
  {
    return $this->column['length'];
  }

  /**
   * Returns size of the column. This is an alias for getSize()
   *
   * @return <type> integer
   * @see    getSize()
   */
  public function getLength()
  {
    return $this->getSize();
  }

  function isNotNull()
  {
    if(isset($this->column['notnull']))
    {
      return $this->column['notnull'];
    }
    return false;
  }

  /**
   * Returns true if the column is a primary key and false if it is not
   *
   * @return void
   */
  public function isPrimaryKey()
  {
    if(isset($this->column['primary']))
    {
      return $this->column['primary'];
    }
    return false;
  }

  /**
   * Returns true if this column is a foreign key and false if it is not
   *
   * @return boolean $isForeignKey
   */
  public function isForeignKey()
  {
    return $this->isForeignKey;        
  }

  public function getForeignClassName()
  {
    return isset($this->foreignClassName) ?  $this->foreignClassName : false;
  }

  public function isRelationAlias()
  {
    return $this->isRelationAlias;
  }

  public function relationIsI18n()
  {
    return $this->relationIsI18n;
  }

  public function isI18n()
  {
    return $this->isI18n;
  }

  public function isTree()
  {
    return $this->isTree;
  }

  public function isManyToMany()
  {
    return $this->isManyToMany;
  }

  public function isSortable()
  {
    return $this->isSortable;
  }

  public function isTagsAlias()
  {
    return $this->isRelationAlias() && $this->getPhpName() == 'Tags';
  }

}
