<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRootConfigHandler allows you to specify configuration handlers for the
 * application or on a module level.
 *
 * @package    Sift
 * @subpackage config
 */
class sfRootConfigHandler extends sfYamlConfigHandler
{
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
    $config = $this->parseYamls($configFiles);

    // determine if we're loading the system config_handlers.yml or a module config_handlers.yml
    $moduleLevel = ($this->getParameterHolder()->get('module_level') === true) ? true : false;

    if ($moduleLevel)
    {
      // get the current module name
      $moduleName = $this->getParameterHolder()->get('module_name');
    }

    // init our data and includes arrays
    $data     = array();
    $includes = array();

    // let's do our fancy work
    foreach ($config as $category => $keys)
    {
      if ($moduleLevel)
      {
        // module-level registration, so we must prepend the module
        // root to the category
        $category = 'modules/'.$moduleName.'/'.$category;
      }

      if(!isset($keys['class']))
      {
        // missing class key
        throw new sfParseException(sprintf('Configuration file "%s" specifies category "%s" with missing class key', $configFiles[0], $category));
      }

      $class = $keys['class'];

      if (isset($keys['file']))
      {
        // we have a file to include
        $file = $this->replaceConstants($keys['file']);
        $file = $this->replacePath($file);

        if (!is_readable($file))
        {
          // handler file doesn't exist
          throw new sfParseException(sprintf('Configuration file "%s" specifies class "%s" with nonexistent or unreadable file "%s"', $configFiles[0], $class, $file));
        }

        // append our data
        $includes[] = sprintf("require_once('%s');", $file);
      }

      // parse parameters
      $parameters = (isset($keys['param']) ? var_export($keys['param'], true) : null);

      // append new data
      $data[] = sprintf("\$this->handlers['%s'] = new %s();", $category, $class);

      // initialize the handler with parameters
      $data[] = sprintf("\$this->handlers['%s']->initialize(%s);", $category, $parameters);
    }

    // compile data
    $retval = sprintf("<?php\n" .
                      "// auto-generated by sfRootConfigHandler\n".
                      "// date: %s\n%s\n%s\n",
                      date('Y/m/d H:i:s'), implode("\n", $includes), implode("\n", $data));

    return $retval;
  }
}
