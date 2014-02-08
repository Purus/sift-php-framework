<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDebugBacktraceHtmlDecorator renders the backtrace as HTML
 *
 * @package    Sift
 * @subpackage debug
 */
class sfDebugBacktraceHtmlDecorator extends sfDebugBacktraceDecorator
{
    /**
     * Array of default options
     *
     * @var array
     */
    protected $defaultOptions
        = array(
            'template' => '',
            'class'    => 'debug-backtrace',
            'charset'  => 'UTF-8',
            'template' => 'debug_backtrace_html.php'
        );

    /**
     * Array of required options
     *
     * @var array
     */
    protected $requiredOptions
        = array(
            'template_dir'
        );

    /**
     * Setups the decorator
     */
    public function setup()
    {
        if (!is_dir($dir = $this->getOption('template_dir'))) {
            throw new RuntimeException(sprintf('The template directory "%s" does not exist', $dir));
        } elseif (!is_readable(($template = ($dir . '/' . $this->getOption('template'))))) {
            throw new RuntimeException(sprintf('The template "%s" does not exist or is not readable.', $template));
        }
    }

    /**
     * Output the backtrace as HTML
     *
     * @return string
     */
    public function toString()
    {
        return $this->render(
            $this->getOption('template_dir') . '/' . $this->getOption('template'),
            array(
                'traces' => $this->getBacktrace()->get(),
                'class'  => $this->getOption('class')
            )
        );
    }

}
