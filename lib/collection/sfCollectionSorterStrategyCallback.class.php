<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCollectionSorterStrategyCallback is a sorter which uses php callback as sorting method.
 *
 * @package    Sift
 * @subpackage collection
 */
class sfCollectionSorterStrategyCallback implements sfICollectionSorterStrategy
{
    /**
     * Callback
     *
     * @var string|array|Closure
     */
    protected $callback;

    /**
     * Constructor
     *
     * @param string|array|sfCallbale $callback Valid callback function for sorting
     *
     * @throws InvalidArgumentException If callback is invalid
     */
    public function __construct($callback)
    {
        if ($callback instanceof sfCallable) {
            $callback = $callback->getCallable();
        }

        // check the callback
        if (!sfToolkit::isCallable($callback, false, $callableName)) {
            throw new InvalidArgumentException(sprintf('Invalid callback "%s" given.', $callableName));
        }

        $this->callback = $callback;
    }

    /**
     *
     * @see sfICollectionStragegy::compareTo
     */
    public function compareTo($a, $b)
    {
        return call_user_func($this->callback, $a, $b);
    }

}
