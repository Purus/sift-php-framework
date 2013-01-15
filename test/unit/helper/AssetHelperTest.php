<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Url', 'Asset'));

$t = new lime_test(53, new lime_output_color());

class myRequest
{
  public $relativeUrlRoot = '';

  public function getRelativeUrlRoot()
  {
    return $this->relativeUrlRoot;
  }

  public function isSecure()
  {
    return false;
  }

  public function getHost()
  {
    return 'localhost';
  }
  
  public function getMethodName()
  {
    return 'GET';
  }
}

class myController
{
  public function genUrl($parameters = null, $absolute = false)
  {
    $root = sfContext::getInstance()->getRequest()->getRelativeUrlRoot();
    
    if($root)
    {
      return sprintf('%s/%s', $root, $parameters);
    }
    
    return '/' . $parameters;
    
  }
}

class sfLessCompiler {
  
  public static function getInstance()
  {    
    return new sfLessCompiler();
  }
  
  public function compileStylesheetIfNeeded($less)
  {
    return '/cache'.$less.'.css';
  }
  
}

$context = sfContext::getInstance();
$context->controller = new myController();
$context->request = new myRequest();

// _compute_public_path()
$t->diag('_compute_public_path');
$t->is(_compute_public_path('foo', 'css', 'css'), '/css/foo.css', '_compute_public_path() converts a string to a web path');
$t->is(_compute_public_path('foo', 'css', 'css', true), 'http://localhost/css/foo.css', '_compute_public_path() can create absolute links');
$t->is(_compute_public_path('foo.css2', 'css', 'css'), '/css/foo.css2', '_compute_public_path() does not add suffix if one already exists');
$context->request->relativeUrlRoot = '/bar';
$t->is(_compute_public_path('foo', 'css', 'css'), '/bar/css/foo.css', '_compute_public_path() takes into account the relative url root configuration');
$context->request->relativeUrlRoot = '';
$t->is(_compute_public_path('foo.css?foo=bar', 'css', 'css'), '/css/foo.css?foo=bar', '_compute_public_path() takes into account query strings');
$t->is(_compute_public_path('foo?foo=bar', 'css', 'css'), '/css/foo.css?foo=bar', '_compute_public_path() takes into account query strings');

