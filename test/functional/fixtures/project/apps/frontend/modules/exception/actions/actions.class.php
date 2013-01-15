<?php

class exceptionActions extends sfActions
{
  public function executeNoException()
  {
    return $this->renderText('foo');
  }

  public function executeThrowsException()
  {
    throw new Exception('Exception message');
  }

  public function executeThrowsSfException()
  {
    throw new sfException('sfException message');
  }
}
