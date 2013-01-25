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
  protected $strings = array();
  protected $module  = '';
  protected $parentModule;

  /**
   * Array of required options
   * 
   * @var array  
   */
  protected $requiredOptions = array(
    'module'      
  );
  
  public function  __construct($options) 
  {
    parent::__construct($options);    
    $this->module = $this->getOption('module');    
  }

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

    foreach($config as $id => $item)
    {      
      $this->getFromItem($item);
    }
    
    return $this->strings;
  }

  protected function getFromItem($item)
  {
    if(isset($item['module']) && $item['module'] == $this->module
      || (isset($this->parentModule) && $this->parentModule == $this->module))
    {
      if(isset($item['module']))
      {
        $this->parentModule = $item['module'];
      }

      // get title
      if(isset($item['title']))
      {
        $this->strings[] = $item['title'];
      }      
      elseif(isset($item['name']))
      {
        $this->strings[] = $item['name'];
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
  }

}
