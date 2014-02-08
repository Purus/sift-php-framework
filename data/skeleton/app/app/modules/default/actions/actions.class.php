<?php
/**
 * This is a part of ##PROJECT_NAME##
 *
 */

/**
 * Default actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage module
 * @author     ##AUTHOR_NAME##
 */
class defaultActions extends myActions
{
    /**
     * Index action
     *
     */
    public function executeIndex()
    {
    }

    /**
     * "Page not found" action
     *
     */
    public function executeError404()
    {
    }

    /**
     * Login action
     *
     */
    public function executeLogin()
    {
    }

    /**
     * Secure action.
     *
     */
    public function executeSecure()
    {
        $this->getResponse()->setStatusCode(403);
    }

    /**
     * Module has been disabled
     *
     */
    public function executeDisabled()
    {
    }

    /**
     * Application is unavailable.
     */
    public function executeUnavailable()
    {
    }


}
