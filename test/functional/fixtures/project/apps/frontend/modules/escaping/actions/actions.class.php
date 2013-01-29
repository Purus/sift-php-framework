<?php

class escapingActions extends sfActions
{
  public function preExecute()
  {
    sfConfig::set('sf_escaping_method', ESC_SPECIALCHARS);
    
    $this->var = 'Lorem <strong>ipsum</strong> dolor sit amet.';
    $this->setLayout(false);
    $this->setTemplate('index');
  }

  public function executeOn()
  {
    sfConfig::set('sf_escaping_strategy', true);
  }

  public function executeOff()
  {
    sfConfig::set('sf_escaping_strategy', false);
  }
  
}
