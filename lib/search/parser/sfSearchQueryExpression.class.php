<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchQueryExpression class.
 *
 * @package Sift
 * @subpackage search
 */
class sfSearchQueryExpression
{
  protected $phrases = array();
  protected $subExpressions = array();
  protected $parent;
  protected $mode;

  /**
   * The original query
   *
   * @var string
   */
  protected $query;

  const MODE_DEFAULT = 'default';
  const MODE_OR = 'or';
  const MODE_AND = 'and';
  const MODE_EXCLUDE = 'not';

  /**
   * Constructs the expression
   *
   * @param string $query The original query
   * @param sfSearchQueryExpression $parent
   * @param string $mode
   */
  public function __construct($query, sfSearchQueryExpression $parent = null, $mode = self::MODE_DEFAULT)
  {
    $this->query = $query;
    $this->parent = $parent;
    $this->mode   = $mode;
  }

  /**
   * Is this expression valid?
   *
   * @return boolean
   */
  public function isValid()
  {
    $valid = false;

    foreach ($this->phrases as $phrase) {
      if ($phrase->getMode() != sfSearchQueryPhrase::MODE_EXCLUDE) {
        return true;
      }
    }

    foreach ($this->subExpressions as $subExpression) {
      $valid = $subExpression->isValid();
      // break on first valid subexpression
      if ($valid) {
        return true;
      }
    }

    return $valid;
  }

  /**
   * Returns the original query
   *
   * @return string|null
   */
  public function getQuery()
  {
    return $this->query;
  }

  /**
   * Initializes new subexpression
   *
   * @param string $mode Subexpression mode
   * @return sfSearchQueryExpression
   */
  public function initiateSubExpression($mode = self::MODE_DEFAULT)
  {
    $expression = new self(null, $this, $mode);
    $this->subExpressions[] = $expression;

    return $expression;
  }

  /**
   * Returns array of phrases array(sfSearchQueryPhrase)
   * @return array
   */
  public function getPhrases()
  {
    return $this->phrases;
  }

  /**
   * Returns array of subexpressions
   *
   * @return array
   */
  public function getSubExpressions()
  {
    return $this->subExpressions;
  }

  /**
   * Returns parent expression or null if there is no parent expression asigned
   *
   * @return sfSearchQueryExpression|null
   */
  public function getParentExpression()
  {
    return $this->parent;
  }

  /**
   * Add a phrase to this expression
   *
   * @param sfSearchQueryPhrase $phrase
   */
  protected function addQueryPhrase(sfSearchQueryPhrase $phrase)
  {
    $this->phrases[] = $phrase;
  }

  /**
   * Add a string phrase to this expression (with default mode)
   *
   * @param string $input
   */
  public function addPhrase($input)
  {
    $this->addQueryPhrase(new sfSearchQueryPhrase($input));
  }

  /**
   * Add a string phrase to this expression (with or mode)
   *
   * @param string $input
   */
  public function addOrPhrase($input)
  {
    $this->addQueryPhrase(new sfSearchQueryPhrase($input, sfSearchQueryPhrase::MODE_OR));
  }

  /**
   * Add a string phrase to this expression (with and mode)
   *
   * @param string $input
   */
  public function addAndPhrase($input)
  {
    $this->addQueryPhrase(new sfSearchQueryPhrase($input, sfSearchQueryPhrase::MODE_AND));
  }

  /**
   * Add a string phrase to this expression (with not mode)
   *
   * @param string $input
   */
  public function addExclusionPhrase($input)
  {
    $this->addQueryPhrase(new sfSearchQueryPhrase($input, sfSearchQueryPhrase::MODE_EXCLUDE));
  }

  /**
   * Returns the mode of the expression
   *
   * @return string
   */
  public function getMode()
  {
    return $this->mode;
  }

  /**
   * Collects all words from this expression
   *
   * @return array
   */
  public function collectWords()
  {
    $keywords = array();

    foreach ($this->getPhrases() as $phrase) {
      switch ($phrase->getMode()) {
        case sfSearchQueryPhrase::MODE_DEFAULT:
        case sfSearchQueryPhrase::MODE_AND:
        case sfSearchQueryPhrase::MODE_OR:
          $keywords = array_merge($keywords, $phrase->getWords());
        break;

        // we skip excluded words
      }
    }

    $subExpressions = $this->getSubExpressions();
    foreach ($subExpressions as $subExpression) {
      $keywords = array_merge($keywords, $subExpression->collectWords());
    }

    return $keywords;
  }

  /**
   * Converts the expression to string
   *
   */
  public function __toString()
  {
    $builder = new sfSearchQueryBuilder($this);

    return $builder->getResult();
  }

}
