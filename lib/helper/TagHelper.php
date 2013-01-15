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
 * Returns a JavaScript tag with the '$content' inside.
 * Example:
 *   <?php echo javascript_tag("alert('All is good')") ?>
 *   => <script type="text/javascript">alert('All is good')</script>
 */
function javascript_tag($content)
{
  return content_tag('script', javascript_cdata_section(_compress_javascript($content)), array('type' => 'text/javascript'));    
}

/**
 * Returns CDATA section (for usage in javascript_tag())
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
    $options['class'] = join(' ', array_values($classes));
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
 * Starts output buffering using _compress_javascript()
 * 
 */
function start_javascript()
{
  ob_start('_compress_javascript');
}

/**
 * Ends buffering and flushes the buffer
 * 
 */
function end_javascript()
{
  ob_end_flush();
}

/**
 * Compresses javascript using jsMin from sf_javascript_minimizer_path
 * which is in sf_web_dir/min/lib/JSMin.php as default
 * 
 * @staticvar type $jsMinFound
 * @param type $buffer
 * @return string 
 */
function _compress_javascript($buffer)
{
  static $jsMinFound;
  
  // do not compress when cache enabled
  // or minimize library not found in previous run
  if(!sfConfig::get('sf_minimize_javascript', true) || 
          (isset($jsMinFound) && !$jsMinFound))
  {
    return $buffer;
  }
  
  if(!isset($jsMinFound))
  {
    $minimizerPath = sfConfig::get('sf_javascript_minimizer_path', 
            sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . '/min/lib/JSMin.php');
    $jsMinFound = @include_once $minimizerPath;
  }

  if(!$jsMinFound)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->err('Javascript compression library is not present. Cannot minimize inline scripts.');
    }    
    return $buffer;
  }
  
  try
  {
    $t1     = microtime(true);
    $result = JSMin::minify($buffer);
    $t2     = microtime(true);
    $result .= "\n// packed in: " . sprintf('%.4f', ($t2 - $t1)) . 's';
    return $result;
  }
  catch(Exception $e)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->err('Error while minimizing inline script: ' . $e->getMessage());
    }
    
    return $buffer;
  }
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

