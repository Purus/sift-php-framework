<?php

class autoloadPluginActions extends sfActions
{
  public function executeIndex()
  {
    $this->lib1 = myLibClass::ping();
    $this->lib2 = myAppsFrontendLibClass::ping();
    $this->lib3 = myPluginsSfAutoloadPluginModulesAutoloadPluginLibClass::ping();
    $this->lib4 = class_exists('myPluginsSfAutoloadPluginModulesAutoloadPluginLibClass') ? 'pong' : 'nopong';    
  }
}
