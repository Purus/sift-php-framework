<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for configurable classes
 *
 * @package    Sift
 * @subpackage config
 */
abstract class sfConfigurable implements sfIConfigurable
{
    /**
     * Default options
     *
     * @var array
     */
    protected $defaultOptions = array();

    /**
     * Required options
     *
     * @var array
     */
    protected $requiredOptions = array();

    /**
     * Valid options
     *
     * @var array
     */
    protected $validOptions = array();

    /**
     * Created from defaults and passed options
     * This should only be modified and accessed through {@link setOption()}, {@link setOptions()}, {@link getOption()}, and {@link getOptions()}
     *
     * @var array
     */
    private $options = array();

    /**
     * Constructor
     *
     * If options are passed they will be merged with {@link $defaultOptions} using
     * the {@link setOptions()} method.
     *
     * After handling the options the {@link setup()} method is called.
     *
     * @param array|object $options
     *
     * @return void
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
        $this->setup();
    }

    /**
     * Returns default options for this object. Takes also default options
     * from inherited classes.
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        $reflection = new ReflectionObject($this);
        $parent = $reflection->getParentClass();
        $defaults = array();
        do {
            if ($parent->isSubclassOf('sfConfigurable')) {
                $defaultProperties = $parent->getDefaultProperties();
                if (isset($defaultProperties['defaultOptions'])) {
                    // we want the defaults to be overriden as inheritance goes up
                    $defaults = array_merge($defaultProperties['defaultOptions'], $defaults);
                }
            }
            $parent = $parent->getParentClass();
        } while (false !== $parent);

        return array_merge($defaults, $this->defaultOptions);
    }

    /**
     * Returns required options. Takes also required options
     * from inherited classes.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        $reflection = new ReflectionObject($this);
        $parent = $reflection->getParentClass();
        $required = array();
        do {
            if ($parent->isSubclassOf('sfConfigurable')) {
                $defaultProperties = $parent->getDefaultProperties();
                if (isset($defaultProperties['requiredOptions'])) {
                    // we want the required options to be overriden as inheritance goes up
                    $required = array_merge($defaultProperties['requiredOptions'], $required);
                }
            }
            $parent = $parent->getParentClass();
        } while (false !== $parent);

        return array_unique(array_merge($required, $this->requiredOptions));
    }

    /**
     * Returns valid options. Takes also valid options from inherited classes.
     *
     * @return array
     */
    protected function getValidOptions()
    {
        $reflection = new ReflectionObject($this);
        $parent = $reflection->getParentClass();
        $valid = array();
        do {
            if ($parent->isSubclassOf('sfConfigurable')) {
                $defaultProperties = $parent->getDefaultProperties();
                if (isset($defaultProperties['validOptions'])) {
                    // we want the valid options to be overriden as inheritance goes up
                    $valid = array_merge($defaultProperties['validOptions'], $valid);
                }
            }
            $parent = $parent->getParentClass();
        } while (false !== $parent);

        return array_unique(array_merge($valid, $this->validOptions));
    }

    /**
     * Validates options only if getValidOptions() returns some.
     *
     * @param array $options
     *
     * @return boolean
     * @throws RuntimeException
     */
    protected function validateOptions($options)
    {
        $validOptions = $this->getValidOptions();

        if (!count($validOptions)) {
            return true;
        }

        $currentOptionKeys = array_values($validOptions);
        $optionKeys = array_keys($options);

        // check options
        if ($diff = array_diff($optionKeys, $currentOptionKeys)) {
            throw new RuntimeException(sprintf(
                '%s does not support the following options: \'%s\'. Valid options are: \'%s\'',
                get_class($this),
                implode('\', \'', $diff),
                implode('\', \'', $currentOptionKeys)
            ));
        }

        return true;
    }

    /**
     * Set options
     *
     * If $options is an object it will be converted into an array by called
     * it's toArray method.
     *
     * @throws InvalidArgumentException
     *
     * @param array|object $options
     *
     * @return sfConfigurable
     */
    public function setOptions($options)
    {
        // first convert to array if needed
        if (!is_array($options)) {
            if (is_object($options) && is_callable(array($options, 'toArray'))) {
                $options = $options->toArray();
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Options for "%s" must be an array or a object with ->toArray() method',
                    get_class($this)
                ));
            }
        }

        // combine the passed options with the defaults
        $defaults = $this->getDefaultOptions();
        $this->options = array_merge($defaults, $options);

        // check required options
        $currentOptionKeys = array_keys($this->options);
        $optionKeys = array_keys($options);
        // check required options
        if ($diff = array_diff($this->getRequiredOptions(), array_merge($currentOptionKeys, $optionKeys))) {
            throw new RuntimeException(sprintf(
                '%s requires the following options: \'%s\'.',
                get_class($this),
                implode('\', \'', $diff)
            ));
        }

        $this->validateOptions($this->options);

        return $this;
    }

    /**
     * Initialization hook
     *
     * Can be used by classes for special behaviour. For instance some options
     * have extra setup work in their 'set' method that also need to be called
     * when the option is passed as a constructor argument.
     *
     * This hook is called by the constructor after saving the constructor
     * arguments in {@link $_options}
     *
     * @internal This empty implementation can optionally be implemented in
     * descending classes. It's not an abstract method on purpose, there are
     * many cases where no initialization is needed.
     *
     * @return void
     */
    protected function setup()
    {
    }

    /**
     * Set an option
     * Returns Configurable for chaining
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return sfConfigurable
     */
    public function setOption($name, $value)
    {
        // not dot syntax
        if (strpos($name, '.') === false) {
            $this->options[$name] = $value;
        } else {
            sfArray::set($this->options, $name, sfToolkit::getValue($value));
        }

        return $this;
    }

    /**
     * Get an option value by name
     *
     * If the option is empty or not set a NULL value will be returned.
     *
     * @param string $name
     * @param mixed  $default Default value if confiuration of $name is not present
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        // no dot found
        if (strpos($name, '.') === false) {
            return $default;
        }

        // allow for groups and multi-dimensional arrays
        return sfArray::get($this->options, $name, $default);
    }

    /**
     * Checks is the object has option with given $name
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasOption($name)
    {
        if (strpos($name, '.') === false) {
            return isset($this->options[$name]);
        }

        return sfArray::keyExists($this->options, $name);
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Adds options. Overrides options already set with the same name.
     *
     * @param array $options
     *
     * @return sfConfigurable
     */
    public function addOptions($options)
    {
        foreach ($options as $o => $v) {
            $this->setOption($o, $v);
        }

        return $this;
    }

}
