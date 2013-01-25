<?php

// dimensions are valid only for front application
if(!defined('SF_APP') || SF_APP != 'front')
{
  return;
}

/*
// detect domain
$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : false;
if($serverName)
{
  if(preg_match('|^en\.|', $serverName))
  {
    $culture = 'en';
  }
  else
  {
    $culture = 'cs';
  }
  // set dimensions
  sfDimensions::setDimension(array('culture' => $culture));  
}
*/
