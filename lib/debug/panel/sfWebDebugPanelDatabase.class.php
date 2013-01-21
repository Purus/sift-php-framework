<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelLogs adds a panel to the web debug toolbar with log messages.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelDatabase extends sfWebDebugPanel
{
  protected $events = array();
  
  public function getTitle()
  {
    return sprintf('%s', count($this->getEvents()));
  }

  public function getPanelTitle()
  {
    return sprintf('Database queries (%s)', count($this->events));
  }

  public function getPanelContent()
  {    
    if(!count($this->events))
    {
      return '';
    }
    
    $html = array();
    $html[] = '<table class="sfWebDebugLogs">';
    
    foreach($this->events as $i => $event)
    {
      $html[] = sprintf('<tr><td>%s</td><td>%s</td></tr>', ++$i, $event);
    }
    
    $html[] = '</table>';
    
    return join("\n", $html);
  }
  
  protected function getEvents()
  {
    $this->events = array();    
    $class = 'sfPDO';    
    foreach($this->webDebug->getLogger()->getLogs() as $log)
    {
      // catch sfPDO messages
      if(($class == $log['type'] || (class_exists($log['type'], false) && is_subclass_of($log['type'], $class))))
      {        
        $this->events[] = $this->formatSql(htmlspecialchars($log['message'], ENT_QUOTES, sfConfig::get('sf_charset')));
      }
    }
    return $this->events;
  }
  
}
