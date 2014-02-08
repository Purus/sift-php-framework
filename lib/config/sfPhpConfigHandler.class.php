<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPhpConfigHandler allows you to override php.ini configuration at runtime.
 *
 * @package    Sift
 * @subpackage config
 */
class sfPhpConfigHandler extends sfYamlConfigHandler
{
  /**
   * Array of directives removed from PHP 5.4 and up
   *
   * @var array
   * @link http://www.php.net/manual/en/migration54.incompatible.php
   */
  protected $removed = array(
    'register_globals',
    'magic_quotes_gpc',
    'magic_quotes_runtime',
    'magic_quotes_sybase',
    'register_long_arrays'
  );

  /**
   * Executes this configuration handler
   *
   * @param array An array of absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   * @throws sfInitializationException If a php.yml key check fails
   */
  public function execute($configFiles)
  {
    if (!sfToolkit::isCallable('ini_set')) {
      throw new sfInitializationException('The function ini_set() is not available. It may be disabled by the system admin.');
    }

    // parse the yaml
    $config = self::replaceConstants(self::parseYamls($configFiles));

    // init our data array
    $data = array();

    // get all php.ini configuration
    $configs = ini_get_all();

    // set some php.ini keys
    if (isset($config['set'])) {
      foreach ($config['set'] as $key => $value) {
        $key = strtolower($key);
        // key exists?
        if (!array_key_exists($key, $configs)) {
          // can be this directive ignored?
          if ($this->canBeIgnored($key)) {
            continue;
          }
          throw new sfParseException(sprintf('Configuration file "%s" specifies key "%s" which is not a php.ini directive.', $configFiles[0], $key));
        }

        // key is overridable?
        // 63 is returned by PHP 5.2.6 instead of 7 when a php.ini key is changed several times per script
        // PHP bug:         http://bugs.php.net/bug.php?id=44936
        // Resolution diff: http://cvs.php.net/viewvc.cgi/ZendEngine2/zend_ini.c?r1=1.39.2.2.2.26&r2=1.39.2.2.2.27&pathrev=PHP_5_2
        if ($configs[$key]['access'] != 7 && $configs[$key]['access'] != 63) {
          throw new sfParseException(sprintf('Configuration file "%s" specifies key "%s" which cannot be overrided.', $configFiles[0], $key));
        }
        $data[] = sprintf("ini_set('%s', %s);", $key, $this->getValue($value));
      }
    }

    // check some php.ini settings
    if (isset($config['check'])) {
      foreach ($config['check'] as $key => $value) {
        $key = strtolower($key);

        // key exists?
        if (!array_key_exists($key, $configs)) {
          // can be this directive ignored?
          if ($this->canBeIgnored($key)) {
            continue;
          }
          throw new sfParseException(sprintf('Configuration file "%s" specifies key "%s" which is not a php.ini directive.', $configFiles[0], $key));
        }

        if (ini_get($key) != $value) {
          throw new sfInitializationException(sprintf('Configuration file "%s" specifies that php.ini "%s" key must be set to "%s". The current value is "%s" (%s).', $configFiles[0], $key, $this->varExport($value), $this->convertToString($this->varExport(ini_get($key))), $this->getIniPath()));
        }
      }
    }

    // warn about some php.ini settings
    if (isset($config['warn'])) {
      foreach ($config['warn'] as $key => $value) {
        $key = strtolower($key);

        // key exists?
        if (!array_key_exists($key, $configs)) {
          // can be this directive ignored?
          if ($this->canBeIgnored($key)) {
            continue;
          }
          throw new sfParseException(sprintf('Configuration file "%s" specifies key "%s" which is not a php.ini directive.', $configFiles[0], $key));
        }

        $warning = sprintf('{sfPhpConfigHandler} php.ini "%s" key is better set to "%s" (current value is "%s" - %s).', $key, $this->varExport($value), $this->varExport(ini_get($key)), $this->getIniPath());
        $data[] = sprintf("if(ini_get('%s') != %s)\n{\n  sfLogger::getInstance()->warning('%s');\n}\n", $key, $this->varExport($value), str_replace("'", "\\'", $warning));
      }
    }

    // check for some extensions
    if (isset($config['extensions'])) {
      foreach ($config['extensions'] as $extension_name) {
        if (!extension_loaded($extension_name)) {
          throw new sfInitializationException(sprintf('Configuration file "%s" specifies that the PHP extension "%s" should be loaded. (%s).', $configFiles[0], $extension_name, $this->getIniPath()));
        }
      }
    }

    // compile data
    $retval = sprintf("<?php\n" .
        "// auto-generated by sfPhpConfigHandler\n" .
        "// date: %s\n%s\n", date('Y/m/d H:i:s'), implode("\n", $data));

    return $retval;
  }

  /**
   * Convert the value to string
   *
   * @param mixed $value
   * @return string
   */
  protected function convertToString($value)
  {
    return $value;
  }

  /**
   * Gets the value for ini_set usage
   *
   * @param mixed $value
   * @return string|integer
   */
  protected function getValue($value)
  {
    if (is_bool($value)) {
      return $value ? 1 : 0;
    } elseif (is_numeric($value)) {
      return intval($value);
    }

    return sprintf("'%s'", str_replace("'", "\\'", trim($value)));
  }

  /**
   * Gets the php.ini path used by PHP.
   *
   * @return string the php.ini path
   */
  protected function getIniPath()
  {
    $cfg_path = get_cfg_var('cfg_file_path');
    if ($cfg_path == '') {
      $ini_path = 'WARNING: system is not using a php.ini file';
    } else {
      $ini_path = sprintf('php.ini location: "%s"', $cfg_path);
    }

    return $ini_path;
  }

  /**
   * Can be this php.ini directive ignored?
   *
   * @param string $directive The directive name like `register_globals`
   * @return boolean
   */
  protected function canBeIgnored($directive)
  {
    return in_array($directive, $this->removed) || strpos($directive, 'xdebug.') === 0;
  }

}
