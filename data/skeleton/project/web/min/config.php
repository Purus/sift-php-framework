<?php
/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configuration directives for minifier utility.
 *
 * @see https://bitbucket.org/mishal/sift-php-framework/wiki/MinifierUtilityScript
 */

// Load Sift lib and data dirs definition
// Defines: $sf_sift_data_dir and $sf_sift_lib_dir variables
require_once realpath(dirname(__FILE__) . '/../../config/config.php');

return array(
  // enable cache?
  'cache_enabled' => true,
  // path to a cache folder
  'cache_dir' => realpath(dirname(__FILE__) . '/../cache/minify'),
  // web root directory
  'web_root_dir' => realpath(dirname(__FILE__) . '/../'),
  // path aliases
  'path_aliases' => array(
    '/sf' => $sf_sift_data_dir . '/web'
  ),
  // allowed file extensions
  'allowed_extensions' => array(
    'js', 'css'
  ),
  // minifier driver map
  // extension => array(driverName, array of options)
  'minifier_driver_map' => array(
    'js' => array(
      'JsMin', array()
    ),
    'css' => array(
      'CssSimple', array()
    )
  ),
  // extension to mime definitions
  'mime_map' => array(
    'js' => 'text/javascript',
    'css' => 'text/css',
  ),
  // sift defintinions
  'sf_sift_lib_dir' => $sf_sift_lib_dir,
  'sf_sift_data_dir' => $sf_sift_data_dir
);
