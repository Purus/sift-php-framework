<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extract i18n strings
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
abstract class sfI18nExtract extends sfConfigurable {

  /**
   * Unknown translation domain
   *
   */
  const UNKNOWN_DOMAIN = '___UNKNOWN_DOMAIN___';

  /**
   * Global application domain
   */
  const GLOBAL_APPLICATION_DOMAIN = '__GLOBAL_APPLICATION_DOMAIN__';

  protected
    $sources = array(),
    $allSeenMessages = array(),
    $currentMessages = array(),
    $oldMessages = array(),
    $newMessages = array(),
    $culture = null,
    $catalogueName = 'messages';

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'source_type'     => 'gettext',

    'config_dir_name' => 'config',
    'module_dir_name' => 'modules',
    'action_dir_name' => 'actions',
    'lib_dir_name' => 'lib',
    'template_dir_name' => 'templates',
    'validate_dir_name' => 'validate',
    'i18n_dir_name' => 'i18n'
  );

  /**
   * Array of required options
   * @var array
   */
  protected $requiredOptions = array(
    'culture'
  );

  /**
   * Array of options
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    parent::__construct($options);

    $this->culture = $options['culture'];
    $this->configure();
  }

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
  abstract public function extract();

  /**
   * Saves the new messages.
   *
   */
  public function saveNewMessages()
  {
    foreach($this->newMessages as $catalogue => $messages)
    {
      // no new messages in this source
      if(!count($messages))
      {
        continue;
      }

      $source = $this->sources[$catalogue];

      foreach($messages as $message)
      {
        $source->append($message);
      }

      $source->save(basename($catalogue));
    }
  }

  /**
   * Deletes old messages.
   *
   */
  public function deleteOldMessages()
  {
    foreach($this->oldMessages as $catalogue => $messages)
    {
      if(!count($messages))
      {
        continue;
      }
      $source = $this->sources[$catalogue];
      foreach($messages as $message)
      {
        $source->delete($message, basename($catalogue));
      }
    }
  }

  /**
   * Gets the new i18n strings.
   *
   * @param array An array of i18n strings
   */
  final public function getNewMessages()
  {
    return $this->newMessages;
  }

  /**
   * Gets the new i18n strings.
   *
   * @param array An array of i18n strings
   */
  final public function getNewMessagesCount()
  {
    $count = 0;
    foreach($this->newMessages as $source => $messages)
    {
      $count += count($messages);
    }
    return $count;
  }

  /**
   * Gets the current i18n strings.
   *
   * @param array An array of i18n strings
   */
  public function getCurrentMessages()
  {
    return $this->currentMessages;
  }

  /**
   * Gets the current i18n strings.
   *
   * @param array An array of i18n strings
   */
  public function getCurrentMessagesCount()
  {
    $count = 0;
    foreach($this->currentMessages as $source => $messages)
    {
      $count += count($messages);
    }
    return $count;
  }

  /**
   * Gets all i18n strings seen during the extraction process.
   *
   * @param array An array of i18n strings
   */
  public function getAllSeenMessages()
  {
    return $this->allSeenMessages;
  }

  /**
   * Gets count of all i18n strings seen during the extraction process.
   *
   * @param integer Count of all seen messages
   */
  public function getAllSeenMessagesCount()
  {
    $count = 0;
    foreach($this->allSeenMessages as $source => $messages)
    {
      $count += count($messages);
    }
    return $count;
  }

  /**
   * Gets old i18n strings.
   *
   * This returns all strings that weren't seen during the extraction process
   * and are in the current messages.
   *
   * @param array An array of i18n strings
   */
  final public function getOldMessages()
  {
    return $this->oldMessages;
  }

  /**
   * Gets old i18n strings.
   *
   * This returns all strings that weren't seen during the extraction process
   * and are in the current messages.
   *
   * @param array An array of i18n strings
   */
  final public function getOldMessagesCount()
  {
    $count = 0;
    foreach($this->oldMessages as $source => $messages)
    {
      $count += count($messages);
    }
    return $count;
  }

  protected function extractFromPhpFiles($dir)
  {
    $phpExtractor = new sfI18nPhpExtractor();

    $files = sfFinder::type('file')->name('*.php');

    $extracted = array();
    foreach($files->in($dir) as $file)
    {
      $e = $phpExtractor->extract(file_get_contents($file));
      if(!count($e))
      {
        continue;
      }
      $extracted = array_merge_recursive($extracted, $e);
    }

    foreach($extracted as $domain => $messages)
    {
      $extracted[$domain] = array_unique($messages);
    }

    return $extracted;
  }

  /**
   * Replaces constants in the string
   *
   * @param string $string
   * @return string
   */
  protected function replaceConstants($string)
  {
    return strtr($string, array(
          '%SF_SIFT_DATA_DIR%' => $this->getOption('sf_sift_data_dir'),
          '%SF_PLUGINS_DIR%' => $this->getOption('sf_plugins_dir')
    ));
  }

}
