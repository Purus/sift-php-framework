<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMimeType class provides methods for detecting mime type from files
 *
 * @package Sift
 * @subpackage util
 *
 */
class sfMimeType {

  protected static $mimeTypes = null;

  /**
   * @throws LogicException
   */
  public function __construct()
  {
    throw new LogicException('This class is just a utility library.');
  }

  /**
   * Returns the extension associated with the given content type.
   *
   * @param  string $type     The content type
   * @param  string $default  The default extension to use
   * @param  boolean $dot  Prepend dot the the extension?
   *
   * @return string The extension (default with the dot)
   */
  public static function getExtensionFromType($type, $default = 'bin', $dot = true)
  {
    self::loadMimeTypes();

    $type = strtolower(trim($type));
    $default = trim($default);

    if(!$type)
    {
      return $dot ? sprintf('.%s', $default) : $default;
    }

    // we know the type
    if(isset(self::$mimeTypes[$type]))
    {
      return $dot ? sprintf('.%s', self::$mimeTypes[$type]['extension'][0]) :
        self::$mimeTypes[$type]['extension'][0];
    }
    else // try to detect it
    {
      foreach(self::$mimeTypes as $mimeName => $mimeType)
      {
        foreach($mimeType['alias'] as $alias)
        {
          if($alias == $type)
          {
            return $dot ? sprintf('.%s', self::$mimeTypes[$mimeName]['extension'][0]) :
              self::$mimeTypes[$mimeName]['extension'][0];
          }
        }
      }
    }

    return $dot ? sprintf('.%s', $default) : $default;
  }

  /**
   * Returns content type associated with the given extension of filename
   *
   * @param  string $extension The file extension or full filename
   * @param  string $default  The default type to use
   *
   * @return string The extension (with the dot)
   */
  public static function getTypeFromExtension($extension, $default = 'application/octet-stream')
  {
    self::loadMimeTypes();

    // we need real extension (the last part after last dot)
    // tar.gz will be coverted to gz
    $extension = self::getFileExtension($extension);

    foreach(self::$mimeTypes as $mimeType => $property)
    {
      $key = array_search($extension, $property['extension']);
      if($key !== false)
      {
        return $mimeType;
      }
    }

    return $default;
  }

  /**
   * Returns mime type of the string. It put the string to temporary file and detects the mime type from that file.
   *
   * @param string $string String to be detected
   * @param string $default Default mime type if auto detection fails (application/octet-stream)
   * @param string $fileName Filename
   * @return string
   * @see getTypeFromFile
   */
  public static function getTypeFromString($string, $default = 'application/octet-stream', $fileName = null)
  {
    $tmp = tempnam(sfToolkit::getTmpDir(), 'mime_detect');
    file_put_contents($tmp, $string);

    $mime = sfMimeType::getTypeFromFile($tmp, $default, $fileName);
    unlink($tmp);

    return $mime;
  }

  /**
   * Returns mime type of the file.
   *
   * Detection is performed in following steps:
   *
   *   1. Detects mime using system ("finfo", "file" command or deprecated "mime_content_type")
   *   2. Detects mime based on the file extension (using mime database)
   *   3. Compares the value from step1 with value detected in step2 and checks
   *      if the value1 is not one of value2 parent mime types
   *      If yes: returns value2
   *      else returns value1
   *
   * @param string $file absolute path to a file
   * @param string $default Default mime type if auto detection fails (application/octet-stream)
   * @param string $originalFileName Original filename
   * @return string
   */
  public static function getTypeFromFile($file, $default = 'application/octet-stream', $originalFileName = null)
  {
    // no filename passed
    if(!$originalFileName)
    {
      $originalFileName = basename($file);
    }

    $mimeType    = false;
    $customMagic = sfConfig::get('sf_mime_detect_magic_path', false);

    // STEP 1
    if(class_exists('finfo'))
    {
      $fileInfo = ($customMagic) ?
        new finfo(FILEINFO_MIME, $customMagic) :
        new finfo(FILEINFO_MIME);

      $result = $fileInfo->file($file);
    }
    else // fallback
    {
      if(!ini_get('safe_mode') && DIRECTORY_SEPARATOR != '\\')
      {
        $result = trim(@exec(sprintf('file -bi %s%s',
                 escapeshellarg($file),
                ($customMagic ? sprintf(' -m %s', escapeshellarg($customMagic)) : '')
        )));
      }
      elseif(function_exists('mime_content_type'))
      {
        $result = mime_content_type($file);
      }
    }

    if(is_string($result) && !empty($result))
    {
      $mimeType = $result;
    }

    $mimeType = self::fixMimeType($mimeType);

    // STEP 2
    $mimeType2 = self::getTypeFromExtension($originalFileName, $default);

    // HACKish!!!
    // CORRECT THE RESULTS, correct the results for js, css, json and php and other extensions
    // HACKish!!!
    // invalid files and its content types
    // @link http://stackoverflow.com/questions/7416936/finfo-returns-wrong-mime-type-on-some-js-files-text-x-c
    // @link http://stackoverflow.com/questions/5226289/php-doesnt-return-the-correct-mime-type
    if(preg_match('/\.(js|css|json|php|docx|xlsx)$/i', $originalFileName))
    {
      $mimeType = $mimeType2;
    }

    // auto detection failed, we will simply return autodetected value
    if(!$mimeType)
    {
      return $mimeType2;
    }

    // STEP3
    if($mimeType != $mimeType2 && isset(self::$mimeTypes[$mimeType2]))
    {
      // yes, its the parent,
      if(in_array($mimeType, self::$mimeTypes[$mimeType2]['parent']))
      {
        return $mimeType2;
      }
    }

    return $mimeType;
  }

  protected static function getFileExtension($filename)
  {
    $extension = $filename;
    // finds the last occurence of .
    $pos = strrpos($extension, '.');
    if($pos !== false)
    {
      $extension = substr($filename, $pos + 1);
    }
    return strtolower($extension);
  }

  /**
   * Imports mime types definitions from the data file
   */
  protected static function loadMimeTypes()
  {
    if(is_null(self::$mimeTypes))
    {
      $definitions = unserialize(file_get_contents(
        sfConfig::get('sf_sift_data_dir').'/data/mime_types.dat'));
      self::$mimeTypes = $definitions;
    }
  }

  /**
   * Fixes mime type values like: application/zip; charset=binary
   * to just "application/zip"
   *
   * @param string $mimeType
   * @return string
   */
  public static function fixMimeType($mimeType)
  {
    $mimeType = preg_replace('#[\s;].*$#', '', $mimeType);
    // IE8 mime wrong types!
    switch($mimeType)
    {
      case 'image/pjpeg':
        $mimeType = 'image/jpeg';
      break;
    }
    return $mimeType;
  }

  /**
   * Get Human readable type from $mime
   *
   * @param string $mime Mime type
   * @return string Human readable string like "ZIP archive" for "
   */
  public static function getNameFromType($mime, $default = 'Unknown')
  {
    self::loadMimeTypes();

    foreach(self::$mimeTypes as $mimeType => $property)
    {
      if($mime == $mimeType)
      {
        return $property['name'];
      }

      // loop all aliases
      foreach($property['alias'] as $alias)
      {
        if($mime == $alias)
        {
          return $property['name'];
        }
      }

    }

    return $default;
  }

}
