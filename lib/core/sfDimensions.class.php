<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDimensions is used for setting and getting dimensions
 *
 * @package    Sift
 * @subpackage core
 */
class sfDimensions
{
    /**
     * Stores the current dimensions
     *
     * @var array
     */
    protected $currentDimension = array();

    /**
     * All available dimensions
     *
     * @var array
     */
    protected $availableDimensions = array();

    /**
     * Stores all the possible directories in order based on current dimension
     *
     * @var array
     */
    protected $currentDimensionDirectories = array();

    /**
     * Constructs the dimension
     *
     * @param array $availableDimensions
     * @param array $defaultDimension
     */
    public function __construct(array $availableDimensions, $defaultDimension = null)
    {
        $this->availableDimensions = $availableDimensions;

        if (null === $defaultDimension) {
            $defaultDimension = array();

            foreach ($availableDimensions as $key => $dimension) {
                $dimension = (array)$dimension;
                $defaultDimension[$key] = array_shift($dimension);
            }
        }

        $this->currentDimension = $defaultDimension;
    }

    /**
     * Sets the current dimension
     *
     * @param array $dimension Current dimension
     *
     * @return sfDimensions
     */
    public function setCurrentDimension($dimension)
    {
        if (!$this->isAvailable($dimension)) {
            throw new InvalidArgumentException(sprintf(
                'Dimension "%s" is not available.',
                var_export($dimension, true)
            ));
        }

        // reset
        $this->currentDimensionDirectories = array();
        $this->currentDimension = $dimension;

        return $this;
    }

    /**
     * Gets the current dimension
     *
     * @return array Current dimension
     */
    public function getCurrentDimension()
    {
        return $this->currentDimension;
    }

    /**
     * Checks if given dimension is available
     *
     * @param array $dimension
     */
    public function isAvailable(array $dimension)
    {
        $allowed = array_keys($dimension);
        foreach ($allowed as $name) {
            if (!isset($this->availableDimensions[$name])
                || !in_array($dimension[$name], $this->availableDimensions[$name])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns an array of available dimensions
     *
     * @return array
     */
    public function getAvailableDimensions()
    {
        return $this->availableDimensions;
    }

    /**
     * Gets all available dimension directories based on the current dimension
     *
     * @return array all available dimension directories in lookup order
     */
    public function getDimensionDirs()
    {
        if (empty($this->currentDimensionDirectories) && !empty($this->currentDimension)) {
            $dimensions = array();
            $key = array_keys($this->currentDimension);

            $c = count($this->currentDimension);
            for ($i = 0; $i < $c; $i++) {
                $tmp = $this->currentDimension;
                for ($j = $i; $j > 0; $j--) {
                    array_pop($tmp);
                }
                $val = $this->toString($tmp);

                $dimensions['combinations'][] = $val;
                $dimensions['roots'][] = $this->currentDimension[$key[$i]];
            }

            array_pop($dimensions['combinations']);
            $dimensions = array_merge($dimensions['combinations'], array_reverse($dimensions['roots']));

            $this->currentDimensionDirectories = array_unique($this->flatten($dimensions));
        }

        return $this->currentDimensionDirectories;
    }

    /**
     * Gets current dimension as a flattened string
     *
     * @return string the current dimension as a string
     */
    public function getDimensionString()
    {
        return $this->toString($this->getCurrentDimension());
    }

    /**
     * Helper function to flatten an array
     *
     * @param array an array to be flattened
     *
     * @return string a flat string of the array input
     */
    public function flatten($array)
    {
        for ($x = 0; $x < sizeof($array); $x++) {
            $element = $array[$x];
            if (is_array($element)) {
                $results = $this->flatten($element);
                for ($y = 0; $y < sizeof($results); $y++) {
                    $flat_array[] = $results[$y];
                }
            } else {
                $flat_array[] = $element;
            }
        }

        return $flat_array;
    }

    /**
     * Converts array values to a string
     *
     * @param array   Input array to be converted as string
     * @param boolean Separate by underscore
     *
     * @return string
     */
    public function toString($array, $underscore = true)
    {
        $i = 0;
        $return = false;
        foreach ($array as $index => $val) {
            $divider = (isset($underscore) && $i > 0) ? '_' : '';
            $return .= $divider . $val;
            $i++;
        }

        return $return;
    }

}
