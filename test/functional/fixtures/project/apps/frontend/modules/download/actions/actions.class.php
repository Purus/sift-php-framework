<?php

class downloadActions extends sfActions
{
  public function executeData()
  {
    return $this->downloadData('this is just a text', array(
      'mime' => 'text/plain',
      'filename' => 'foobar.txt'  
    ));    
  }

}
