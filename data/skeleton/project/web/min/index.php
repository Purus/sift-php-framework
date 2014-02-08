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
 * @link    http://rakaz.nl/code/combine
 * @version 1.0
 */

// turn off reporting
// Prevent hack attempts
error_reporting(0);
ini_set('display_errors', 'Off');

if (!isset($_GET['f'])) {
    header('HTTP/1.0 503 Not Implemented');
    echo '/* Repent and turn to Jesus! */';
    exit;
};

// load configuration
$config = require_once dirname(__FILE__) . '/config.php';

// cache configuration
$cacheEnabled = $config['cache_enabled'];
// cache directory
$cachedir = $config['cache_dir'];
$clientCacheTime = $config['client_cache_time'];
// driver configuration
$minifierDriverMap = $config['minifier_driver_map'];
// web root
$webRootDir = $config['web_root_dir'];
// path aliases
$aliases = $config['path_aliases'];
// allowed extension without dot
$allowedExtensions = $config['allowed_extensions'];
// extensions to mime map
$mimeMap = $config['mime_map'];

// version
$v = isset($_GET['v']) ? intval($_GET['v']) : 1;
$elements = explode(',', $_GET['f']);

$type = false;
// collection of files to load
$files = array();
// Determine last modification date of the files
$lastmodified = 0;
while (list(, $element) = each($elements)) {
    $base = false;
    foreach ($aliases as $alias => $path) {
        // is this a file from sift directory?
        if (preg_match('#^' . preg_quote($alias, '#') . '#', $element)) {
            $base = $path;
            break;
        }
    }

    if (!$base) {
        $base = $webRootDir;
    }

    $path = realpath($base . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $element), '/'));

    if (!$path || !file_exists($path)) {
        header('HTTP/1.0 404 Not Found');
        // Header for FastCGI
        header('Status: 404 Not Found');
        exit;
    }

    $basename = basename($element);

    $extension = '';
    // finds the last occurence of .
    if (($pos = strrpos($basename, '.')) !== false) {
        $extension = substr($basename, $pos + 1);
    }

    // security check
    if (!in_array($extension, $allowedExtensions)) {
        echo '/* Repent and turn to Jesus! */';
        header('HTTP/1.0 403 Forbidden');
        exit;
    }

    $lastmodified = max($lastmodified, filemtime($path));

    // mixing of types is not allowed
    if ($type && $extension !== $type) {
        header('HTTP/1.0 503 Not Implemented');
        echo '/* Repent and turn to Jesus! */';
        exit;
    }

    $type = $extension;
    $mimeType = $mimeMap[$type];

    // add to stack
    $files[] = $path;
}

// Send Etag hash
$hash = $lastmodified . '-' . md5($_GET['f'] . $v);

header('Vary: Accept-Encoding');
header(sprintf('Etag: "%s"', $hash));
header(sprintf('Last-modified: %s', gmdate('D, d M Y H:i:s T', $lastmodified)));
header(sprintf('Expires: %s', gmdate('D, d M Y H:i:s T', $lastmodified + strtotime($clientCacheTime))));

if (isset($_SERVER['HTTP_IF_NONE_MATCH'])
    && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"'
) {
    // Return visit and no modifications, so do not send anything
    header('HTTP/1.0 304 Not Modified');
    header('Content-Length: 0');
    exit;
} else {
    // First time visit or files were modified
    if ($cacheEnabled) {
        // Determine supported compression method
        $gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
        $deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

        // Determine used compression method
        $encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

        // Check for buggy versions of Internet Explorer
        if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera')
            && preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)
        ) {
            $version = floatval($matches[1]);
            if ($version < 6) {
                $encoding = 'none';
            }

            if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) {
                $encoding = 'none';
            }
        }

        // Try the cache first to see if the combined files were already generated
        $cachefile = $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');

        if (file_exists($cachedir . '/' . $cachefile)) {
            if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {
                if ($encoding != 'none') {
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

    require_once $sf_sift_lib_dir . '/autoload/sfCoreAutoload.class.php';
    // register core classes
    sfCoreAutoload::register();

    list($driver, $driverOptions) = $minifierDriverMap[$type];

    try {
        // create minifier instance for this type
        $minifier = sfMinifier::factory($driver, $driverOptions);
    } catch (Exception $e) {
        $minifier = false;
    }

    // Get contents of the files
    $contents = array();
    foreach ($files as $element) {
        // check if its worth the work
        // is this minified version?
        if (strpos($element, '.min.js') !== false
            || strpos($element, '.minified.js') !== false
            || !$minifier
        ) {
            $contents[] = file_get_contents($element);
        } else {
            $contents[] = $minifier->processFile($element);
        }
    }

    $contents = join("\n", $contents);

    // Send Content-Type
    header(sprintf('Content-Type: %s', $mimeType));

    if (isset($encoding) && $encoding != 'none') {
        // Send compressed contents
        $contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
        header(sprintf('Content-Encoding: %s', $encoding));
        header(sprintf('Content-Length: %s', strlen($contents)));
    } else {
        // Send regular contents
        header(sprintf('Content-Length: %s', strlen($contents)));
    }

    echo $contents;

    // Store cache
    if ($cacheEnabled) {
        if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
            // lock the file
            flock($fp, LOCK_EX);
            fwrite($fp, $contents);
            // release the lock
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
