<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Exif using native php "exif_read_data" function
 *
 * @package Sift
 * @subpackage image
 */
class sfExifAdapterNative extends sfExifAdapter
{
  /**
   * Setup the adapter
   *
   * @throws BadFunctionCallException
   */
  public function setup()
  {
    if (!function_exists('exif_read_data')) {
      throw new BadFunctionCallException('Missing required "exif_read_data" function.');
    }
  }

  /**
   *
   * @see sfExifAdapter
   */
  public function getData($file)
  {
    return $this->processData(@exif_read_data($file, 0, false));
  }

  /**
   *
   * @see sfExifAdapter
   */
  public function supportedCategories()
  {
    return array('EXIF');
  }

}
