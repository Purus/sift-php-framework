<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMarkdownTextFilter converts the simple markdown text to HTML
 *
 * @package    Sift
 * @subpackage text_filter
 */
class sfMarkdownTextFilter extends sfTextFilter
{
    /**
     * Markdown parser
     *
     * @var sfMarkdDownParser
     */
    protected $parser;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->parser = new sfMarkdownParser($this->getOptionsForParser());
    }

    /**
     * Returns options for the markdown parser
     *
     * @return array
     */
    protected function getOptionsForParser()
    {
        return array();
    }

    /**
     * Filters the content
     *
     * @param sfTextFilterContent $content
     */
    public function filter(sfTextFilterContent $content)
    {
        $converted = $this->parser->transform($content->getText());
        $content->setText($converted);
    }

}
