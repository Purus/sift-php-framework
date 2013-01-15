<?php

class cacheComponents extends sfComponents
{
  public function executeComponent()
  {
    $this->componentParam = 'componentParam';
    $this->requestParam = $this->getRequestParameter('param');
  }

  public function executeCacheableComponent()
  {
    $this->componentParam = 'componentParam';
    $this->requestParam = $this->getRequestParameter('param');
  }

  public function executeContextualComponent()
  {
    $this->componentParam = 'componentParam';
    $this->requestParam = $this->getRequestParameter('param');
  }

  public function executeContextualCacheableComponent()
  {
    $this->componentParam = 'componentParam';
    $this->requestParam = $this->getRequestParameter('param');
  }
}
