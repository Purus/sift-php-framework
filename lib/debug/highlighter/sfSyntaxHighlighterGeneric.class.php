<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generic code highlighter
 *
 * @package    Sift
 * @subpackage debug_highlighter
 */
class sfSyntaxHighlighterGeneric extends sfSyntaxHighlighter
{
    /**
     * Array of patterns
     *
     * @var array
     */
    protected $patterns = array();

    /**
     * Array of strings
     *
     * @var array
     */
    protected $strings = array();

    /**
     * Preprocesses the code with some generic patterns
     *
     * @return void
     */
    protected function setup()
    {
        $this->addStringPattern();
        $this->addMathPattern();
        $this->addNumberPattern();
        $this->addConstantsPattern();
        $this->addKeywordPattern();
        $this->addBoolPattern();
        $this->addVarPattern();
    }

    /**
     * Reset the highlighter
     *
     * @return sfSyntaxHighlighter
     */
    public function reset()
    {
        $this->patterns = array();
        $this->strings = array();

        return parent::reset();
    }

    /**
     * Process the highlighting
     *
     */
    protected function process()
    {
        $this->setup();
        $this->html = $this->processStrings($this->runPatterns($this->code));
    }

    /**
     * Sets the code
     *
     * @param string $code
     * @param string $charset
     *
     * @return sfSyntaxHighlighter
     */
    public function setCode($code, $charset = 'UTF-8')
    {
        $this->reset();

        return parent::setCode($code, $charset);
    }

    /**
     * Tokenize strings
     *
     * @param string $match
     *
     * @return string
     */
    protected function tokenize($match)
    {
        $key = '##' . uniqid() . '##';

        if (isset($match[0]) && ($match[0] == '/' || $match[0] == '#')) {
            $this->strings[$key] = '<span class="' . $this->getCssPrefix() . 'comment">' . $match . '</span>';

            return $key;
        }

        $this->strings[$key] = '<span class="' . $this->getCssPrefix() . 'string">' . $match . '</span>';

        return $key;
    }

    /**
     * Process all the regex patterns
     *
     * @param string $code
     *
     * @return string The code with patterns replaced
     */
    protected function runPatterns($code)
    {
        return preg_replace(array_keys($this->patterns), array_values($this->patterns), $code);
    }

    /**
     * Process all strings and comments within the code block
     *
     * @param string $code The code to process
     *
     * @return string The string with strings processed
     */
    protected function processStrings($code)
    {
        return str_replace(array_keys($this->strings), array_values($this->strings), $code);
    }

    /**
     * Adds a regex pattern
     *
     * @param string $pattern
     * @param string $replacement
     *
     * @return void
     */
    protected function addPattern($pattern, $replacement)
    {
        $this->patterns[$pattern] = $replacement;
    }

    /**
     * Prepends a regex pattern to the front of the list
     *
     * @param string $pattern
     * @param string $replacement
     *
     * @return void
     */
    protected function prependPattern($pattern, $replacement)
    {
        $pattern = array($pattern => $replacement);
        $this->patterns = array_merge($pattern, $this->patterns);
    }

    /**
     * Regex for strings
     *
     * @return void
     */
    protected function addStringPattern()
    {
        $this->addPattern(
            '/(\/\*.*?\*\/|(?<!\:)\/\/.*?\n|\#.*?\n|(?<!\\\)&quot;.*?(?<!\\\)&quot;|(?<!\\\)\'(.*?)(?<!\\\)\')/isex',
            '$this->tokenize(\'$1\')'
        );
    }

    /**
     * Regex for math stuff
     *
     * @return void
     */
    protected function addMathPattern()
    {
        $this->addPattern(
            '/(&gt;=|&amp;|&lt;=|&gt;|&lt;(?![\?])|=(?![^<>]*>)|\+|\-|\*|[:]{2}|[\|]{2}|[&]{2}|\!)/',
            '<span class="' . $this->getCssPrefix() . 'keyword">$1</span>'
        );
    }

    /**
     * Regex for number pattern
     *
     * @return void
     */
    protected function addNumberPattern()
    {
        $this->addPattern(
            '/(?<!\w)(0x[\da-f]+|\d+)(?!\w)/ix',
            '<span class="' . $this->getCssPrefix() . 'int">$1</span>'
        );
    }

    /**
     * Regex for constants
     *
     * @return void
     */
    protected function addConstantsPattern()
    {
        $this->addPattern(
            '/(?<!\w|>|\$)([A-Z_0-9]{2,})(?!\w|\[)/x',
            '<span class="' . $this->getCssPrefix() . 'int">$1</span>'
        );
    }

    /**
     * Regex for keywords
     *
     * @return void
     */
    protected function addKeywordPattern()
    {
        $this->addPattern(
            '/(?<!\w|\$|\%|\@|>|\\\)(and|or|xor|for|do|while|foreach|as|return|die|exit|if|then|else|
                        elseif|new|delete|try|throw|catch|finally|endif|endforeach|endswitch|class|abstract|function|string|
                        array|object|resource|var|bool|boolean|int|integer|float|double|
                        real|string|array|global|const|case|break|continue|static|public|private|protected|
                        published|extends|switch|void|this|self|struct|
                        char|signed|unsigned|short|long)(?!\w|=")/ix',
            '<span class="' . $this->getCssPrefix() . 'keyword">$1</span>'
        );
    }

    /**
     * Regex for booleans
     *
     * @return void
     */
    protected function addBoolPattern()
    {
        $this->addPattern(
            '/(?<!\w|\$|\%|\@|>)(true|false|null)(?!\w|=")/ix',
            '<span class="' . $this->getCssPrefix() . 'int">$1</span>'
        );
    }

    /**
     * Regex for variables
     *
     * @return void
     */
    protected function addVarPattern()
    {
        $this->addPattern(
            '/(?<!\w)((\$|\%|\@)(\-&gt;|\w)+)(?!\w)/ix',
            '<span class="' . $this->getCssPrefix() . 'variable">$1</span>'
        );
    }

}
