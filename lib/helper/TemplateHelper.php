<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * TemplateHelper defines some base helpers for constructing javascript templates.
 *
 * @package    Sift
 * @subpackage helper
 */

/**
 * Starts template buffering.
 *
 * @param string $id Id of the template
 * @param array $options Array of options
 * @param string $type Type of the template (default is text/html)
 */
function start_template($id, $options = array(), $type = 'text/html')
{
  $request = sfContext::getInstance()->getRequest();

  // prevent concurrent buffer
  if(!is_null($request->getAttribute('started', null, 'template_javascript')))
  {
    throw new LogicException(sprintf('Template buffering already started. Buffering template id: "%s"',
            $request->getAttribute('id', '?', 'template_javascript')));
  }

  // validate id
  if(preg_match('/\s+/', $id) || !strlen($id))
  {
    throw new InvalidArgumentException('The template id "%s" is not valid. Should be without spaces and long at least one character');
  }

  $request->setAttribute('started', true, 'template_javascript');
  $request->setAttribute('id', $id, 'template_javascript');
  $request->setAttribute('type', $type, 'template_javascript');

  if(!empty($options))
  {
    $request->setAttribute('options', _parse_attributes($options), 'template_javascript');
  }

  // start buffering
  ob_start();
}

/**
 * Ends the template
 */
function end_template()
{
  // get the buffer
  $template = ob_get_clean();
  $request = sfContext::getInstance()->getRequest();

  // output
  $result = false;
  // we have javascript, so we need to output correct script tag
  if(sfConfig::get('sf_javascript_templates.precompile_enabled'))
  {
    $options = $request->getAttribute('options', array(), 'template_javascript');
    $id = $request->getAttribute('id', null, 'template_javascript');

    // we have to deside if to put as <script src=""> or inline scrip tag
    // inline
    if(isset($options['inline']) && $options['inline'])
    {
      // compile template
      $compiled = _compile_template($template);

      // create a call to Template (see core/template.js for more info about the API)
      $script = sprintf("\nTemplate.add('%s', %s);\n", $id, $compiled);

      $result = content_tag('script', $script, array(
        'type' => 'text/javascript'
      )) . "\n";

    }
    // linked file
    // we will put the compiled template to cache
    else
    {
      $cache = dechex(crc32($id));

      // cache on the disk
      $cacheFile = sprintf('%s/cache/js/%s.js', sfConfig::get('sf_web_dir'), $cache);

      // cache from web
      $cacheWebFile = sprintf('%s/cache/js/%s.js', $request->getRelativeUrlRoot(), $cache);

      // cache does not exist
      if(!file_exists($cacheFile) || sfConfig::get('sf_environment') == 'dev')
      {
        // compile template
        $compiled = _compile_template($template);
        // create a call to Template (see core/template.js for more info about the API)
        $script = sprintf("\nTemplate.add('%s', %s);\n", $id, $compiled);
        sfJavascriptTemplateCompiler::writeCache($cacheFile, $script);
      }

      // include the template to response
      sfContext::getInstance()->getResponse()->addJavascript($cacheWebFile);
    }
  }
  else
  {
    $result = content_tag('script', $template, array(
        'id' => $request->getAttribute('id', '', 'template_javascript'),
        'type' => $request->getAttribute('type', '', 'template_javascript')
    )) . "\n";
  }

  if($result)
  {
    echo $result;
  }

  // cleanup request attributes
  $request->getAttributeHolder()->removeNamespace('template_javascript');
}

/**
 * Captures the output between start_template() and end_template().
 *
 * @param string $buffer
 */
function _compile_template($buffer)
{
  if(!sfConfig::get('sf_javascript_templates.precompile_enabled', true))
  {
    return $buffer;
  }

  // cache is enabled we will look for cached version of the buffer
  if(sfConfig::get('sf_cache'))
  {
    $context = sfContext::getInstance();
    $request = $context->getRequest();

    $cache = $context->getViewCacheManager()->getCache();
    $options = $request->getAttribute('options', array(), 'template_javascript');

    // cache lifetime
    $lifetime = _get_option($options, 'cache_lifetime');
    // cache key
    $key = _get_option($options, 'cache_key');

    // skip cache
    if($key !== false)
    {
      if(!$key)
      {
        $key = md5($context->getModuleName() . $context->getActionName() . $buffer . serialize($options));
      }

      if($cache->has($key, 'template_javascript'))
      {
        return $cache->get($key, 'template_javascript');
      }
    }
  }

  // compile the template
  $result = compile_javascript_template($buffer, isset($options['compile_options']) ?
          (array)$options['compile_options'] : array());

  if(sfConfig::get('sf_javascript_minify.enabled'))
  {
    $result = minify_javascript($result);
  }

  if(sfConfig::get('sf_cache') && $key)
  {
    $cache->set($key, 'template_javascript', $result, $lifetime);
  }

  return $result;
}

/**
 * Compiles the javascript template. This function does not care about cache,
 * so use it with caution.
 *
 * @staticvar sfIJavascriptTemplateCompiler $compiler
 * @param string $string Template to compile
 * @param array $options Array of options for the compile() method
 * @return string Compiled template
 */
function compile_javascript_template($string, $options = array())
{
  static $compiler;

  if(!$compiler)
  {
    // create compiler
    $compiler = sfJavascriptTemplateCompiler::factory(
                  sfConfig::get('sf_javavascript_templates.driver', 'handlebars'),
                  sfConfig::get('sf_javascript_templates.driver_options', array())
                );
  }

  return $compiler->compile($string, $options);
}
