<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchResultCollection class.
 *
 * @package Sift
 * @subpackage search 
 */
class sfSearchResultCollection implements Countable, IteratorAggregate, ArrayAccess {

  /**
   * @var array $data An array containing the records of this collection
   */
  protected $data = array();

  protected $source;

  public function __construct(sfISearchSource $source = null)
  {
    $this->source = $source;
  }
  
  public function getNumberResults()
  {
    return $this->source->getNumberResults();
  }

  public function getSource()
  {
    return $this->source;
  }

  /**
   * Set the data for the mySearchResultCollection instance
   *
   * @param array $data
   * @return mySearchResultCollection
   */
  public function setData(array $data)
  {
    $this->data = $data;
  }

  /**
   * Get all the records as an array
   *
   * @return array
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Get the first record in the collection
   *
   * @return mixed
   */
  public function getFirst()
  {
    return reset($this->data);
  }

  /**
   * Get the last record in the collection
   *
   * @return mixed
   */
  public function getLast()
  {
    return end($this->data);
  }

  /**
   * Get the last record in the collection
   *
   * @return mixed
   */
  public function end()
  {
    return end($this->data);
  }

  /**
   * Get the current key
   *
   * @return mixed
   */
  public function key()
  {
    return key($this->data);
  }

  /**
   * Gets the number of records in this collection
   * This class implements interface countable
   *
   * @return integer
   */
  public function count()
  {
    return count($this->data);
  }

  /**
   * Merges collection into $this and returns merged collection
   *
   * @param mySearchResultCollection $coll
   * @return mySearchResultCollection
   */
  public function merge(sfSearchResultCollection $coll)
  {
    foreach($coll->getData() as $record)
    {
      $this->add($record);
    }
    return $this;
  }

  /**
   * Adds a record to collection
   *
   * @param Doctrine_Record $record              record to be added
   * @param string $key                          optional key for the record
   * @return boolean
   */
  public function add($record)
  {
    /**
     * For some weird reason in_array cannot be used here (php bug ?)
     * 
     * Due to fatal error: Nesting level too deep - recursive dependency?
     */
    foreach($this->data as $r)
    {
      if($r === $record)
      {
        return false;
      }
    }
    $this->data[] = $record;
    return true;
  }

  /**
   * Clears the collection.
   *
   * @return void
   */
  public function clear()
  {
    $this->data = array();
  }

  /**
   * Get collection data iterator
   *
   * @return object ArrayIterator
   */
  public function getIterator()
  {
    $data = $this->data;
    return new ArrayIterator($data);
  }

  /**
   * OffsetSet ArrayAccess method
   *
   * @param string $key
   * @param mixed $value
   */
  public function offsetSet($key, $value)
  {
    $this->data[$key] = $value;
  }

  /**
   * Returns key (ArrayAccess method)
   *
   * @param string $key
   * @return mixed
   * @throws Exception Throws an Exception if there is no such key
   */
  public function offsetGet($key)
  {
    $this->checkKey($key, true);
    return $this->data[$key];
  }

  /**
   * Checks if key does exist (ArrayAccess method)
   *
   * @param string $key
   */
  public function offsetExists($key)
  {
    return $this->checkKey($key);
  }

  /**
   * Removes key (ArrayAccess method)
   *
   * @param string $key
   * @throws Exception Throws Exception is there is not such key
   */
  public function offsetUnset($key)
  {
    $this->checkKey($key, true);
    unset($this->data[$key]);
  }

  /**
   * Checks if given key exists
   *
   * @param string $key
   * @param bool $exception Should exception be thrown or return false?
   */
  private function checkKey($key, $exception = false)
  {
    if(!array_key_exists($key, $this->data))
    {
      if($exception)
      {
        throw new Exception(sprintf('Item index "%s" does not exist!', $key));
      }
      else
      {
        return false;
      }
    }
    return true;
  }

}
