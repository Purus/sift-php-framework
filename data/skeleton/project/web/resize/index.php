<?php
/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * On-fly image resizer script
 *
 * @version 1.0
 * @link    @see https://bitbucket.org/mishal/sift-php-framework/wiki/ImageResizerUtilityScript
 */

// turn off reporting
// Prevent hack attempts
error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('zlib.output_compression', 0);

if (!isset($_GET['img'])) {
    header('HTTP/1.0 503 Not Implemented');
    echo '/* Repent and turn to Jesus! */';
    exit;
};

// load configuration
$config = require_once dirname(__FILE__) . '/config.php';
$webRootDir = $config['web_root_dir'];
$maxWidth = $config['max_width'];
$maxHeight = $config['max_height'];
$cacheEnabled = $config['cache_enabled'];
$cacheDir = $config['cache_dir'];
$imageAdapter = $config['adapter'];

$sf_sift_lib_dir = $config['sf_sift_lib_dir'];
require_once $sf_sift_lib_dir . '/autoload/sfCoreAutoload.class.php';
// register core classes
sfCoreAutoload::register();

if ($cacheEnabled) {
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777);
    }
}

// make the filename safe to use
$img = trim(strip_tags(htmlspecialchars($_GET['img'])));
$img = str_replace('..', '', $img); // no going up in directory tree
// get the command specified in the filename
// example: big_green_frog.thumb150.jpg
preg_match("/\.([^\.]*)\..{2,3}$/", $img, $match); // this also means no double commands (possible DOS attack)

if (!isset($match[1])) {
    header('HTTP/1.0 503 Not Implemented');
    echo '/* Repent and turn to Jesus! */';
    exit;
}

$request = $match[1];
$width = $height = false;
if (strpos($request, 'x')) {
    list($width, $height) = explode('x', $request);
}

if (!$width || $width >= $maxWidth || !$height || $height >= $maxHeight) {
    header('HTTP/1.0 503 Not Implemented');
    echo '/* Repent and turn to Jesus! */';
    exit;
}

// get original filename
// example: big_green_frog.jpg
$imgFile = str_replace('.' . $request, '', $img, $replaceCount);
$imgFile = $webRootDir . DIRECTORY_SEPARATOR . $imgFile;

// stop the possibility of creating unlimited files
// example (attack): abc.120.jpg, abc.120.120.jpg, abc.120.....120.jpg - this could go on forever
if ($replaceCount > 1 || !is_readable($imgFile)) {
    header('HTTP/1.0 505 Internal Error');
    echo '/* Invalid file or command given. */';
    exit;
}

$extension = get_extension($img);
$md5 = md5($img);
$cacheKey = $md5 . '.' . $extension;

if (!$cacheEnabled
    || !is_readable($cacheDir . DIRECTORY_SEPARATOR . $cacheKey)
) {
    $img = new sfImage($imgFile, '', $imageAdapter);
    $img->thumbnail($width, $height);
    $img->saveAs($cacheDir . DIRECTORY_SEPARATOR . $cacheKey);
}

send_file($cacheDir . DIRECTORY_SEPARATOR . $cacheKey, filemtime($imgFile));

function get_extension($file)
{
    $parts = (explode('.', $file));

    return end($parts);
}

function send_file($file, $lastMod)
{
    $extension = get_extension($file);
    $md5 = md5($file);

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
        && (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastMod
            || trim($_SERVER['HTTP_IF_NONE_MATCH']) == $md5)
    ) {
        header('HTTP/1.1 304 Not Modified');
        exit;
    }

    if (is_readable($file)) {
        $fsize = filesize($file);

        switch (strtolower($extension)) {
            case 'gif':
                header('Content-type: image/gif');
                break;

            case 'png':
                header('Content-type: image/png');
                break;

            case 'jpg':
            case 'jpeg':
            case 'jpe':
                header('Content-type: image/png');
                break;
        }

        header(sprintf('Last-Modified: %s GMT', gmdate("D, d M Y H:i:s", $lastMod)));
        header(sprintf('Etag: %s', $md5));
        header('Cache-control: public');
        header(sprintf('Content-length: %s', $fsize));

        $fp = fopen($file, 'rb');
        while (!feof($fp)) {
            $buf = fread($fp, 4096);
            echo $buf;
        }
        exit;
    }
}
