<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelTimer adds a panel to the web debug toolbar with timer information.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelTimer extends sfWebDebugPanel {

  /**
   * Constructor.
   *
   * @param sfWebDebug $webDebug The web debug toolbar instance
   */
  public function __construct(sfWebDebug $webDebug)
  {
    parent::__construct($webDebug);
  }

  public function getTitle()
  {
    return ($this->getTotalTime() . ' ms');
  }

  public function getIcon()
  {    
  }

  public function getPanelTitle()
  {
    return 'Timers';
  }

  public function getPanelContent()
  {
    if(sfTimerManager::getTimers())
    {
      $totalTime = $this->getTotalTime();
      $panel = '<table class="sf-web-debug-logs" style="width: 30%">
                <tr>
                <th>type</th>
                <th>calls</th>
                <th>time (ms)</th>
                <th>time (%)</th>
                </tr>';

      foreach(sfTimerManager::getTimers() as $name => $timer)
      {
        $panel .= sprintf(
                '<tr><td class="sf-web-debug-log-type">%s</td>
          <td class="sf-web-debug-log-number" style="text-align: right">%d</td>
          <td style="text-align: right">%.2f</td>
          <td style="text-align: right">%d</td>
          </tr>', $name, $timer->getCalls(), $timer->getElapsedTime() * 1000, $totalTime ? ($timer->getElapsedTime() * 1000 * 100 / $totalTime) : 'n/a');
      }
      $panel .= '</table>';

      return $panel;
    }
  }

  protected function getTotalTime()
  {
    return sprintf('%.0f', (microtime(true) - sfConfig::get('sf_timer_start')) * 1000);
  }

}