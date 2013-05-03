<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// before 5.3.0
if(!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);
if(!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', 16384);

/**
 * sfErrorHandler - php error handler function
 *
 * @package    Sift
 * @subpackage exception
 */
class sfPhpErrorException extends sfException {

  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfPhpErrorException');
    parent::__construct($message, $code);
  }

  /**
   * Callback used as error handler
   *
   * set_error_handler(array('sfPhpErrorException',
   *                                        'handleErrorCallback'), E_ALL);
   *
   * @param integer $code
   * @param string $string
   * @param string $file
   * @param integer $line
   * @param array $context An array of every variable that existed in the scope the error was triggered in.
   * @return mixed throws and exception of returns null
   *                      (if error suppressed or deprecated error)
   */
  public static function handleErrorCallback($code, $string, $file, $line, $context)
  {
    // Do not throw an exception if this is a suppressed error @func()
    if(error_reporting() === 0)
    {
      return;
    }

    switch($code)
    {
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
      case E_NOTICE:
      case E_USER_NOTICE:
      case E_STRICT:

      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->warning(sprintf('{sfPhpErrorException} %s, file: "%s", line: %s', $string, $file, $line));
      }

      return;

      break;
    }

    $e = new self($string . ' (in file ' . $file . ', line: ' . $line . ')', $code);
    $e->line = $line;
    $e->file = $file;

    throw $e;
  }

  /**
   * Handles php errors E_ERROR and E_PARSE. Tthis method is set as shutdown function.
   *
   * @link http://insomanic.me.uk/post/229851073/php-trick-catching-fatal-errors-e-error-with-a
   */
  public static function fatalErrorShutdownHandler()
  {
    $last_error = error_get_last();
    if($last_error['type'] === E_ERROR || $last_error['type'] === E_PARSE)
    {
      $env = sfConfig::get('sf_environment');
      try
      {
        self::handleErrorCallback(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line'], '');
      }
      catch(sfPhpErrorException $e)
      {
        if($env == 'prod')
        {
          // clear output buffers
          while(ob_get_level() > 0)
          {
            ob_get_clean();
          }
          sfCore::displayErrorPage('error500');
        }
        else
        {
          $e->printStackTrace();
        }
      }
    }

  }

}