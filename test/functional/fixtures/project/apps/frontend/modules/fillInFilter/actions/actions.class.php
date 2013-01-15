<?php

class fillInFilterActions extends sfActions
{
  public function executeForward()
  {
    if ($this->getRequest()->getMethod() === sfRequest::POST)
    {
      $this->forward('fillInFilter', 'done');
    }
  }

  public function executeDone()
  {
  }

  public function handleErrorForward()
  {
    return sfView::SUCCESS;
  }

  public function executeIndex()
  {
  }

  public function executeUpdate()
  {
    $this->forward('fillInFilter', 'index');
  }

  public function handleErrorUpdate()
  {
    $this->forward('fillInFilter', 'index');
  }
}
