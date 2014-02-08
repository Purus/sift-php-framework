<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('http_build_url')) {
    define('HTTP_URL_REPLACE', 1);          // Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);        // Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);       // Join query strings
    define('HTTP_URL_STRIP_USER', 8);       // Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);      // Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);      // Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);      // Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);     // Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);    // Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512);	// Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host
}

// shortcut
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/**
 * Core class
 *
 * @package    Sift
 * @subpackage core
 */
class sfCore
{
  /**
   * Framework version
   */
  const VERSION = '1.1.1';

  /**
   * Shorthand for carrige return.
   */
  const CR = "\r";

  /**
   * Shorthand for directory separator.
   */
  const DS = DIRECTORY_SEPARATOR;

  /**
   * Shorthand for line feed.
   */
  const LF = "\n";

  /**
   * Shorthand for path separator.
   */
  const PS = PATH_SEPARATOR;

  /**
   *
   * @var boolean
   */
  protected static $bootstrapped = false;

  /**
   * Project instance holder
   *
   * @var sfProject
   */
  protected static $project = null;

  /**
   * Returns an instance of given application
   *
   * @param string $application Application name
   * @param string $environment Environment
   * @param boolean $debug Turn on debug feature?
   * @param boolean $forceReload Force the reloading of the app?
   * @return sfApplication
   * @throws RuntimeException If Sift has not been boostrapped yet
   */
  public static function getApplication($application, $environment, $debug = false, $forceReload = false)
  {
    return self::getProject()->getApplication($application, $environment, $debug, $forceReload);
  }

  /**
   * Returns project instance
   *
   * @return sfProject
   * @throws RuntimeException
   */
  public static function getProject()
  {
    if (!self::hasProject()) {
      throw new RuntimeException('Sift it not bootstrapped to an existing project');
    }

    return self::$project;
  }

  /**
   * Is Sift bounded to an existing project?
   *
   * @return boolean
   */
  public static function hasProject()
  {
    return isset(self::$project);
  }

  /**
   * Is bootstapped to the project?
   *
   * @return boolean
   */
  public static function isBootstrapped()
  {
    return self::$bootstrapped;
  }

  /**
   * Binds a project
   *
   * @param sfProject $project
   * @return boolean
   */
  public static function bindProject(sfProject $project)
  {
    self::$project = $project;

    return true;
  }

  /**
   * Setups (X)html generation for sfHtml and sfWidget classes
   * based on sf_html5 setting
   *
   * @see sfProject
   */
  public static function initHtmlTagConfiguration()
  {
    return self::getProject()->initHtmlTagConfiguration();
  }

  /**
   * Returns event dispatcher
   *
   * @return sfEventDispatcher
   */
  public static function getEventDispatcher()
  {
    return self::getProject()->getActiveApplication()->getEventDispatcher();
  }

  /**
   * Bootstraps the framework
   *
   * @param string $sf_sift_lib_dir
   * @param string $sf_sift_data_dir
   * @param boolean $test Should be bootstrapped in test mode?
   */
  public static function bootstrap($sf_sift_lib_dir, $sf_sift_data_dir, $test = false)
  {
    if (self::$bootstrapped) {
      return;
    }

    if (!defined('SF_ROOT_DIR')) {
      throw new sfException('Root directory is not defined. Define SF_ROOT_DIR constant.');
    }

    $config = array(
      'sf_root_dir'       => SF_ROOT_DIR,
      'sf_sift_lib_dir'   => $sf_sift_lib_dir,
      'sf_sift_data_dir'  => $sf_sift_data_dir,
      'sf_test'           => $test,
    );

    $projectFile = SF_ROOT_DIR . '/lib/myProject.class.php';

    if (is_readable($projectFile)) {
      require_once $projectFile;
      $class = 'myProject';
    } else {
      $class = 'sfGenericProject';
    }

    self::bindProject(new $class($config));

    self::$bootstrapped = true;
  }

  /**
   * Displays error page
   *
   * @param string $error The error page
   * @param string $format The format
   */
  public static function displayErrorPage($error = 'error500', $format = 'html')
  {
    return self::getProject()->getActiveApplication()->displayErrorPage($error, $format);
  }

  /**
   * Filters variable using event dispatcher. Dispatches
   * an event with given name and parameters passed.
   * Returns modified (is any listener touched it) value
   *
   * @param mixed $value
   * @param string $eventName
   * @param array $params Params for the event
   */
  public static function filterByEventListeners(&$value, $eventName, $params = array())
  {
    return self::getProject()->getActiveApplication()->filterByEventListeners($value, $eventName, $params);
  }

  /**
   * Dispatches an event using the event system
   *
   * @param string $name event_namespace.event_name
   * @param array $data associative array of data
   */
  public static function dispatchEvent($name, $data = array())
  {
    return self::getProject()->getActiveApplication()->dispatchEvent($name, $data);
  }

  /**
   * Compare the specified version string $version with the current version
   *
   * @param  string  $version  A version string (e.g. "0.7.1").
   * @return boolean           -1 if the $version is older,
   *                           0 if they are the same,
   *                           and +1 if $version is newer.
   */
  public static function compareVersion($version)
  {
    return version_compare($version, self::getVersion());
  }

  /**
   * Returns core helpers array ('Helper', 'Url', 'Asset', 'Tag', 'Escaping')
   *
   * @return array
   */
  public static function getCoreHelpers()
  {
    return self::getProject()->getActiveApplication()->getCoreHelpers();
  }

  /**
   * Returns current Sift version
   *
   * @return string
   */
  public static function getVersion()
  {
    return self::VERSION;
  }

}
