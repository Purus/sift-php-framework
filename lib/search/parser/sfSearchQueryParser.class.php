<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchQueryParser class.
 *
 * @package    Sift
 * @subpackage search
 */
class sfSearchQueryParser implements sfISearchQueryParser
{
    /**
     * Holds the parser's state
     *
     * @var string
     */
    private $state;

    /**
     * Contains the current stack level
     *
     * @var int
     */
    private $stackLevel;

    /**
     * Contains the current stack elements query type ('default', 'and' or 'or').
     *
     * @var string
     */
    private $stackType;

    /**
     * Contains a prefix for the following clause ('+', '-' or null).
     *
     * @var mixed
     */
    private $prefix = null;

    /**
     * Stack of the tokens
     *
     * @var array
     */
    protected $tokenStack = array();

    /**
     * The lexer holder
     *
     * @var sfISearchQueryLexer
     */
    protected $lexer;

    /**
     * The buffer
     *
     * @var string
     */
    protected $buffer;

    /**
     * Constructs the parser
     *
     * @param sfISearchQueryLexer $lexer
     */
    public function __construct(sfISearchQueryLexer $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Sets the query lexer
     *
     * @param sfISearchQueryLexer $lexer
     *
     * @return sfSearchQueryParser
     */
    public function setLexer(sfISearchQueryLexer $lexer)
    {
        $this->lexer = $lexer;

        return $this;
    }

    /**
     * Returns the lexer
     *
     * @return sfISearchQueryLexer
     */
    public function getLexer()
    {
        return $this->lexer;
    }

    /**
     * Parses the query
     *
     * @param string $query
     *
     * @return sfSearchQueryExpression
     */
    public function parse($query)
    {
        $this->reset();
        $query = (string)$query;
        // execute the lexer
        $this->lexer->execute($query);
        $this->tokenStack = $this->lexer->getTokens();
        $expression = new sfSearchQueryExpression($query);

        return $this->processTokens($this->tokenStack, $expression);
    }

    /**
     * Resets the parser
     *
     * @return void
     */
    protected function reset()
    {
        $this->buffer = '';
        $this->state = 'normal';
        $this->stackLevel = 0;
        $this->stackType = array();
        $this->stackType[$this->stackLevel] = 'default';
        $this->prefix = null;
    }

    /**
     * Processes the tokens
     *
     * @param array                   $tokens
     * @param sfSearchQueryExpression $expression
     *
     * @return sfSearchQueryExpression
     */
    protected function processTokens($tokens, sfSearchQueryExpression $expression)
    {
        foreach ($tokens as $token) {
            $expression = $this->processToken($token, $expression);
        }

        return $expression;
    }

    /**
     * Processes the single token
     *
     * @param sfSearchQueryToken      $token
     * @param sfSearchQueryExpression $expression
     *
     * @return sfSearchQueryExpression
     */
    protected function processToken(sfSearchQueryToken $token, sfSearchQueryExpression $expression)
    {
        switch ($this->state) {
            case 'normal':

                switch ($token->type) {
                    // space and colon
                    case sfSearchQueryToken::SPACE:
                    case sfSearchQueryToken::COLON:
                        // do nothing
                        break;

                    // quote start
                    case sfSearchQueryToken::QUOTE:
                        $this->buffer = '';
                        $this->state = 'in-quotes';
                        break;

                    case sfSearchQueryToken::STRING:

                        // we have prefix set!
                        if ($this->prefix) {
                            switch ($this->prefix) {
                                case sfSearchQueryToken::PLUS:
                                    $expression->addPhrase($this->token);
                                    break;

                                case sfSearchQueryToken::MINUS:
                                    $expression->addExclusionPhrase($token->token);
                                    break;
                            }
                            // reset the prefix
                            $this->prefix = null;
                        } // prefix is not set
                        else {
                            // switch the stack mode
                            switch ($this->stackType[$this->stackLevel]) {
                                case 'or':
                                    $expression->addOrPhrase($token->token);
                                    break;

                                case 'and':
                                    $expression->addAndPhrase($token->token);
                                    break;

                                default:
                                    $expression->addPhrase($token->token);
                                    break;
                            }
                        }
                        break;

                    // logical or, mark the stack type as "or"
                    case sfSearchQueryToken::LOGICAL_OR:
                        $this->stackType[$this->stackLevel] = 'or';
                        break;

                    // logical and, mark the stack type as "and"
                    case sfSearchQueryToken::LOGICAL_AND:
                        $this->stackType[$this->stackLevel] = 'and';
                        break;

                    case sfSearchQueryToken::BRACE_OPEN:

                        $mode = $this->stackType[$this->stackLevel];
                        switch ($mode) {
                            case 'or':
                                $mode = sfSearchQueryExpression::MODE_OR;
                                break;

                            case 'and':
                                $mode = sfSearchQueryExpression::MODE_AND;
                                break;

                            case 'default':
                                $mode = sfSearchQueryExpression::MODE_DEFAULT;
                                break;
                        }

                        $this->stackLevel++;
                        $this->stackType[$this->stackLevel] = 'default';

                        return $expression->initiateSubExpression($mode);
                        break;

                    case sfSearchQueryToken::BRACE_CLOSE:
                        $this->stackLevel--;

                        return $expression->getParentExpression();
                        break;

                    case sfSearchQueryToken::PLUS:
                        $this->prefix = $token->type;
                        break;

                    case sfSearchQueryToken::MINUS:
                        $this->prefix = $token->type;
                        break;
                }

                break;

            case 'in-quotes':

                switch ($token->type) {
                    // quote has ended
                    case sfSearchQueryToken::QUOTE:

                        switch ($this->stackType[$this->stackLevel]) {
                            case 'or':
                                $expression->addOrPhrase($this->buffer);
                                break;

                            case 'and':
                                $expression->addAndPhrase($this->buffer);
                                break;

                            default:
                                $expression->addPhrase($this->buffer);
                                break;
                        }
                        $this->state = 'normal';
                        break;

                    case sfSearchQueryToken::STRING:
                    case sfSearchQueryToken::COLON:
                    case sfSearchQueryToken::SPACE:
                    case sfSearchQueryToken::LOGICAL_AND:
                    case sfSearchQueryToken::LOGICAL_OR:
                    case sfSearchQueryToken::PLUS:
                    case sfSearchQueryToken::MINUS:
                    case sfSearchQueryToken::BRACE_OPEN:
                    case sfSearchQueryToken::BRACE_CLOSE:
                        $this->buffer .= $token->token;
                        break;
                }

                break;
        }

        return $expression;
    }

}
