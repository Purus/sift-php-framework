<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFilterConfigHandler allows you to register filters with the system.
 *
 * @package    Sift
 * @subpackage config
 */
class sfFilterConfigHandler extends sfYamlConfigHandler {

  /**
   * Executes this configuration handler
   *
   * @param array An array of absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   */
  public function execute($configFiles)
  {
    // parse the yaml
    $config = self::getConfiguration($configFiles);

    // init our data and includes arrays
    $data = array();
    $includes = array();

    $execution = false;
    $rendering = false;

    // let's do our fancy work
    foreach($config as $category => $keys)
    {
      if(isset($keys['enabled']) && !$keys['enabled'])
      {
        continue;
      }

      if(!isset($keys['class']))
      {
        // missing class key
        throw new sfParseException(sprintf('Configuration file "%s" specifies category "%s" with missing class key', $configFiles[0], $category));
      }

      $class = $keys['class'];

      if(isset($keys['file']))
      {
        // we have a file to include
        $file = $this->replaceConstants($keys['file']);
        $file = $this->replacePath($file);

        if(!is_readable($file))
        {
          // filter file doesn't exist
          throw new sfParseException(sprintf('Configuration file "%s" specifies class "%s" with nonexistent or unreadable file "%s"', $configFiles[0], $class, $file));
        }

        // append our data
        $includes[] = sprintf("require_once('%s');\n", $file);
      }

      // replace constants for all parameters
      if(isset($keys['param']))
      {
        $keys['param'] = $this->replaceConstants($keys['param']);
        foreach($keys['param'] as $paramName => &$paramValue)
        {
          if(is_string($paramValue) && strpos($paramName, '_condition') !== false)
          {
            $paramValue = self::parseCondition($paramValue);
          }
        }
      }

      $condition = true;
      if(isset($keys['param']['condition']))
      {
        $condition = self::parseCondition($keys['param']['condition']);
        unset($keys['param']['condition']);
      }

      $type = isset($keys['param']['type']) ? $keys['param']['type'] : null;
      unset($keys['param']['type']);

      if($condition)
      {
        // parse parameters
        $parameters = isset($keys['param']) ? $this->varExport($keys['param']) : 'null';

        // append new data
        if('security' == $type)
        {
          $data[] = $this->addSecurityFilter($category, $class, $parameters);
        }
        else
        {
          $data[] = $this->addFilter($category, $class, $parameters);
        }

        if('rendering' == $type)
        {
          $rendering = true;
        }

        if('execution' == $type)
        {
          $execution = true;
        }
      }
    }

    if(!$rendering)
    {
      throw new sfParseException(sprintf('Configuration file "%s" must register a filter of type "rendering"', $configFiles[0]));
    }

    if(!$execution)
    {
      throw new sfParseException(sprintf('Configuration file "%s" must register a filter of type "execution"', $configFiles[0]));
    }

    // compile data
    $retval = sprintf("<?php\n" .
        "// auto-generated by sfFilterConfigHandler\n" .
        "// date: %s%s\n%s\n\n", date('Y/m/d H:i:s'), implode("\n", $includes), implode("\n", $data));

    return $retval;
  }

  /**
   * Adds a filter statement to the data.
   *
   * @param string The category name
   * @param string The filter class name
   * @param array  Filter default parameters
   *
   * @return string The PHP statement
   */
  protected function addFilter($category, $class, $parameters)
  {
    $code = array();
    $code[] = sprintf('list($class, $parameters) = sfConfig::get(\'sf_%s_filter\', array(\'%s\', %s));', sfInflector::tableize($category), $class, $parameters);
    $code[] = sprintf('$filter = $this->context->getServiceContainer()->createObject($class);');
    $code[] = 'if(!($filter instanceof sfIFilter))';
    $code[] = '{';
    $code[] = sprintf('  throw new LogicException(sprintf(\'The filter "%%s" does not implement sfIFilter interface.\', get_class($filter), \'%s\'));', $class);
    $code[] = '}';
    $code[] = '$filter->initialize($this->context, $parameters);';
    $code[] = '$this->register($filter);';
    return join("\n", $code) . "\n";
  }

  /**
   * Adds a security filter statement to the data.
   *
   * @param string The category name
   * @param string The filter class name
   * @param array  Filter default parameters
   *
   * @return string The PHP statement
   */
  protected function addSecurityFilter($category, $class, $parameters)
  {
    $code = array();

    // does this action require security?
    $code[] = '// does this action require security?';
    $code[] = 'if($actionInstance->isSecure())';
    $code[] = '{';
    $code[] = sprintf('  list($class, $parameters) = sfConfig::get(\'sf_%s_filter\', array(\'%s\', %s));', sfInflector::tableize($category), $class, $parameters);
    $code[] = sprintf('  $filter = $this->context->getServiceContainer()->createObject($class);');
    $code[] = '  if(!in_array(\'sfISecurityUser\', class_implements($this->getContext()->getUser())))';
    $code[] = '  {';
    $code[] = '      throw new LogicException(\'Security is enabled, but your sfUser implementation does not implement sfISecurityUser interface.\');';
    $code[] = '  }';
    $code[] = '  $filter->initialize($this->context, $parameters);';
    $code[] = '  $this->register($filter);';
    $code[] = '}';

    return join("\n", $code) . "\n";
  }

  /**
   * @see sfConfigHandler
   */
  static public function getConfiguration(array $configFiles)
  {
    $config = self::parseYaml($configFiles[0]);
    foreach(array_slice($configFiles, 1) as $i => $configFile)
    {
      // we get the order of the new file and merge with the previous configurations
      $previous = $config;

      $config = array();
      foreach (self::parseYaml($configFile) as $key => $value)
      {
        $value = (array) $value;
        $config[$key] = isset($previous[$key]) ? sfToolkit::arrayDeepMerge($previous[$key], $value) : $value;
      }

      // check that every key in previous array is still present (to avoid problem when upgrading)
      foreach (array_keys($previous) as $key)
      {
        if (!isset($config[$key]))
        {
          throw new sfConfigurationException(sprintf('The filter name "%s" is defined in "%s" but not present in "%s" file. To disable a filter, add a "enabled" key with a false value.', $key, $configFiles[$i], $configFile));
        }
      }
    }

    $config = self::replaceConstants($config);

    foreach ($config as $category => $keys)
    {
      if (isset($keys['file']))
      {
        $config[$category]['file'] = self::replacePath($keys['file']);
      }
    }

    return $config;
  }
}
