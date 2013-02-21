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
abstract class sfGeneratorModelColumn implements sfIGeneratorModelColumn {

  /**
   * Column name
   * 
   * @var string 
   */
  protected $name;
  
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
   * Contructts the column
   * 
   * @param string $name
   * @param array $flags
   */
  public function __construct($name, $flags = array())
  {
    $this->name = $name;
    $this->flags = $flags;
    
    $this->setup();
  }

  /**
   * Setups the column
   */
  public function setup()
  {
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
   * Returns foreign class name
   * 
   * @return string|null
   */
  public function getForeignClassName()
  {
    return $this->foreignClassName;
  }  
  
  /**
   * 
   * @throws Exception
   */
  public function isNotNull()
  {
    throw new Exception('Not implemented');
  }
  
  /**
   * 
   * @throws Exception
   */
  public function isNull()
  {
    throw new Exception('Not implemented');
  }
  
  /**
   * 
   * @throws Exception
   */
  public function getType()
  {
    throw new Exception('Not implemented');
  }
  
  /**
   * 
   * @throws Exception
   */
  public function getSize()
  {
    throw new Exception('Not implemented');
  }    
  
}
