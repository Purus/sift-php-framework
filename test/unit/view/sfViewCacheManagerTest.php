<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(41);

function get_cache_manager($context)
{
  myCache::clear();
  $m = new myViewCacheManager($context, new myCache());

  return $m;
}

function get_cache_config($contextual = false)
{
  return array(
    'with_layout'     => false,
    'lifetime'       => 86400,
    'client_lifetime' => 86400,
    'contextual'     => $contextual,
    'vary'           => array(),
  );
}

class myViewCacheManager extends sfViewCacheManager
{
  public function registerConfiguration($moduleName)
  {
  }
}

class myController extends sfWebController
{
}

class myRequest
{
  public $getParameters = array('page' => 5, 'sort' => 'asc');

  public function getHost()
  {
    return 'localhost';
  }

  public function getScriptName()
  {
    return 'index.php';
  }

  public function getHttpHeader($headerName)
  {
    return '/foo#|#/bar/';
  }

  public function getGetParameters()
  {
    return $this->getParameters;
  }
}

class myCache extends sfCache
{
  static public $cache = array();

  public function initialize($parameters = array())
  {
  }

  public function get($key, $default = null)
  {
    return isset(self::$cache[$key]) ? self::$cache[$key] : $default;
  }

  public function has($key)
  {
    return isset(self::$cache[$key]);
  }

  public function set($key, $data, $lifetime = null)
  {
    self::$cache[$key] = $data;
  }

  public function remove($key)
  {
    unset(self::$cache[$key]);
  }

  public function removePattern($pattern, $delimiter = ':')
  {
    $pattern = '#^' . str_replace('*', '.*', $pattern) . '$#';
    foreach(self::$cache as $key => $value)
    {
      if(preg_match($pattern, $key))
      {
        unset(self::$cache[$key]);
      }
    }
  }

  public function clean($mode = sfCache::ALL)
  {
    self::$cache = array();
  }

  public function getTimeout($key)
  {
    return time() - 60;
  }

  public function getLastModified($key)
  {
    return time() - 600;
  }

  static public function clear()
  {
    self::$cache = array();
  }
}

class myRouting extends sfRouting
{
  public $currentInternalUri = 'currentModule/currentAction?currentKey=currentValue';

  public function getCurrentInternalUri($with_route_name = false)
  {
    return $this->currentInternalUri;
  }
}

class sfRouting
{
  public $currentInternalUri = 'currentModule/currentAction?currentKey=currentValue';

  protected static
    $instances         = array();

  protected
    $current_route_name = '',
    $routes             = array();

  /**
  * Retrieve the singleton instance of this class.
   *
   * @return  sfRouting The sfRouting implementation instance
   */
  public static function getInstance($name = null)
  {
    if(null == $name)
    {
      if(sfCore::hasProject() && sfCore::getProject()->hasActive())
      {
        $name = sfCore::getProject()->getActiveApplication()->getName();
      }
      else
      {
        $name = 'default';
      }
    }

    if (!isset(self::$instances[$name]))
    {
      self::$instances[$name] = new sfRouting();
    }

    return self::$instances[$name];
  }

  /**
   * Sets the current route name.
   *
   * @param string The route name
   */
  protected function setCurrentRouteName($name)
  {
    $this->current_route_name = $name;
  }

  /**
   * Gets the current route name.
   *
   * @return string The route name
   */
  public function getCurrentRouteName()
  {
    return $this->current_route_name;
  }

  /**
   * Gets the internal URI for the current request.
   *
   * @param boolean Whether to give an internal URI with the route name (@route)
   *                or with the module/action pair
   *
   * @return string The current internal URI
   */
  public function getCurrentInternalUri($with_route_name = false)
  {
    return $this->currentInternalUri;
  }

  /**
   * Gets the current compiled route array.
   *
   * @return array The route array
   */
  public function getRoutes()
  {
    return $this->routes;
  }

  /**
   * Sets the compiled route array.
   *
   * @param array The route array
   *
   * @return array The route array
   */
  public function setRoutes($routes)
  {
    return $this->routes = $routes;
  }

  /**
   * Returns true if this instance has some routes.
   *
   * @return  boolean
   */
  public function hasRoutes()
  {
    return count($this->routes) ? true : false;
  }

