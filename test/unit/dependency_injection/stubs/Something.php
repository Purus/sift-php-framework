<?php

class Something {

  /**
   *
   * @param Apple $apple
   * @inject apple required:false
   */
  public function __construct(Apple $apple = null)
  {
    $this->apple = $apple;
  }

  /**
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

}
