<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRenderingFilter is the last filter registered for each filter chain. This
 * filter does the rendering.
 *
 * @package    Sift
 * @subpackage filter
 */
class sfRenderingFilter extends sfFilter {

  /**
   * Executes this filter.
   *
   * @param sfFilterChain The filter chain.
   * @throws sfInitializeException If an error occurs during view initialization
   * @throws sfViewException       If an error occurs while executing the view
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // this is a fix for double response
    if(!$this->isFirstCall())
    {
      return;
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfFilter} render to client');
    }

    // get response object
    $response = $this->getContext()->getResponse();

    // send headers + content
    if(sfView::RENDER_VAR != $this->getContext()->getController()->getRenderMode())
    {
      $response->send();
    }

    // log timers information
    if(sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $logger = $this->getContext()->getLogger();
      foreach(sfTimerManager::getTimers() as $name => $timer)
      {
        $logger->info(sprintf('{sfTimerManager} %s %.2f ms (%d)', $name, $timer->getElapsedTime() * 1000, $timer->getCalls()));
      }
    }
  }

}
