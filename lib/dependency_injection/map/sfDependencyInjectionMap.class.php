<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service map is a collection of items
 *
 * @package    Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionMap extends sfCollection
{
    /**
     * Item type
     *
     * @var string
     */
    protected $itemType = 'sfDependencyInjectionMapItem';

    /**
     * Returns array of items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->getArrayCopy();
    }

    /**
     * Returns an array of items based on the injectWith
     *
     * @param string injectWith return an array of only items that match injectWith
     *
     * @return array
     */
    public function getItemsFor($injectWith)
    {
        $return = array();
        foreach ($this as $item) {
            if ($item->getInjectWith() == $injectWith) {
                $return[] = $item;
            }
        }

        return $return;
    }

    /**
     * Checks to see if the map has dependencies for
     * $injectWith (injection with = method, constructor, etc)
     *
     * @param string $injectWith method, constructor, property, etc
     *
     * @return boolean
     */
    public function has($injectWith)
    {
        if (count($this->getItemsFor($injectWith)) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
