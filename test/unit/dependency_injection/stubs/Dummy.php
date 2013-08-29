<?php

/**
 * @inject new:Something method:setForce force:true
 * @inject apple method:setApple
 */
class Dummy {


  protected $apple;

  /**
   * @inject pear
   */
  public $pear;

  private $constructorArg;

  /**
   *
   * @param Banana $constructorArg
   * @inject banana
   */
  public function __construct(Banana $constructorArg = null)
  {
    $this->constructorArg = $constructorArg;
  }

  /**
   *
   * @param Apple $apple
   * @inject apple
   */
  public function setApple(Apple $apple)
  {
    $this->apple = $apple;
  }

  public function getApple()
  {
    return $this->apple;
  }

  public function getConstructorArg()
  {
    return $this->constructorArg;
  }

  public function __call($name, $args)
  {
    $var = substr($name, 3, (strlen($name) - 3));
    $var[0] = strtolower($var[0]);
    $this->{$var} = $args[0];
  }

  public function getForcedVar()
  {
    return $this->force;
  }

}
