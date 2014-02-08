<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Dummy class which simulates logged in user. Used for extracting
 * strings from forms (which can be dynamically configured to display fields only
 * for logged in user)
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nExtractLoggedInUser extends sfI18nExtractUser
{
    public function hasCredential($credential)
    {
        return true;
    }

    public function isAuthenticated()
    {
        return true;
    }

    public function isLoggedIn()
    {
        return true;
    }

}
