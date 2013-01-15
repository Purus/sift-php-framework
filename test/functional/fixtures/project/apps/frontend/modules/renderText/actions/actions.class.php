<?php

class renderTextActions extends sfActions
{
  public function executeIndex()
  {
    return $this->renderText('foo');
  }
}
