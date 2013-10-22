<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDebugBacktrace provides an utility for debug_backtrace() result
 *
 * @package Sift
 * @subpackage debug
 */
class sfDebugBacktrace extends sfConfigurable implements Serializable, sfIJsonSerializable {

  /**
   * Object
   */
  const TYPE_OBJECT = 'object';

  /**
   * Array
   */
  const TYPE_ARRAY = 'array';

  /**
   * Resource
   */
  const TYPE_RESOURCE = 'resource';

  /**
   * Numeric
   */
  const TYPE_NUMERIC = 'numeric';

  /**
   * String
   */
  const TYPE_STRING = 'string';

  /**
   * Boolean
   */
  const TYPE_BOOLEAN = 'boolean';

  /**
   * Null
   */
  const TYPE_NULL = 'null';

  /**
   * The original backtrace
   *
   * @var array
   */
  protected $backtrace;

  /**
   * Processed flag
   *
   * @var boolean
   */
  protected $processed = false;

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'skip' => 0,
    // which classes should be not listed in the stack
    // interface of classes
    'skip_classes' => array(
      'sfILogger', 'sfPhpErrorException', 'sfShutdownScheduler'
    ),
    'file_url_format' => 'editor://open?file=%file%&line=%line%',
    // include file excerpt?
    'file_excerpt' => false,
    'file_excerpt_limit_lines' => 15,
    'shorten_file_paths' => false
  );

  /**
   * Constructor
   *
   * @param array $backtrace
   * @param array $options
   */
  public function __construct($backtrace, $options = array())
  {
    $this->backtrace = $backtrace;
    parent::__construct($options);
  }

  /**
   * Returns the processed trace
   *
   * @return array
   */
  public function get()
  {
    if($this->processed === false)
    {
      $this->processTrace();
    }
    return $this->processed;
  }

  /**
   * Processes the trace
   *
   */
  protected function processTrace($withArgs = true)
  {
    $skip = $this->getOption('skip');
    $processed = array();
    foreach($this->backtrace as $i => $trace)
    {
      if($i < $skip
          || (isset($trace['class']) && $this->skipClass($trace['class'])))
      {
        continue;
      }

      // prepare method argments
      $args = array();
      if(isset($trace['args']) && $withArgs)
      {
        foreach($trace['args'] as $name => $arg)
        {
          $args[$name] = $this->getCalledArgument($arg);
        }
      }

      $methodName = $trace['function'];
      if(isset($trace['class']) && isset($trace['function']))
      {
        if(isset($trace['object']) && get_class($trace['object']) != $trace['class'])
        {
          $className = get_class($trace['object']) . '[' . $trace['class'] . ']';
        }
        else
        {
          $className = $trace['class'];
        }

        $methodName = sprintf('%s%s%s', $className, isset($trace['type']) ? $trace['type'] : '->', $trace['function']);
      }

      if(!isset($trace['file']))
      {
        $trace['file'] = '';
      }

      if(!isset($trace['line']))
      {
        $trace['line'] = '';
      }

      $excerpt = '';
      if($this->getOption('file_excerpt')
          && $trace['file'] !== '' && $trace['line'] !== '')
      {
        $excerpt = $this->getFileExcerpt($trace['file'], $this->getOption('file_excerpt_limit_lines'), $trace['line']);
      }

      $fileShort = '';
      if($trace['file'])
      {
        if($this->getOption('shorten_file_paths'))
        {
          $trace['file'] = $this->shortenFilePath($trace['file']);
          $fileShort = $trace['file'];
        }
        else
        {
          $fileShort = $this->shortenFilePath($trace['file']);
        }
      }

      $processed[] = array(
        'file' => $trace['file'],
        'file_short' => $fileShort,
        'file_excerpt' => $excerpt,
        'line' => $trace['line'],
        'file_edit_url' => $trace['file'] ? $this->getFileEditUrl($trace['file'], $trace['line']) : '',
        'function' => $methodName,
        'arguments' => $args,
      );
    }

    $this->processed = $processed;
  }

  /**
   * Skip class from the processing of the trace?
   *
   * @param string $class
   * @return boolean
   */
  protected function skipClass($class)
  {
    if(!class_exists($class, false))
    {
      return true;
    }

    if(in_array($class, $this->getOption('skip_classes')))
    {
      return true;
    }

    $implements = class_implements($class, false);
    foreach($this->getOption('skip_classes') as $skip)
    {
      // is_subclass_of works for interfaces in php > 5.3.7
      if(is_subclass_of($class, $skip) ||
          in_array($skip, $implements))
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Returns the file edito url
   *
   * @param string $file The asbolute path to a file
   * @param string $line The line number
   * @return string
   */
  protected function getFileEditUrl($file, $line)
  {
    if(!$linkFormat = $this->getOption('file_url_format'))
    {
      return '';
    }
    return strtr($linkFormat, array('%file%' => $file, '%line%' => $line));
  }

  /**
   * Shortens file path
   *
   * @param string $file
   * @return string
   */
  protected function shortenFilePath($file)
  {
    return sfDebug::shortenFilePath($file);
  }

  /**
   * Format argument in called method
   *
   * @param mixed $arg
   * @return array Array of (type => $type, value => value)
   */
  protected function getCalledArgument($arg)
  {
    $type = $value = null;

    if(is_object($arg))
    {
      $type = self::TYPE_OBJECT;
      $value = sprintf("$%s (#%s)", get_class($arg), spl_object_hash($arg));
    }
    else if(is_resource($arg))
    {
      $type = self::TYPE_RESOURCE;
      $value = sprintf('[resource: %s]', get_resource_type($arg));
    }
    else if(is_array($arg))
    {
      $type = self::TYPE_ARRAY;
      $isAssociative = false;
      $args = array();
      foreach($arg as $k => $v)
      {
        if(!is_numeric($k))
        {
          $isAssociative = true;
        }
        $argValue = $this->getCalledArgument($v);
        $args[$k] = $argValue['value'];
      }

      if($isAssociative)
      {
        $arr = array();
        foreach($args as $k => $v)
        {
          $argValue = $this->getCalledArgument($k);
          $arr[] = sprintf('%s => %s', $argValue['value'], $v);
        }
        $value = 'array(' . join(', ', $arr) . ')';
      }
      else
      {
        $value = 'array(' . join(', ', $args) . ')';
      }
    }
    else if(is_null($arg))
    {
      $type = self::TYPE_NULL;
      $value = 'NULL';
    }
    else if(is_numeric($arg) || is_float($arg))
    {
      $type = self::TYPE_NUMERIC;
      $value = (string)$arg;
    }
    else if(is_string($arg))
    {
      $type = self::TYPE_STRING;
      $value = sprintf("'%s'", strtr($arg, array("\t" => '\t', "\r" => '\r', "\n" => '\n')));
    }
    else if(is_bool($arg))
    {
      $type = self::TYPE_BOOLEAN;
      $value = $arg === true ? 'true' : 'false';
    }

    return array(
      'type' => $type,
      'value' => $value
    );
  }

  /**
   * Returns an excerpt of a code file around the given line number.
   *
   * @param string $file The absolute path to a file
   * @param integer $limitLines How many lines to display?
   * @param integer The selected line number
   * @return string An HTML string
   */
  public static function getFileExcerpt($file, $limitLines = false, $line = -1)
  {
    static $highlighter;
    if(!$highlighter)
    {
      $highlighter = sfSyntaxHighlighter::factory('php');
    }
    return $highlighter->setCode(file_get_contents($file))->getExcerpt($limitLines, $line);
  }

  /**
   * Serializes the backtrace
   *
   * @return string
   */
  public function serialize()
  {
    return serialize(array($this->getOptions(), $this->get()));
  }

  /**
   * Unserialized the backtrace
   *
   * @param string $data
   * @return array
   */
  public function unserialize($data)
  {
    list($options, $this->processed) = unserialize($data);
    $this->setOptions($options);
  }

  /**
   * Converts the backtrace to string
   *
   * @return string
   */
  public function __toString()
  {
    $decorator = new sfDebugBacktraceLogDecorator($this);
    return $decorator->toString();
  }

  /**
   * Returns an array which should be serialized to JSON
   *
   * @return string
   */
  public function jsonSerialize()
  {
    return $this->__toString();
  }

}