  /**
   * Returns true if the route name given is defined.
   *
   * @param string The route name
   *
   * @return  boolean
   */
  public function hasRouteName($name)
  {
    return isset($this->routes[$name]) ? true : false;
  }

  /**
   * Gets a route by its name.
   *
   * @param string The route name
   *
   * @return  array A route array
   */
  public function getRouteByName($name)
  {
    if ($name[0] == '@')
    {
      $name = substr($name, 1);
    }

    if (!isset($this->routes[$name]))
    {
      $error = 'The route "%s" does not exist';
      $error = sprintf($error, $name);

      throw new sfConfigurationException($error);
    }

    return $this->routes[$name];
  }

  /**
   * Clears all current routes.
   */
  public function clearRoutes()
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfRouting} clear all current routes');
    }

    $this->routes = array();
  }

  /**
   * Adds a new route at the beginning of the current list of routes.
   *
   * @see connect
   */
  public function prependRoute($name, $route, $default = array(), $requirements = array())
  {
    $routes = $this->routes;
    $this->routes = array();
    $newroutes = $this->connect($name, $route, $default, $requirements);
    $this->routes = array_merge($newroutes, $routes);

    return $this->routes;
  }

  /**
   * Adds a new route.
   *
   * Alias for the connect method.
   *
   * @see connect
   */
  public function appendRoute($name, $route, $default = array(), $requirements = array())
  {
    return $this->connect($name, $route, $default, $requirements);
  }

 /**
  * Adds a new route at the end of the current list of routes.
  *
  * A route string is a string with 2 special constructions:
  * - :string: :string denotes a named paramater (available later as $request->getParameter('string'))
  * - *: * match an indefinite number of parameters in a route
  *
  * Here is a very common rule in a Sift project:
  *
  * <code>
  * $r->connect('/:module/:action/*');
  * </code>
  *
  * @param  string The route name
  * @param  string The route string
  * @param  array  The default parameter values
  * @param  array  The regexps parameters must match
  *
  * @return array  current routes
  */
  public function connect($name, $route, $default = array(), $requirements = array())
  {
    // route already exists?
    if (isset($this->routes[$name]))
    {
      $error = 'This named route already exists ("%s").';
      $error = sprintf($error, $name);

      throw new sfConfigurationException($error);
    }

    $parsed = array();
    $names  = array();
    $suffix = (($sf_suffix = sfConfig::get('sf_suffix')) == '.') ? '' : $sf_suffix;

    // used for performance reasons
    $names_hash = array();

    $r = null;
    if (($route == '') || ($route == '/'))
    {
      $regexp = '/^[\/]*$/';
      $this->routes[$name] = array($route, $regexp, array(), array(), $default, $requirements, $suffix);
    }
    else
    {
      $elements = array();
      foreach (explode('/', $route) as $element)
      {
        if (trim($element))
        {
          $elements[] = $element;
        }
      }

      if (!isset($elements[0]))
      {
        return false;
      }

      // specific suffix for this route?
      // or /$ directory
      if (preg_match('/^(.+)(\.\w*)$/i', $elements[count($elements) - 1], $matches))
      {
        $suffix = ($matches[2] == '.') ? '' : $matches[2];
        $elements[count($elements) - 1] = $matches[1];
        $route = '/'.implode('/', $elements);
      }
      else if ($route{strlen($route) - 1} == '/')
      {
        $suffix = '/';
      }

      $regexp_suffix = preg_quote($suffix);

      foreach ($elements as $element)
      {
        if (preg_match('/^:(.+)$/', $element, $r))
        {
          $element = $r[1];

          // regex is [^\/]+ or the requirement regex
          if (isset($requirements[$element]))
          {
            $regex = $requirements[$element];
            if (0 === strpos($regex, '^'))
            {
              $regex = substr($regex, 1);
            }
            if (strlen($regex) - 1 === strpos($regex, '$'))
            {
              $regex = substr($regex, 0, -1);
            }
          }
          else
          {
            $regex = '[^\/]+';
          }

          $parsed[] = '(?:\/('.$regex.'))?';
          $names[] = $element;
          $names_hash[$element] = 1;
        }
        elseif (preg_match('/^\*$/', $element, $r))
        {
          $parsed[] = '(?:\/(.*))?';
        }
        else
        {
          $parsed[] = '/'.$element;
        }
      }
      $regexp = '#^'.join('', $parsed).$regexp_suffix.'$#';

      $this->routes[$name] = array($route, $regexp, $names, $names_hash, $default, $requirements, $suffix);
    }

    return $this->routes;
  }

 /**
  * Generates a valid URLs for parameters.
  *
  * @param  array  The parameter values
  * @param  string The divider between key/value pairs
  * @param  string The equal sign to use between key and value
  *
  * @return string The generated URL
  */
  public function generate($name, $params, $querydiv = '/', $divider = '/', $equals = '/')
  {
    $global_defaults = sfConfig::get('sf_routing_defaults', null);

    // named route?
    if ($name)
    {
      if (!isset($this->routes[$name]))
      {
        $error = 'The route "%s" does not exist.';
        $error = sprintf($error, $name);

        throw new sfConfigurationException($error);
      }

      list($url, $regexp, $names, $names_hash, $defaults, $requirements, $suffix) = $this->routes[$name];
      if ($global_defaults !== null)
      {
        $defaults = array_merge($defaults, $global_defaults);
      }

      // all params must be given
      foreach ($names as $tmp)
      {
        if (!isset($params[$tmp]) && !isset($defaults[$tmp]))
        {
          throw new sfException(sprintf('Route named "%s" have a mandatory "%s" parameter', $name, $tmp));
        }
      }
    }
    else
    {
      // find a matching route
      $found = false;
      foreach ($this->routes as $name => $route)
      {
        list($url, $regexp, $names, $names_hash, $defaults, $requirements, $suffix) = $route;
        if ($global_defaults !== null)
        {
          $defaults = array_merge($defaults, $global_defaults);
        }

        $tparams = array_merge($defaults, $params);

        // we must match all names (all $names keys must be in $params array)
        foreach ($names as $key)
        {
          if (!isset($tparams[$key])) continue 2;
        }

        // we must match all defaults with value except if present in names
        foreach ($defaults as $key => $value)
        {
          if (isset($names_hash[$key])) continue;

          if (!isset($tparams[$key]) || $tparams[$key] != $value) continue 2;
        }

        // we must match all requirements for rule
        foreach ($requirements as $req_param => $req_regexp)
        {
          if (!preg_match('/'.str_replace('/', '\\/', $req_regexp).'/', $tparams[$req_param]))
          {
            continue 2;
          }
        }

        // we must have consumed all $params keys if there is no * in route
        if (!strpos($url, '*'))
        {
          if (count(array_diff(array_keys($tparams), $names, array_keys($defaults))))
          {
            continue;
          }
        }

        // match found
        $found = true;
        break;
      }

      if (!$found)
      {
        $error = 'Unable to find a matching routing rule to generate url for params "%s".';
        $error = sprintf($error, var_export($params, true));

        throw new sfConfigurationException($error);
      }
    }

    $params = sfToolkit::arrayDeepMerge($defaults, $params);

    $real_url = preg_replace('/\:([^\/]+)/e', 'urlencode($params["\\1"])', $url);

    // we add all other params if *
    if (strpos($real_url, '*'))
    {
      $tmp = array();
      foreach ($params as $key => $value)
      {
        if (isset($names_hash[$key]) || isset($defaults[$key])) continue;

        if (is_array($value))
        {
          foreach ($value as $v)
          {
            $tmp[] = $key.$equals.urlencode($v);
          }
        }
        else
        {
          $tmp[] = urlencode($key).$equals.urlencode($value);
        }
      }
      $tmp = implode($divider, $tmp);
      if (strlen($tmp) > 0)
      {
        $tmp = $querydiv.$tmp;
      }
      $real_url = preg_replace('/\/\*(\/|$)/', "$tmp$1", $real_url);
    }

    // strip off last divider character
    if (strlen($real_url) > 1)
    {
      $real_url = rtrim($real_url, $divider);
    }

    if ($real_url != '/')
    {
      $real_url .= $suffix;
    }

    return $real_url;
  }

 /**
  * Parses a URL to find a matching route.
  *
  * Returns null if no route match the URL.
  *
  * @param  string URL to be parsed
  *
  * @return array  An array of parameters
  */
  public function parse($url)
  {
    // an URL should start with a '/', mod_rewrite doesn't respect that, but no-mod_rewrite version does.
    if ($url && ('/' != $url[0]))
    {
      $url = '/'.$url;
    }

    // we remove the query string
    if ($pos = strpos($url, '?'))
    {
      $url = substr($url, 0, $pos);
    }

    // we remove multiple /
    $url = preg_replace('#/+#', '/', $url);
    foreach ($this->routes as $route_name => $route)
    {
      $out = array();
      $r = null;

      list($route, $regexp, $names, $names_hash, $defaults, $requirements, $suffix) = $route;

      $break = false;

      if (preg_match($regexp, $url, $r))
      {
        $break = true;

        // remove the first element, which is the url
        array_shift($r);

        // hack, pre-fill the default route names
        foreach ($names as $name)
        {
          $out[$name] = null;
        }

        // defaults
        foreach ($defaults as $name => $value)
        {
          if (preg_match('#[a-z_\-]#i', $name))
          {
            $out[$name] = urldecode($value);
          }
          else
          {
            $out[$value] = true;
          }
        }

        $pos = 0;
        foreach ($r as $found)
        {
          // if $found is a named url element (i.e. ':action')
          if (isset($names[$pos]))
          {
            $out[$names[$pos]] = urldecode($found);
          }
          // unnamed elements go in as 'pass'
          else
          {
            $pass = explode('/', $found);
            $found = '';
            for ($i = 0, $max = count($pass); $i < $max; $i += 2)
            {
              if (!isset($pass[$i + 1])) continue;

              $found .= $pass[$i].'='.$pass[$i + 1].'&';
            }

            parse_str($found, $pass);

            if (get_magic_quotes_gpc())
            {
              $pass = sfToolkit::stripslashesDeep((array) $pass);
            }

            foreach ($pass as $key => $value)
            {
              // we add this parameters if not in conflict with named url element (i.e. ':action')
              if (!isset($names_hash[$key]))
              {
                $out[$key] = $value;
              }
            }
          }
          $pos++;
        }

        // we must have found all :var stuffs in url? except if default values exists
        foreach ($names as $name)
        {
          if ($out[$name] == null)
          {
            $break = false;
          }
        }

        if ($break)
        {
          // we store route name
          $this->setCurrentRouteName($route_name);

          if (sfConfig::get('sf_logging_enabled'))
          {
            sfLogger::getInstance()->info('{sfRouting} match route ['.$route_name.'] "'.$route.'"');
          }

          break;
        }
      }
    }

    // no route found
    if (!$break)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->info(sprintf('{sfRouting} no matching route found for "%s"', $url));
      }

      return null;
    }

    return $out;
  }
}


