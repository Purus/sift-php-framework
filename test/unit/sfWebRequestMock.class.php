<?php

class sfWebRequest
{
  function initialize()
  {
  }

  function getRelativeUrlRoot()
  {
    return sfConfig::get('test_sfWebRequest_relative_url_root', '');
  }

  public function getMethod()
  {
    return 'GET';
  }
  
  public function setAttribute()
  {    
  }

}
