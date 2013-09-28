<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Url', 'Asset', 'Form'));

$t = new lime_test(73, new lime_output_color());

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


class MyForm extends sfForm
{
  public function getStylesheets()
  {
    return array(
 		      '/path/to/a/foo.css',                                             // use default "screen" media
 		      '/path/to/a/bar.css' => 'print',                                  // media set a value
 		      '/path/to/a/buz.css' => array('position' => 'last'), // position set as an option
 		      '/path/to/a/baz.css' => array('media' => 'print'),                // options support
 	   );
  }

  public function getJavaScripts()
  {
    return array(
 		      '/path/to/a/foo.js',                                             // doesn't use any option
 		      '/path/to/a/bar.js' => array('position' => 'last'), // position set as an option
 	        '/path/to/a/baz.js' => array('ie_condition' => 'IE'),               // options support
    );
  }
}

// get_javascripts_for_form() get_stylesheets_for_form()
$t->diag('get_javascripts_for_form() get_stylesheets_for_form()');
$form = new MyForm();
$output = <<<EOF
<script type="text/javascript" src="/path/to/a/foo.js"></script>
<script type="text/javascript" src="/path/to/a/bar.js"></script>
<!--[if IE]><script type="text/javascript" src="/path/to/a/baz.js"></script><![endif]-->

EOF;
$t->is(get_javascripts_for_form($form), fix_linebreaks($output), 'get_javascripts_for_form() returns script tags');
$output = <<<EOF
<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/path/to/a/foo.css" />
<link rel="stylesheet" type="text/css" media="print" href="/path/to/a/bar.css" />
<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/path/to/a/buz.css" />
<link rel="stylesheet" type="text/css" media="print" href="/path/to/a/baz.css" />

EOF;
$t->is(get_stylesheets_for_form($form), fix_linebreaks($output), 'get_stylesheets_for_form() returns link tags');

// use_javascripts_for_form() use_stylesheets_for_form()
$t->diag('use_javascripts_for_form() use_stylesheets_for_form()');

$response = sfContext::getInstance()->getResponse();
$form = new MyForm();

$response->resetAssets();
use_stylesheets_for_form($form);

$t->is_deeply(
	  $response->getAllStylesheets(),
	  array(
	    '/path/to/a/foo.css' => array('media' => 'screen,projection,tv'),
	    '/path/to/a/bar.css' => array('media' => 'print'),
	    '/path/to/a/baz.css' => array('media' => 'print'),
	    '/path/to/a/buz.css' => array('media' => 'screen,projection,tv'),
	  ),
	  'use_stylesheets_for_form() adds stylesheets to the response'
);

$response->resetAssets();
use_javascripts_for_form($form);
$t->is_deeply(
 	  $response->getAllJavaScripts(),
 	  array(
 	    '/path/to/a/foo.js' => array(),
 	    '/path/to/a/bar.js' => array(),
 	    '/path/to/a/baz.js' => array('ie_condition' => 'IE'),
 	  ),
 	  'use_javascripts_for_form() adds javascripts to the response'
 	);

// custom web paths
$t->diag('Custom asset path handling');

sfConfig::set('sf_web_js_dir_name', 'static/js');
$t->is(javascript_path('xmlhr'), '/static/js/xmlhr.js', 'javascript_path() decorates a relative filename with js dir name and extension with custom js dir');
$t->is(javascript_include_tag('xmlhr'),
  '<script type="text/javascript" src="/static/js/xmlhr.js"></script>'."\n",
  'javascript_include_tag() takes a javascript name as its first argument');

sfConfig::set('sf_web_css_dir_name', 'static/css');
$t->is(stylesheet_path('style'), '/static/css/style.css', 'stylesheet_path() decorates a relative filename with css dir name and extension with custom css dir');
$t->is(stylesheet_tag('style'),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/static/css/style.css" />'."\n",
  'stylesheet_tag() takes a stylesheet name as its first argument');

sfConfig::set('sf_web_images_dir_name', 'static/img');
$t->is(image_path('img'), '/static/img/img.png', 'image_path() decorates a relative filename with images dir name and png extension with custom images dir');
$t->is(image_tag('test'), '<img src="/static/img/test.png" alt="Test" />', 'image_tag() takes an image name as its first argument');

