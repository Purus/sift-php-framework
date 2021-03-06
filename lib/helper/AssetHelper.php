<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * AssetHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

/**
 * Returns a <link> tag that browsers and news readers
 * can use to auto-detect a RSS or ATOM feed for the current page,
 * to be included in the <head> section of a HTML document.
 *
 * <b>Options:</b>
 * - rel - defaults to 'alternate'
 * - type - defaults to 'application/rss+xml'
 * - title - defaults to the feed type in upper case
 *
 * <b>Examples:</b>
 * <code>
 *  echo auto_discovery_link_tag('rss', 'module/feed');
 *    => <link rel="alternate" type="application/rss+xml" href="http://www.curenthost.com/module/feed" />
 *  echo auto_discovery_link_tag('rss', 'module/feed', array('title' => 'My RSS'));
 *    => <link rel="alternate" type="application/rss+xml" title="My RSS" href="http://www.curenthost.com/module/feed" />
 * </code>
 *
 * @param  string feed type ('rss', 'atom')
 * @param  string 'module/action' or '@rule' of the feed
 * @param  array additional HTML compliant <link> tag parameters
 * @return string HTML <link> tag
 */
function auto_discovery_link_tag($type = 'rss', $url, $tag_options = array())
{
  $params = array(
      'rel' => isset($tag_options['rel']) ? $tag_options['rel'] : 'alternate',
      'href' => url_for($url, true)
  );

  if(isset($tag_options['type']))
  {
    $params['type'] = $tag_options['type'];
  }
  elseif(!empty($type))
  {
    if(strpos($type, 'application/') !== false)
    {
      $params['type'] = $type;
    }
    else
    {
      $params['type'] = 'application/' . $type . '+xml';
    }
  }

  if(isset($tag_options['title']))
  {
    $params['title'] = $tag_options['title'];
  }
  else
  {
    $params['title'] = ucfirst($type);
  }

  return tag('link', $params);
}

/**
 * Returns the path to a JavaScript asset.
 *
 * <b>Example:</b>
 * <code>
 *  echo javascript_path('myscript');
 *    => /js/myscript.js
 * </code>
 *
 * <b>Note:</b> The asset name can be supplied as a...
 * - full path, like "/my_js/myscript.js"
 * - file name, like "myscript.js", that gets expanded to "/js/myscript.js"
 * - file name without extension, like "myscript", that gets expanded to "/js/myscript.js"
 *
 * @param  string asset name
 * @param  bool return absolute path ?
 * @return string file path to the JavaScript file
 * @see    javascript_include_tag
 */
function javascript_path($source, $absolute = false)
{
  return _compute_public_path(_replace_constants($source), sfConfig::get('sf_web_js_dir_name', 'js'), 'js', $absolute);
}

/**
 * Returns a <script> include tag per source given as argument.
 *
 * <b>Examples:</b>
 * <code>
 *  echo javascript_include_tag('xmlhr');
 *    => <script type="text/javascript" src="/js/xmlhr.js"></script>
 *  echo javascript_include_tag('common.javascript', '/elsewhere/cools');
 *    => <script type="text/javascript" src="/js/common.javascript"></script>
 *       <script type="text/javascript" src="/elsewhere/cools.js"></script>
 * </code>
 *
 * @param  string asset names
 * @param  array additional HTML compliant <link> tag parameters
 *
 * @return string XHTML compliant <script> tag(s)
 * @see    javascript_path
 */
function javascript_include_tag()
{
  $sources = func_get_args();
  $sourceOptions = (func_num_args() > 1 && is_array($sources[func_num_args() - 1])) ? array_pop($sources) : array();

  $html = '';
  foreach($sources as $source)
  {
    $absolute = false;
    if(isset($sourceOptions['absolute']))
    {
      unset($sourceOptions['absolute']);
      $absolute = true;
    }

    $condition = null;
    if(isset($sourceOptions['ie_condition']))
    {
      $condition = $sourceOptions['ie_condition'];
      unset($sourceOptions['ie_condition']);
    }

    $raw = false;
    if(isset($sourceOptions['raw']))
    {
      $raw = true;
      unset($sourceOptions['raw']);
    }
    elseif(isset($sourceOptions['generated']))
    {
      $source = _dynamic_path($source, $absolute);
      $raw = true;
      unset($sourceOptions['generated']);
    }

    if(!$raw)
    {
      $source = javascript_path($source, $absolute);
    }

    $options = array_merge(array('type' => 'text/javascript', 'src' => $source), $sourceOptions);
    $tag = content_tag('script', '', $options);

    if(!is_null($condition))
    {
      $tag = ie_conditional_comment($condition, $tag);
    }

    $html .= $tag . "\n";
  }

  return $html;
}

