<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextMacro class is based on shortcodes functionality from Wordpress.
 * It behaves the same, but is wrapped into this class. Macros or shortcodes
 * is wordpress speak are configured in text_filters.yml file inside plugins.
 *
 * @package    Sift
 * @subpackage text_macro
 */
class sfTextMacroRegistry implements Countable, sfILoggerAware {

  /**
   * Holder for tags
   *
   * @var array
   */
  protected $tags = array();

  /**
   * Object cache
   *
   * @var array
   */
  protected $objectCache = array();

  /**
   * The logger instance
   *
   * @var sfILogger
   */
  protected $logger;

  /**
   * Service container
   *
   * @var sfServiceContainer
   */
  protected $serviceContainer;

  /**
   * Constructor
   *
   * @param sfServiceContainer $serviceContainer The service container
   * @param sfILogger $logger The logger
   * @inject logger
   */
  public function __construct(sfServiceContainer $serviceContainer, sfILogger $logger = null)
  {
    $this->serviceContainer = $serviceContainer;
    $this->setLogger($logger);

    // load macros
    $this->loadMacros();
  }

  /**
   * Loads macros using sfConfigCache from text_filters.yml configuration file
   */
  protected function loadMacros()
  {
    $configuration = include(sfConfigCache::getInstance()->checkConfig(
        sfConfig::get('sf_app_config_dir_name') . '/text_filters.yml'));
    foreach($configuration['macros'] as $tagName => $definition)
    {
      $this->register($tagName, $definition);
    }
  }

  /**
   * Search content for tags and filter tags through their hooks.
   *
   * If there are no macro tags defined, then the content will be returned
   * without any filtering. This might cause issues when plugins are disabled but
   * the macro will still show up in the post or content.
   *
   * @uses getMacroRegex() gets the search pattern for searching tags.
   * @param string $content Content to search for tags
   * @param array $allowedTags Array of allowed tags (if empty, all will be used)
   * @return string Content with tags filtered out.
   */
  public function parse($content, $allowedTags = array())
  {
    if(!count($this->tags)
        || (($regexp = $this->getMacrosRegex($allowedTags)) === false))
    {
      return $content;
    }

    return preg_replace_callback('/' . $regexp . '/s', array($this, 'doMacroTag'), $content);
  }

  /**
   * Registers hook for macro tag.
   *
   * @param string $tag macro tag to be searched in post content.
   * @param callable|sfTextMacroCallbackDefinition $callable Hook to run when macro is found.
   * @throws InvalidArgumentException If callable is invalid
   * @return sfTextMacroRegistry
   */
  public function register($tag, $callable)
  {
    if(!$callable instanceof sfTextMacroCallbackDefinition)
    {
      // create the definition from the function
      if(is_string($callable))
      {
        $callable = new sfTextMacroCallbackDefinition($callable);
      }
      elseif(is_array($callable))
      {
        // this is not a real definition, just a static call
        if(!isset($callable['class']) && !isset($callable['function']))
        {
          $callable = $callable = new sfTextMacroCallbackDefinition(
              sprintf('%s::%s', $callable[0], $callable[1]));
        }
        else
        {
          $callable = sfTextMacroCallbackDefinition::createFromArray($callable);
        }
      }
    }
    $this->tags[$tag] = $callable;

    return $this;
  }

  /**
   * Removes hook for macro
   *
   * @param string $tag macro tag to remove hook for.
   * @return sfTextMacroRegistry
   */
  public function unregister($tag)
  {
    unset($this->tags[$tag]);

    return $this;
  }

  /**
   * Remove all tags from the given content.
   *
   * @param string $content Content to remove macro tags.
   * @param string $allowedTags Array of allowed tags
   * @return string Content without macro tags.
   */
  public function strip($content, $allowedTags = array())
  {
    if(!count($this->tags)
        || (($regexp = $this->getMacrosRegex($allowedTags)) === false))
    {
      return $content;
    }

    return preg_replace('/' . $regexp . '/s', '$1$6', $content);
  }

