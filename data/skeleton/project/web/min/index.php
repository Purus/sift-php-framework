<?php
/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This is a CSS and Javascript combinator utility script. Based on the combinator
 * by Niels Leenheer.
 *
 * Server should be configured to rewrite the urls of javascript and css files:
 *
 * @code
 *   # minifier, currently only supports f=/,
 *   RewriteRule ^min/([0-9]+)/([a-z]=.*) /min/index.php?$2&v=$1 [L,NE]
 *
 * @link http://rakaz.nl/code/combine
 * @version 1.0
 */

// turn off reporting
error_reporting(0);
ini_set('display_errors', 'Off');

// configurable settings
$cacheEnabled = true;

// driver configuration
// extensions to driver name
$minifierDriver = array(
  'js' => 'JsSimple',
  'css' => 'CssSimple'
);

$minifierOptions = array(
  'js' => array(),
  'css' => array()
);

// allowed extension without dot
$allowedExtensions = array(
  'js', 'css'
);

// extensions to mime map
$mimeMap = array(
  'js' => 'text/javascript',
  'css' => 'text/css',
);

// web root
$webRootDir = realpath(dirname(__FILE__) . '/../');
// cache directory
$cachedir = dirname(__FILE__) . '/../cache/minify';

// Determine the directory and type we should use
if(!isset($_GET['f']))
{
  header('HTTP/1.0 503 Not Implemented');
  echo '/* Repent and turn to Jesus! */';
  exit;
};

// version
$v = isset($_GET['v']) ? intval($_GET['v']) : 1;

$elements = explode(',', $_GET['f']);

// Determine last modification date of the files
$lastmodified = 0;
while(list(, $element) = each($elements))
{
  $base = $webRootDir;
  $path = realpath($base . '/' . $element);
  $basename = basename($element);

  $extension = '';
  // finds the last occurence of .
  if(($pos = strrpos($basename, '.')) !== false)
  {
    $extension = substr($basename, $pos + 1);
  }

  // security check
  if(!in_array($extension, $allowedExtensions))
  {
    echo '/* Repent and turn to Jesus! */';
    header('HTTP/1.0 403 Forbidden');
    exit;
  }

  if(substr($path, 0, strlen($base)) != $base || !file_exists($path))
  {
    header('HTTP/1.0 404 Not Found');
    exit;
  }

  $lastmodified = max($lastmodified, filemtime($path));

  $type = $extension;
}

// Send Etag hash
$hash = $lastmodified . '-' . md5($_GET['f'] . $v);

header('Vary: Accept-Encoding');
header(sprintf('Etag: "%s"', $hash));

if(isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
        stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"')
{
  // Return visit and no modifications, so do not send anything
  header('HTTP/1.0 304 Not Modified');
  header('Content-Length: 0');
}
else
{
  // First time visit or files were modified
  if($cacheEnabled)
  {
    // Determine supported compression method
    $gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
    $deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

    // Determine used compression method
    $encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

    // Check for buggy versions of Internet Explorer
    if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') &&
            preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
      $version = floatval($matches[1]);
      if ($version < 6) {
        $encoding = 'none';
      }

      if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) {
        $encoding = 'none';
      }
    }

    $mimeType = $mimeMap[$type];
    // Try the cache first to see if the combined files were already generated
    $cachefile = $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');

    if(file_exists($cachedir . '/' . $cachefile))
    {
      if($fp = fopen($cachedir . '/' . $cachefile, 'rb'))
      {
        if($encoding != 'none')
        {
          header(sprintf('Content-Encoding: %s', $encoding));
        }

        header(sprintf('Content-Type: %s', $mimeType));
        header(sprintf('Content-Length: %s', filesize($cachedir . '/' . $cachefile)));
        fpassthru($fp);
        fclose($fp);
        exit;
      }
    }
  }

  // Load Sift lib and data dirs definition
  require_once $webRootDir . '/../config/config.php';
  require_once $sf_sift_lib_dir . '/autoload/sfCoreAutoload.class.php';
  // register core classes
  sfCoreAutoload::register();

  // create minifier instance for this type
  $minifier = sfMinifier::factory($minifierDriver[$type], $minifierOptions[$type]);

  // Get contents of the files
  $contents = array();
  reset($elements);
  while(list(, $element) = each($elements))
  {
    $path = realpath($base . '/' . $element);

    // check if its worth the work
    // is this minified version?
    if(strpos($element, '.min.js') !== false
      || strpos($element, '.minified.js') !== false)
    {
      $contents[] = file_get_contents($path);
    }
    else
    {
      $contents[] = $minifier->processFile($path);
    }
  }

  $contents = join("\n", $contents);

  // Send Content-Type
  header(sprintf('Content-Type: %s', $mimeType));

  if(isset($encoding) && $encoding != 'none')
  {
    // Send compressed contents
    $contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
    header(sprintf('Content-Encoding: %s', $encoding));
    header(sprintf('Content-Length: %s', strlen($contents)));
  }
  else
  {
    // Send regular contents
    header(sprintf('Content-Length: %s', strlen($contents)));
  }

  echo $contents;

  // Store cache
  if($cacheEnabled)
  {
    if($fp = fopen($cachedir . '/' . $cachefile, 'wb'))
    {
      // lock the file
      flock($fp, LOCK_EX);
      fwrite($fp, $contents);
      // release the lock
      flock($fp, LOCK_UN);
      fclose($fp);
    }
  }
}
