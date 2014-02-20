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
class sfAjaxResult implements sfIJsonSerializable
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
     * @param boolean $success    Success flag
     * @param string  $html       HTML result
     * @param array   $properties Array of additional properties
     */
    public function __construct($success = true, $html = '', $properties = array())
    {
        $this->success = (boolean)$success;
        $this->html = $html;
        $this->fromArray($properties);
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

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array|object
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

}
