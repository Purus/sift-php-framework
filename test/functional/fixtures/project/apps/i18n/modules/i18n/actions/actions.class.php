<?php

class i18nActions extends sfActions
{
  public function executeIndex()
  {
    $this->test = $this->getContext()->getI18N()->__('an english sentence');
    $this->localTest = $this->getContext()->getI18N()->__('a local english sentence');
    $this->otherTest = $this->getContext()->getI18N()->__('an english sentence', array(), 'other');
    $this->otherLocalTest = $this->getContext()->getI18N()->__('a local english sentence', array(), 'other');
  }
}
