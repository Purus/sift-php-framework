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
  public function execute(sfFilterChain $filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // get response object
    $response = $this->getContext()->getResponse();

    // rethrow sfForm and|or sfFormField __toString() exceptions (see sfForm and sfFormField)
    if(sfForm::hasToStringException())
    {
      throw sfForm::getToStringException();
    }
    elseif(sfFormField::hasToStringException())
    {
      throw sfFormField::getToStringException();
    }

    // send headers + content
    if(sfView::RENDER_VAR != $this->getContext()->getController()->getRenderMode())
    {
      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->info('{sfFilter} Render to client');
      }
      $response->send();
    }
  }

}
