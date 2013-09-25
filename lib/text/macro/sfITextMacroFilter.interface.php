<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfITextMacroFilter interface
 *
 * @package    Sift
 * @subpackage text
 */
interface sfITextMacroFilter {

  /**
   * Filters given content
   *
   * @param array $attributes The array of attributes
   * @param string $value The value
   */
  public function filter($attributes, $value = null);

}
