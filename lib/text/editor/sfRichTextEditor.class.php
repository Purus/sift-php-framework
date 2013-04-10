<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRichTextEditor is an abstract class for rich text editor classes.
 *
 * @package    Sift
 * @subpackage text_editor
 */
abstract class sfRichTextEditor extends sfConfigurable implements sfIRichTextEditor
{
  /**
   * Returns an instance of rich editor driver
   *
   * @param string $driver Driver name
   * @param array $options Array of options for the driver
   * @return sfIRichTextEditor
   * @throws LogicException
   * @throws InvalidArgumentException
   */
  public static function factory($driver, $options = array())
  {
    $driverObj = false;
    if(class_exists($class = sprintf('sfRichTextEditorDriver%s', ucfirst($driver))))
    {
      $driverObj = new $class($options);
    }
    else if(class_exists($class = $driver))
    {
      $driverObj = new $class($options);
    }

    if($driverObj)
    {
      if(!$driverObj instanceof sfIRichTextEditor)
      {
        throw new LogicException(sprintf('Driver "%s" does not implement sfIRichTextEditor interface.', $driver));
      }
      return $driverObj;
    }
    throw new InvalidArgumentException(sprintf('Invalid rich editor driver "%s".', $driver));
  }

  /**
   * Constructs the editor
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    $options = sfToolkit::arrayDeepMerge($this->loadOptions(), $options);
    parent::__construct($options);
  }

  /**
   * Loads options from config/rich_editor.yml file. Also filters the configuration
   * using "rich_text_editor.load_options" event.
   *
   * @return array Array of options
   */
  public function loadOptions()
  {
    $config = include sfConfigCache::getInstance()->checkConfig('config/rich_editor.yml');
    array_walk_recursive($config, create_function('&$config', '$config = sfToolkit::replaceConstantsWithModifiers($config);'));
    return sfCore::filterByEventListeners($config, 'rich_text_editor.load_options', array(
      'editor' => $this
    ));
  }

  /**
   * Returns options for javascript
   *
   * @return array Array of options to be exported to javascript
   */
  public function getOptionsForJavascript()
  {
    return $this->getOptions();
  }

}