/**
 * Returns the path to a stylesheet asset.
 *
 * <b>Example:</b>
 * <code>
 *  echo stylesheet_path('style');
 *    => /css/style.css
 * </code>
 *
 * <b>Note:</b> The asset name can be supplied as a...
 * - full path, like "/my_css/style.css"
 * - file name, like "style.css", that gets expanded to "/css/style.css"
 * - file name without extension, like "style", that gets expanded to "/css/style.css"
 *
 * @param  string asset name
 * @param  bool return absolute path ?
 * @return string file path to the stylesheet file
 * @see    stylesheet_tag
 */
function stylesheet_path($source, $absolute = false)
{
  return _compute_public_path(_replace_constants($source), sfConfig::get('sf_web_css_dir_name', 'css'), 'css', $absolute);
}

/**
 * Returns a css <link> tag per source given as argument,
 * to be included in the <head> section of a HTML document.
 *
 * <b>Options:</b>
 * - rel - defaults to 'stylesheet'
 * - type - defaults to 'text/css'
 * - media - defaults to 'screen'
 *
 * <b>Examples:</b>
 * <code>
 *  echo stylesheet_tag('style');
 *    => <link href="/stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
 *  echo stylesheet_tag('style', array('media' => 'all'));
 *    => <link href="/stylesheets/style.css" media="all" rel="stylesheet" type="text/css" />
 *  echo stylesheet_tag('random.styles', '/css/stylish');
 *    => <link href="/stylesheets/random.styles" media="screen" rel="stylesheet" type="text/css" />
 *       <link href="/css/stylish.css" media="screen" rel="stylesheet" type="text/css" />
 * </code>
 *
 * @param  string asset names
 * @param  array additional HTML compliant <link> tag parameters
 * @return string HTML compliant <link> tag(s)
 * @see    stylesheet_path
 */
function stylesheet_tag()
{
  $sources = func_get_args();
  $sourceOptions = (func_num_args() > 1 && is_array($sources[func_num_args() - 1])) ? array_pop($sources) : array();

  $html = '';
  foreach($sources as $source)
  {
    $absolute = false;
    if(isset($sourceOptions['absolute']))
    {
      unset($sourceOptions['absolute']);
      $absolute = true;
    }

    $raw = false;
    if(isset($sourceOptions['raw']))
    {
      $raw = true;
      unset($sourceOptions['raw']);
    }
    elseif(isset($sourceOptions['generated']))
    {
      $raw = true;
      unset($sourceOptions['generated']);
    }

    $condition = null;
    if(isset($sourceOptions['ie_condition']))
    {
      $condition = $sourceOptions['ie_condition'];
      unset($sourceOptions['ie_condition']);
    }

    if(!$raw)
    {
      // less support
      if(isset($sourceOptions['less'])
              || preg_match('/\.less$/i', $source))
      {
        $source = stylesheet_path($source);

        // is base domain is affecting the path, we need to take care of it
        if($baseDomain = sfConfig::get('sf_base_domain'))
        {
          $source = preg_replace(sprintf('~https?://%s%s~', $baseDomain,
              sfContext::getInstance()->getRequest()->getRelativeUrlRoot()), '', $source);
        }

        $source = sfContext::getInstance()->getService('less_compiler')->compileStylesheetIfNeeded($source);

        if($baseDomain)
        {
          $source = stylesheet_path($source);
        }

        unset($sourceOptions['less']);
      }
      else
      {
        $source = stylesheet_path($source, $absolute);
      }
    }

    $options = array_merge(array(
        'rel' => 'stylesheet',
        'type' => 'text/css',
        'media' => sfConfig::get('sf_default_stylesheet_media', 'screen,projection,tv'),
        'href' => $source), $sourceOptions);

    $tag = tag('link', $options);

    if(!is_null($condition))
    {
      $tag = ie_conditional_comment($condition, $tag);
    }

    $html .= $tag . "\n";
  }

  return $html;
}

/**
 * Adds a stylesheet to the response object.
 *
 * @see sfResponse->addStylesheet()
 */
