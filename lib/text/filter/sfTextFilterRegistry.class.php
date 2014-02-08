<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextFilterRegistry represents a registry for text filters
 *
 * @package    Sift
 * @subpackage text_filter
 */
class sfTextFilterRegistry implements Countable, sfILoggerAware
{
    /**
     * Default priority
     *
     */
    const DEFAULT_PRIORITY = 10;

    /**
     * Filters holder
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Object cache
     *
     * @var array
     */
    protected $objectCache = array();

    /**
     * The service container
     *
     * @var sfServiceContainer
     */
    protected $serviceContainer;

    /**
     * Constructor
     *
     * @param sfServiceContainer $serviceContainer The service container
     * @param sfILogger          $logger           The logger
     *
     * @inject service_container
     * @inject logger
     */
    public function __construct(sfServiceContainer $serviceContainer, sfILogger $logger = null)
    {
        $this->serviceContainer = $serviceContainer;
        $this->setLogger($logger);

        // load filters
        $this->loadFilters();
    }

    /**
     * Loads filters using sfConfigCache from text_filters.yml configuration file
     */
    protected function loadFilters()
    {
        $configuration = include(sfConfigCache::getInstance()->checkConfig(
            sfConfig::get('sf_app_config_dir_name') . '/text_filters.yml'
        ));
        foreach ($configuration['filters'] as $tagName => $filters) {
            foreach ($filters as $filter) {
                $priority = self::DEFAULT_PRIORITY;
                if (is_array($filter)) {
                    if (isset($filter['priority'])) {
                        $priority = (integer)$filter['priority'];
                        unset($filter['priority']);
                    }
                }

                $this->log(
                    'Registering filter "{filter}" for "{tag}", priority: {priority} ',
                    sfILogger::INFO,
                    array(
                        'filter'   => (is_string($filter) ? $filter : var_export($filter, true)),
                        'tag'      => $tagName,
                        'priority' => $priority
                    )
                );

                // register the filter
                $this->register($tagName, $filter, $priority);
            }
        }
    }

    /**
     * Registers a text tag
     *
     * @param string                 $tag      The tag name
     * @param callable|sfITextFilter $function The callable or instance of the text filter
     * @param integer                $priority Priority
     *
     * @throws InvalidArgumentException If the function is not callable
     * @return sfTextFilterRegistry
     */
    public function register($tag, $function, $priority = self::DEFAULT_PRIORITY)
    {
        if (!$function instanceof sfTextFilterCallbackDefinition) {
            // create the definition from the function
            if (is_string($function)) {
                $function = new sfTextFilterCallbackDefinition($function);
            } elseif (is_array($function)) {
                $function = sfTextFilterCallbackDefinition::createFromArray(
                    $function,
                    'sfTextFilterCallbackDefinition'
                );
            }
        }

        if (!isset($this->filters[$tag])) {
            $this->filters[$tag] = array();
        }

        if (!isset($this->filters[$tag][$priority])) {
            $this->filters[$tag][$priority] = array();
        }

        $this->filters[$tag][$priority][] = $function;

        return $this;
    }

    /**
     * Remove a filter tag
     *
     * @param string $tag The tag name
     *
     * @return sfTExtFilterRegistry
     */
    public function unregister($tag)
    {
        if (isset($this->filters[$tag])) {
            unset($this->filters[$tag]);
        }

        return $this;
    }

    /**
     * Clears the filters
     *
     * @return sfTextFilterRegistry
     */
    public function clear()
    {
        $this->filters = array();

        return $this;
    }

    /**
     * Apply the filters to the content
     *
     * @param string       $tag
     * @param string|array $content
     *
     * @return string|array
     * @throws sfException
     */
    public function apply($tag, $content)
    {
        if (!($filters = $this->getFiltersForTag($tag))) {
            return $content;
        }

        $this->log(
            'Applying tag "{tag}"',
            sfILogger::INFO,
            array(
                'tag' => $tag
            )
        );

        // create the content object
        $content = new sfTextFilterContent($content);
        foreach ($filters as $callbacks) {
            foreach ($callbacks as $callback) {
                $this->callFilter($callback, $content);
                if ($content->cancelBubble()) {
                    break 2;
                }
            }
        }

        return (string)$content;
    }

