<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfUploadedFile represents an uploaded file.
 *
 * @package    Sift
 * @subpackage request
 */
class sfUploadedFile implements ArrayAccess
{
  protected $originalName   = '',
    $generatedName  = '',
    $error          = UPLOAD_ERR_OK,
    $tempName       = '',
    $savedName      = null,
    // detected mime type
    $type           = '',
    // original mime type set by browser
    $originalType   = '',
    $size           = 0,
    $path           = null,
    // other data used for extending this object
    $data           = array();

  public static function create($file)
  {
    $self = new self($file['name'], $file['type'], $file['tmp_name'], $file['size']);
    if(isset($file['error']))
    {
      $self->error = $file['error'];
    }

    return $self;
  }

  public function isUploaded()
  {
    return $this->tempName && $this->getError() === UPLOAD_ERR_OK;
  }

  /**
   * Constructor.
   *
   * @param string $originalName  The original file name
   * @param string $type          The file content type
   * @param string $tempName      The absolute temporary path to the file
   * @param int    $size          The file size (in bytes)
   */
  public function __construct($originalName, $type, $tempName, $size, $path = null)
  {
    $this->originalName = $originalName;
    $this->tempName     = $tempName;
    $this->originalType = $type;
    $this->size         = $size;
    $this->path         = $path;
    $this->sanitizedName = sfFilesystem::sanitizeFilename($this->originalName);

    // don't trust what browser said!
    $this->type         = $this->isUploaded() ? $this->detectMimeType($tempName) : $this->fixMimeType($type);
  }

  /**
   * Returns the name of the saved file.
   */
  public function __toString()
  {
    return is_null($this->savedName) ? '' : $this->savedName;
  }

  /**
   * Saves the uploaded file.
   *
   * This method can throw exceptions if there is a problem when saving the file.
   *
   * If you don't pass a file name, it will be generated by the generateFilename method.
   * This will only work if you have passed a path when initializing this instance.
   *
   * @param  string $file      The file path to save the file
   * @param  int    $fileMode  The octal mode to use for the new file
   * @param  bool   $create    Indicates that we should make the directory before moving the file
   * @param  int    $dirMode   The octal mode to use when creating the directory
   *
   * @return string The filename without the $this->path prefix
   *
   * @throws Exception
   */
  public function save($file = null, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    if (is_null($file))
    {
      $file = $this->generateFilename();
    }

    if ($file[0] != '/' && $file[0] != '\\' && !(strlen($file) > 3 && ctype_alpha($file[0]) && $file[1] == ':' && ($file[2] == '\\' || $file[2] == '/')))
    {
      if (is_null($this->path))
      {
        throw new RuntimeException('You must give a "path" when you give a relative file name.');
      }

      $file = $this->path.DIRECTORY_SEPARATOR.$file;
    }

    // get our directory path from the destination filename
    $directory = dirname($file);

    if (!is_readable($directory))
    {
      if ($create && !mkdir($directory, $dirMode, true))
      {
        // failed to create the directory
        throw new Exception(sprintf('Failed to create file upload directory "%s".', $directory));
      }

      // chmod the directory since it doesn't seem to work on recursive paths
      chmod($directory, $dirMode);
    }

    if (!is_dir($directory))
    {
      // the directory path exists but it's not a directory
      throw new Exception(sprintf('File upload path "%s" exists, but is not a directory.', $directory));
    }

    if (!is_writable($directory))
    {
      // the directory isn't writable
      throw new Exception(sprintf('File upload path "%s" is not writable.', $directory));
    }

    // copy the temp file to the destination file
    copy($this->getTempName(), $file);

    // chmod our file
    chmod($file, $fileMode);

    $this->savedName = $file;

    return is_null($this->path) ? $file : str_replace($this->path.DIRECTORY_SEPARATOR, '', $file);
  }

  /**
   * Generates a unique filename for the current file.
   *
   * @return string A unique name to represent the current file
   */
  public function generateFilename()
  {
    return substr(sha1($this->getOriginalName().rand(11111, 99999)), 0, 8) .
            $this->getExtension($this->getOriginalExtension());
  }

  /**
   * Unique name for this file
   *
   * @return string
   */
  public function getGeneratedName()
  {
    if(!$this->generatedName)
    {
      $this->generatedName = $this->generateFilename();
    }

    return $this->generatedName;
  }

  /**
   * Returns the error
   *
   * @return string
   */
  public function getError()
  {
    return $this->error;
  }

