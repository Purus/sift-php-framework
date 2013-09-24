<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ssfWebDebugPanelLessCompiler adds a panel to the web debug toolbar with information about less compiling.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelLessCompiler extends sfWebDebugPanelLogs {

  /**
   * Array of events
   *
   * @var array
   */
  protected $events = array();

  /**
   *
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    return sprintf('%s', count($this->getEvents()));
  }

  /**
   *
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
    return sprintf('Less compile events (%s)', count($this->events));
  }

  /**
   *
   * @see sfWebDebugPanel
   */
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
      $html[] = sprintf('<tr class="sfWebDebugLogLine sfWebDebug%s"><td>%s</td><td>%s</td></tr>', ucfirst($event['priority']), ++$i, $event['message']);
    }

    $html[] = '</table>';

    return join("\n", $html);
  }

  /**
   * Returns sfLessCompiler events
   *
   * @return array
   */
  protected function getEvents()
  {
    $this->events = array();
    $class = 'sfLessCompiler';
    foreach($this->webDebug->getLogger()->getLogs() as $log)
    {
      if(($class == $log['type'] || (class_exists($log['type'], false) && is_subclass_of($log['type'], $class))))
      {
        $level = $this->webDebug->getPriority($log['level']);
        $this->events[] = array(
            'level' => $level,
            'message' => $this->formatLogLine($log['message'])
        );
      }
    }
    return $this->events;
  }

}