    /**
     * Calls the filter
     *
     * @param sfTextFilterDefinition $callback
     * @param sfTextFilterContent    $content
     */
    protected function callFilter(sfCallbackDefinition $definition, sfTextFilterContent $content)
    {
        $this->log(
            'Calling filter "{filter}"',
            sfILogger::INFO,
            array(
                'filter' => (string)$definition
            )
        );

        // function
        if (($function = $definition->getFunction())) {
            return call_user_func($function, $content);
        } // class
        else {
            $cacheKey = md5(serialize($definition));
            if (!isset($this->objectCache[$cacheKey])) {
                $filter = $this->objectCache[$cacheKey] = $this->serviceContainer->createObjectFromDefinition(
                    $definition
                );
            } else {
                $filter = $this->objectCache[$cacheKey];
            }

            // does the filtering
            return $filter->filter($content);
        }
    }

    /**
     * Replaces parameter placeholders (%name%) by their values.
     *
     * @param  mixed $value A value
     *
     * @return mixed The same value with all placeholders replaced by their values
     * @throw RuntimeException if a placeholder references a parameter that does not exist
     */
    public function resolveValue($value)
    {
        if (is_array($value)) {
            $args = array();
            foreach ($value as $k => $v) {
                $args[$this->resolveValue($k)] = $this->resolveValue($v);
            }
            $value = $args;
        } elseif (is_string($value)) {
            $value = $this->replaceConstants($value);
        }

        return $value;
    }

    /**
     * Replaces constants (like %SF_CACHE_DIR%...)
     *
     * @param string $value
     *
     * @return mixed
     */
    protected function replaceConstants($value)
    {
        if (preg_match('/%(.+?)%/', $value, $matches)) {
            $name = strtolower($matches[1]);
            if (sfConfig::has($name)) {
                return sfConfig::get($name);
            }
        }

        return $value;
    }

    /**
     * Returns filters for the given tag.
     *
     * @param type $tag
     *
     * @return type
     */
    protected function getFiltersForTag($tag)
    {
        $filters = array();
        // now search the wildcarded filters
        foreach ($this->filters as $filterTag => $priority) {
            // specific tag
            if ($filterTag == $tag) {
                $filters = $this->merge($filters, $this->filters[$filterTag]);
                continue;
            }

            // we have a tag with wildcard
            if (strpos($filterTag, '*') !== false) {
                $parts = explode('.', $filterTag);
                $tagParts = explode('.', $tag);
                if ($parts[0] == '*'
                    && end($parts) == end($tagParts)
                ) {
                    $filters = $this->merge($filters, $this->filters[$filterTag]);
                }
            }
        }
        // sort by priority
        krsort($filters);

        return $filters;
    }

    /**
     * Merges array together while preserving the keys
     *
     * @return array
     */
    protected function merge()
    {
        $output = array();
        foreach (func_get_args() as $array) {
            foreach ($array as $key => $value) {
                $output[$key] = isset($output[$key]) ?
                    array_merge($output[$key], $value) : $value;
            }
        }

        return $output;
    }

    /**
     * Return number of registered filters
     *
     * @return integer
     */
    public function count()
    {
        $count = 0;
        foreach ($this->filters as $tags) {
            foreach ($tags as $callbacks) {
                $count += count($callbacks);
            }
        }

        return $count;
    }

    /**
     * Returns the tags which are registered
     *
     * @return array
     */
    public function getTags()
    {
        return array_keys($this->filters);
    }

    /**
     * Sets the logger
     *
     * @param sfILogger $logger
     */
    public function setLogger(sfILogger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the logger
     *
     * @return sfILogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Logs message to the logger
     *
     * @param string  $message The message to log
     * @param integer $level   The log level
     * @param array   $context Array of context variables
     */
    protected function log($message, $level = sfILogger::INFO, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log(sprintf('{sfTextFilterRegistry} %s', $message), $level, $context);
        }
    }

}
