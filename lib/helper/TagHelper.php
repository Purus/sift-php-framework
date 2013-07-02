<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * TagHelper defines some base helpers to construct html tags.
 *
 * @package    Sift
 * @subpackage helper
 */

/**
 * Constructs an html tag.
 *
 * @param  $name    string  tag name
 * @param  $options array   tag options
 * @param  $open    boolean true to leave tag open
 * @return string
 */
function tag($name, $options = array(), $open = false)
{
  if(!$name)
  {
    return '';
  }
  return '<'.$name._tag_options($options).(($open) ? '>' : (sfConfig::get('sf_html5', false) ? '>' : ' />'));
}

function content_tag($name, $content = '', $options = array())
{
  if(!$name)
  {
    return '';
  }
  return '<'.$name._tag_options($options).'>'.$content.'</'.$name.'>';
}

function cdata_section($content)
{
  return "<![CDATA[$content]]>";
}

/**
 * Escape carrier returns and single and double quotes for Javascript segments.
 *
 * @param type $javascript
 * @return string
 */
function escape_javascript($javascript)
{
  $javascript = preg_replace('/\r\n|\n|\r/', "\\n", $javascript);
  $javascript = preg_replace('/(["\'])/', '\\\\\1', $javascript);
  return $javascript;
}

/**
 * Returns a javascript tag with the $content inside it.
 *
 * @example
 *   <?php echo javascript_tag("alert('All is good')") ?>
 *   => <script type="text/javascript">alert('All is good')</script>
 * @param string $content
 * @return string
 */
function javascript_tag($content)
{
  return content_tag('script',
          sfHtml::isXhtml() ?
            javascript_cdata_section(_compress_javascript($content)) :
            ("\n" . _compress_javascript($content) . "\n"),
          array('type' => 'text/javascript'));
}

/**
 * Returns CDATA section (for usage in javascript_tag()). When using XHML.
 *
 * @param string $content
 * @return string
 */
function javascript_cdata_section($content)
{
  return "\n//".cdata_section("\n$content\n//")."\n";
}

/**
 * Escapes an HTML string.
 *
 * @param  string HTML string to escape
 * @return string escaped string
 */
function escape_once($html)
{
  return fix_double_escape(htmlspecialchars($html, ENT_COMPAT, sfConfig::get('sf_charset')));
}

/**
 * Fixes double escaped strings.
 *
 * @param  string HTML string to fix
 * @return string escaped string
 */
function fix_double_escape($escaped)
{
  return preg_replace('/&amp;([a-z]+|(#\d+)|(#x[\da-f]+));/i', '&$1;', $escaped);
}

/**
 * Converts options for usage in tag
 *
 * @param type $options
 * @return string
 */
function _tag_options($options = array())
{
  $options = _parse_attributes($options);
  $html    = '';
  foreach($options as $key => $value)
  {
    $html .= ' '.$key.'="'.escape_once($value).'"';
  }
  return $html;
}

/**
 * Converts string attributes to array
 *
 * @param type $string
 * @return type array
 */
function _parse_attributes($string)
{
  return is_array($string) ? $string : sfToolkit::stringToArray($string);
}

/**
 * Returns option with name in given array of options. Also unsets the option
 * from the array
 *
 * @param array $options
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function _get_option(&$options, $name, $default = null)
{
  if(array_key_exists($name, $options))
  {
    $value = $options[$name];
    unset($options[$name]);
  }
  else
  {
    $value = $default;
  }
  return $value;
}

/**
 * Returns body tag (use in layout) with assigned id or classes, or onload events
 *
 * @param arary $options
 * @return string
 */
function body_tag($options = array())
{
  $options = _parse_attributes($options);

  $classes = sfContext::getInstance()->getResponse()->getBodyClasses();

  if(count($classes))
  {
    $options['class'] = isset($options['class']) ?
            join(' ', array_merge(array_values($classes), array($options['class']))) :
            join(' ', array_merge(array_values($classes)));
  }
  $id = sfContext::getInstance()->getResponse()->getBodyId();
  if($id)
  {
    $options['id'] = $id;
  }
  $onload = sfContext::getInstance()->getResponse()->getBodyOnload();
  if(count($onload))
  {
    $options['onload'] = join(';', $onload);
  }
  $onunload = sfContext::getInstance()->getResponse()->getBodyOnUnload();
  if(count($onunload))
  {
    $options['onunload'] = join(';', $onunload);
  }
  return tag('body', $options, true) . "\n";
}

