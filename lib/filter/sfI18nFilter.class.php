<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18nFilter sets the culture based on the current dimension
 *
 * @package Sift
 * @subpackage filter
 */
class sfI18nFilter extends sfFilter {

  /**
   * Executes the filter
   *
   * @param sfFilterChain $filterChain
   */
  public function execute(sfFilterChain $filterChain)
  {
    // execute only if i18n is enabled
    if(sfConfig::get('sf_i18n') && $this->isFirstCall())
    {
      $culture = false;
      $dimension = sfConfig::get('sf_dimension', array());
      $cultures = sfConfig::get('sf_i18n_enabled_cultures', array());
      $routingDefaults = sfConfig::get('sf_routing_defaults', array());

      $cultureDimension = isset($dimension['culture']) ? $dimension['culture'] :
                           (isset($routingDefaults['sf_culture']) ? $routingDefaults['sf_culture'] :
                           sfConfig::get('sf_i18n_default_culture'));

      if(count($dimension))
      {
        foreach($cultures as $_culture)
        {
          if($cultureDimension == $_culture
            || $cultureDimension == substr($_culture, 0, 2))
          {
            $culture = $_culture;
            break;
          }
        }
      }

      if($culture)
      {
        $context = $this->getContext();
        $user = $context->getUser();
        $response = $context->getResponse();

        if($user->getCulture() != $culture)
        {
          $user->setCulture($culture);
        }

        // we are generating html
        if(preg_match('|text/html|', $response->getContentType()))
        {
          // meta language is not valid in HTML5
          if(!sfConfig::get('sf_html5', false))
          {
            $response->addMeta('language', $culture, true);
          }

          if(sfConfig::get('sf_i18n_default_culture') != $culture)
          {
            if($this->getParameter('add_stylesheet'))
            {
              $response->addStylesheet($culture, 'last');
            }

            if($this->getParameter('add_javascript'))
            {
              $response->addJavascript(sprintf('i18n/%s.js', $culture), 'last');
            }
          }
        }
      }
    }

    $filterChain->execute();

    if(sfConfig::get('sf_i18n_learning_mode'))
    {
      $this->addTranslationToolbox();
    }

  }

  /**
   * Adds a toolbox for web based translation
   *
   */
  protected function addTranslationToolbox()
  {
    $response = $this->getContext()->getResponse();
    $content = $response->getContent();

    if((false !== strpos($content, '</body>')))
    {
      sfLoader::loadHelpers(array('Tag', 'Url', 'Partial'));
      $html = get_component('sfI18n', 'translationTool');
      // add web debug information to response content
      $newContent = str_ireplace('</body>', $html . '</body>', $content);
      if($content == $newContent)
      {
        $newContent .= $html;
      }
      $response->setContent($newContent);
    }
  }

}
