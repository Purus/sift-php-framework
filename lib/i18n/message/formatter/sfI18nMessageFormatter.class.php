<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18nMessageFormatter class
 *
 * Format a message, that is, for a particular message find the 
 * translated message. The following is an example using 
 * a SQLite database to store the translation message. 
 * Create a new message format instance and echo "Hello"
 * in simplified Chinese. This assumes that the world "Hello"
 * is translated in the database.
 *
 * <code>
 *  $source = sfI18nMessageSource::factory('SQLite', 'sqlite://messages.db');
 *  $source->setCulture('zh_CN');
 *  $source->setCache(new sfI18nMessageCache('./tmp'));
 *
 *  $formatter = new sfI18nMessageFormatter($source); 
 *  
 *  echo $formatter->format('Hello');
 * </code>
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @package Sift
 * @subpackage i18n
 */
class sfI18nMessageFormatter {

  /**
   * The message source.
   * @var sfMessageSource
   */
  protected $source;

  /**
   * A list of loaded message catalogues.
   * @var array
   */
  protected $catalogues = array();

  /**
   * The translation messages.
   * @var array
   */
  protected $messages = array();

  /**
   * A list of untranslated messages.
   * @var array
   */
  protected $untranslated = array();

  /**
   * The prefix and suffix to append to untranslated messages.
   * @var array
   */
  protected $postscript = array('', '');

  /**
   * Set the default catalogue.
   * @var string 
   */
  public $catalogue = 'messages';

  /**
   * Output encoding charset
   * @var string
   */
  protected $charset = 'UTF-8';

  /**
   * Constructor.
   * Create a new instance of sfMessageFormat using the messages
   * from the supplied message source.
   *
   * @param MessageSource the source of translation messages.
   * @param string charset for the message output.
   */
  function __construct(sfII18NMessageSource $source, $charset = 'UTF-8')
  {
    $this->source = &$source;
    if($charset)
    {
      $this->setCharset($charset);
    }    
  }

  /**
   * Sets the charset for message output.
   *
   * @param string charset, default is UTF-8
   */
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }

  /**
   * Gets the charset for message output. Default is UTF-8.
   *
   * @return string charset, default UTF-8
   */
  public function getCharset()
  {
    return $this->charset;
  }

  /**
   * Loads the message from a particular catalogue. A listed
   * loaded catalogues is kept to prevent reload of the same
   * catalogue. The load catalogue messages are stored
   * in the $this->messages array.
   *
   * @param string message catalogue to load.
   */
  protected function loadCatalogue($catalogue)
  {
    if(in_array($catalogue, $this->catalogues))
    {
      return;
    }

    if($this->source->load($catalogue))
    {
      $this->messages[$catalogue] = $this->source->read();
      $this->catalogues[] = $catalogue;
    }
  }

  /**
   * Formats the string. That is, for a particular string find
   * the corresponding translation. Variable subsitution is performed
   * for the $args parameter. A different catalogue can be specified
   * using the $catalogue parameter.
   * The output charset is determined by $this->getCharset();
   *
   * @param string the string to translate.
   * @param array a list of string to substitute.
   * @param string get the translation from a particular message
   * @param string charset, the input AND output charset catalogue.
   * @return string translated string.
   */
  public function format($string, $args = array(), $catalogue = null, $charset = null)
  {
    if(empty($charset))
    {
      $charset = $this->getCharset();
    }

    $s = $this->formatString(sfI18n::i18n2utf8($string, $charset), $args, $catalogue);

    return sfI18n::i18n2Encoding($s, $charset);
  }

  public function formatExists($string, $args = array(), $catalogue = null, $charset = null)
  {
    if(empty($charset))
    {
      $charset = $this->getCharset();
    }

    $s = $this->getFormattedString(sfI18n::i18n2Utf8($string, $charset), $args, $catalogue);

    return sfI18n::i18n2Encoding($s, $charset);
  }

  /**
   * Do string translation.
   *
   * @param string the string to translate.
   * @param array a list of string to substitute.
   * @param string get the translation from a particular message catalogue.
   * @return string translated string.
   */
  protected function formatString($string, $args = array(), $catalogue = null)
  {
    if(empty($args))
    {
      $args = array();
    }

    $target = $this->getFormattedString($string, $args, $catalogue);

    // well we did not find the translation string.
    if(!$target)
    {
      $this->source->append($string);
      $target = $this->postscript[0] . $this->replaceArgs($string, $args) . $this->postscript[1];
    }

    return $target;
  }

  public function getUnformattedString($string, $catalogue = null)
  {
    if(empty($catalogue))
    {
      $catalogue = empty($this->catalogue) ? 'messages' : $this->catalogue;
    }

    $this->loadCatalogue($catalogue);

    foreach($this->messages[$catalogue] as $variant)
    {
      // we found it, so return the target translation
      if(isset($variant[$string]))
      {
        $target = $variant[$string];

        // check if it contains only strings.
        if(is_array($target))
        {
          $target = array_shift($target);
        }

        // found, but untranslated
        if(empty($target))
        {
          return $string;
        }

        return $target;
      }
    }

    return $string;
  }

  protected function getFormattedString($string, $args = array(), $catalogue = null)
  {
    if(empty($catalogue))
    {
      $catalogue = empty($this->catalogue) ? 'messages' : $this->catalogue;
    }

    if(empty($args))
    {
      $args = array();
    }

    $this->loadCatalogue($catalogue);

    foreach($this->messages[$catalogue] as $variant)
    {
      // we found it, so return the target translation
      if(isset($variant[$string]))
      {
        $target = $variant[$string];

        // check if it contains only strings.
        if(is_array($target))
        {
          $target = array_shift($target);
        }

        // found, but untranslated
        if(empty($target))
        {
          return $this->postscript[0] . $this->replaceArgs($string, $args) . $this->postscript[1];
        }
        return $this->replaceArgs($target, $args);
      }
    }

    return null;
  }

  protected function replaceArgs($string, $args)
  {
    // replace object with strings
    foreach($args as $key => $value)
    {
      if(is_object($value) && method_exists($value, '__toString'))
      {
        $args[$key] = $value->__toString();
      }
    }

    return strtr($string, $args);
  }

  /**
   * Gets the message source.
   *
   * @return MessageSource 
   */
  public function getSource()
  {
    return $this->source;
  }

  /**
   * Sets the prefix and suffix to append to untranslated messages.
   * e.g. $postscript=array('[T]','[/T]'); will output 
   * "[T]Hello[/T]" if the translation for "Hello" can not be determined.
   *
   * @param array first element is the prefix, second element the suffix.
   */
  public function setUntranslatedPS($postscript)
  {
    if(is_array($postscript) && count($postscript) >= 2)
    {
      $this->postscript[0] = $postscript[0];
      $this->postscript[1] = $postscript[1];
    }
  }

}
