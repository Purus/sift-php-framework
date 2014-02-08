<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCollection is a object collection
 *
 * @package Sift
 * @subpackage collection
 * @link http://offshootinc.com/blog/2011/04/01/reusable-sorting-for-collection-objects-in-php/
 */
abstract class sfCollection extends ArrayObject implements sfIArrayAccessByReference {

  /**
   * Type (class, interface, PHP type)
   *
   * @var string
   */
  protected $itemType;

  /**
   * Function to verify type
   *
   * @var string|array
   */
  protected $checkCallback;

  /**
   * Frozen state
   *
   * @var boolean
   */
  protected $frozen = false;

  /**
   * Constructor
   *
   * @param array $array Array of the data
   * @param string class/interface name or ':type'
   * @throws InvalidArgumentException
   */
  public function __construct($array = null, $type = null)
  {
    if($type !== null)
    {
      if(substr($type, 0, 1) === ':')
      {
        $this->itemType = substr($type, 1);
        $this->checkCallback = 'is_' . $this->itemType;
      }
      else
      {
        $this->itemType = $type;
      }
    }

    if($array !== null)
    {
      $this->import($array);
    }
  }

  /**
   * Add an item to this collection
   *
   * @param $item
   * @return sfCollection
   * @throws InvalidArgumentException
   */
  public function append($item)
  {
    $this->beforeAppend($item);
    parent::append($item);
    return $this;
  }

  /**
   * Removes the first occurrence of the specified element.
   *
   * @param  mixed
   * @return bool true if this collection changed as a result of the call
   * @throws NotSupportedException
   */
  public function remove($item)
  {
    $this->checkFreezed();
    $index = $this->search($item);
    if($index === false)
    {
      return false;
    }
    else
    {
      parent::offsetUnset($index);
      return true;
    }
  }

  /**
   * Merge an existing collection with this one
   *
   * @param sfCollection $collection
   * @return sfCollection
   */
  public function merge(sfCollection $collection)
  {
    foreach($collection as $item)
    {
      $this->append($item);
    }
    return $this;
  }

  /**
   * Import from array or any traversable object.
   *
   * @param array|Traversable $array
   * @return void
   * @throws InvalidArgumentException
   */
  public function import($array)
  {
    if(!(is_array($array) || $array instanceof Traversable))
    {
      throw new InvalidArgumentException('Invalid argument given. The argument must be traversable.');
    }

    $this->clear();
    foreach($array as $item)
    {
      $this->append($item);
    }
  }

  /**
   * Clear the collection
   *
   * @return sfCollection
   * @throws InvalidStateException If the object is freezed
   */
  public function clear()
  {
    $this->checkFreezed();
    $this->data = array();
    return $this;
  }

  /**
   * Return the iterator
   *
   * @return ArrayIterator
   */
  public function getIterator()
  {
    return new ArrayIterator($this->getArrayCopy());
  }

  /**
   * Convert the collection to array
   *
   * @return array
   */
  public function toArray()
  {
    return (array)$this;
  }

  /**
   * Returns the index of the first occurrence of the specified element
   * or false if this collection does not contain this element.
   *
   * @param mixed
   * @return int|FALSE
   */
  protected function search($item)
  {
    return array_search($item, $this->getArrayCopy(), true);
  }

  /**
   * Returns true if this collection contains the specified item.
   *
   * @param  mixed
   * @return bool
   */
  public function contains($item)
  {
    return $this->search($item) !== false;
  }

  /**
   * Responds when the item is about to be added to the collection.
   *
   * @param  mixed
   * @return void
   * @throws InvalidArgumentException, InvalidStateException
   */
  protected function beforeAppend($item)
  {
    $this->checkFreezed();

    if($this->itemType !== null)
    {
      if($this->checkCallback === null)
      {
        if(!($item instanceof $this->itemType))
        {
          throw new InvalidArgumentException(
              sprintf('Cannot add item to the collection. The item must be "%s" object. Instance of "%s" given.',
                  $this->itemType, is_object($item) ? get_class($item) : gettype($item)));
        }
      }
      else
      {
        if(!call_user_func($this->checkCallback, $item))
        {
          throw new InvalidArgumentException(sprintf('Cannot add item to the collection. Item must be "%s" type. "%s" given.', $this->itemType, gettype($item)));
        }
      }
    }
  }

  /**
   * Sort the collection
   *
   * @param sfICollectionSorter $sorter
   * @param integer $dir Sorting direction
   */
  public function sort(sfCollectionSorter $sorter, $dir = sfCollectionSorter::DIRECTION_ASC)
  {
    $sorter->setDirection($dir);
    $this->uasort(array($sorter, $sorter->getCallback()));
  }

  /**
   * Not supported. Use import()
   *
   * @param array|Traversable
   * @return array Returns the old array
   * @throws NotSupportedException
   */
  public function exchangeArray($array)
  {
    throw new NotSupportedException('Use ' . __CLASS__ . '::import()');
  }

  /**
   * Returns the item type
   *
   * @return string
   */
  public function getItemType()
  {
    return $this->itemType;
  }

  /**
   * Makes the object unmodifiable.
   *
   * @return void
   */
  public function freeze()
  {
    $this->frozen = true;
  }

  /**
   * Is the object unmodifiable?
   *
   * @return bool
   */
  final public function isFrozen()
  {
    return $this->frozen;
  }

  /**
   * Creates a modifiable clone of the object.
   *
   * @return void
   */
  public function __clone()
  {
    $this->frozen = false;
  }

  /**
   * Check if this object is freezed
   *
   * @return void
   * @throws InvalidStateException If the object is freezed
   */
  protected function checkFreezed()
  {
    if($this->frozen)
    {
      throw new InvalidStateException(sprintf('Cannot modify a frozen object "%s".', get_class($this)));
    }
  }

  /**
   * Sets a value by reference
   *
   * @param mixed $offset Offset
   * @param mixed $value Value
   * @return mixed $value
   */
  public function &offsetSetByReference($offset, &$value)
  {
    parent::offsetSet($offset, $value);
    // should return in case called within an assignment chain
    return $value;
  }

  /**
   * @see sfIArrayAccessByReference::offsetGetByReference
   */
  public function &offsetGetByReference($offset)
  {
    $ret = null;
    if($this->offsetExists($offset))
    {
      $ret = parent::offsetGet($offset);
    }
    return $ret;
  }

}