// image_tag()
$t->diag('image_tag()');
$t->is(image_tag(''), '', 'image_tag() returns nothing when called without arguments');
$t->is(image_tag('test'), '<img src="/images/test.png" alt="Test" />', 'image_tag() takes an image name as its first argument');
$t->is(image_tag('test.png'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an image name with an extension');
$t->is(image_tag('/images/test.png'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an absolute image path');
$t->is(image_tag('/images/test'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an absolute image path without extension');
$t->is(image_tag('test.jpg'), '<img src="/images/test.jpg" alt="Test" />', 'image_tag() can take an image name with an extension');
$t->is(image_tag('test', array('alt' => 'Foo')), '<img alt="Foo" src="/images/test.png" />', 'image_tag() takes an array of options as its second argument to override alt');
$t->is(image_tag('test', array('size' => '10x10')), '<img src="/images/test.png" alt="Test" height="10" width="10" />', 'image_tag() takes a size option');
$t->is(image_tag('test', array('class' => 'bar')), '<img class="bar" src="/images/test.png" alt="Test" />', 'image_tag() takes whatever option you want');

// stylesheet_tag()
$t->is(stylesheet_tag('style'), '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/style.css" />'."\n", 'stylesheet_tag() takes a stylesheet name as its first argument');

$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/random.styles" />'."\n".
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/stylish.css" />'."\n", 'stylesheet_tag() can takes n stylesheet names as its arguments');

$t->is(stylesheet_tag('style'), 
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/style.css" />'."\n", 
  'stylesheet_tag() takes a stylesheet name as its first argument');
$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/random.styles" />'."\n".
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/stylish.css" />'."\n", 
  'stylesheet_tag() can takes n stylesheet names as its arguments');
$t->is(stylesheet_tag('style', array('media' => 'all')), 
  '<link rel="stylesheet" type="text/css" media="all" href="/css/style.css" />'."\n", 
  'stylesheet_tag() can take a media option');
$t->is(stylesheet_tag('style', array('absolute' => true)), 
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="http://localhost/css/style.css" />'."\n", 
  'stylesheet_tag() can take an absolute option to output an absolute file name');
$t->is(stylesheet_tag('style', array('raw' => true)), 
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="style" />'."\n", 
  'stylesheet_tag() can take a raw option to bypass file name decoration');
$t->is(stylesheet_tag('style', array('ie_condition' => 'IE 6')),
  '<!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/css/style.css" /><![endif]-->'."\n",
  'stylesheet_tag() can take a ie_condition option');

// less support
$t->is(stylesheet_tag('style.less'), '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/cache/css/style.less.css" />'."\n", 'stylesheet_tag() accepts less files.');

// javascript_include_tag()
$t->is(javascript_include_tag('xmlhr'),
  '<script type="text/javascript" src="/js/xmlhr.js"></script>'."\n");

$t->is(javascript_include_tag('common.javascript', '/elsewhere/cools'),
  '<script type="text/javascript" src="/js/common.javascript"></script>'."\n".
  '<script type="text/javascript" src="/elsewhere/cools.js"></script>'."\n");

// asset_javascript_path()
$t->is(javascript_path('xmlhr'),
  '/js/xmlhr.js');

// asset_style_path()
$t->is(stylesheet_path('style'),
  '/css/style.css');

// asset_style_link()
$t->is(stylesheet_tag('style'),
  "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen,projection,tv\" href=\"/css/style.css\" />\n");

$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen,projection,tv\" href=\"/css/random.styles\" />\n".
  "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen,projection,tv\" href=\"/css/stylish.css\" />\n");

// asset_image_path()
$t->is(image_path('xml'), '/images/xml.png');

// asset_image_tag()
$t->is(image_tag('xml'),
  '<img src="/images/xml.png" alt="Xml" />');

$t->is(image_tag('rss', array('alt' => 'rss syndication')),
  '<img alt="rss syndication" src="/images/rss.png" />');

$t->is(image_tag('gold', array('size' => '45x70')),
  '<img src="/images/gold.png" alt="Gold" height="70" width="45" />');

//// auto_discovery_link_tag()
$t->is(auto_discovery_link_tag('rss', '@route'),       
  '<link rel="alternate" href="/@route" type="application/rss+xml" title="Rss" />');

$t->is(auto_discovery_link_tag('atom', '@route'),
  '<link rel="alternate" href="/@route" type="application/atom+xml" title="Atom" />');
 
$t->is(auto_discovery_link_tag('rss', 'feed'),
  '<link rel="alternate" href="/feed" type="application/rss+xml" title="Rss" />');

$context->request->relativeUrlRoot = '/mypath';

// auto_discovery()
$t->is(auto_discovery_link_tag('rss', 'feed'),
  '<link rel="alternate" href="/mypath/feed" type="application/rss+xml" title="Rss" />');

$t->is(auto_discovery_link_tag('atom', 'feed'),
  '<link rel="alternate" href="/mypath/feed" type="application/atom+xml" title="Atom" />');

// javascript_path()
$t->is(javascript_path('xmlhr'),
  '/mypath/js/xmlhr.js');

// javascript_include()
$t->is(javascript_include_tag('xmlhr'),
  '<script type="text/javascript" src="/mypath/js/xmlhr.js"></script>'."\n");

$t->is(javascript_include_tag('common.javascript', '/elsewhere/cools'),
  '<script type="text/javascript" src="/mypath/js/common.javascript"></script>'."\n".
  '<script type="text/javascript" src="/mypath/elsewhere/cools.js"></script>'."\n");

// style_path()
$t->is(stylesheet_path('style'),
  '/mypath/css/style.css');

// style_link()
$t->is(stylesheet_tag('style'),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/mypath/css/style.css" />'."\n");

$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/mypath/css/random.styles" />'."\n".
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/mypath/css/stylish.css" />'."\n");

// image_path()
$t->is(image_path('xml'),
  '/mypath/images/xml.png');

// image_tag()
$t->is(image_tag('xml'),
  '<img src="/mypath/images/xml.png" alt="Xml" />');

$t->is(image_tag('rss', array('alt' => 'rss syndication')),
  '<img alt="rss syndication" src="/mypath/images/rss.png" />');

$t->is(image_tag('gold', array('size' => '45x70')),
  '<img src="/mypath/images/gold.png" alt="Gold" height="70" width="45" />');

$t->is(image_tag('http://www.example.com/images/icon.gif'),
  '<img src="http://www.example.com/images/icon.gif" alt="Icon" />');

// stylesheet_with_asset_host_already_encoded()
$t->is(stylesheet_tag("http://bar.example.com/css/style.css"),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="http://bar.example.com/css/style.css" />'."\n");

$context->request->relativeUrlRoot = '';

// use_dynamic_javascript()
$t->diag('use_dynamic_javascript()');
use_dynamic_javascript('module/action');
$t->is(get_javascripts(),
  '<script type="text/javascript" src="/module/action.js"></script>'."\n",
  'use_dynamic_javascript() register a dynamic javascript in the response'
);

// use_dynamic_stylesheet()
$t->diag('use_dynamic_stylesheet()');
use_dynamic_stylesheet('module/action');
$t->is(get_stylesheets(),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/module/action" />'."\n", 
  'use_dynamic_stylesheet() register a dynamic stylesheet in the response'
);
