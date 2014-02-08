<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDatabaseConfigHandler allows you to setup database connections in a
 * configuration file that will be created for you automatically upon first
 * request.
 *
 * @package    Sift
 * @subpackage config
 */
class sfDatabaseConfigHandler extends sfYamlConfigHandler
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
     */
    public function execute($configFiles)
    {
        list($includes, $data) = $this->parse($configFiles);

        foreach ($includes as $i => $include) {
            $includes[$i] = sprintf("require_once('%s');", $include);
        }

        foreach ($data as $name => $database) {
            $data[$name] = sprintf("\n'%s' => new %s(%s),", $name, $database[0], $this->varExport($database[1]));
        }

        // compile data
        return sprintf(
            "<?php\n" .
            "// auto-generated by sfDatabaseConfigHandler\n" .
            "// date: %s\n%s\nreturn array(%s);\n",
            date('Y/m/d H:i:s'),
            implode("\n", $includes),
            implode("\n", $data)
        );
    }

    public function evaluate($configFiles)
    {
        list($includes, $data) = $this->parse($configFiles);

        foreach ($includes as $i => $include) {
            require_once($include);
        }

        $databases = array();
        foreach ($data as $name => $database) {
            $databases[$name] = new $database[0]($database[1]);
        }

        return $databases;
    }

    protected function parse($configFiles)
    {
        // parse the yaml
        $config = self::getConfiguration($configFiles);

        // init our data and includes arrays
        $data = array();
        $databases = array();
        $includes = array();

        // get a list of database connections
        foreach ($config as $name => $dbConfig) {
            // is this category already registered?
            if (in_array($name, $databases)) {
                // this category is already registered
                throw new sfParseException(sprintf(
                    'Configuration file "%s" specifies previously registered category "%s".',
                    $configFiles[0],
                    $name
                ));
            }

            // add this database
            $databases[] = $name;

            // let's do our fancy work
            if (!isset($dbConfig['class'])) {
                // missing class key
                throw new sfParseException(sprintf(
                    'Configuration file "%s" specifies category "%s" with missing class key.',
                    $configFiles[0],
                    $name
                ));
            }

            if (isset($dbConfig['file'])) {
                // we have a file to include
                if (!is_readable($dbConfig['file'])) {
                    // database file doesn't exist
                    throw new sfParseException(sprintf(
                        'Configuration file "%s" specifies class "%s" with nonexistent or unreadable file "%s".',
                        $configFiles[0],
                        $dbConfig['class'],
                        $dbConfig['file']
                    ));
                }

                // append our data
                $includes[] = $dbConfig['file'];
            }

            // parse parameters
            $parameters = array();
            if (isset($dbConfig['param'])) {
                $parameters = $dbConfig['param'];
            }
            $parameters['name'] = $name;

            // append new data
            $data[$name] = array($dbConfig['class'], $parameters);
        }

        return array($includes, $data);
    }

    /**
     * @see sfConfigHandler
     */
    public function getConfiguration(array $configFiles)
    {
        $config = $this->replaceConstants(self::flattenConfigurationWithEnvironment(self::parseYamls($configFiles)));

        foreach ($config as $name => $dbConfig) {
            if (isset($dbConfig['file'])) {
                $config[$name]['file'] = self::replacePath($dbConfig['file']);
            }
        }

        return $config;
    }

}
