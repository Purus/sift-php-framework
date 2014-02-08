<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Html code highlighter
 *
 * @package    Sift
 * @subpackage debug_highligher
 */
class sfSyntaxHighlighterHtml extends sfSyntaxHighlighterGeneric
{
    /**
     * @var array
     */
    protected $phpBlocks = array();

    /**
     * regex rules for html
     *
     * @return void
     */
    protected function setup()
    {
        $this->addStringPattern();
        $this->addPattern(
            '/(&gt;)((.|\n)*?)(&lt;)/i',
            '&gt;<span class="' . $this->getCssPrefix() . 'default">$2</span>&lt;'
        );
        $this->addPattern(
            '/(&lt;\!--)(.*?)(--&gt;)/',
            '<span class="' . $this->getCssPrefix() . 'comment">$1$2$3</span>'
        );
    }

    /**
     * overriding parent process() so we can get php blocks
     *
     * @return Html
     */
    public function process()
    {
        $this->tokenizePhp();
        parent::process();
        foreach ($this->phpBlocks as $key => $value) {
            $this->html = str_replace($key, $value, $this->html);
        }
    }

    /**
     * takes matching php block and converts to php code
     *
     * @param array $code matches from preg_replace_callback
     *
     * @return string $token
     */
    protected function processPhp($code)
    {
        $token = '$' . uniqid() . '$';
        $php = new sfSyntaxHighligherPhp($code[2]);
        $php->setCssPrefix($this->getCssPrefix());
        $html = $code[1] . $php->getHtml() . $code[3];
        $this->phpBlocks[$token] = '<span class="' . $this->getCssPrefix() . 'default">' . $html . '</span>';

        return $token;
    }

    /**
     * takes html input and replaces php blocks with tokens
     *
     * @return void
     */
    protected function tokenizePhp()
    {
        $this->html = preg_replace_callback('/(&lt;\?)(.+?)(\?&gt;)/i', array($this, 'processPhp'), $this->html);
    }

}