$context = sfContext::getInstance();
$context->controller = new myController($context);
$context->request = new myRequest();

$r = sfRouting::getInstance();
$r->connect('default', '/:module/:action/*');

// ->initialize()
$t->diag('->initialize()');
$m = new myViewCacheManager($context, $cache = new myCache());
$t->is($m->getCache(), $cache, '->initialize() takes a sfCache object as its second argument');

// ->generateCacheKey()
$t->diag('->generateCacheKey');
$t->is($m->generateCacheKey('mymodule/myaction'), '/localhost/all/mymodule/myaction', '->generateCacheKey() creates a simple cache key from an internal URI');
$t->is($m->generateCacheKey('mymodule/myaction', 'foo'), '/foo/all/mymodule/myaction', '->generateCacheKey() can take a hostName as second parameter');
$t->is($m->generateCacheKey('mymodule/myaction', null, 'bar'), '/localhost/bar/mymodule/myaction', '->generateCacheKey() can take a serialized set of vary headers as third parameter');

$t->is($m->generateCacheKey('mymodule/myaction?key1=value1&key2=value2'), '/localhost/all/mymodule/myaction/key1/value1/key2/value2', '->generateCacheKey() includes request parameters as key/value pairs');
$t->is($m->generateCacheKey('mymodule/myaction?akey=value1&ckey=value2&bkey=value3'), '/localhost/all/mymodule/myaction/akey/value1/bkey/value3/ckey/value2', '->generateCacheKey() reorders request parameters alphabetically');

