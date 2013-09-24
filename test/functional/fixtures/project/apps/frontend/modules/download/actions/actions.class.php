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
      'cache_control' => sfHttpDownload::CACHE_CONTROL_PUBLIC
    ));
  }

  public function executeFileCached()
  {
    return $this->downloadFile(sfConfig::get('sf_data_dir').'/email/files/foo.pdf', array(
      'cache_control' => sfHttpDownload::CACHE_CONTROL_PRIVATE_NO_EXPIRE,
      'speed_limit' => 0.5
    ));
  }

  public function executeEtag()
  {
    return $this->downloadData('sample data', array(
      'etag' => 'THIS-IS-AN-ETAG',
      'client_lifetime' => 1
    ));
  }

}
