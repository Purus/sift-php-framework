<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBrowseHistoryItem provides an item which can be added to browse history
 *
 * @package    Sift
 * @subpackage history
 */
class sfBrowseHistoryItem implements Serializable {

  /**
   * Id holder
   *
   * @var integer
   */
  protected $id;

  /**
   * Parameter holder
   *
   * @var sfParameterHolder
   */
  protected $parameter_holder;

  /**
   * Name holder
   *
   * @var string
   */
  protected $name;

  /**
   * Constructs a new item to store in the browse history.
   */
  public function __construct($id, $name = null)
  {
    $this->id = $id;
    $this->name = $name;
    $this->parameter_holder = new sfParameterHolder();
  }

  /**
   * Returns unique identifier for this item.
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Sets unique identifier for this item.
   *
   * @param integer $id
   * @return sfBrowseHistoryItem
   */
  public function setId($id)
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Returns name of the item
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return sfBrowseHistoryItem
   */
  public function setName($name)
  {
    $this->name = (string)$name;

    return $this;
  }

  /**
   * Returns parameter holder
   *
   * @return sfParameterHolder
   */
  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }

  /**
   * Returns parameter
   *
   * @param string $name
   * @param string $default Default value
   * @param string $ns Parameter namespace
   * @return mixed
   */
  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameter_holder->get($name, $default, $ns);
  }

  /**
   * Check if the item has parameter $name
   *
   * @param string $name Parameter name
   * @param string $ns Namespace
   * @return boolean true is the item has the parameter $name
   */
  public function hasParameter($name, $ns = null)
  {
    return $this->parameter_holder->has($name, $ns);
  }

  /**
   * Sets parameter
   *
   * @param string $name Parameter name
   * @param mixed $value Parameter value
   * @param string $ns Namespace
   * @return sfBrowseHistoryItem
   */
  public function setParameter($name, $value, $ns = null)
  {
    $this->parameter_holder->set($name, $value, $ns);

    return $this;
  }

  /**
   * Serializes the item
   *
   * @return string
   */
  public function serialize()
  {
    $vars = get_object_vars($this);

    return serialize($vars);
  }

  /**
   * This method is automatically called everytime an instance is unserialized
   *
   * @param string $serialized sfBrowseHistoryItem as serialized string
   * @return void
   */
  public function unserialize($serialized)
  {
    $vars = unserialize($serialized);
    foreach($vars as $var => $value)
    {
      $this->$var = $value;
    }
  }

  /**
   * Magic mathod __call
   *
   * @param string $m Method name
   * @param array $a Arguments
   * @return mixed
   * @throws BadMethodCallException
   */
  public function __call($m, $a)
  {
    $verb = substr($m, 0, 3);
    $column = substr($m, 3);

    // convert ColumnName => column_name
    $column = sfInflector::tableize($column);
    // prepend column in arguments array
    array_unshift($a, $column);

    if($verb == 'get')
    {
      return call_user_func_array(array($this, 'getParameter'), $a);
    }
    elseif($verb == 'set')
    {
      return call_user_func_array(array($this, 'setParameter'), $a);
    }
    elseif($verb == 'has')
    {
      return call_user_func_array(array($this, 'hasParameter'), $a);
    }

    throw new BadMethodCallException(sprintf('Method "%s" in not valid callback', $m));
  }

}