  /**
   * Returns the path to use when saving a file with a relative filename.
   *
   * @return string The path to use when saving a file with a relative filename
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * Returns the file extension, based on the content type of the file.
   *
   * @param  string $default  The default extension to return if none was given
   *
   * @return string The extension (with the dot)
   */
  public function getExtension($default = '')
  {
    return $this->getExtensionFromType($this->type, $default);
  }

  /**
   * Returns the original uploaded file name extension.
   *
   * @param  string $default  The default extension to return if none was given
   *
   * @return string The extension of the uploaded name (with the dot)
   */
  public function getOriginalExtension($default = '')
  {
    return (false === $pos = strrpos($this->getOriginalName(), '.')) ? $default : substr($this->getOriginalName(), $pos);
  }

  /**
   * Returns true if the file has already been saved.
   *
   * @return Boolean true if the file has already been saved, false otherwise
   */
  public function isSaved()
  {
    return !is_null($this->savedName);
  }

  /**
   * Returns the path where the file has been saved
   *
   * @return string The path where the file has been saved
   */
  public function getSavedName()
  {
    return $this->savedName;
  }

  /**
   * Returns the original file name.
   *
   * @return string The file name
   */
  public function getOriginalName()
  {
    return $this->originalName;
  }

  /**
   * Returns the original mime type said by browser.
   *
   * @return string The file mime type
   */
  public function getOriginalType()
  {
    return $this->originalType;
  }

  /**
   * Returns the absolute temporary path to the uploaded file.
   *
   * @return string The temporary path
   */
  public function getTempName()
  {
    return $this->tempName;
  }

  /**
   * Returns the file content type.
   *
   * @return string The content type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Returns the file content type. Alias for getType() method
   *
   * @return string The content type
   */
  public function getMimeType()
  {
    return $this->getType();
  }

  /**
   * Returns the size of the uploaded file.
   *
   * @return int The file size
   */
  public function getSize()
  {
    return $this->size;
  }

  /**
   * Returns sanitized name
   *
   * @return string
   */
  public function getSanitizedName()
  {
    return $this->sanitizedName;
  }

  /**
   * Returns the mime type of a file.
   *
   * This method always returns a lower-cased string as mime types are case-insensitive
   * as per the RFC 2616 (http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.7).
   *
   * @param  string $file      The absolute path of a file
   * @param  string $fallback  The default mime type to return if not guessable
   *
   * @return string The mime type of the file (fallback is returned if not guessable)
   */
  protected function detectMimeType($file)
  {
    return sfMimeType::getTypeFromFile($file, 'application/octet-stream',
            $this->originalName);
  }

  /**
   * Fixes mime type
   *
   * @param string $mime
   * @return string
   */
  protected function fixMimeType($mime)
  {
    return sfMimeType::fixMimeType($mime);
  }

  /**
   * Returns the extension associated with the given content type.
   *
   * @param  string $type     The content type
   * @param  string $default  The default extension to use
   *
   * @return string The extension (with the dot)
   */
  protected function getExtensionFromType($type, $default = '')
  {
    return sfMimeType::getExtensionFromType($type, $default);
  }

  public function offsetSet($offset, $value)
  {
    throw new sfException('You cannot modify the uploaded file.');
  }

  public function offsetExists($var)
  {
    if(in_array($var, array('name', 'tmp_name', 'size', 'error', 'type'))
       || isset($this->data[$var]))
    {
      return true;
    }

    return false;
  }

  public function offsetUnset($var)
  {
    throw new sfException('You cannot modify the uploaded file.');
  }

  public function offsetGet($var)
  {
    switch($var)
    {
      case 'name':
        return $this->getOriginalName();
      break;

      case 'tmp_name':
        return $this->getTempName();
      break;

      case 'size':
        return $this->getSize();
      break;

      case 'type':
      case 'mime_type':
        return $this->getType();
      break;

      case 'error':
        return $this->getError();
      break;

      default:

        if(isset($this->data[$var]))
        {
          return $this->data[$var];
        }

      break;
    }

    throw new sfException(sprintf('Error in offsetGet(). "%s" is not valid.', $var));
  }

  public function __call($method, $arguments)
  {
    $verb   = substr($method, 0, 3);
    $column = substr($method, 3);

    // first character lowercase
    $column[0] = strtolower($column[0]);
    if($verb == 'get')
    {
      return $this->offsetGet($column);
    }
    elseif($verb == 'set')
    {
      throw new sfException('You cannot modify the uploaded file.');
    }

    throw new sfException(sprintf('Unknown method "%s"', $method));
  }

}
