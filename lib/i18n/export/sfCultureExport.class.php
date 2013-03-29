<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Exports culture information
 * 
 * @package Sift
 * @subpackage i18n_export
 */
class sfCultureExport extends sfConfigurable implements sfICultureExport {

  /**
   * Culture holder
   * 
   * @var string 
   */
  protected $culture;

  /**
   * Constructs the exporter
   * 
   * @param string $culture User culture which will be exported
   * @param array $options Array of options
   */
  public function __construct($culture, $options = array())
  {
    $this->culture = $culture;
    
    parent::__construct($options);
  }
  
  /**
   * Returns an instance of export driver
   *
   * @param string $driver Driver name
   * @param string $culture Culture which will be exported
   * @return sfCultureExport
   * @throws sfInvalidArgumentException
   */
  public static function factory($driver, $culture)
  {
    $class = sprintf('sfCultureExport%s', $driver);
    if(!class_exists($class))
    {
      throw new sfInvalidArgumentException(sprintf('Invalid export driver "%s" given.', $driver));
    }

    return new $class($culture);
  }

  /**
   * Exports the culture information
   * 
   */
  public function export()
  {
    throw new BadMethodCallException('Implement on your own');
  }

}
