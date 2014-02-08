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
  /**
   * Database queries
   *
   * @var array
   */
  protected $queries = false;

  /**
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    return sprintf('%s', count($this->getQueries()));
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
    return sprintf('Database queries (%s)', count($this->getQueries()));
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelContent()
  {
    if (!count($queries = $this->getQueries())) {
      return '';
    }

    return $this->webDebug->render($this->getOption('template_dir').'/panel/database.php', array(
      'queries' => $queries
    ));
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getIcon()
  {
    return sfWebDebugIcon::get('database');
  }

  /**
   * Returns the queries
   *
   * @return array
   */
  protected function getQueries()
  {
    if ($this->queries !== false) {
      return $this->queries;
    }

    $queries = array();
    $class = 'sfPDO';
    foreach ($this->webDebug->getLogger()->getLogs() as $log) {
      // catch sfPDO messages
      if(($class == $log['type'] ||
          (class_exists($log['type'], false) && is_subclass_of($log['type'], $class))))
      {
        $queries[] = $this->formatSql($log['message_formatted']);
      }
    }

    $queries = $this->webDebug->getEventDispatcher()->filter(
        new sfEvent('web_debug.filter_database_queries'), $queries)->getReturnValue();

    return $this->queries = $queries;
  }

}