sfConfig::clear();

$t->diag('replacing constants');
sfConfig::set('my_constant', '/just-an-alias');
$t->is(stylesheet_path('%MY_CONSTANT%/foo'), '/just-an-alias/foo.css', 'stylesheet_path() works ok with constants');
$t->is(javascript_path('%MY_CONSTANT%/foo'), '/just-an-alias/foo.js', 'javascript_path() works ok with constants');
$t->is(image_path('%MY_CONSTANT%/foo.png'), '/just-an-alias/foo.png', 'image_path() works ok with constants');

$t->is(stylesheet_tag('%MY_CONSTANT%/foo'),
  '<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/just-an-alias/foo.css" />'."\n",
  'stylesheet_tag() works with constants');

$t->is(javascript_include_tag('%MY_CONSTANT%/foo'),
  '<script type="text/javascript" src="/just-an-alias/foo.js"></script>'."\n",
  'javascript_include_tag() worsk with constants');

$t->is(image_tag('%MY_CONSTANT%/test'), '<img src="/just-an-alias/test.png" alt="Test" />', 'image_tag() works with constants');

class MySecondForm extends sfForm
{
  public function getStylesheets()
  {
    return array(
 		      '%MY_CONSTANT%/path/to/a/foo.css', // use default "screen" media
 		      '%MY_CONSTANT%/path/to/a/bar.css' => 'print',                                  // media set a value
 		      '%MY_CONSTANT%/path/to/a/buz.css' => array('position' => 'last'), // position set as an option
 		      '%MY_CONSTANT%/path/to/a/baz.css' => array('media' => 'print'),                // options support
 	   );
  }

  public function getJavaScripts()
  {
    return array(
 		      '%MY_CONSTANT%/path/to/a/foo.js',                                             // doesn't use any option
 		      '%MY_CONSTANT%/path/to/a/bar.js' => array('position' => 'last'), // position set as an option
 	        '%MY_CONSTANT%/path/to/a/baz.js' => array('ie_condition' => 'IE'),               // options support
    );
  }
}

$form = new MySecondForm();

$output = <<<EOF
<script type="text/javascript" src="/just-an-alias/path/to/a/foo.js"></script>
<script type="text/javascript" src="/just-an-alias/path/to/a/bar.js"></script>
<!--[if IE]><script type="text/javascript" src="/just-an-alias/path/to/a/baz.js"></script><![endif]-->

EOF;
$t->is(get_javascripts_for_form($form), fix_linebreaks($output), 'get_javascripts_for_form() works ok with constants');
$output = <<<EOF
<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/just-an-alias/path/to/a/foo.css" />
<link rel="stylesheet" type="text/css" media="print" href="/just-an-alias/path/to/a/bar.css" />
<link rel="stylesheet" type="text/css" media="screen,projection,tv" href="/just-an-alias/path/to/a/buz.css" />
<link rel="stylesheet" type="text/css" media="print" href="/just-an-alias/path/to/a/baz.css" />

EOF;

$t->is(get_stylesheets_for_form($form), fix_linebreaks($output), 'get_stylesheets_for_form() works ok with constants');

$response->resetAssets();
use_stylesheets_for_form($form);

$t->is_deeply(
	  $response->getAllStylesheets(),
	  array(
	    '%MY_CONSTANT%/path/to/a/foo.css' => array('media' => 'screen,projection,tv'),
	    '%MY_CONSTANT%/path/to/a/bar.css' => array('media' => 'print'),
	    '%MY_CONSTANT%/path/to/a/baz.css' => array('media' => 'print'),
	    '%MY_CONSTANT%/path/to/a/buz.css' => array('media' => 'screen,projection,tv'),
	  ),
	  'use_stylesheets_for_form() works ok with constants'
);

$response->resetAssets();
use_javascripts_for_form($form);
$t->is_deeply(
 	  $response->getAllJavaScripts(),
 	  array(
 	    '%MY_CONSTANT%/path/to/a/foo.js' => array(),
 	    '%MY_CONSTANT%/path/to/a/bar.js' => array(),
 	    '%MY_CONSTANT%/path/to/a/baz.js' => array('ie_condition' => 'IE'),
 	  ),
 	  'use_javascripts_for_form() works ok with constants'
 	);