function use_stylesheet($css, $position = '', $options = array())
{
  if(!is_array($css))
  {
    $css = array($css);
  }

  foreach($css as $stylesheet)
  {
    sfContext::getInstance()->getResponse()->addStylesheet($stylesheet, $position, $options);
  }
}

/**
 * Adds a javascript to the response object.
 *
 * @see sfResponse->addJavascript()
 */
function use_javascript($js, $position = '', $options = array())
{
  if(!is_array($js))
  {
    $js = array($js => array());
  }

  foreach($js as $javascript => $options)
  {
    sfContext::getInstance()->getResponse()->addJavascript($javascript, $position, $options);
  }
}

/**
 * Decorates the current template with a given layout.
 *
 * @param mixed The layout name or path or false to disable the layout
 */
function decorate_with($layout)
{
  $view = sfContext::getInstance()->getActionStack()->getLastEntry()->getViewInstance();
  if(false === $layout)
  {
    $view->setDecorator(false);
  }
  else
  {
    $view->setDecoratorTemplate($layout);
  }
}

/**
 * Returns the path to an image asset.
 *
 * <b>Example:</b>
 * <code>
 *  echo image_path('foobar');
 *    => /images/foobar.png
 * </code>
 *
 * <b>Note:</b> The asset name can be supplied as a...
 * - full path, like "/my_images/image.gif"
 * - file name, like "rss.gif", that gets expanded to "/images/rss.gif"
 * - file name without extension, like "logo", that gets expanded to "/images/logo.png"
 *
 * @param  string asset name
 * @param  bool return absolute path ?
 * @return string file path to the image file
 * @see    image_tag
 */
function image_path($source, $absolute = false)
{
  return _compute_public_path(_replace_constants($source), sfConfig::get('sf_web_images_dir_name', 'images'), 'png', $absolute);
}

/**
 * Returns an <img> image tag for the asset given as argument.
 *
 * <b>Options:</b>
 * - 'absolute' - to output absolute file paths, useful for embedded images in emails
 * - 'alt'  - defaults to the file name part of the asset (capitalized and without the extension)
 * - 'size' - Supplied as "XxY", so "30x45" becomes width="30" and height="45"
 *
 * <b>Examples:</b>
 * <code>
 *  echo image_tag('foobar');
 *    => <img src="images/foobar.png" alt="Foobar" />
 *  echo image_tag('/my_images/image.gif', array('alt' => 'Alternative text', 'size' => '100x200'));
 *    => <img src="/my_images/image.gif" alt="Alternative text" width="100" height="200" />
 * </code>
 *
 * @param  string image asset name
 * @param  array additional HTML compliant <img> tag parameters
 * @return string XHTML compliant <img> tag
 * @see    image_path
 */
function image_tag($source, $options = array())
{
  if(!$source)
  {
    return '';
  }

  $options = _parse_attributes($options);

  $absolute = false;
  if(isset($options['absolute']))
  {
    unset($options['absolute']);
    $absolute = true;
  }

  // source is mail embed image source cid:1267089287.4b863f87c8b37@server
  if(strpos($source, 'cid:') !== false)
  {
    $options['src'] = $source;
  }
  else
  {
    $options['src'] = image_path($source, $absolute);
  }

  if(!isset($options['alt']))
  {
    $path_pos = strrpos($source, '/');
    $dot_pos = strrpos($source, '.');
    $begin = $path_pos ? $path_pos + 1 : 0;
    $nb_str = ($dot_pos ? $dot_pos : strlen($source)) - $begin;
    $options['alt'] = ucfirst(substr($source, $begin, $nb_str));
  }

  if(isset($options['size']))
  {
    if(strpos($options['size'], 'x') == false)
    {
      if(sfConfig::get('sf_debug'))
      {
        throw new InvalidArgumentException(sprintf('Size option "%s" is not valid', $options['size']));
      }
    }
    list($options['width'], $options['height']) = explode('x', $options['size'], 2);
    unset($options['size']);
  }

  return tag('img', $options);
}

/**
 * Computes public path for given $source
 *
 * @param string $source Source
 * @param string $dir Directory
 * @param string $ext Default extension
 * @param boolean $absolute Absolute path?
 * @return string
 */
