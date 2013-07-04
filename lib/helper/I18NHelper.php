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

/**
 * Formats number choice
 *
 * @param string $text
 * @param array $args
 * @param integer $number
 * @param string $catalogue
 * @return string
 * @throws sfException If choice cannot be formatted
 */
function format_number_choice($text, $args = array(), $number, $catalogue = 'messages')
{
  $translated = __($text, $args, $catalogue);

  $choice = new sfI18nChoiceFormatter();
  $retval = $choice->format($translated, $number);

  if($retval === false)
  {
    throw new sfException(sprintf('Unable to parse your choice "%s"', $translated));
  }

  return $retval;
}

/**
 * Formats country ISO code
 *
 * @param string $country_iso
 * @param string $culture
 * @return string
 */
function format_country($country_iso, $culture = null)
{
  $countries = sfCulture::getInstance($culture === null ?
                  sfContext::getInstance()->getUser()->getCulture() : $culture)
                  ->getCountries();
  return isset($countries[$country_iso]) ? $countries[$country_iso] : '';
}

/**
 * Formats language ISO code
 *
 * @param string $language_iso
 * @param string $culture
 * @return string
 */
function format_language($language_iso, $culture = null)
{
  $language_iso = substr($language_iso, 0, 2);

  $languages = sfCulture::getInstance($culture === null ?
                  sfContext::getInstance()->getUser()->getCulture() : $culture)
                ->getLanguages();
  return isset($languages[$language_iso]) ? $languages[$language_iso] : '';
}

/**
 * Format the currency ISO to name
 *
 * @param string $currency_iso Currency ISO code
 * @param string $culture
 * @return string The currency name
 */
function get_currency_name($currency_iso, $culture = null)
{
  return sfCulture::getInstance($culture === null ?
          sfContext::getInstance()->getUser()->getCulture() : $culture)
          ->getCurrency($currency_iso);
}

/**
 * Returns currency symbol for given $culture
 *
 * @param string $currency_iso Currency ISO code
 * @param string $culture
 * @return string The symbol or $currency_iso when symbol is not set
 */
function get_currency_symbol($currency_iso, $culture = null)
{
  $symbol = sfCulture::getInstance($culture === null ?
          sfContext::getInstance()->getUser()->getCulture() : $culture)
          ->getCurrencySymbol($currency_iso);

  return $symbol ? $symbol : $currency_iso;
}

/**
 * Display culture selector
 *
 * @return string HTML code
 */
function i18n_culture_selector($options = array())
{
  $options = _parse_attributes($options);

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

/**
 * Returns an array of enabled cultures
 *
 * @param boolean $format Format using format_language() ?
 * @return array Array of enabled cultures
 */
function get_enabled_cultures($format = true)
{
  $enabled = sfConfig::get('sf_i18n_enabled_cultures', array());
  $cultures = array();
  foreach($enabled as $culture)
  {
    $cultures[$culture] = $format ? format_language($culture) : $culture;
  }
  return $cultures;
}
