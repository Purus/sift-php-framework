<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FileHelper.
 *
 * @package    Sift
 * @subpackage helper_file
 */

/**
 * Returns human readable name for given $mime. Also translates the name
 *
 * @param string $mime Mime type (like image/jpg)
 * @param string $unknown Unknown string if mime is uknown
 * @return string
 */
function file_mime_name($mime, $unknown = 'Unknown')
{
  return __(
    sfMimeType::getNameFromType($mime, $unknown), array(),
          sfConfig::get('sf_sift_data_dir') . '/i18n/catalogues/mime_type'
  );
}

/**
 * Returns max size of file which can be uploaded. Specified by PHP settings
 *
 * @param boolean $format
 * @return string|integer
 */
function file_max_upload_size($format = true)
{
  $max_size = sfToolkit::getMaxUploadSize();
  if($format)
  {
    $max_size = file_format_size($max_size);
  }
  return $max_size;
}

/**
 * Formats file size
 *
 * @param integer $size Size of the file in bytes
 * @param integer $round Precision
 * @return string Formatted filesize (100 kB)
 */
function file_format_size($size, $round = 1)
{
  return sfFilesystem::formatFileSize($size, $round);
}
