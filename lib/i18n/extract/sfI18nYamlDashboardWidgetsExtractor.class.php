<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts messages from dashboard_widgets.yml files
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nYamlDasboardWidgetsExtractor extends sfI18nYamlMenuExtractor
{
  /**
   * Returns translatable strings for the $item
   *
   * @param array $item
   */
  protected function getFromItem($item)
  {
    if (isset($item['catalogue'])) {
      $this->domain = $this->fixCatalogue($item['catalogue'], $this->getOption('default_catalogue_name', 'messages'));
    }
    // extract from component
    else {
      // domain is module
      // it looks like: component: [myModule, componentName]
      $this->domain = $this->fixCatalogue($item['component'][0], $this->getOption('default_catalogue_name', 'messages'));
    }

    if (isset($item['name'])) {
      $this->strings[$this->domain][] = $item['name'];
    }

    if (isset($item['description'])) {
      $this->strings[$this->domain][] = $item['description'];
    }
  }

}
