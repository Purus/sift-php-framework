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
   * RENAME!!!
   *
   * @param array $extracted
   * @param string $context
   * @param string $module
   * @todo REFACTOR!
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
        if(preg_match('/^([a-zA-Z]+)\/([a-zA-Z]+)$/', $domain, $matches))
        {
          $module    = $matches[1];
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

  /**
   * Extracts i18n strings.
   *
   */
  public function extract()
  {
    // extract string from application files
    $extracted = $this->extractFromPhpFiles(array(
      $this->getOption('root_dir').'/'.$this->getOption('lib_dir_name'),
      $this->getOption('app_dir').'/'.$this->getOption('lib_dir_name'),
      $this->getOption('app_dir').'/'.$this->getOption('template_dir_name'),
    ));

    $this->sortExtracted($extracted);

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

    foreach($this->allSeenMessages as $catalogue => $messages)
    {
      $source = sfI18nMessageSource::factory('gettext', dirname($catalogue));
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

}
