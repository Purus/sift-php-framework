<?php

class viewActions extends sfActions
{
  public function executeIndex()
  {
    $this->setTemplate('foo');
  }
  
  public function executePlain()
  {
  }
  
  public function executeImage()
  {
  }
}
