<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCommonFilter automatically adds javascripts, stylesheets and auto
 * discovery links information in the sfResponse content.
 *
 * @package    Sift
 * @subpackage filter
 * @link       http://www.zalas.eu/put-your-stylesheets-at-the-top-and-your-scripts-at-the-bottom
 */
class sfCommonFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   * @throws sfException
   */
  public function execute(sfFilterChain $filterChain)
  {
    // execute next filter
    $filterChain->execute();

    $response = $this->getContext()->getResponse();
    $metas = $response->getMetas();

    // we assume that this means that the design is responsive
    if(isset($metas['viewport']))
    {
      $this->getContext()->getEventDispatcher()->notify(new sfEvent('response.responsive', array(
        'response' => $response
      )));
    }

    $mode = strtolower($this->getParameter('mode', 'normal'));
    switch($mode)
    {
      case 'normal':
        $this->includeAssetsNormal();
      break;

      case 'optimized':
        $this->includeAssetsOptimized();
      break;

      default:
        throw new sfException(sprintf('{sfCommonFilter} Invalid mode "%s". Valid modes are: "normal", "optimized"', $mode));
      break;
    }
  }

  protected function includeAssetsNormal()
  {
    $response = $this->getContext()->getResponse();
    $content  = $response->getContent();

    if(is_string($content) && false !== ($pos = strpos($content, '</head>')))
    {
      sfLoader::loadHelpers(array('Tag', 'Asset'));

      $html = '';
      if(!$response->getParameter('auto_discovery_links_included', false, 'sift/view/asset'))
      {
        $html .= get_auto_discovery_links();
      }

      if(!$response->getParameter('stylesheets_included', false, 'sift/view/asset'))
      {
        $html .= get_stylesheets();
      }

      if(!$response->getParameter('javascripts_included', false, 'sift/view/asset'))
      {
        $html .= get_javascripts();
      }

      if($this->getParameter('javascript_config') &&
        !$response->getParameter('javascript_configuration_included', false, 'sift/view/asset'))
      {
        $html .= get_javascript_configuration();
      }

      if($html)
      {
        $response->setContent(substr($content, 0, $pos).$html.substr($content, $pos));
      }
    }

    $response->setParameter('javascripts_included', false, 'sift/view/asset');
    $response->setParameter('javascript_configuration_included', false, 'sift/view/asset');
    $response->setParameter('stylesheets_included', false, 'sift/view/asset');
    $response->setParameter('auto_discovery_links_included', false, 'sift/view/asset');
  }

  protected function includeAssetsOptimized()
  {
    // execute this filter only once
    $response = $this->getContext()->getResponse();
    // include stylesheets
    $content = $response->getContent();
    if(is_string($content) && false !== ($pos = strpos($content, '</head>')))
    {
      sfLoader::loadHelpers(array('Tag', 'Asset'));
      $html = '';

      if(!$response->getParameter('auto_discovery_links_included', false, 'sift/view/asset'))
      {
        $html .= get_auto_discovery_links();
      }

      if(!$response->getParameter('stylesheets_included', false, 'sift/view/asset'))
      {
        $html .= get_stylesheets();
      }

      if($html)
      {
        $response->setContent(substr($content, 0, $pos) . $html . substr($content, $pos));
      }
    }

    // include javascripts
    $content = $response->getContent();
    if(is_string($content) && false !== ($pos = strpos($content, '</body>')))
    {
      sfLoader::loadHelpers(array('Tag', 'Asset'));
      $html = '';

      if(!$response->getParameter('javascripts_included', false, 'sift/view/asset'))
      {
        $html .= get_javascripts();
      }

      if($this->getParameter('javascript_config') &&
        !$response->getParameter('javascript_configuration_included', false, 'sift/view/asset'))
      {
        $html .= get_javascript_configuration();
      }

      // we need to get all inline scripts to be included after our javascripts!
      if($inlineScripts = $this->getInlineScripts($content))
      {
        foreach($inlineScripts as $inlineScript)
        {
          $html .= $inlineScript . "\n";
        }
      }

      if($html)
      {
        // we need to get new position where to place it,
        // since content may be modified in getInlineScripts() call
        $pos = strpos($content, '</body>');
        $response->setContent(substr($content, 0, $pos) . $html . substr($content, $pos));
      }
    }

    $response->setParameter('javascripts_included', false, 'sift/view/asset');
    $response->setParameter('javascript_configuration_included', false, 'sift/view/asset');
    $response->setParameter('stylesheets_included', false, 'sift/view/asset');
    $response->setParameter('auto_discovery_links_included', false, 'sift/view/asset');
  }

  /**
   * Gets inline javascript from the content. Also removes the scripts from the content
   * which is passed as reference
   *
   * @param type $content
   * @return type array
   */
  protected function getInlineScripts(&$content)
  {
    // pull out the script blocks
    preg_match_all("!<script[^>]+>.*?</script>!is", $content, $match);
    $scripts = $match[0];
    foreach($scripts as $s => $script)
    {
      // we need to detect document.write here, so the flow is not altered
      if(preg_match('~document\.write\(~i', $script)
         || strpos($script, 'LEAVE HERE') !== false)
      {
        unset($scripts[$s]);
        continue;
      }
      $content = str_replace($script, '', $content);
    }
    return $scripts;
  }

}
