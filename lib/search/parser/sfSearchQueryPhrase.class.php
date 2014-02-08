<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchQueryPhrase class.
 *
 * @package    Sift
 * @subpackage search
 */
class sfSearchQueryPhrase
{
    const MODE_DEFAULT = 'default';
    const MODE_OR = 'or';
    const MODE_AND = 'and';
    const MODE_EXCLUDE = 'not';

    protected $phrase;
    protected $mode;
    protected $isMultiWord = false;

    /**
     * Constructs the object
     *
     * @param string $input
     * @param string $mode
     */
    public function __construct($input, $mode = self::MODE_DEFAULT)
    {
        $this->phrase = trim($input);
        $this->mode = $mode;
        $this->isMultiWord = (boolean)preg_match('/\s+/', $this->phrase);
    }

    /**
     * Returns the phrase mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Magic
     *
     * @return string
     */
    public function __toString()
    {
        return $this->phrase;
    }

    /**
     * Does the phrase contain more the one word?
     *
     * @return boolean
     */
    public function isMultiWord()
    {
        return $this->isMultiWord;
    }

    /**
     * Returns the words of the phrase.
     *
     * @return array
     */
    public function getWords()
    {
        if ($this->isMultiWord()) {
            return explode(' ', $this->phrase);
        }

        return array($this->phrase);
    }

}
