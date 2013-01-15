<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Provides debugginf info to the reponse
 *
 * @package    Sift
 * @subpackage filter
 */
class sfWebDebugFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute this filter only once
    if ($this->isFirstCall())
    {
      // register sfWebDebug assets
      sfWebDebug::getInstance()->registerAssets();
    }

    // execute next filter
    $filterChain->execute();

    $context    = $this->getContext();
    $response   = $context->getResponse();
    $controller = $context->getController();

    $content    = $response->getContent();
    
    // don't add debug toolbar:
    // * for XHR requests
    // * if response status code is in the 3xx range
    // * if not rendering to the client
    // * if HTTP headers only
    if (
      !is_string($content) ||
      $this->getContext()->getRequest()->isXmlHttpRequest() ||
      strpos($content, 'html') === false ||
      '3' == substr($response->getStatusCode(), 0, 1) ||
      $controller->getRenderMode() != sfView::RENDER_CLIENT ||
      $response->isHeaderOnly() ||
      !strpos($content, '</body>')
    )
    {
      return;
    }

    $webDebug = sfWebDebug::getInstance()->getResults();

    // add web debug information to response content
    $newContent = str_ireplace('</body>', $webDebug.'</body>', $content);
    if ($content == $newContent)
    {
      $newContent .= $webDebug;
    }

    $response->setContent($newContent);
  }
}