<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSearchResults class is a wrapper for result collections.
 * 
 * @package Sift
 * @subpackage search
 */
class sfSearchResults implements Countable, IteratorAggregate, ArrayAccess {

  protected $data = array();

  function offsetExists($offset)
  {
    if(isset($this->data[$offset]))
    {
      return true;
    }
    return false;
  }

  function offsetGet($offset)
  {
    if($this->offsetExists($offset))
    {
      return $this->data[$offset];
    }
    return false;
  }

  function offsetSet($offset, $value)
  {
    if(!$value instanceof sfSearchResultCollection)
    {
      throw new sfException('{sfSearchResults} Value should be instance of sfSearchResultCollection!');
    }

    if($offset)
    {
      $this->data[$offset] = $value;
    }
    else
    {
      $this->data[] = $value;
    }
  }

  function offsetUnset($offset)
  {
    unset($this->data[$offset]);
  }

  function getIterator()
  {
    return new ArrayIterator($this->data);
  }

  function count()
  {
    $count = 0;
    foreach($this->data as $collection)
    {
      $count += $collection->getNumberResults();
    }
    return $count;
  }

}