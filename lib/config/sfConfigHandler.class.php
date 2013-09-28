<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConfigHandler allows a developer to create a custom formatted configuration
 * file pertaining to any information they like and still have it auto-generate
 * PHP code.
 *
 * @package    Sift
 * @subpackage config
 */
abstract class sfConfigHandler {

  protected
  $parameterHolder = null;

  /**
   * Executes this configuration handler
   *
   * @param array An array of filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   */
  abstract public function execute($configFiles);

  /**
   * Initializes this configuration handler.
   *
   * @param array An associative array of initialization parameters
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws sfInitializationException If an error occurs while initializing this ConfigHandler
   */
  public function initialize($parameters = null)
  {
    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);
  }

  /**
   * Replaces constant identifiers in a value.
   *
   * If the value is an array replacements are made recursively.
   *
   * @param mixed The value on which to run the replacement procedure
   *
   * @return string The new value
   */
  public static function replaceConstants($value)
  {
    if(is_array($value))
    {
      array_walk_recursive($value, create_function('&$value', '$value = sfToolkit::replaceConstants($value);'));
    }
    else
    {
      $value = sfToolkit::replaceConstants($value);
    }

    return $value;
  }

  /**
   * Parses the condition. Condition can be prepend
   * with an exclamation mark, which means that the condition will be negated.
   *
   * @param string $condition The condition like: !%SF_WEB_DEBUG%
   * @return string|boolean The condition
   */
  public static function parseCondition($condition)
  {
    $condition = sfToolkit::replaceConstants($condition);

    $negative = false;
    // negative
    if(preg_match('/^!+/', $condition, $matches, PREG_OFFSET_CAPTURE))
    {
      $condition = str_replace('!', '', $condition);
      $negative = strlen($matches[0][0]);
    }

    $condition = filter_var($condition, FILTER_VALIDATE_BOOLEAN);

    if($negative)
    {
      // odd number, it means the it will be negative
      if($negative % 2 != 0)
      {
        $condition = !$condition;
      }
    }

    return $condition;
  }

  /**
   * Replaces a relative filesystem path with an absolute one.
   *
   * @param string A relative filesystem path
   *
   * @return string The new path
   */
  public static function replacePath($path)
  {
    if(!sfToolkit::isPathAbsolute($path))
    {
      // not an absolute path so we'll prepend to it
      $path = sfConfig::get('sf_app_dir') . '/' . $path;
    }

    return $path;
  }

  /**
   * Gets the parameter holder for this configuration handler.
   *
   * @return sfParameterHolder A sfParameterHolder instance
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Special function for var_export()
   *
   * @param string $var
   * @return void
   */
  public function varExport($var)
  {
    return sfToolkit::varExport($var);
  }

}
