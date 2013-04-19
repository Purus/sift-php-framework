<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts messages from menu.yml files
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nYamlMenuExtractor extends sfI18nYamlExtractor
{
  /**
   * Extracted strings
   *
   * @var array
   */
  protected $strings = array();

  /**
   * Catalogue domain
   *
   * @var string
   */
  protected $domain = sfI18nExtract::UNKNOWN_DOMAIN;

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    // default catalogue name for fixCatalogue()
    'default_catalogue_name' => 'messages'
  );

  /**
   * Extract i18n strings for the given content.
   *
   * @param  string The content
   *
   * @return array An array of i18n strings
   */
  public function extract($content)
  {
    $this->strings = array();

    $config = sfYaml::load($content);

    foreach($config as $item)
    {
      $this->getFromItem($item);
    }

    return $this->strings;
  }

  /**
   * Returns translatable strings for the $item
   *
   * @param array $item
   */
  protected function getFromItem($item)
  {
    if(isset($item['catalogue']))
    {
      $this->domain = $this->fixCatalogue($item['catalogue'], $this->getOption('default_catalogue_name', 'messages'));
    }
    // BC compat
    elseif(isset($item['module']))
    {
      $this->domain = $this->fixCatalogue($item['module'], $this->getOption('default_catalogue_name', 'messages'));
    }

    // get title
    if(isset($item['title']))
    {
      $this->strings[$this->domain][] = $item['title'];
    }

    if(isset($item['name']))
    {
      $this->strings[$this->domain][] = $item['name'];
    }

    // BC compatibility
    if(isset($item['sub']) && is_array($item['sub']))
    {
      foreach($item['sub'] as $child)
      {
        $this->getFromItem($child);
      }
    }
    elseif(isset($item['children']) && is_array($item['children']))
    {
      foreach($item['children'] as $child)
      {
        $this->getFromItem($child);
      }
    }
  }

  /**
   * Fixes catalogue name, if there is only moduleName present, appends $default
   * as catalogue name. myFooModule -> myFooModule/messages (when $default = 'messages')
   *
   * @param string $catalogue
   * @param string $default Default catalogue name
   * @return string
   */
  protected function fixCatalogue($catalogue, $default = 'messages')
  {
    // we have to check the presence of catalogue name
    // if there is no catalogue name, we will use "messages"
    if(strpos($catalogue, '/') === false)
    {
      $catalogue = sprintf('%s/%s', $catalogue, $default);
    }
    return $catalogue;
  }

}
