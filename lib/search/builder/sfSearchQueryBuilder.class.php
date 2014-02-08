<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchQueryBuilderAbstract class.
 *
 * @package    Sift
 * @subpackage search
 */
class sfSearchQueryBuilder implements sfISearchQueryBuilder
{
    /**
     * sfSearchQueryExpression holder
     *
     * @var sfSearchQueryExpression
     */
    protected $expression;

    /**
     * Query holder
     *
     * @var string
     */
    protected $query;

    /**
     * Constructs the builder
     *
     * @param sfSearchQueryExpression $expression
     */
    public function __construct(sfSearchQueryExpression $expression = null)
    {
        if ($expression) {
            $this->setExpression($expression);
        }
    }

    /**
     * Sets the expression
     *
     * @param sfSearchQueryExpression $expression
     *
     * @return sfSearchQueryBuilder
     */
    public function setExpression(sfSearchQueryExpression $expression)
    {
        $this->expression = $expression;
        $this->query = $this->processExpression($this->expression);

        return $this;
    }

    /**
     * Returns the expression
     *
     * @return sfSearchQueryExpression
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Processes the expression and builds the final query string.
     *
     * @param sfSearchQueryExpression $expression
     *
     * @return string
     */
    protected function processExpression(sfSearchQueryExpression $expression)
    {
        $query = '';
        $phrases = $expression->getPhrases();
        $subExpressions = $expression->getSubExpressions();

        foreach ($phrases as $phrase) {
            switch ($phrase->getMode()) {
                case sfSearchQueryPhrase::MODE_AND:
                case sfSearchQueryPhrase::MODE_DEFAULT:
                    $phrase->isMultiWord() ? $format = 'AND "%s" ' : $format = "AND %s ";
                    break;

                case sfSearchQueryPhrase::MODE_OR:
                    $phrase->isMultiWord() ? $format = 'OR "%s" ' : $format = "OR %s ";
                    break;

                case sfSearchQueryPhrase::MODE_EXCLUDE:
                    $phrase->isMultiWord() ? $format = '-"%s" ' : $format = "-%s ";
                    break;

                default:
                    $phrase->isMultiWord() ? $format = '"%s" ' : $format = "%s ";
                    break;
            }

            $query .= sprintf($format, $phrase);
        }

        foreach ($subExpressions as $subExpression) {
            switch ($subExpression->getMode()) {
                case sfSearchQueryExpression::MODE_OR:
                    $query .= sprintf('OR (%s)', $this->processExpression($subExpression));
                    break;

                case sfSearchQueryExpression::MODE_AND:
                case sfSearchQueryExpression::MODE_DEFAULT:
                    $query .= sprintf('AND (%s)', $this->processExpression($subExpression));
                    break;
            }
        }

        $query = preg_replace('/^(AND|OR) /', '', $query);

        return trim($query);
    }

    /**
     * Returns the result
     *
     * @return string
     */
    public function getResult()
    {
        return $this->query;
    }

}
