<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts messages from module
 * 
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nModuleExtract extends sfI18nExtract
{
  /**
   * Required options
   * 
   * @var array 
   */
  protected $requiredOptions = array(
    'module_dir'
  );
  
  /**
   * Configures the current extract object.
   */
  public function configure()
  {
  }

  /**
   * Extracts i18n strings.
   *
   * This class must be implemented by subclasses.
   */
  public function extract()
  {
    $moduleDir = $this->getOption('module_dir');
    
    $messages = $this->extractFromPhpFiles(array(
      $moduleDir.'/'.$this->getOption('action_dir_name'),
      $moduleDir.'/'.$this->getOption('lib_dir_name'),
      $moduleDir.'/'.$this->getOption('template_dir_name'),
    ));
    
    // Extract from generator.yml files
    $generator = $moduleDir.'/'.$this->getOption('config_dir_name').'/generator.yml';
    if(file_exists($generator))
    {
      $yamlExtractor = new sfI18nYamlGeneratorExtractor();      
      $generatorMessages = $yamlExtractor->extract(file_get_contents($generator));

      if(!isset($messages[sfI18nExtract::UNKNOWN_DOMAIN]))
      {
        $messages[sfI18nExtract::UNKNOWN_DOMAIN] = array();
      }
        
      $messages[sfI18nExtract::UNKNOWN_DOMAIN] = array_merge($messages[sfI18nExtract::UNKNOWN_DOMAIN], 
                                                            $generatorMessages);      
    }

    // Extract from validate/*.yml files
    $validateFiles = glob($moduleDir.'/'.$this->getOption('validate_dir_name').'/*.yml');
    if (is_array($validateFiles))
    {
      foreach ($validateFiles as $validateFile)
      {
        $yamlExtractor = new sfI18nYamlValidateExtractor();
        $validatorMessages = ($yamlExtractor->extract(file_get_contents($validateFile)));
        
        if(!isset($messages[sfI18nExtract::UNKNOWN_DOMAIN]))
        {
          $messages[sfI18nExtract::UNKNOWN_DOMAIN] = array();
        }        
        $messages[sfI18nExtract::UNKNOWN_DOMAIN] = array_merge($messages[sfI18nExtract::UNKNOWN_DOMAIN], 
                                                  $validatorMessages);        
      }
    }
    
//    // Extract from menu.yml file
//    $menu = sfConfig::get('sf_plugins_dir').'/'.$this->plugin.'/'.sfConfig::get('sf_app_module_config_dir_name').'/admin/menu.yml';
//    if(file_exists($menu))
//    {
//      $yamlExtractor = new sfI18nYamlMenuExtractor(array('module' => $this->module));
//      $this->updateMessages($yamlExtractor->extract(file_get_contents($menu)));
//    }
//    
//    // Extract from user_profile.yml file
//    $menu = sfConfig::get('sf_plugins_dir').'/'.$this->plugin.'/'.sfConfig::get('sf_app_module_config_dir_name').'/user_profile.yml';
//    if(file_exists($menu))
//    {
//      $yamlExtractor = new sfI18nYamlMenuExtractor(array('module' => $this->module));
//      
//      ob_start(); 
/*      @eval('?>' . file_get_contents($menu) . '<?php '); */
//      $contents = ob_get_contents();
//      ob_end_clean();
//      
//      $this->updateMessages($yamlExtractor->extract($contents));
//    }
    
    return $messages;
  }
    
}