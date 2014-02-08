<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for exif adapters.
 *
 * @package Sift
 * @subpackage image
 */
abstract class sfExifAdapter extends sfConfigurable
{
  /**
   * Logger instance holder
   *
   * @var sfILogger
   */
  protected $logger;

  /**
   * Constructs the adapter
   *
   * @param array $options Array of options
   * @param sfILogger $logger
   */
  public function __construct($options = array(), sfILogger $logger = null)
  {
    parent::__construct($options);

    $this->logger = $logger;
  }

  /**
   * Returns the EXIF data from the $file
   *
   * @return array
   */
  abstract public function getData($file);

  /**
   * Returns an array of supported categories
   *
   * @return array
   */
  abstract public function supportedCategories();

  /**
   * Process exif data
   *
   * @param $exif
   * @return unknown_type
   */
  protected function processData($exif)
  {
    if (!$exif) {
      return array();
    }

    $results = array();
    $fields = sfExif::getFields($this);
    foreach ($fields as $field => $data) {
      $value = isset($exif[$field]) ? $exif[$field] : '';
      // Don't store empty fields.
      if ($value === '') {
        continue;
      }

      /* Special handling of GPS data */
      if ($data['type'] == 'gps') {
        $value = $this->parseGPSData($exif[$field]);
        if(!empty($exif[$field . 'Ref']) &&
                in_array($exif[$field . 'Ref'], array('S', 'South', 'W', 'West')))
        {
          $value = - abs($value);
        }
      }

      /* Date fields are converted to a timestamp. */
      if ($data['type'] == 'date') {
        @list($ymd, $hms) = explode(' ', $value, 2);
        @list($year, $month, $day) = explode(':', $ymd, 3);
        if (!empty($hms) && !empty($year) && !empty($month) && !empty($day)) {
          $time = "$month/$day/$year $hms";
          $value = strtotime($time);
        }
      }

      if ($data['type'] == 'array' || is_array($value)) {
        if (is_array($value)) {
          $value = implode(',', $value);
        }
      }

      $results[$field] = $value;
    }

    return $results;
  }

  /**
   *
   * @return array
   */
  public function getSupportedFields()
  {
    return sfExif::getFields($this);
  }

  /**
   * Parse the Longitude and Latitude values into a standardized format
   * regardless of the source format.
   *
   * @param mixed $data  An array containing degrees, minutes, seconds
   *                     in index 0, 1, 2 respectifully.
   *
   * @return double  The location data in a decimal format.
   */
  protected function parseGPSData($data)
  {
    // According to EXIF standard, GPS data can be in the form of
    // dd/1 mm/1 ss/1 or as a decimal reprentation.
    if (!is_array($data)) {
      // Assume a scalar is a decimal representation. Cast it to a float
      // which will get rid of any stray ordinal indicators. (N, S,
      // etc...)
      return (double) $data;
    }

    if ($data[0] == 0) {
      return 0;
    }

    if (strpos($data[1], '/') !== false) {
      $min = explode('/', $data[1]);
      if (count($min) > 1) {
        $min = $min[0] / $min[1];
      } else {
        $min = $min[0];
      }
    } else {
      $min = $data[1];
    }

    if (strpos($data[2], '/') !== false) {
      $sec = explode('/', $data[2]);
      if (count($sec) > 1) {
        $sec = $sec[0] / $sec[1];
      } else {
        $sec = $sec[0];
      }
    } else {
      $sec = $data[2];
    }

    return self::degToDecimal($data[0], $min, $sec);
  }

  /**
   *
   * @param $degrees
   * @param $minutes
   * @param $seconds
   * @return unknown_type
   */
  protected function degToDecimal($degrees, $minutes, $seconds)
  {
    $degs = (double) ($degrees + ($minutes / 60) + ($seconds / 3600));

    return round($degs, 6);
  }

  /**
   * Logs message to the logger
   *
   * @param string $message
   * @param string $priority
   */
  protected function log($message, $priority = 'info')
  {
    if ($this->logger) {
      $this->logger->log($message, $priority);
    }
  }

}