function _compute_public_path($source, $dir, $ext, $absolute = false)
{
  // absolute url or absolute url without protocol?
  if(strpos($source, '://') || strpos($source, '//') === 0)
  {
    return $source;
  }

  $request = sfContext::getInstance()->getRequest();
  $sf_relative_url_root = $request->getRelativeUrlRoot();
  if(0 !== strpos($source, '/'))
  {
    $source = $sf_relative_url_root . '/' . $dir . '/' . $source;
  }

  $query_string = '';
  if(false !== $pos = strpos($source, '?'))
  {
    $query_string = substr($source, $pos);
    $source = substr($source, 0, $pos);
  }

  if(false === strpos(basename($source), '.'))
  {
    $source .= '.' . $ext;
  }

  if($sf_relative_url_root && 0 !== strpos($source, $sf_relative_url_root))
  {
    $source = $sf_relative_url_root . $source;
  }

  $host = $request->getHost();

  $baseDomain = sfConfig::get('sf_base_domain');
  if($baseDomain && $baseDomain != $request->getHost())
  {
    $absolute = true;
    $host = $baseDomain;
  }

  if($absolute)
  {
    $source = 'http' . ($request->isSecure() ? 's' : '') . '://' . $host . $source;
  }

  return $source . $query_string;
}

/**
 * Prints a set of <meta> tags according to the response attributes,
 * to be included in the <head> section of a HTML document.
 *
 * <b>Examples:</b>
 * <code>
 *  include_metas();
 *    => <meta name="title" content="Sift PHP framework" />
 *       <meta name="robots" content="index, follow" />
 *       <meta name="description" content="Sift" />
 * </code>
 *
 * @return string Meta tag(s)
 * @see    include_http_metas
 */
function include_metas()
{
  foreach(sfContext::getInstance()->getResponse()->getMetas() as $name => $content)
  {
    echo tag('meta', array('name' => $name, 'content' => $content)) . "\n";
  }
}

/**
 * Returns a set of <meta http-equiv> tags according to the response attributes,
 * to be included in the <head> section of a HTML document.
 *
 * <b>Examples:</b>
 * <code>
 *  include_http_metas();
 *    => <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 * </code>
 *
 * <b>Note:</b> Modify the sfResponse object or the view.yml to change, add or remove HTTP metas.
 *
 * @return string XHTML compliant <meta> tag(s)
 * @see    include_metas
 */
function include_http_metas()
{
  foreach(sfContext::getInstance()->getResponse()->getHttpMetas() as $httpequiv => $value)
  {
    echo tag('meta', array('http-equiv' => $httpequiv, 'content' => $value)) . "\n";
  }
}

/**
 * Returns the title of the current page according to the response attributes,
 * to be included in the <title> section of a HTML document.
 *
 * <b>Note:</b> Modify the sfResponse object or the view.yml to modify the title of a page.
 *
 * @return string page title
 */
function include_title($withGlobal = true)
{
  echo content_tag('title', get_title($withGlobal)) . "\n";
}

/**
 * Returns page title
 *
 * @return string
 */
function get_title($withGlobal = true)
{
  return apply_filters('html.title', sfContext::getInstance()->getResponse()->getTitle($withGlobal));
}

/**
 * Returns <script> tags for all javascripts configured in view.yml or added to the response object.
 *
 * You can use this helper to decide the location of javascripts in pages.
 * By default, if you don't call this helper, Sift will automatically include javascripts before </head>.
 * Calling this helper disables this behavior.
 *
 * @return string <script> tags
 */
function get_javascripts()
{
  $response = sfContext::getInstance()->getResponse();
  $response->setParameter('javascripts_included', true, 'sift/view/asset');

  $already_seen = array();
  $html = '';

  foreach(array('first', '', 'last') as $position)
  {
    foreach($response->getJavascripts($position) as $files => $options)
    {
      if(!is_array($files))
      {
        $files = array($files);
      }
      foreach($files as $file)
      {
        $tag = javascript_include_tag($file, $options);

        if(isset($already_seen[$tag]))
        {
          continue;
        }

        $html .= $tag;
        $already_seen[$tag] = true;
      }
    }
  }
  return $html;
}

/**
 * Prints <script> tags for all javascripts configured in view.yml or added to the response object.
 *
 * @see get_javascripts()
 */
function include_javascripts()
{
  echo get_javascripts();
}

/**
 * Returns <link> tags for all stylesheets configured in view.yml or added to the response object.
 *
 * You can use this helper to decide the location of stylesheets in pages.
 * By default, if you don't call this helper, Sift will automatically include stylesheets before </head>.
 * Calling this helper disables this behavior.
 *
 * @return string <link> tags
 */
