<?php

$app = 'frontend';
if(!include(dirname(__FILE__) . '/../bootstrap/functional.php'))
{
  return;
}

class sfAuthTestBrowser extends sfTestBrowser {

  public function checkNonAuth()
  {
    return $this->
            get('/auth/basic')->
            isStatusCode(401)->
            isRequestParameter('module', 'auth')->
            isRequestParameter('action', 'basic')->
            checkResponseElement('#user', '')->
            checkResponseElement('#password', '')->
            checkResponseElement('#msg', 'KO')
    ;
  }

  public function checkAuth()
  {
    return $this->
            get('/auth/basic')->
            isStatusCode(200)->
            isRequestParameter('module', 'auth')->
            isRequestParameter('action', 'basic')->
            checkResponseElement('#user', 'foo')->
            checkResponseElement('#password', 'bar')->
            checkResponseElement('#msg', 'OK')
    ;
  }

}

$b = new sfAuthTestBrowser();
$b->initialize();

// default main page
$b->checkNonAuth()->setAuth('foo', 'bar')->checkAuth()->restart()->checkNonAuth();