  /**
   * Has the content given macro tag?
   *
   * @param string $tag The tag to seatch for
   * @param string $content The content to search in
   */
  public function hasTag($tag, $content)
  {
    if(!isset($this->tags[$tag]))
    {
      return false;
    }

    preg_match_all('/' . $this->getMacrosRegex() . '/s', $content, $matches, PREG_SET_ORDER);

    if(!empty($matches))
    {
      foreach($matches as $tags)
      {
        if($tag === $tags[2])
        {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Returns number of registered tags
   *
   * @return integer
   */
  public function count()
  {
    return count($this->tags);
  }

  /**
   * Clear all tags.
   *
   * @return sfTextMacroRegistry
   */
  public function clear()
  {
    $this->tags = array();
    $this->objectCache = array();

    return $this;
  }

  /**
   * Is the given tag registered?
   *
   * @param string $tag
   * @return boolean
   */
  public function isRegistered($tag)
  {
    return isset($this->tags[$tag]);
  }

  /**
   * Return registered tags
   *
   * @return array
   */
  public function getTags()
  {
    return array_keys($this->tags);
  }

  /**
   * Sets the logger instance
   *
   * @param sfILogger $logger
   */
  public function setLogger(sfILogger $logger = null)
  {
    $this->logger = $logger;
  }

  /**
   * Returns the logger instance
   *
   * @return sfILogger
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Logs a message to the logger
   *
   * @param string $message The message to log
   * @param integer $level The level
   * @param array $context Array of context variables
   */
  protected function log($message, $level = sfILogger::INFO, array $context = array())
  {
    if($this->logger)
    {
      $this->logger->log(sprintf('{sfTextMacroRegistry} %s', $message), $level, $context);
    }
  }

  /**
   * Retrieve all attributes from the macro tag.
   *
   * The attributes list has the attribute name as the key and the value of the
   * attribute as the value in the key/value pair. This allows for easier
   * retrieval of the attributes, since all attributes have to be known.
   *
   * @param string $text
   * @return array List of attributes and their value.
   */
  protected function parseAttributes($text)
  {
    $atts = array();
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
    if(preg_match_all($pattern, $text, $match, PREG_SET_ORDER))
    {
      foreach($match as $m)
      {
        if(!empty($m[1]))
        {
          $atts[strtolower($m[1])] = stripcslashes($m[2]);
        }
        else if(!empty($m[3]))
        {
          $atts[strtolower($m[3])] = stripcslashes($m[4]);
        }
        else if(!empty($m[5]))
        {
          $atts[strtolower($m[5])] = stripcslashes($m[6]);
        }
        else if(isset($m[7]) and strlen($m[7]))
        {
          $atts[] = stripcslashes($m[7]);
        }
        elseif(isset($m[8]))
        {
          $atts[] = stripcslashes($m[8]);
        }
      }
    }
    else
    {
      $atts = ltrim($text);
    }

    return $atts;
  }

  /**
   * Regular Expression callable for parse() for calling macro hook.
   *
   * @see getMacroRegex() for details of the match array contents.
   * @param array $m Regular expression match array
   * @return mixed False on failure.
   */
  protected function doMacroTag($m)
  {
    // allow [[foo]] syntax for escaping a tag
    if($m[1] == '[' && $m[6] == ']')
    {
      return substr($m[0], 1, -1);
    }

    $tag = $m[2];
    $attr = $this->parseAttributes($m[3]);

    if(isset($m[5]))
    {
      // enclosing tag - extra parameter
      return $this->call($this->tags[$tag], $attr, $m[5], $tag) . $m[6];
    }
    else
    {
      // self-closing tag
      return $this->call($this->tags[$tag], $attr, null, $tag) . $m[6];
    }
  }

  /**
   * Calls the callback defined in the definition
   *
   * @param sfTextMacroCallbackDefinition $definition
   *
   * @param array $attributes
   * @param string $value
   * @param string $tag
   * @return string
   */
  protected function call(sfTextMacroCallbackDefinition $definition, $attributes, $value, $tag)
  {
    if(($function = $definition->getFunction()))
    {
      return call_user_func($function, $attributes, $value, $tag);
    }

    $cacheKey = md5(serialize($definition));

    if(!isset($this->objectCache[$cacheKey]))
    {
      $object = $this->objectCache[$cacheKey] = $this->serviceContainer->createObjectFromDefinition($definition);
    }
    else
    {
      $object = $this->objectCache[$cacheKey];
    }

    // we distinguish which method is the right based on the interface
    $method = $object instanceof sfITextMacroWidget ? 'getHtml' : 'filter';

    return call_user_func(array($object, $method), $attributes, $value, $tag);
  }

  /**
   * Retrieve the tags regular expression for searching.
   *
   * The regular expression combines the macro tags in the regular expression
   * in a regex class.
   *
   * The regular expresion contains 6 different sub matches to help with parsing.
   *
   * 1/6 - An extra [ or ] to allow for escaping tags with double [[]]
   * 2 - The macro name
   * 3 - The macro argument list
   * 4 - The self closing /
   * 5 - The content of a macro when it wraps some content.
   *
   * @param array $allowedTags Array of allowed tags. If empty all tags will be used
   * @return string|false The macro search regular expression, false if there is no tag
   */
  protected function getMacrosRegex($allowedTags = array())
  {
    if(count($allowedTags))
    {
      $tagNames = array_intersect($allowedTags, $this->getTags());
    }
    else
    {
      $tagNames = $this->getTags();
    }

    $regexp = join('|', array_map('preg_quote', $tagNames));
    if(empty($regexp))
    {
      return false;
    }

    // WARNING! Do not change this regex without changing doMacroTag() and strip()
    return
      '\\['                              // Opening bracket
    . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
    . "($regexp)"                        // 2: Shortcode name
    . '(?![\\w-])'                       // Not followed by word character or hyphen
    . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
    .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
    .     '(?:'
    .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
    .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
    .     ')*?'
    . ')'
    . '(?:'
    .     '(\\/)'                        // 4: Self closing tag ...
    .     '\\]'                          // ... and closing bracket
    . '|'
    .     '\\]'                          // Closing bracket
    .     '(?:'
    .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
    .             '[^\\[]*+'             // Not an opening bracket
    .             '(?:'
    .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
    .                 '[^\\[]*+'         // Not an opening bracket
    .             ')*+'
    .         ')'
    .         '\\[\\/\\2\\]'             // Closing shortcode tag
    .     ')?'
    . ')'
    . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
  }

}
