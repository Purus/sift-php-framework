<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * 
 * @param string $message
 * @param string $priority
 * @return void
 */ 
function debug_message($message, $priority = 'info')
{
  if(sfConfig::get('sf_web_debug'))
  {
    return log_message($message, $priority);    
  }
}

function log_message($message, $priority = 'info')
{
  if(sfConfig::get('sf_logging_enabled'))
  {
    sfContext::getInstance()->getLogger()->log($message, constant('SF_LOG_' . strtoupper($priority)));
  }
}
