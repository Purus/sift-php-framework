<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCalendarRenderer is a renderer base class
 *
 * @package Sift
 * @subpackage calendar
 */
abstract class sfCalendarRenderer implements sfICalendarRenderer
{
  /**
   * Options holder
   *
   * @var array
   */
  protected $options = array();

  /**
   * Translation catalogue
   *
   * @var string
   */
  protected $translationCatalogue = '%SF_SIFT_DATA_DIR%/i18n/catalogues/calendar';

  /**
   * Contructs the renderer
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    $this->setOptions($options);

    // setup translation catalogue
    if ($this->translationCatalogue) {
      $this->setTranslationCatalogue($this->translationCatalogue);
    }
  }

  /**
   * Sets options for this renderer
   *
   * @param array $options
   * @throws InvalidArgumentException
   */
  public function setOptions($options)
  {
    if (!is_array($options)) {
      throw new InvalidArgumentException(
              sprintf('Invalid argument passed. "%s" given. Options should be an array.', gettype($options))
              );
    }
    $this->options = sfToolkit::arrayDeepMerge($this->options, $options);
  }

  /**
   * Returns options
   *
   * @return array
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Returns option with given $name
   *
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public function getOption($name, $default = null)
  {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }

  /**
   * Returns an array of required stylesheets
   *
   * @return array
   */
  public function getStylesheets()
  {
    return array();
  }

  /**
   * Returns an array of required javascripts
   *
   * @return array
   */
  public function getJavascripts()
  {
    return array();
  }

  /**
   * Generates url from the given $route using sfController
   *
   * @param string $route
   * @param boolean $absolute
   * @return string
   */
  protected function generateUrl($route, $absolute = false)
  {
    return sfContext::getInstance()->getController()->genUrl($route, $absolute);
  }

  /**
   * Translates given $string
   *
   * @param string $str
   * @param array $arguments
   * @return string
   */
  protected function __($str, $arguments = array())
  {
    return __($str, $arguments, $this->getTranslationCatalogue());
  }

  /**
   * Returns translation catalogue
   *
   * @return string
   */
  protected function getTranslationCatalogue()
  {
    return $this->translationCatalogue;
  }

  /**
   * Sets translation catalogue to this renderer
   *
   * @param string $catalogue Catalogue name. Can use constants and can be module/catalogue pair
   * @return sfCalendar
   * @throws InvalidArgumentException
   */
  public function setTranslationCatalogue($catalogue)
  {
    if ($catalogue) {
      $catalogue = sfToolkit::replaceConstants($catalogue);
      if (!sfToolkit::isPathAbsolute($catalogue)) {
        // we have to do some detection
        $parts = explode('/', $catalogue);
        if (count($parts) != 2) {
          throw new InvalidArgumentException(sprintf(
            'Invalid translation catalogue "%s" given to the calendar renderer "%s"',
                  $catalogue, get_class($this)));
        }
        $moduleName = $parts[0];
        $catalogueName = $parts[1];
        $catalogue = sfLoader::getI18NDir($moduleName) . '/' . $catalogueName;
      }
    }
    $this->translationCatalogue = $catalogue;

    return $this;
  }

}