function get_stylesheets()
{
  $response = sfContext::getInstance()->getResponse();
  $response->setParameter('stylesheets_included', true, 'sift/view/asset');

  $already_seen = array();
  $html = '';

  foreach(array('first', '', 'last') as $position)
  {
    foreach($response->getStylesheets($position) as $files => $options)
    {
      if(!is_array($files))
      {
        $files = array($files);
      }

      foreach($files as $file)
      {
        $tag = stylesheet_tag($file, $options);
        if(isset($already_seen[$tag]))
        {
          continue;
        }

        $already_seen[$tag] = true;
        $html .= $tag;
      }
    }
  }

  return $html;
}

/**
 * Prints <link> tags for all stylesheets configured in view.yml or added to the response object.
 *
 * @see get_stylesheets()
 */
function include_stylesheets()
{
  echo get_stylesheets();
}

/**
 * Returns html code for all auto discovery links assigned to response object
 * @return string
 */
function get_auto_discovery_links()
{
  $response = sfContext::getInstance()->getResponse();
  $response->setParameter('auto_discovery_links_included', true, 'sift/view/asset');
  $html = array();
  foreach($response->getAutoDiscoveryLinks() as $link)
  {
    $html[] = auto_discovery_link_tag($link['type'], $link['url'], $link['tag_options']);
  }
  return join("\n", $html) . "\n";
}

/**
 * Prints <link> tags for all auto discovery link configured in view.yml
 * or added to the response object.
 */
function include_auto_discovery_links()
{
  echo get_auto_discovery_links();
}

/**
 * Adds auto discovery links to response
 *
 * Autodiscovery link is something like:
 * <link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.curenthost.com/module/feed" />
 *
 * @param string url of the feed (not routing rule!)
 * @param string feed type ('rss', 'atom')
 * @param  array additional HTML compliant <link> tag parameters
 */
function add_auto_discovery_link($url, $type = 'rss', $tag_options = array())
{
  sfContext::getInstance()->getResponse()->addAutoDiscoveryLink($url, $type, $tag_options);
}

/**
 * Sets canonical URL
 *
 * @param string $url Url
 */
function set_canonical_url($url)
{
  sfContext::getInstance()->getResponse()->setCanonicalUrl($url);
}

/**
 * Gets canonical url from response
 *
 * @param boolean $raw Raw Url or include inside <link rel="canonical" href="url" />
 * @param boolean $detect Detect the canonical url from the current URI?
 * @return string string
 */
function get_canonical_url($raw = true, $detect = true)
{
  // custom
  $url = sfContext::getInstance()->getResponse()->getCanonicalUrl();

  if(!$url && $detect)
  {
    if(sfContext::getInstance()->getModuleName() == sfConfig::get('sf_error_404_module')
        && sfContext::getInstance()->getActionName() == sfConfig::get('sf_error_404_action'))
    {
      return '';
    }
    else if(($route = sfRouting::getInstance()->getCurrentInternalUri(false)))
    {
      $url = url_for($route, true);
    }
    else
    {
      $url = sfContext::getInstance()->getRequest()->getUri();
    }
  }
  return $raw ? $url : tag('link', array('rel' => 'canonical', 'href' => $url));
}

/**
 * Clears canonical URL
 *
 * @return void
 */
function clear_canonical_url()
{
  sfContext::getInstance()->getResponse()->clearCanonicalUrl();
}

/**
 * Echoes canonical url (use inside layout template)
 *
 * @return void
 */
function include_canonical_url()
{
  if(!($canonicalUrl = get_canonical_url(false, true)))
  {
    return;
  }
  echo $canonicalUrl . "\n";
}

/**
 * Returns javascript configuration for application
 *
 * @param array $options
 * @param string $app
 * @return string string
 */
function get_javascript_configuration($options = array(), $app = null)
{
  $response = sfContext::getInstance()->getResponse();
  $response->setParameter('javascript_configuration_included', true, 'sift/view/asset');

  if(is_null($app))
  {
    $app = sfConfig::get('sf_app');
  }

  // default configuration
  $default = array(
    'culture' => sfContext::getInstance()->getUser()->getCulture(),
    'debug' => sfConfig::get('sf_debug'),
    'cookie_domain' => '.' . sfContext::getInstance()->getRequest()->getBaseDomain(), // cross domain cookie
    'cookie_path' => sfConfig::get('sf_relative_url_root') ? sfConfig::get('sf_relative_url_root') : '/',
    'homepage_url' => url_for('@homepage'),
    'url_suffix' => sfConfig::get('sf_suffix'),
    'ajax_timeout' => sfConfig::get('sf_ajax_timeout'),
    'timezones_enabled' => sfConfig::get('sf_timezones_enabled')
  );

  use_package('core');

  $config = array_merge($default, $options);

  // filter config thru event system
  $event = new sfEvent('core.javascript.configuration', array('app' => $app));
  sfCore::getEventDispatcher()->filter($event, $config);
  $config = $event->getReturnValue();

  return javascript_tag(sprintf('Config.add(%s);', sfJson::encode($config))) . "\n";
}

