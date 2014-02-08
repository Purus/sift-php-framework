<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Culture specific phone number formatter interface
 *
 * @package Sift
 * @subpackage i18n
 */
interface sfII18nPhoneNumberCultureFormatter {

  /**
   * Format the phone number without international prefix
   *
   * @param string $phoneNumber The number without international prefix
   */
  public static function format($phoneNumber);

}
