<?php
/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configuration directives for image resizer utility.
 *
 * @see https://bitbucket.org/mishal/sift-php-framework/wiki/ImageResizerUtilityScript
 */

// Load Sift lib and data dirs definition
// Defines: $sf_sift_data_dir and $sf_sift_lib_dir variables
require_once realpath(dirname(__FILE__) . '/../../config/config.php');

return array(
  // max width
  'max_width' => 1200,
  // image adapter used for resizing
  // GD or ImageMagick
  'adapter' => 'GD',
  // max height
  'max_height' => 1200,
  // enable cache?
  'cache_enabled' => true,
  // path to a cache folder
  'cache_dir' => realpath(dirname(__FILE__) . '/../cache/images'),
  // web root directory
  'web_root_dir' => realpath(dirname(__FILE__) . '/../'),
  // sift defintinions
  'sf_sift_lib_dir' => $sf_sift_lib_dir,
  'sf_sift_data_dir' => $sf_sift_data_dir
);
