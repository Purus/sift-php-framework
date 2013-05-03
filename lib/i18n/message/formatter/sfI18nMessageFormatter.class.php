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
 * translated message.
 *
 * <code>
 *  $source = sfI18nMessageSource::factory('gettext', '/i18n');
 *  $source->setCulture('en_GB');
 *  $formatter = new sfI18nMessageFormatter($source);
 *
 *  echo $formatter->format('Hello');
 * </code>
 *
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
   * Resets the formatter.
   *
   * @return sfI18nMessageFormatter
   */
  public function reset()
  {
    $this->messages = array();
    $this->catalogues = array();
    return $this;
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
    // prevent collisions with different cultures
    $catalogueHash = $catalogue . $this->source->getCulture();

    if(in_array($catalogueHash, $this->catalogues))
    {
      return;
    }

    if($this->source->load($catalogue))
    {
      $this->messages[$catalogue] = $this->source->read();
      $this->catalogues[] = $catalogueHash;
    }
  }

  /**
   * Formats the string. That is, for a particular string find
   * the corresponding translation. Variable subsitution is performed
   * for the $args parameter. A different catalogue can be specified
   * using the $catalogue parameter.
   * The output charset is determined by $this->getCharset();
   *
   * @param string $string the string to translate.
   * @param array $args a list of string to substitute.
   * @param string $catalogue get the translation from a particular message
   * @param string $charset charset, the input AND output charset catalogue.
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

  /**
   * Checks if given string exists in the source
   *
   * @param string $string
   * @param array $args
   * @param string $catalogue
   * @param string $charset
   * @return
   */
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

  /**
   * Returns formatted string. Does the lookup in the catalogue.
   *
   * @param string $string
   * @param array $args
   * @param string $catalogue
   * @return null|string Null if translation not found, or when NOT in translation mode and translation is empty
   */
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
        // If the translation is empty, we need to decide what to do:
        // 1) if IN translator mode, return formatted message using nontranslated prefix/suffix
        // 2) if NOT in translator mode, return null
        if(empty($target))
        {
          // we are in translator mode
          if($this->isInTranslatorMode())
          {
            return $this->postscript[0] . $this->replaceArgs($string, $args) . $this->postscript[1];
          }

          return null;
        }

        return $this->replaceArgs($target, $args);
      }
    }

    return null;
  }

  /**
   * Is the formatter in translator mode? If there are non blank values for
   * untranslated prefix or suffix
   *
   * @return boolean True if in translator mode, false otherwise
   * @see setUntranslatedPS
   */
  protected function isInTranslatorMode()
  {
    return !empty($this->postscript[0]) || !empty($this->postscript[1]);
  }

  /**
   * Replaces arguments in given string
   *
   * @param string $string
   * @param array $args
   * @return string
   */
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
