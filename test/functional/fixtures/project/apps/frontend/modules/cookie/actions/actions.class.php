<?php

class cookieActions extends sfActions
{
  public function executeIndex()
  {
    return $this->renderText('<p>'.$this->getRequest()->getCookie('foo').'.'.
            $this->getRequest()->getCookie('bar').'-'.$this->getRequest()->getCookie('foobar').'</p>');
  }

  public function executeSetCookie()
  {
    $this->getResponse()->setCookie('foobar', 'barfoo');

    return sfView::NONE;
  }

  public function executeRemoveCookie()
  {
    $this->getResponse()->setCookie('foobar', 'foofoobar', time() - 10);

    return sfView::NONE;
  }
}
