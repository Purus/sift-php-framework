<?php

class httpcacheActions extends sfActions
{
  public function executePage1()
  {
    $this->setTemplate('index');
  }

  public function executePage2()
  {
    $this->setTemplate('index');
  }

  public function executePage3()
  {
    $this->getResponse()->setHttpHeader('Last-Modified', sfWebResponse::getDate(time() - 86400));

    $this->setTemplate('index');
  }

  public function executePage4()
  {
    $this->getResponse()->setHttpHeader('Last-Modified', sfWebResponse::getDate(time() - 86400));

    $this->setTemplate('index');
  }
}
