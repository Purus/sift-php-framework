<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAutoloadConfigHandler
 *
 * @package    Sift
 * @subpackage config
 */
class sfAutoloadConfigHandler extends sfYamlConfigHandler
{
    /**
     * Executes this configuration handler.
     *
     * @param array $configFiles An array of absolute filesystem path to a configuration file
     *
     * @return string Data to be written to a cache file
     *
     * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
     * @throws sfParseException If a requested configuration file is improperly formatted
     */
    public function execute($configFiles)
    {
        // set our required categories list and initialize our handler
        $this->initialize(array('required_categories' => array('autoload')));

        $data = array();

        // prepend core
        $coreAutoload = sfCoreAutoload::getInstance();
        $classes = $coreAutoload->getClassMap();

        // we add core classes as first so project, application can override
        // some classes
        $data[] = sprintf("\n// %s", 'core classes');

        foreach ($classes as $class => $path) {
            $data[] = sprintf("'%s' => '%s',", $class, sfConfig::get('sf_sift_lib_dir') . '/' . $path);
        }

        foreach ($this->parse($configFiles) as $name => $mapping) {
            $data[] = sprintf("\n  // %s", $name);

            foreach ($mapping as $class => $file) {
                $data[] = sprintf("  '%s' => '%s',", $class, str_replace('\\', '\\\\', $file));
            }
        }

        // compile data
        return sprintf(
            "<?php\n" .
            "// auto-generated by sfAutoloadConfigHandler\n" .
            "// date: %s\nreturn array(\n%s\n);\n",
            date('Y/m/d H:i:s'),
            implode("\n", $data)
        );
    }

    /**
     * Parses the configuration files
     *
     * @param array $configFiles
     *
     * @return array
     */
    protected function parse(array $configFiles)
    {
        // parse the yaml
        $config = self::replaceConstants(self::parseYamls($configFiles));
        $catched = array();
        $mappings = array();
        foreach ($config['autoload'] as $name => $entry) {
            $mapping = array();

            // file mapping or directory mapping?
            if (isset($entry['files'])) {
                // file mapping
                foreach ($entry['files'] as $class => $file) {
                    $mapping[strtolower($class)] = $file;
                }
            } else {
                // directory mapping
                $ext = isset($entry['ext']) ? $entry['ext'] : '.php';
                $path = $entry['path'];

                $finder = sfFinder::type('file')->name('*' . $ext)->followLink();

                // recursive mapping?
                $recursive = isset($entry['recursive']) ? $entry['recursive'] : false;
                if (!$recursive) {
                    $finder->maxdepth(0);
                }

                // exclude files or directories?
                if (isset($entry['exclude']) && is_array($entry['exclude'])) {
                    $finder->prune($entry['exclude'])->discard($entry['exclude']);
                }

                if ($matches = sfGlob::find($path, GLOB_BRACE | GLOB_NOSORT | GLOB_ONLYDIR)) {
                    foreach ($finder->in($matches) as $file) {
                        $mapping = array_merge(
                            $mapping,
                            $this->parseFile($path, $file, isset($entry['prefix']) ? $entry['prefix'] : '')
                        );
                        $catched[] = array($path, $file);
                    }
                }
            }

            $mappings[$name] = $mapping;
        }

        return $mappings;
    }

    public static function parseFile($path, $file, $prefix)
    {
        $mapping = array();
        preg_match_all(
            '~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi',
            file_get_contents($file),
            $classes
        );
        foreach ($classes[1] as $class) {
            $localPrefix = '';
            if ($prefix) {
                $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
                $path = preg_quote($path, '~');
                $path = str_replace(array('\{', '\}', '\*'), array('{', '}', '(.+?)'), $path);
                // glob BRACE patterns
                $path = preg_replace_callback(
                    array('/{.*}/'),
                    array('sfAutoloadConfigHandler', 'replacePatterns'),
                    $path
                );
                preg_match(sprintf('~^%s~', $path), str_replace('/', DIRECTORY_SEPARATOR, $file), $match);
                if (isset($match[$prefix])) {
                    $localPrefix = $match[$prefix] . '/';
                }
            }
            $mapping[$localPrefix . strtolower($class)] = $file;
        }

        return $mapping;
    }

    /**
     * Evaluates the configuration files.
     *
     * @param array $configFiles Return a map of className => file
     *
     * @return array
     */
    public function evaluate($configFiles)
    {
        $mappings = array();
        foreach ($this->parse($configFiles) as $mapping) {
            foreach ($mapping as $class => $file) {
                $mappings[$class] = $file;
            }
        }

        return $mappings;
    }

    /**
     * Replaces glob patterns
     *
     * @param array $match
     *
     * @return string
     */
    public static function replacePatterns($match)
    {
        return sprintf('%s', sfGlobToRegex::toRegex($match[0], false, false, true));
    }

}
