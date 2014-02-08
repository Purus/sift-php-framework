<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extractable interface for forms. If the form cannot be extracted by standard way
 * implement this interface to enable the extraction.
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
interface sfII18nExtractableForm {

  /**
   * Construct the form for extraction
   *
   * @return sfForm
   */
  public static function __construct_I18n();

}
