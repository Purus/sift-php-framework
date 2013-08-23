<?php

class defaultActions extends myActions
{
  /**
   * Congratulations page for creating an application
   *
   */
  public function executeIndex()
  {
  }

  /**
   * Error page for page not found (404) error
   *
   */
  public function executeError404()
  {
  }

  /**
   * Warning page for restricted area - requires login
   *
   */
  public function executeSecure()
  {
  }

  /**
   * Warning page for restricted area - requires credentials
   *
   */
  public function executeLogin()
  {
  }

  /**
   * Website temporarily unavailable
   *
   */
  public function executeUnavailable()
  {
  }

  /**
   * Website disabled by the site administrator (in settings.yml)
   *
   */
  public function executeDisabled()
  {
  }
  
}