try
{
  $m->generateCacheKey('@rule?key=value');
  $t->fail('->generateCacheKey() throws an sfException when passed an internal URI with a rule');
}
catch(sfException $e)
{
  $t->pass('->generateCacheKey() throws an sfException when passed an internal URI with a rule');
}
try
{
  $m->generateCacheKey('@sf_cache_partial?module=mymodule&action=myaction');
  $t->pass('->generateCacheKey() does not throw an sfException when passed an internal URI with a @sf_cache_partial rule');
}
catch(sfException $e)
{
  $t->fail('->generateCacheKey() does not throw an sfException when passed an internal URI with a @sf_cache_partial rule');
}
try
{
  $m->generateCacheKey('@sf_cache_partial?key=value');
  $t->fail('->generateCacheKey() throws an sfException when passed an internal URI with a @sf_cache_partial rule with no module or action param');
}
catch(sfException $e)
{
  $t->pass('->generateCacheKey() throws an sfException when passed an internal URI with a @sf_cache_partial rule with no module or action param');
}

$t->is($m->generateCacheKey('@sf_cache_partial?module=foo&action=bar&sf_cache_key=value'), '/localhost/all/sf_cache_partial/foo/bar/sf_cache_key/value', '->generateCacheKey() can deal with internal URIs to partials');