/**
 * Use jquery library
 *
 */
function use_jquery()
{
  use_package('jquery');
}

/**
 * Use Jquery UI
 *
 */
function use_jquery_ui()
{
  use_package('ui');
}

/**
 * Use asset package. Accepts more arguments
 *
 * @param $name Package name
 */
function use_package()
{
  foreach(func_get_args() as $name)
  {
    foreach(sfAssetPackage::getJavascripts($name) as $javascript)
    {
      $options = array();
      if(is_array($javascript))
      {
        $options = (array)current($javascript);
        $javascript = key($javascript);
      }
      $position = '';
      if(isset($options['position']))
      {
        $position = $options['position'];
        unset($options['position']);
      }
      use_javascript($javascript, $position, $options);
    }

    foreach(sfAssetPackage::getStylesheets($name) as $stylesheet)
    {
      $options = array();
      if(is_array($stylesheet))
      {
        $options = (array)current($stylesheet);
        $stylesheet = key($stylesheet);
      }
      $position = '';
      if(isset($options['position']))
      {
        $position = $options['position'];
        unset($options['position']);
      }
      use_stylesheet($stylesheet, $position, $options);
    }
  }
}

/**
 * User jquery plugin.
 *
 * Accepts variable number of arguments as plugin names
 *
 * @param $name Plugin name
 */
function use_jquery_plugin()
{
  use_jquery();
  foreach(func_get_args() as $name)
  {
    use_package($name);
  }
}

/**
 * Includes jquery
 *
 */
function include_jquery()
{
  return use_jquery();
}

/**
 * Use jquery validation plugin
 */
function use_jquery_validation()
{
  use_package('validation');
}

/**
 * Returns a <script> include tag for the given internal URI.
 *
 * @param  string $uri       The internal URI for the dynamic javascript
 * @param  bool   $absolute  Whether to generate an absolute URL
 * @param  array  $options   An array of options
 *
 * @return string  HTML compliant <script> tag(s)
 * @see    javascript_include_tag
 */
function dynamic_javascript_include_tag($uri, $absolute = false, $options = array())
{
  $options['raw'] = true;

  return javascript_include_tag(_dynamic_path($uri, $absolute), $options);
}

/**
 * Adds a dynamic javascript to the response object.
 *
 * The first argument is an internal URI.
 *
 * @see sfResponse->addJavascript()
 */
function use_dynamic_javascript($js, $position = '', $options = array())
{
  $options['raw'] = true;

  return use_javascript(_dynamic_path($js), $position, $options);
}

/**
 * Adds a dynamic stylesheet to the response object.
 *
 * The first argument is an internal URI.
 *
 * @see sfResponse->addStylesheet()
 */
function use_dynamic_stylesheet($css, $position = '', $options = array())
{
  $options['raw'] = true;

  return use_stylesheet(_dynamic_path($css), $position, $options);
}

/**
 * Returns url for the internal $uri
 *
 * @param string $uri Internal uri
 * @param boolean $absolute Absolute url?
 * @return string
 */
function _dynamic_path($uri, $absolute = false)
{
  return url_for($uri, $absolute);
}

/**
 * Applies text filters for $tag to given $data.
 *
 * @param string $tag
 * @param string $string
 * @return string
 */
function apply_filters($tag, $string)
{
  if(sfContext::hasInstance()
      && sfContext::getInstance()->hasService('text_filters_registry'))
  {
    return sfContext::getInstance()
            ->getService('text_filters_registry')
            ->apply($tag, $string);
  }
  return $string;
}

/**
 * Replaces constants with modifiers. Allows to use constants in paths.
 * 
 * @param string $value
 * @internal
 */
function _replace_constants($value)
{
  if(strpos($value, '%') !== false)
  {
    return sfAssetPackage::replaceVariables($value);
  }
  return $value;
}
