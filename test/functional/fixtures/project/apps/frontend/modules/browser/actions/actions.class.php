<?php

class browserActions extends sfActions
{
  public function executeIndex()
  {
    return $this->renderText('<html><body><h1>html</h1></body></html>');
  }

  public function executeText()
  {
    $this->getResponse()->setContentType('text/plain');

    return $this->renderText('text');
  }

  public function executeResponseHeader()
  {
    $response = $this->getResponse();

    $response->setContentType('text/plain');
    $response->setHttpHeader('foo', 'bar', true);
    $response->setHttpHeader('foo', 'foobar', false);

    return $this->renderText('ok');
  }
}
