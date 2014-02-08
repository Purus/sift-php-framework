<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Phone number formatter for czech phone number formatting conventions
 *
 * @package    Sift
 * @subpackage i18n
 */
class sfI18nPhoneNumberCultureFormatterCS implements sfII18nPhoneNumberCultureFormatter
{
    /**
     * Format the phone number without international prefix
     *
     * @param string $phoneNumber The number without international prefix
     *
     * @return string
     */
    public static function format($phoneNumber)
    {
        switch (($length = strlen($phoneNumber))) {
            // make the number look like: 606 123 456
            case 9:
                return join(' ', str_split($phoneNumber, 3));
                break;

            default:
                // make the larger numbers grouped by 2 or 3 numbers from right to left
                // number like: 8002221222 will be converted to 80 02 22 12 22
                // number like: 22166992750 will be converted to 22 166 992 750
                return join(
                    ' ',
                    array_reverse(
                        array_map(
                            'strrev',
                            str_split(
                                strrev($phoneNumber),
                                // split by 2 or 3
                                ($length % 2 == 0) ? 2 : 3
                            )
                        )
                    )
                );
                break;
        }
    }

}
