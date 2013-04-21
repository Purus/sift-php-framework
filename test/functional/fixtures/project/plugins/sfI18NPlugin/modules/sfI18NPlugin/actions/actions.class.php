<?php

class sfI18NPluginActions extends sfActions
{
  public function executeIndex()
  {
    return $this->forward('i18n', 'index');
    
    $this->test = $this->getContext()->getI18N()->__('an english sentence');

    $this->localTest = $this->getContext()->getI18N()->__('a local english sentence');
    
  }
}