/**
 * Starts output buffering using _compress_javascript(). Script must be ended with
 * end_javascript()
 *
 * @example
 *
 *   <script type="text/javascript">
 *   <?php start_javascript(); ?>
 *
 *   var myObject = {};
 *
 *   <?php end_javascript(); ?>
 *   </script>
 *
 * @param stirng $cacheKey Key for cache (if null, will be generated automatically)
 * @param integer $cacheLigeTime Cache lifetime if cache is enabled
 */
function start_javascript($cacheKey = null, $cacheLifeTime = null)
{
  if(!is_null($cacheKey))
  {
    sfContext::getInstance()->getRequest()->setAttribute('key', $cacheKey, 'minimize_script');
  }

  if(!is_null($cacheLifeTime))
  {
    sfContext::getInstance()->getRequest()->setAttribute('lifetime', $cacheLifeTime, 'minimize_script');
  }

  // start the buffer
  ob_start('_compress_javascript');
}

/**
 * Ends buffering and flushes the buffer
 *
 */
function end_javascript()
{
  ob_end_flush();

  // cleanup request attributes
  sfContext::getInstance()->getRequest()->getAttributeHolder()->removeNamespace('minimize_script');
}

/**
 * Compresses javascript using sfMinifier. Looks for settings:
 *
 *  * sf_minifier_driver: simple
 *  * sf_minifier_options array()
 *
 * @staticvar sfMinifier $minifier
 * @param string $buffer Buffer
 * @return string
 * @see start_javascript()
 */
function _compress_javascript($buffer)
{
  // do not compress when minify is disabled
  if(!sfConfig::get('sf_javascript_minify.enabled', true))
  {
    return $buffer;
  }

  // cache is enabled we will look for cached version of the buffer
  if(sfConfig::get('sf_cache'))
  {
    $context = sfContext::getInstance();
    $request = $context->getRequest();

    // cache lifetime
    $lifetime = $request->getAttribute('lifetime', 3600, 'minimize_script');
    $cache = $context->getViewCacheManager()->getCache();

    $key = $request->getAttribute('key', null, 'minimize_script');

    // skip cache
    if($key !== false)
    {
      if(!$key)
      {
        $key = md5($context->getModuleName() . $context->getActionName() . $buffer);
      }

      if($cache->has($key, 'minimize_javascript'))
      {
        return $cache->get($key, 'minimize_javascript');
      }
    }
  }

  // minify the buffer
  $result = minify_javascript($buffer);

  if(sfConfig::get('sf_cache') && $key)
  {
    $cache->set($key, 'minimize_javascript', $result, $lifetime);
  }

  return $result;
}

/**
 * Minifies javascript. Does not care about caching. Use with caution.
 *
 * @staticvar sfIMinifier $minifier
 * @param string $js Javascript code to minify
 * @return string Minified javascript code
 */
function minify_javascript($js)
{
  // minifier holder
  static $minifier;

  if(!isset($minifier))
  {
    // create minifier instance
    $minifier = sfMinifier::factory(
                  sfConfig::get('sf_javascript_minify.driver', 'JsMin'),
                  sfConfig::get('sf_javascript_minify.driver_options', array())
                );
  }

  // minify the javascript
  return $minifier->processString($js);
}

/**
 * Alias for sfConfig::get() method.
 *
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function config_get($name, $default = null)
{
  return sfConfig::get($name, $default);
}

/**
 * Wraps the content in conditional comments.
 *
 * @param  string $condition
 * @param  string $content
 * @return string
 *
 * @see http://msdn.microsoft.com/en-us/library/ms537512(VS.85).aspx
 */
function ie_conditional_comment($condition, $content)
{
  return sfHtml::ieConditionalComment($condition, $content);
}

/**
 * Encodes given $variable to JSON using sfJson::encode() and optionally escapes it.
 *
 * @param mixed $variable Variable to encode
 * @param boolean $escape Escape the value?
 * @return string
 */
function jsonize($variable, $escape = true)
{
  $value = sfJson::encode($variable);
  return $escape ? escape_once($value) : $value;
}
