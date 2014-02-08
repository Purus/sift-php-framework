<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFilterChain manages registered filters for a specific context.
 *
 * @package    Sift
 * @subpackage filter
 */
class sfFilterChain
{
    protected $context = null,
        $chain = array(),
        $index = -1;

    /**
     * Constructs the chain
     *
     * @param sfContext $context
     *
     * @inject context
     */
    public function __construct(sfContext $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the context
     *
     * @return sfContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Load filters for given action
     *
     * @param sfAction $actionInstance
     */
    public function load(sfAction $actionInstance)
    {
        require(sfConfigCache::getInstance()->checkConfig(
            sfConfig::get('sf_app_module_dir_name') . '/' . $actionInstance->getModuleName() . '/' . sfConfig::get(
                'sf_app_module_config_dir_name'
            ) . '/filters.yml'
        ));
    }

    /**
     * Executes the next filter in this chain.
     */
    public function execute()
    {
        // skip to the next filter
        ++$this->index;

        if ($this->index < count($this->chain)) {
            // execute if not disabled
            if (!$this->chain[$this->index]->isDisabled(
                $this->context->getModuleName(),
                $this->context->getActionName()
            )
            ) {
                if (sfConfig::get('sf_logging_enabled')) {
                    sfLogger::getInstance()->info(
                        '{sfFilterChain} Executing filter "{filter}".',
                        array(
                            'filter' => get_class($this->chain[$this->index])
                        )
                    );
                }
                // execute the next filter
                $this->chain[$this->index]->execute($this);
            } else {
                if (sfConfig::get('sf_logging_enabled')) {
                    sfLogger::getInstance()->info(
                        '{sfFilterChain} Skipping execution of filter "{filter}".',
                        array(
                            'filter' => get_class($this->chain[$this->index])
                        )
                    );
                }
                // call itself again
                $this->execute();
            }
        }
    }

    /**
     * Returns true if the filter chain contains a filter of a given class.
     *
     * @param string The class name of the filter
     *
     * @return boolean true if the filter exists, false otherwise
     */
    public function hasFilter($class)
    {
        $class = strtolower($class);
        foreach ($this->chain as $filter) {
            if (strtolower(get_class($filter)) == $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registers a filter with this chain.
     *
     * @param sfFilter A sfFilter implementation instance.
     */
    public function register(sfIFilter $filter)
    {
        $this->chain[] = $filter;
    }

}
