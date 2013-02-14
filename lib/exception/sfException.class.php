<?php
/*
 * This file is part of the Sift PHP framework
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfException is the base class for all Sift related exceptions and
 * provides an additional method for printing up a detailed view of an
 * exception.
 *
 * @package    Sift
 * @subpackage exception
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org> 
 */
class sfException extends Exception {

  protected $name = null;

  protected
    $wrappedException = null;

  static protected
    $lastException = null;

  
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    if($this->getName() === null)
    {
      $this->setName('sfException');
    }

    parent::__construct($message, $code);

    if(sfConfig::get('sf_logging_enabled') && $this->getName() != 'sfStopException')
    {
      sfLogger::getInstance()->err('{' . $this->getName() . '} ' . $message);
    }
  }
  
  /**
   * Wraps an Exception.
   *
   * @param Exception $e An Exception instance
   *
   * @return sfException An sfException instance that wraps the given Exception object
   */
  static public function createFromException(Exception $e)
  {
    $exception = new sfException(sprintf('Wrapped %s: %s', get_class($e), $e->getMessage()));
    $exception->setWrappedException($e);
    self::$lastException = $e;

    return $exception;
  }

  /**
   * Sets the wrapped exception.
   *
   * @param Exception $e An Exception instance
   */
  public function setWrappedException(Exception $e)
  {
    $this->wrappedException = $e;

    self::$lastException = $e;
  }

  /**
   * Gets the last wrapped exception.
   *
   * @return Exception An Exception instance
   */
  static public function getLastException()
  {
    return self::$lastException;
  }

  /**
   * Clears the $lastException property (added for #6342)
   */
  static public function clearLastException()
  {
  	self::$lastException = null;
  }

  /**
   * Retrieves the name of this exception.
   *
   * @return string This exception's name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Prints the stack trace for this exception.
   *
   * @param Exception An Exception implementation instance
   */
  public function printStackTrace(Exception $exception = null)
  {
    if(!$exception)
    {
      if (null === $this->wrappedException)
      {
        $this->setWrappedException($this);
      }
      $exception = $this->wrappedException;
    }

    $event = sfCore::getEventDispatcher()->notifyUntil(new sfEvent('application.throw_exception', array(
                        'exception' => $this)));

    if($event->isProcessed())
    {
      return;
    }

    // don't print message if it is an sfStopException exception
    if(method_exists($exception, 'getName') && $exception->getName() == 'sfStopException')
    {
      if(!sfConfig::get('sf_test'))
      {
        exit(1);
      }

      return;
    }

    $catchedOutput = 'N/A';

    if(!sfConfig::get('sf_test') && 'cli' != PHP_SAPI)
    {
      @header('HTTP/1.0 500 Internal Server Error');

      // catchedOutput
      $catchedOutput = ob_get_contents();
      if(!$catchedOutput)
      {
        $catchedOutput = 'N/A';
      }

      // clean current output buffer
      while(@ob_end_clean());

      ob_start();
    }

    // send an error 500 if not in debug mode
    if(!sfConfig::get('sf_debug'))
    {
      // error_log($exception->getMessage());
      sfCore::displayErrorPage($exception);
      return;
    }

    $message = null !== $exception->getMessage() ? $exception->getMessage() : 'n/a';
    $name = get_class($exception);
    $format = 'cli' == PHP_SAPI ? 'plain' : 'html';
    $traces = $this->getTraces($exception, $format);

    // extract error reference from message
    $error_reference = '';
    if(preg_match('/\[(err\d+)\]/', $message, $matches))
    {
      $error_reference = $matches[1];
    }

    // dump main objects values
    $sf_settings = '';
    $settingsTable = $requestTable = $responseTable = $globalsTable = '';

    if(class_exists('sfContext', false) && sfContext::hasInstance())
    {
      $context = sfContext::getInstance();
      $settingsTable = $this->formatArrayAsHtml(sfDebug::settingsAsArray());
      $requestTable = $this->formatArrayAsHtml(sfDebug::requestAsArray($context->getRequest()));
      $responseTable = $this->formatArrayAsHtml(sfDebug::responseAsArray($context->getResponse()));
      $globalsTable = $this->formatArrayAsHtml(sfDebug::globalsAsArray());
      $catchedOutput = $catchedOutput;

      $request = $context->getRequest();
      if(method_exists($request, 'isAjax') && $request->isAjax())
      {
        $format = 'ajax';
      }
      
      if(is_object($response = sfContext::getInstance()->getResponse()))
      {
        if ($response->getStatusCode() < 300)
        {
          // status code has already been sent, but is included here for the purpose of testing
          $response->setStatusCode(500);
        } 
      }
      
    }

    $ext = ($format == 'html' ? 'php' : 'txt');
    if($format == 'ajax')
    {
      $ext = 'ajax';
      header('Content-Type: application/json');
    }
    
    ob_start();
    include(sfConfig::get('sf_sift_data_dir') . '/data/exception.' . $ext);
    $content = ob_get_clean();

    $content = sfCore::filterByEventListeners($content, 
                'application.render_exception', array(             
                    'content' => $content,
                    'exception' => &$exception 
    ));
    
    echo $content;

    // if test, do not exit
    if(!sfConfig::get('sf_test'))
    {
      exit(1);
    }
    
  }

  /**
   * Returns an array of exception traces.
   *
   * @param Exception An Exception implementation instance
   * @param string The trace format (plain or html)
   *
   * @return array An array of traces
   */
  public function getTraces($exception, $format = 'plain')
  {
    $traceData = $exception->getTrace();
    array_unshift($traceData, array(
        'function' => '',
        'file' => $exception->getFile() != null ? $exception->getFile() : 'n/a',
        'line' => $exception->getLine() != null ? $exception->getLine() : 'n/a',
        'args' => array(),
    ));

    $traces = array();
    if($format == 'html')
    {
      $lineFormat = 'at <strong>%s%s%s</strong>(%s)<br />in <em>%s</em> line %s <a href="#" onclick="toggle(\'%s\'); return false;">...</a><br /><ul class="trace" id="%s" style="display: %s">%s</ul>';
    }
    else
    {
      $lineFormat = 'at %s%s%s(%s) in %s line %s';
    }
    for($i = 0, $count = count($traceData); $i < $count; $i++)
    {
      $line = isset($traceData[$i]['line']) ? $traceData[$i]['line'] : 'n/a';
      $file = isset($traceData[$i]['file']) ? $traceData[$i]['file'] : 'n/a';
      $shortFile = preg_replace(array('#^' . preg_quote(sfConfig::get('sf_root_dir')) . '#', '#^' . preg_quote(realpath(sfConfig::get('sf_sift_lib_dir'))) . '#'), array('SF_ROOT_DIR', 'sf_sift_lib_DIR'), $file);
      $args = isset($traceData[$i]['args']) ? $traceData[$i]['args'] : array();
      $traces[] = sprintf($lineFormat,
                      (isset($traceData[$i]['class']) ? $traceData[$i]['class'] : ''),
                      (isset($traceData[$i]['type']) ? $traceData[$i]['type'] : ''),
                      $traceData[$i]['function'],
                      $this->formatArgs($args, false, $format),
                      $shortFile,
                      $line,
                      'trace_' . $i,
                      'trace_' . $i,
                      $i == 0 ? 'block' : 'none',
                      $this->fileExcerpt($file, $line)
      );
    }

    return $traces;
  }

  /**
   * Returns an HTML version of an array as YAML.
   *
   * @param array The values array
   *
   * @return string An HTML string
   */
  protected function formatArrayAsHtml($values)
  {
    return '<pre>' . self::escape(@sfYaml::dump($values)) . '</pre>';
  }

  /**
   * Returns an excerpt of a code file around the given line number.
   *
   * @param string A file path
   * @param int The selected line number
   *
   * @return string An HTML string
   */
  protected function fileExcerpt($file, $line)
  {
    if(is_readable($file))
    {
      $content = preg_split('#<br />#', highlight_file($file, true));

      $lines = array();
      for($i = max($line - 3, 1), $max = min($line + 3, count($content)); $i <= $max; $i++)
      {
        $lines[] = '<li' . ($i == $line ? ' class="selected"' : '') . '>' . $content[$i - 1] . '</li>';
      }

      return '<ol start="' . max($line - 3, 1) . '">' . implode("\n", $lines) . '</ol>';
    }
  }

  /**
   * Formats an array as a string.
   *
   * @param array The argument array
   * @param boolean 
   * @param string The format string (html or plain)
   *
   * @return string
   */
  protected function formatArgs($args, $single = false, $format = 'html')
  {
    $result = array();

    $single and $args = array($args);

    foreach($args as $key => $value)
    {
      if(is_object($value))
      {
        $formattedValue = ($format == 'html' ? '<em>object</em>' : 'object') . sprintf("('%s')", get_class($value));
      }
      else if(is_array($value))
      {
        $formattedValue = ($format == 'html' ? '<em>array</em>' : 'array') . sprintf("(%s)", self::formatArgs($value));
      }
      else if(is_string($value))
      {
        $formattedValue = ($format == 'html' ? sprintf("'%s'", self::escape($value)) : "'$value'");
      }
      else if(is_null($value))
      {
        $formattedValue = ($format == 'html' ? '<em>null</em>' : 'null');
      }
      else
      {
        $formattedValue = $value;
      }

      $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", self::escape($key), $formattedValue);
    }

    return implode(', ', $result);
  }

  /**
   * Escapes a string value with html entities
   *
   * @param  string  $value
   *
   * @return string
   */
  static protected function escape($value)
  {
    if(!is_string($value))
    {
      return $value;
    }

    return htmlspecialchars($value, ENT_QUOTES, sfConfig::get('sf_charset', 'UTF-8'));
  }

  /**
   * Sets the name of this exception.
   *
   * @param string An exception name
   */
  protected function setName($name)
  {
    $this->name = $name;
  }

}
