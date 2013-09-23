<?php

class downloadActions extends sfActions
{
  public function executeData()
  {
    return $this->downloadData('this is just a text', array(
      'content_type' => 'text/plain',
      'content_disposition' => 'inline',
      'filename' => 'foobar.txt'
    ));
  }

  public function executeFile()
  {
    return $this->downloadFile(sfConfig::get('sf_data_dir').'/email/files/foo.pdf', array(
      'allow_cache' => false
    ));
  }

  public function executeFileCached()
  {
    return $this->downloadFile(sfConfig::get('sf_data_dir').'/email/files/foo.pdf', array(
      'allow_cache' => true,
      'speed_limit' => 0.5
    ));
  }

  public function executeEtag()
  {
    return $this->downloadFile(sfConfig::get('sf_data_dir').'/email/files/foo.pdf', array(
      'allow_cache' => true,
      'etag' => 'THIS-IS-AN-ETAG'
    ));
  }

}