$m = get_cache_manager($context);
$m->addCache('foo', 'bar', get_cache_config(true));
$t->is($m->generateCacheKey('@sf_cache_partial?module=foo&action=bar&sf_cache_key=value'), '/localhost/all/currentModule/currentAction/currentKey/currentValue/foo/bar/value', '->generateCacheKey() can deal with internal URIs to contextual partials');

$t->is($m->generateCacheKey('@sf_cache_partial?module=foo&action=bar&sf_cache_key=value', null, null, 'baz'), '/localhost/all/baz/foo/bar/value', '->generateCacheKey() can take a prefix for contextual partials as fourth parameter');

$m = get_cache_manager($context);
$m->addCache('module', 'action', array('vary' => array('myheader', 'secondheader')));
$t->is($m->generateCacheKey('module/action'), '/localhost/myheader-_foo_bar_-secondheader-_foo_bar_/module/action', '->generateCacheKey() creates a directory friendly vary cache key');

// ->generateNamespace()
$t->diag('->generateNamespace()');
$m = get_cache_manager($context);

// ->addCache()
$t->diag('->addCache()');
$m = get_cache_manager($context);
$m->set('test', 'module/action');
$t->is($m->has('module/action'), false, '->addCache() register a cache configuration for an action');

$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action');
$t->is($m->get('module/action'), 'test', '->addCache() register a cache configuration for an action');

// ->set()
$t->diag('->set()');
$m = get_cache_manager($context);
$t->is($m->set('test', 'module/action'), false, '->set() returns false if the action is not cacheable');
$m->addCache('module', 'action', get_cache_config());
$t->is($m->set('test', 'module/action'), true, '->set() returns true if the action is cacheable');

$m = get_cache_manager($context);
$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action');
$t->is($m->get('module/action'), 'test', '->set() stores the first parameter in a key computed from the second parameter');

$m = get_cache_manager($context);
$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action?key1=value1');
$t->is($m->get('module/action?key1=value1'), 'test', '->set() works with URIs with parameters');
$t->is($m->get('module/action?key2=value2'), null, '->set() stores a different version for each set of parameters');
$t->is($m->get('module/action'), null, '->set() stores a different version for each set of parameters');

$m = get_cache_manager($context);
$m->addCache('module', 'action', get_cache_config());
$m->set('test', '@sf_cache_partial?module=module&action=action');
$t->is($m->get('@sf_cache_partial?module=module&action=action'), 'test', '->set() accepts keys to partials');

