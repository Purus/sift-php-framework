<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSanitizeConfigHandler class
 *
 * @package    Sift
 * @subpackage config
 */
class sfSanitizeConfigHandler extends sfSimpleYamlConfigHandler
{
    /**
     * Executes this configuration handler.
     *
     * @param array An array of absolute filesystem path to a configuration file
     *
     * @return string Data to be written to a cache file
     *
     * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
     * @throws sfParseException If a requested configuration file is improperly formatted
     * @throws sfInitializationException If a view.yml key check fails
     */
    public function execute($configFiles)
    {
        // parse the yaml
        $myConfig = $this->parseYamls($configFiles);

        $all = array();
        if (isset($myConfig['all'])) {
            $all = $this->replaceConstants($myConfig['all']);
            unset($myConfig['all']);
        }

        foreach ($myConfig as $section => $value) {
            $myConfig[$section] = sfToolkit::arrayDeepMerge($all, $this->replaceConstants($value));
        }

        // compile data
        $retval = "<?php\n" .
            "// auto-generated by %s\n" .
            "// date: %s\nreturn %s;\n";

        $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'), var_export($myConfig, true));

        return $retval;
    }

}
