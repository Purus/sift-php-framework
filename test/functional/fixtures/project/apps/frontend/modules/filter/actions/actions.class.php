<?php

class filterActions extends sfActions
{
  public function executeIndex()
  {
    return $this->renderText('foo');
  }

  public function executeIndexWithForward()
  {
    $this->forward('filter', 'index');
  }
}