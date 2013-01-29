<?php

class escapingActions extends sfActions
{
  public function preExecute()
  {
    sfConfig::set('sf_escaping_method', ESC_SPECIALCHARS);
    
    $this->var = 'Lorem <strong>ipsum</strong> dolor sit amet.';
  }

  public function executeOn()
  {
    sfConfig::set('sf_escaping_strategy', true);
    $this->setLayout(false);
    $this->setTemplate('index');    
  }

  public function executeOff()
  {
    sfConfig::set('sf_escaping_strategy', false);
    $this->setLayout(false);
    $this->setTemplate('index');    
  }

  public function executeComponent()
  {
    $this->setLayout(false);
    return sfView::SUCCESS;
  }
  
}
