<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerConfigHandler class
 *
 * @package    Sift
 * @subpackage config
 */
class sfMailConfigHandler extends sfSimpleYamlConfigHandler {

  /**
   * Executes this configuration handler.
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
    $myConfig = $this->parseYamls($configFiles);

    $config = sfToolkit::arrayDeepMerge(
      isset($myConfig['default']) && is_array($myConfig['default']) ? $myConfig['default'] : array(),
      isset($myConfig['all']) && is_array($myConfig['all']) ? $myConfig['all'] : array(),
      isset($myConfig[sfConfig::get('sf_environment')]) && is_array($myConfig[sfConfig::get('sf_environment')]) ? $myConfig[sfConfig::get('sf_environment')] : array()
    );

    // replace all constants
    $config = $this->replaceConstants($config);
    
    // compile data
    $retval = "<?php\n".
              "// auto-generated by %s\n".
              "// date: %s\nreturn %s;\n";
    $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'), var_export($config, true));
    return $retval;
  }

}
