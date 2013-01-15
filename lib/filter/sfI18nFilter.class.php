<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfFlashFilter setups i18n for currect response (stylesheets, javascripts)
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
  public function execute($filterChain)
  {
    
    $context  = $this->getContext();
    $response = $context->getResponse();
    
    if(sfConfig::get('sf_i18n') && $this->isFirstCall()
      && preg_match('|text/html|', $response->getContentType()))
    {
      $found               = false;
      $dimension           = sfConfig::get('sf_dimension', array());
      $routing_defaults    = sfConfig::get('sf_routing_defaults', array());

      $culture_dimension   = isset($dimension['culture']) ? $dimension['culture']
                                : isset($routing_defaults['sf_culture']) ?
                                $routing_defaults['sf_culture'] : Config::get('sf_i18n_default_culture');

      // enabled cultures
      $cultures  = sfConfig::get('sf_i18n_enabled_cultures', array());
      
      foreach($cultures as $culture)
      {
        if($culture_dimension == $culture || $culture_dimension == substr($culture, 0, 2))
        {
          $found = $culture;
          break;
        }        
      }

      if($found)
      {
        if($this->getContext()->getUser()->getCulture() != $found)
        {
          $this->getContext()->getUser()->setCulture($found);
          $this->log(sprintf('Applying detected requested lang to session: %s', $found));
        }
        
        sfConfig::set('sf_current_culture', $culture);
        
        if(!sfConfig::get('sf_html5', false))
        {
          $response->addMeta('language', $culture, true);
        }

        if($this->getParameter('add_stylesheet'))
        {
          if(sfConfig::get('sf_i18n_default_culture') != $culture)
          {
            $stylesheet = substr($culture, 0, 2);
            $this->log(sprintf('Adding custom culture stylesheet "%s.css"', $stylesheet));
            $response->addStylesheet($stylesheet, 'last');
          }
          
          // adds javascript
          $javascript = sprintf('/js/i18n/%s.js', $culture);
          $response->addJavascript($javascript, 'last');
        }
      }
    }

    $filterChain->execute();
    
    $content = $this->getContext()->getResponse()->getContent();
    
    if(sfConfig::get('sf_i18n_learning_mode') && (false !== ($pos = strpos($content, '</body>'))))
    {
      sfLoader::loadHelpers(array('Tag', 'Url', 'Partial'));

      $translations = $this->getContext()->getI18n()->getRequestedTranslations();
      $trans        = array_values($translations);
      $untranslated = 0;
      foreach($trans as $catalogue => $catalogueTranslations)
      {
        foreach($catalogueTranslations as $transId => $trans)
        {
          if($trans['is_translated'] == false) $untranslated++;
        }
      }

      $html = '';
      $html .= get_partial('sf_i18n/translationBox', array('untranslated' => $untranslated));

      $html .= "<script>\n";
      $html .= "\tvar _mg_i18n_messages = ".sfJson::encode($translations);
      $html .= "\n</script>\n";

      // add web debug information to response content
      $newContent = str_ireplace('</body>', $html.'</body>', $content);
      if($content == $newContent)
      {
        $newContent .= $html;
      }
      $response->setContent($newContent);
    }
  }
  
  protected function log($message, $level = SF_LOG_DEBUG)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->log(sprintf('{sfI18nFilter} %s', $message), $level);
    }
  }

}
