<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBrowseHistory provides a stack for storing user browse information like
 * last seen products, photos and so on
 *
 * @package    Sift
 * @subpackage history
 */
class sfBrowseHistory implements Serializable, Countable {

  /**
   * Namespace of sfUser object
   *
   * @var string
   */
  protected static $nameSpace = 'browse_history';

  /**
   * Stack which holds the history items
   *
   * @var array
   */
  protected $stack = array();

  /**
   * Maximum number of items to save
   *
   * @var integer
   */
  protected $maxItems = 10;

  /**
   * Returns an instance if this class
   *
   * @return sfBrowseHistory
   */
  public static function getInstance($maxItems = null)
  {
    $user = sfContext::getInstance()->getUser();

    if(!$user->hasAttribute('browse_history', self::$nameSpace))
    {
      $user->setAttribute('browse_history', new self($maxItems), self::$nameSpace);
    }

    return $user->getAttribute('browse_history', false, self::$nameSpace);
  }

  /**
   * Constructs the history object
   *
   * @param integer $maxItems Max items in the stack
   */
  public function __construct($maxItems = null)
  {
    if(!is_null($maxItems))
    {
      $this->setMaxItems($maxItems);
    }
  }

  /**
   * Returns number of items in the stack
   *
   * @return integer
   */
  public function count()
  {
    return count($this->stack);
  }

  /**
   * Returns max number of items
   *
   * @return integer
   */
  public function getMaxItems()
  {
    return $this->maxItems;
  }

  /**
   * Set maximum number of items
   *
   * @param integer $nb Number of items
   * @return sfBrowseHistory
   */
  public function setMaxItems($nb)
  {
    $this->maxItems = $nb;
    return $this;
  }

  /**
   * Deletes item from the stack
   *
   * @param integer $id Id of the item which should be deleted
   * @return sfBrowseHistory
   */
  public function delete($id)
  {
    $i = 0;
    foreach($this->stack as $item)
    {
      if($item->getId() == $id)
      {
        unset($this->stack[$i]);
        break;
      }
      $i++;
    }

    return $this;
  }

  /**
   * Push item to the stack
   *
   * @param sfBrowseHistoryItem $item
   * @return sfBrowseHistory
   */
  public function pushItem(sfBrowseHistoryItem $item)
  {
    // delete old item, we don't need to list more than once
    $this->delete($item->getId());

    array_unshift($this->stack, $item);
    while(count($this->stack) > $this->maxItems)
    {
      array_pop($this->stack);
    }

    return $this;
  }

  /**
   * Push the item onto the stack.
   *
   * @param integer $id Id if the item
   * @param string $name
   * @param array $params
   * @return sfBrowseHistory
   */
  public function push($id, $name, $params = array())
  {
    $class = sprintf('%sItem', get_class($this));
    if(!class_exists($class))
    {
      $class = 'sfBrowseHistoryItem';
    }

    $item = new $class($id, $name);

    foreach($params as $param => $value)
    {
      $item->setParameter($param, $value);
    }

    return $this->pushItem($item);
  }

  /**
   * Is there something in the history?
   *
   * @return boolean True is there is something in the history
   */
  public function hasHistory()
  {
    if($this->count() > 0)
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Returns items from the stack
   *
   * @return array
   */
  public function getItems()
  {
    return $this->stack;
  }

  /**
   * Clears the history
   *
   */
  public function clear()
  {
    $this->stack = array();
  }

  /**
   * Magic __sleep method
   *
   * @return array
   */
  public function __sleep()
  {
    return array('stack', 'maxItems');
  }

  /**
   * Magic __wakeup method
   *
   */
  public function __wakeup()
  {
  }

  /**
   * Serializes the history
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
   * @param string $serialized  sfBrowseHistory as serialized string
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

}