$m = get_cache_manager($context);
$m->addCache('module', 'action', get_cache_config(true));
$m->set('test', '@sf_cache_partial?module=module&action=action');
$t->is($m->get('@sf_cache_partial?module=module&action=action'), 'test', '->set() accepts keys to contextual partials');

// ->get()
$t->diag('->get()');
$m = get_cache_manager($context);
$t->is($m->get('module/action'), null, '->get() returns null if the action is not cacheable');
$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action');
$t->is($m->get('module/action'), 'test', '->get() returns the saved content if the action is cacheable');

// ->has()
$t->diag('->has()');
$m = get_cache_manager($context);
$t->is($m->has('module/action'), false, '->has() returns false if the action is not cacheable');
$m->addCache('module', 'action', get_cache_config());
$t->is($m->has('module/action'), false, '->has() returns the cache does not exist for the action');
$m->set('test', 'module/action');
$t->is($m->has('module/action'), true, '->get() returns true if the action is in cache');

// ->remove()
$t->diag('->remove()');
$m = get_cache_manager($context);
$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action');
$m->remove('module/action');
$t->is($m->has('module/action'), false, '->remove() removes cache content for an action');

$m->set('test', 'module/action?key1=value1');
$m->set('test', 'module/action?key2=value2');
$m->remove('module/action?key1=value1');
$t->is($m->has('module/action?key1=value1'), false, '->remove() removes accepts an internal URI as first parameter');
$t->is($m->has('module/action?key2=value2'), true, '->remove() does not remove cache content for keys not matching the internal URI');

$m = get_cache_manager($context);
$m->addCache('module', 'action', get_cache_config());
$m->set('test', 'module/action?key1=value1');
$m->set('test', 'module/action?key1=value2');
$m->set('test', 'module/action?key2=value1');
$m->remove('module/action?key1=*');
$t->is($m->has('module/action?key1=value1'), false, '->remove() accepts wildcards in URIs and then removes all keys matching the pattern');
$t->is($m->has('module/action?key1=value2'), false, '->remove() accepts wildcards in URIs and then removes all keys matching the pattern');
$t->is($m->has('module/action?key2=value1'), true, '->remove() accepts wildcards in URIs and lets keys not matching the pattern unchanged');

$t->diag('Cache key generation options');
$m = new myViewCacheManager($context, $cache = new myCache(), array('cache_key_use_vary_headers' => false));
$t->is($m->generateCacheKey('mymodule/myaction'), '/localhost/mymodule/myaction', '->generateCacheKey() uses "cache_key_use_vary_headers" option to know if vary headers changes cache key.');

$m = new myViewCacheManager($context, $cache = new myCache(), array('cache_key_use_host_name' => false));
$t->is($m->generateCacheKey('mymodule/myaction'), '/all/mymodule/myaction', '->generateCacheKey() uses "cache_key_use_host_name" option to know if vary headers changes cache key.');

$m = new myViewCacheManager($context, $cache = new myCache(), array('cache_key_use_host_name' => false, 'cache_key_use_vary_headers' => false));
$t->is($m->generateCacheKey('mymodule/myaction'), '/mymodule/myaction', '->generateCacheKey() allows the use of both "cache_key_use_host_name" and "cache_key_use_vary_headers" options.');

$m = new myViewCacheManager($context, new myCache());
$t->is($m->generateCacheKey('mymodule/myaction?foo=../_bar'), '/localhost/all/mymodule/myaction/foo/_../__bar', '->generateCacheKey() prevents directory traversal');
$t->is($m->generateCacheKey('mymodule/myaction?foo=..\\_bar'), '/localhost/all/mymodule/myaction/foo/_..\\__bar', '->generateCacheKey() prevents directory traversal');

// ->getCurrentCacheKey()
$t->diag('->getCurrentCacheKey()');
$m = get_cache_manager($context);
$t->is($m->getCurrentCacheKey(), 'currentModule/currentAction?currentKey=currentValue&page=5&sort=asc', '->getCurrentCacheKey() appends GET parameters to an existing query string');
$r->currentInternalUri = 'currentModule/currentAction';
$t->is($m->getCurrentCacheKey(), 'currentModule/currentAction?page=5&sort=asc', '->getCurrentCacheKey() adds a query string of GET parameters if none is there');
