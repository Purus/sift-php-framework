<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAjaxResult class is standardized object for communication with client side
 * javascript
 *
 * @package    Sift
 * @subpackage response
 */
class sfAjaxResult
{
    /**
     * Success or error flag
     *
     * @var boolean
     */
    public $success = true;

    /**
     * Result HTML code
     *
     */
    public $html = '';

    /**
     * Constructs new object
     *
     * @param boolean $success
     * @param string  $html HTML result
     */
    public function __construct($success = true, $html = '')
    {
        $this->success = (boolean)$success;
        $this->html = $html;
    }

    /**
     * Converts object instance to JSON
     *
     * @param boolean Force to object (uses JSON_FORCE_OBJECT parameter)
     *
     * @return string
     */
    public function toJson($forceObject = false)
    {
        return $forceObject ? sfJson::encode($this, true, JSON_FORCE_OBJECT) : sfJson::encode($this);
    }

    /**
     * Creates the result from array
     *
     * @param array $array
     */
    public static function createFromArray($array)
    {
        $result = new self();
        $result->fromArray($array);

        return $result;
    }

    /**
     * Creates object properties from associative array
     *
     * @param array $array
     *
     * @return sfAjaxResult
     */
    public function fromArray($array)
    {
        foreach ($array as $key => $val) {
            $this->$key = $val;
        }

        return $this;
    }

    /**
     * Converts the object to string
     *
     * @return string
     * @see toJson()
     */
    public function __toString()
    {
        return $this->toJson();
    }

}
