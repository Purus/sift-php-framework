<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * I18NHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

function format_number_choice($text, $args = array(), $number, $catalogue = 'messages')
{
  $translated = __($text, $args, $catalogue);

  $choice = new sfChoiceFormat();

  $retval = $choice->format($translated, $number);

  if ($retval === false)
  {
    $error = sprintf('Unable to parse your choice "%s"', $translated);
    throw new sfException($error);
  }

  return $retval;
}

function format_country($country_iso, $culture = null)
{
  $c = new sfCulture($culture === null ? sfContext::getInstance()->getUser()->getCulture() : $culture);
  $countries = $c->getCountries();

  return isset($countries[$country_iso]) ? $countries[$country_iso] : '';
}

function format_language($language_iso, $culture = null)
{
  $c = new sfCulture($culture === null ? sfContext::getInstance()->getUser()->getCulture() : $culture);
  $languages = $c->getLanguages();

  return isset($languages[$language_iso]) ? $languages[$language_iso] : '';
}

/**
 * Display culture selector
 *
 * @return string HTML code
 */
function i18n_culture_selector($options = array())
{
  $options          = _parse_attributes($options);

  $supported_langs  = sfConfig::get('sf_i18n_enabled_cultures', array());

  // en_BG => en
  $culture    = substr(sfContext::getInstance()->getUser()->getCulture(), 0, 2);
  // default culture
  $default    = substr(sfConfig::get('sf_i18n_default_culture'), 0, 2);
  // hostname
  $serverName = sfContext::getInstance()->getRequest()->getHost();

  // prepare output
  $html     = array();

  $id = 'i18n-culture-selector';
  if(isset($options['id']))
  {
    $id = $options['id'];
  }

  // display current culture?
  $current = _get_option($options, 'current');
  
  $html[]  = sprintf('<ul id="%s">', $id);

  if(count($supported_langs))
  {
    $reg = array();
    foreach($supported_langs as $supported)
    {
      $reg[substr($supported, 0, 2)] = $supported;
    }

    $regexpr = implode('|', array_keys($reg));
    $clearServerName  = preg_replace("/($regexpr)+\./i", '', $serverName);
  }

  foreach($supported_langs as $lang)
  {
    $lang = substr($lang, 0, 2);
    $f    = format_language($lang, $lang);
    
    // we have current culture
    if($lang == $culture)
    {
      if($current)
      {
        $content = content_tag('span', $f);
      }
      else
      {
        continue;
      }
    }
    else
    {
      $link = 'http://';

      if($lang == $default)
      {
        $link .= $clearServerName;
      }
      else
      {
        $link .= $lang . '.' . $clearServerName;
      }

      $link    = url_for($link, true) . url_for('@homepage');
      $content =  content_tag('a', sprintf('<span><span>%s</span></span>', $f), array('title' => $f, 'href' => $link));
    }

    $html[] = tag('li', array('id' => sprintf('culture-%s', $lang), 'class' => $lang == $culture ? 'current' : 'not-current'), true);
    $html[] = $content;
    $html[] = '</li>';
  }

  $html[] = '</ul>';

  return join("\n", $html);
}

function get_supported_cultures()
{
  return get_enabled_cultures();
}

function get_enabled_cultures()
{
   $enabled = sfConfig::get('sf_i18n_enabled_cultures', array());
   $cultures = array();
   foreach($enabled as $culture)
   {
     $cultures[$culture] = format_language($culture);
   }
   return $cultures;
} 