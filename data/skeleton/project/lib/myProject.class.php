<?php

/**
 * 
 * @package ##PROJECT_NAME##
 * @subpackage project
 */
class myProject extends sfProject {
  
  /**
   * Project version
   */
  const VERSION = '1.0.0';

  /**
   * 
   * @return string
   */
  public static function getVersion()
  {
    return self::VERSION;
  }
 
}
