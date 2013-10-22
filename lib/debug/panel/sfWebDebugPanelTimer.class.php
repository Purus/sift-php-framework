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

  public function getTitle()
  {
    return sprintf('%.0f ms', $this->getTotalTime());
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
    if(!sfTimerManager::getTimers())
    {
      return;
    }

    return $this->webDebug->render($this->getOption('template_dir') . '/panel/timer.php', array(
      'total_time' => $this->getTotalTime(),
      'timers' => sfTimerManager::getTimers()
    ));
  }

  protected function getTotalTime()
  {
    $totalTime = 0;
    foreach(sfTimerManager::getTimers() as $timer)
    {
      $totalTime += $timer->getElapsedTime() * 1000;
    }
    return $totalTime;
  }

}
