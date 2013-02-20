<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Documentation Web Debug Panel
 * 
 * @package     Sift
 * @subpackage  debug
 */
class sfWebDebugPanelDocumentation extends sfWebDebugPanel {

  /**
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    return 'docs';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
    return 'Documentation';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelContent()
  {
    $html = array();
    
    foreach($this->getLinks() as $link => $url)
    {
      $html[] = sfHtml::contentTag('li', sprintf('<a href="%s" target="sift_docs">%s</a>', 
              htmlspecialchars($url, ENT_QUOTES, sfConfig::get('sf_charset')), $link));
    }    
    return sfHtml::contentTag('ul', join("\n", $html));
  }
  
  /**
   * Returns links to documentation
   * 
   * @return array
   */
  protected function getLinks()
  {
    return array(
     'Sift Wiki on Bitbucket' => 'https://bitbucket.org/mishal/sift-php-framework/wiki/Home'        
    );
  }

}
