<?php

/**
 * @inject Force method:setForce force:true
 * @inject new:StubSomething method:setForce2 force:true
 * @inject DoesNotExist method:setDoesNotExist
 * @inject DoesNotExist property:noSuchProperty
 * @inject Apple property:forcedProperty force:true
 */
class StubDummy {

    protected $_apple = null;

    /**
     * @inject Pear
     */
    public $pear;
    
    private $constructorArg;

    /**
     * @inject Banana
     */
    public function  __construct($constructorArg = null) {
        $this->constructorArg = $constructorArg;
    }

    /**
     * @inject Apple
     */
    public function setApple($apple) {
        $this->_apple = $apple;
    }

    public function apple() {
        return $this->_apple;
    }

    public function getConstructorArg() {
        return $this->constructorArg;
    }

    public function hello() {
        return 'world';
    }

    public function __call($name, $args) {
        $var = substr($name, 3, (strlen($name) - 3));
        $var[0] = strtolower($var[0]);

        $this->{$var} = $args[0];

    }

    public function forcedVar() {
        return $this->force;
    }
}
