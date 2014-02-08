<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Define environment config handler.
 *
 * @package    Sift
 * @subpackage config
 */
class sfDefineEnvironmentConfigHandler extends sfYamlConfigHandler
{
  /**
   * Executes this configuration handler.
   *
   * @param  string An absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   */
  public function execute($configFiles)
  {
    // get our prefix
    $prefix = strtolower($this->getParameterHolder()->get('prefix', ''));

    // add dynamic prefix if needed
    if ($this->getParameterHolder()->get('module', false)) {
      $prefix .= "'.strtolower(\$moduleName).'_";
    }

    // parse the yaml
    $myConfig = $this->mergeEnvironment(self::parseYamls($configFiles));

    $values = array();
    foreach ($myConfig as $category => $keys) {
      $values = array_merge($values, $this->getValues($prefix, $category, $keys));
    }

    $data = '';
    foreach ($values as $key => $value) {
      $data .= sprintf("  '%s' => %s,\n", $key, var_export($value, true));
    }

    // compile data
    $retval = '';
    if ($values) {
      $retval = "<?php\n".
                "// auto-generated by sfDefineEnvironmentConfigHandler\n".
                "// date: %s\nsfConfig::add(array(\n%s));\n";
      $retval = sprintf($retval, date('Y/m/d H:i:s'), $data);
    }

    return $retval;
  }

  /**
   * Gets values from the configuration array.
   *
   * @param string The prefix name
   * @param string The category name
   * @param mixed  The key/value array
   *
   * @param array The new key/value array
   */
  protected function getValues($prefix, $category, $keys)
  {
    if (!is_array($keys)) {
      list($key, $value) = $this->fixCategoryValue($prefix.strtolower($category), '', $keys);

      return array($key => $value);
    }

    $values = array();

    $category = $this->fixCategoryName($category, $prefix);

    // loop through all key/value pairs
    foreach ($keys as $key => $value) {
      list($key, $value) = $this->fixCategoryValue($category, $key, $value);
      $values[$key] = $value;
    }

    return $values;
  }

  /**
   * Fixes the category name and replaces constants in the value.
   *
   * @param string The category name
   * @param string The key name
   * @param string The value
   *
   * @param string Return the new key and value
   */
  protected function fixCategoryValue($category, $key, $value)
  {
    // prefix the key
    $key = $category.$key;

    // replace constant values
    $value = $this->replaceConstants($value);

    return array($key, $value);
  }

  /**
   * Fixes the category name.
   *
   * @param string The category name
   * @param string The prefix
   *
   * @return string The fixed category name
   */
  protected function fixCategoryName($category, $prefix)
  {
    // categories starting without a period will be prepended to the key
    if ($category[0] != '.') {
      $category = $prefix.$category.'_';
    } else {
      $category = $prefix;
    }

    return $category;
  }

  /**
   * Merges default, all and current environment configurations.
   *
   * @param array The main configuratino array
   *
   * @param array The merged configuration
   */
  protected function mergeEnvironment($config)
  {
    return sfToolkit::arrayDeepMerge(
      isset($config['default']) && is_array($config['default']) ? $config['default'] : array(),
      isset($config['all']) && is_array($config['all']) ? $config['all'] : array(),
      isset($config[sfConfig::get('sf_environment')]) && is_array($config[sfConfig::get('sf_environment')]) ? $config[sfConfig::get('sf_environment')] : array()
    );
  }
}
