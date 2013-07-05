<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts string from application
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nApplicationExtract extends sfI18nExtract
{
  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'app_dir', 'root_dir'
  );

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'source_driver' => 'gettext'
  );

  /**
   * Extracts i18n strings.
   *
   */
  public function extract()
  {
    $this->extractPhpFiles();
    $this->extractPhpTemplates();
    $this->extractModules();
    $this->extractMenuYamlFiles();
    $this->extractDashboardWidgetsYamlFiles();
    $this->extractUserProfileYamlFiles();
    $this->extractForms();
    $this->extractModels();

    // parse extracted
    $this->parseAllSeenMessages();
  }

  /**
   * Extracts strings from php files belonging to the application.
   *
   */
  protected function extractPhpFiles()
  {
    $directories = sfFinder::type('dir')
            ->not_name('form') // we are extracting forms separatelly
            ->in($this->getOption('app_dir').'/'.$this->getOption('lib_dir_name'));
    $extracted = $this->extractFromPhpFiles($directories);
    $this->sortExtracted($extracted);
  }

  /**
   * Extracts php templates belonging to the application
   *
   */
  protected function extractPhpTemplates()
  {
    $extracted = $this->extractFromPhpFiles(array(
      $this->getOption('app_dir').'/'.$this->getOption('template_dir_name')
    ));
    $this->sortExtracted($extracted);
  }

  /**
   * Extracts strings from the application's modules
   *
   */
  protected function extractModules()
  {
    $modulesDir = $this->getOption('app_dir').'/'.$this->getOption('module_dir_name');
    $modules = sfFinder::type('dir')->maxdepth(0)->ignore_version_control()->in($modulesDir);

    foreach($modules as $module)
    {
      $moduleName = basename($module);

      $moduleExtractor = new sfI18nModuleExtract(array(
          'culture' => $this->getOption('culture'),
          'module_dir' => $module
      ));

      $extracted = $moduleExtractor->extract();
      $this->sortExtracted($extracted, 'module', $moduleName);
    }
  }

  /**
   * Extracts strings from menu.yml yaml files
   *
   */
  protected function extractMenuYamlFiles()
  {
    $menuExtractor = new sfI18nYamlMenuExtractor();
    // menu yaml files
    $menuFiles = sfFinder::type('file')->name('menu.yml')->in($this->getOption('app_dir').'/'.
                  $this->getOption('config_dir_name'));
    foreach($menuFiles as $file)
    {
      $extracted = $menuExtractor->extract(file_get_contents($file));
      $this->sortExtracted($extracted);
    }
  }

  /**
   * Extracts strings from dashboard_widgets.yml yaml files
   *
   * @todo This should be provided by the plugin, not in the core
   */
  protected function extractDashboardWidgetsYamlFiles()
  {
    $menuExtractor = new sfI18nYamlDasboardWidgetsExtractor();
    // menu yaml files
    $menuFiles = sfFinder::type('file')->name('dashboard_widgets.yml')->in($this->getOption('app_dir').'/'.
                  $this->getOption('config_dir_name'));
    foreach($menuFiles as $file)
    {
      $extracted = $menuExtractor->extract(file_get_contents($file));
      $this->sortExtracted($extracted);
    }
  }

  /**
   * Extracts strings from user_profile.yml yaml files
   *
   * @todo This does not belong to core, there should be a way to hook on the extraction
   * process from plugin tasks, leaving this here for now. Needs more work.
   *
   */
  protected function extractUserProfileYamlFiles()
  {
    $menuExtractor = new sfI18nYamlMenuExtractor();

    $userProfileFiles = sfFinder::type('file')->name('user_profile.yml')->in($this->getOption('app_dir').'/'.
                         $this->getOption('config_dir_name'));

    foreach($userProfileFiles as $file)
    {
      ob_start();
      @eval('?>' . file_get_contents($file) . '<?php ');
      $contents = ob_get_contents();
      ob_end_clean();

      if(!$contents)
      {
        continue;
      }

      $extracted = $menuExtractor->extract(file_get_contents($file));
      $this->sortExtracted($extracted);
    }
  }

  /**
   * Extracts strings from application forms
   *
   */
  protected function extractForms()
  {
    $files = sfFinder::type('file')
              ->name('*Form.class.php')
              ->in($this->getOption('app_dir') . '/' .
              $this->getOption('lib_dir_name') . '/form');

    foreach($files as $file)
    {
      // which classes are in the file?
      $classes = sfToolkit::extractClasses($file);
      foreach($classes as $class)
      {
        try {
          // create form extractor
          $extractor = new sfI18nFormExtract(array(
            'form' => $class,
            'culture' => $this->getOption('culture')
          ));
          $this->sortExtracted($extractor->extract());
        }
        catch(Exception $e)
        {
          throw new sfException(
                  sprintf('Error extracting form "%s". Original message was: %s',
                  $class, $e->getMessage()), $e->getCode());
        }
      }
    }
  }

  /**
   * Extract messages from models.
   * Doctrine is only supported ORM.
   *
   */
  protected function extractModels()
  {
    $files = sfFinder::type('file')
              ->name('*.php')
              ->in($this->getOption('app_dir') . '/' .
              $this->getOption('lib_dir_name') . '/model');

    foreach($files as $file)
    {
      // which classes are in the file?
      $classes = sfToolkit::extractClasses($file);
      foreach($classes as $class)
      {
        try
        {
          $extractor = new sfI18nModelExtractor(array(
            'model' => $class
          ));
          $this->sortExtracted($extractor->extract());
        }
        catch(Exception $e)
        {
          throw new sfException(
                  sprintf('Error extracting model "%s". Original message was: %s',
                  $class, $e->getMessage()), $e->getCode());
        }
      }
    }
  }

  /**
   * Parses all seen messages and updates the sources
   *
   */
  protected function parseAllSeenMessages()
  {
    foreach($this->allSeenMessages as $catalogue => $messages)
    {
      $source = sfI18nMessageSource::factory($this->getOption('source_driver'), dirname($catalogue));
      $source->setCulture($this->culture);
      $source->load(basename($catalogue));

      $this->currentMessages[$catalogue] = array();

      foreach($source->read() as $c => $translations)
      {
        foreach($translations as $key => $values)
        {
          $this->currentMessages[$catalogue][] = $key;
        }
      }

      $newMessages = array_diff($this->allSeenMessages[$catalogue], $this->currentMessages[$catalogue]);
      $this->newMessages[$catalogue] = $newMessages;
      $this->oldMessages[$catalogue] = array_diff($this->currentMessages[$catalogue], $this->allSeenMessages[$catalogue]);
      $this->sources[$catalogue] = $source;
    }
  }

  /**
   * Sorts extracted messages by the translation catalogue
   *
   * @param array $extracted
   * @param string $context
   * @param string $module
   */
  protected function sortExtracted($extracted, $context = 'application', $module = null)
  {
    foreach($extracted as $domain => $messages)
    {
      // we have an unknown domain,
      // it means that the translation looks like:
      //
      //  * __('foobar');
      //  * __('foobar %foo%', array('%foo%' => 'string'));
      //
      // and belongs to the global application catalogue

      if(strpos($domain, '%SF_SIFT_DATA_DIR%') !== false)
      {
        continue;
      }
      elseif(strpos($domain, '%SF_PLUGINS_DIR%') !== false)
      {
        continue;
      }

      $domain = $this->replaceConstants($domain);

      var_dump($domain);

      // we have global application catalogue
      if($domain == self::UNKNOWN_DOMAIN ||
              (strpos($domain, '/') === false && $domain == $this->catalogueName))
      {

        switch($context)
        {
          case 'application':
             $key = $this->getOption('app_dir') . '/' . $this->getOption('i18n_dir_name')
                  . '/'. $this->catalogueName;
          break;

          case 'module':
            $key = $this->getOption('app_dir') . '/' . $this->getOption('module_dir_name')
                  . '/'. $module . '/' . $this->getOption('i18n_dir_name') . '/' . $this->catalogueName;
          break;
        }

      }
      // simple catalogue name
      elseif(strpos($domain, '/') === false)
      {
        switch($context)
        {
          case 'application':
              $key =  $this->getOption('app_dir') . '/' . $this->getOption('i18n_dir_name')
                  . '/'. $domain;
          break;

          case 'module':
            $key = $this->getOption('app_dir') . '/' . $this->getOption('module_dir_name')
                  . '/'. $module . '/' . $this->getOption('i18n_dir_name') . '/' . $domain;
          break;
        }
      }
      else
      {
        if(preg_match(sfI18n::$moduleCatalogueRegexp, $domain, $matches))
        {
          $module = $matches[1];
          $catalogue = $matches[2];
          // FIXME: can be from plugin!
          $key = $this->getOption('app_dir') . '/' . $this->getOption('module_dir_name') .
                  '/' . $module . '/' . $this->getOption('i18n_dir_name') . '/' . $catalogue;
        }
        else
        {
          $key = $domain;
        }
      }

      foreach($messages as $message)
      {
        $this->allSeenMessages[$key][] = $message;
      }
    }
  }

}
