<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLessCompilerFilter compiles .less stylesheets from response on fly.
 *
 * @package    Sift
 * @subpackage filter
 */
class sfLessCompilerFilter extends sfFilter {
    
  /**
   * Executes the filter
   * 
   * @param sfFilterChain $filterChain
   */
  public function execute(sfFilterChain $filterChain)
  {    
    $response = $this->getContext()->getResponse();
    $request  = $this->getContext()->getRequest();
    
    if($this->isFirstCall()        
        && !$request->isAjax()
        && preg_match('|text/html|', $response->getContentType()))
    {
      $this->handleStylesheets();
    }    
    $filterChain->execute();    
  }
  
  /**
   * Handles the compilation of stylesheets
   * 
   */
  protected function handleStylesheets()
  {
    sfLoader::loadHelpers(array('Tag', 'Asset'));
          
    $context  = $this->getContext();
    $response = $context->getResponse();
    $request  = $context->getRequest();
    $baseDomain = sfConfig::get('sf_base_domain');
    
    $already_seen = array();
    
    foreach(array('first', '', 'last') as $position)
    {
      foreach($response->getStylesheets($position) as $files => $options)
      {
        if(!is_array($files))
        {
          $files = array($files);
        }
        
        foreach($files as $file)
        {          
          $stylesheet = stylesheet_path($file);
          
          if(isset($already_seen[$stylesheet]))
          {
            continue;
          }
                    
          if(!preg_match('/\.less$/', $stylesheet))
          {
            continue;
          }

          $already_seen[$stylesheet] = true;
          
          if(strpos($file, 'http:') !== false)
          {
            $url = parse_url($stylesheet);
            if(isset($url['host']) && $url['host'] != $request->getHost() 
                && $url['host'] != $baseDomain)
            {
              continue;
            }
          }

          if(sfConfig::get('sf_logging_enabled'))
          {    
            $timer = sfTimerManager::getTimer('{sfLessCompilerFilter} compile stylesheets');
            $timer->startTimer();
          }
          
          try 
          {
            $response->addStylesheet(
                    sfLessCompiler::getInstance()->compileStylesheetIfNeeded(
                            $stylesheet), $position, $options);
          }
          catch(sfLessCompilerException $e)
          {
            if(sfConfig::get('sf_web_debug'))
            {
              throw $e;
            }
          }

          $response->removeStylesheet($file);
          
          if(sfConfig::get('sf_logging_enabled'))
          {
            $timer->addTime();
          }
          
        }
      }
      
      // cleanup helper variables
      unset($already_seen, $baseDomain, $url);      
    }  
  }  
}
