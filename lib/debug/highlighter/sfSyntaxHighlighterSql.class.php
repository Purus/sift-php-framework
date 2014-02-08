<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sql code highlighter
 *
 * @package Sift
 * @subpackage debug_highlighter
 */
class sfSyntaxHighlighterSql extends sfSyntaxHighlighterGeneric
{
  /**
   * Setups the regexes
   *
   */
  protected function setup()
  {
    // Make the bold assumption that an all uppercase words have special meaning
    $this->addPattern('/(?<!\w|>)([A-Z_0-9]{2,})(?!\w)/x', '<span class="' . $this->getCssPrefix() . 'keyword">$1</span>');
    // $this->addPattern('/\b(UPDATE|SET|SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT|DELETE|INSERT|INTO|VALUES)\b/', '<span class="' . $this->getCssPrefix() . 'keyword">$1</span>');
  }

}
