<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelMemory adds a panel to the web debug toolbar with the memory used by the script.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelMemory extends sfWebDebugPanel {

  protected $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB');
    
  public function getTitle()
  {
    $usage = $this->formatMemoryUsage(memory_get_peak_usage(true));
    
    return sprintf('<span title="%s: %s, Available: %s">%s</span>', 
                    $this->getPanelTitle(),
                    $usage,  
                    $this->formatMemoryUsage($this->getAvailableMemory()),
                    $usage);
  }

  public function getPanelTitle()
  {    
    return 'Memory usage';
  }

  public function getPanelContent()
  {
  }
  
  /**
   * Returns available memory size
   * 
   * @return float
   */
  protected function getAvailableMemory()
  {
    return sfToolkit::getAvailableMemory();
  }
  
  /**
   * Formats memory usage
   *
   * @param integer $size Memory usage 
   * @param integer $round Precision
   * @return string Formatted memory usage (100 kB)
   */
  public function formatMemoryUsage($usage, $round = 1)
  {    
    $pos = 0;
    while($usage >= 1024)
    {
      $usage /= 1024;
      $pos++;
    }
    return round($usage, $round) . ' ' . $this->units[$pos];
  }

}
