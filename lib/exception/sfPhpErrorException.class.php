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
 * PHP error exception
 *
 * @package    Sift
 * @subpackage exception
 */
class sfPhpErrorException extends sfException {

  /**
   * Callback used as error handler
   *
   * set_error_handler(array('sfPhpErrorException',
   *                         'handleErrorCallback'), E_ALL);
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

    if(in_array($code, array(
        E_DEPRECATED, E_USER_DEPRECATED,
        E_NOTICE, E_USER_NOTICE,
        E_STRICT
    )))
    {
      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->warning('{sfPhpErrorException} {error}, file: "{file}", line: {line}', array(
          'file' => $file,
          'line' => $line,
          'error' => $string
        ));
      }
      // The error handler must return FALSE to populate $php_errormsg. 
      return false;
    }

    $e = new self($string . ' (in file "' . $file . '", line: ' . $line . ')', $code);
    $e->line = $line;
    $e->file = $file;

    throw $e;
  }

  /**
   * Handles php errors E_ERROR and E_PARSE. This method is set as shutdown function.
   *
   * @link http://insomanic.me.uk/post/229851073/php-trick-catching-fatal-errors-e-error-with-a
   */
  public static function fatalErrorShutdownHandler()
  {
    $error = error_get_last();
    if(in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE)))
    {
      $e = new self(sprintf('%s (in file "%s", on line %s)',
                    $error['message'], $error['file'],
                    $error['line'], $error['type']));

      $e->line = $error['file'];
      $e->file = $error['line'];

      self::fixExceptionStack($e, $error['file'], $error['line']);
      $e->printStackTrace();
    }
  }

  /**
   * Fixes exception stack. Injects the "correct" location
   *
   */
  public static function fixExceptionStack(Exception &$exception, $file, $line)
  {
    $stack = array(
      array(
        'file' => $file,
        'line' => $line,
        'function' => '*unknown*',
        'args' => array(),
      )
    );
    $ref = new ReflectionProperty('Exception', 'trace');

    if(method_exists($ref, 'setAccessible'))
    {
      $ref->setAccessible(true);
      $ref->setValue($exception, $stack);
    }
    return $exception;
  }

}