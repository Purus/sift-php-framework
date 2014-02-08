<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorFile validates an uploaded file.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorFile extends sfValidatorBase {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * max_size:             The maximum file size in bytes (cannot exceed upload_max_filesize in php.ini)
   *  * mime_types:           Allowed mime types array or category (available categories: web_images)
   *  * mime_type_guessers:   An array of mime type guesser PHP callables (must return the mime type or null)
   *  * mime_categories:      An array of mime type categories (web_images is defined by default)
   *  * path:                 The path where to save the file - as used by the sfValidatedFile class (optional)
   *  * uploaded_file_class:  Name of the class that manages the cleaned uploaded file (optional)
   *
   * Available error codes:
   *
   *  * max_size
   *  * mime_types
   *  * partial
   *  * no_tmp_dir
   *  * cant_write
   *  * extension
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    if(!ini_get('file_uploads'))
    {
      throw new LogicException(sprintf('Unable to use a file validator as "file_uploads" is disabled in your php.ini file (%s)', get_cfg_var('cfg_file_path')));
    }

    $this->addOption('max_size');
    $this->addOption('mime_types');

    $this->addOption('mime_categories', array(
        'web_images' => array(
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/x-png',
            'image/gif',
    )));

    $this->addOption('uploaded_file_class', 'sfUploadedFile');
    $this->addOption('path', null);

    // mutiple files?
    $this->addOption('multiple', false);

    $this->setMessage('required', 'Select a file to be uploaded.');
    $this->setMessage('invalid', 'Invalid file uploaded.');

    $this->addMessage('max_size', 'File is too large (maximum is %max_size% bytes).');
    $this->addMessage('mime_types', 'Invalid mime type (%mime_type%).');
    $this->addMessage('partial', 'The uploaded file was only partially uploaded.');
    $this->addMessage('no_tmp_dir', 'Missing a temporary folder.');
    $this->addMessage('cant_write', 'Failed to write file to disk.');
    $this->addMessage('extension', 'File upload stopped by extension.');
  }

  /**
   * This validator always returns a sfValidatedFile object.
   *
   * The input value must be an array with the following keys (if multiple options is set to false):
   *
   *  * tmp_name: The absolute temporary path to the file
   *  * name:     The original file name (optional)
   *  * type:     The file content type (optional)
   *  * error:    The error code (optional)
   *  * size:     The file size in bytes (optional)
   *
   * If multiple is set to true, $value has to be an array of arrays (see above).
   *
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    // multiple option, we are cleaning array of values
    if($this->getOption('multiple'))
    {
      if(!is_array($value))
      {
        throw new sfValidatorError($this, 'invalid');
      }

      $result = array();
      foreach($value as $v)
      {
        $result[] = $this->doCleanSingle($v);
      }

      return $result;
    }
    else
    {
      return $this->doCleanSingle($value);
    }
  }

  /**
   * Cleans a single value
   *
   * @param array $value
   * @return sfUploadedFile
   * @throws sfValidatorError
   */
  protected function doCleanSingle($value)
  {
    if(!is_array($value) || !isset($value['tmp_name']))
    {
      throw new sfValidatorError($this, 'invalid');
    }

    if(!isset($value['name']))
    {
      $value['name'] = '';
    }

    if(!isset($value['error']))
    {
      $value['error'] = UPLOAD_ERR_OK;
    }

    if(!isset($value['size']))
    {
      $value['size'] = filesize($value['tmp_name']);
    }

    if(!isset($value['type']))
    {
      $value['type'] = 'application/octet-stream';
    }

    switch($value['error'])
    {
      case UPLOAD_ERR_INI_SIZE:
        $max = ini_get('upload_max_filesize');
        if($this->getOption('max_size'))
        {
          $max = min($max, $this->getOption('max_size'));
        }
        throw new sfValidatorError($this, 'max_size', array('max_size' => $max, 'size' => (int) $value['size']));
      case UPLOAD_ERR_FORM_SIZE:
        throw new sfValidatorError($this, 'max_size', array('max_size' => 0, 'size' => (int) $value['size']));
      case UPLOAD_ERR_PARTIAL:
        throw new sfValidatorError($this, 'partial');
      case UPLOAD_ERR_NO_TMP_DIR:
        throw new sfValidatorError($this, 'no_tmp_dir');
      case UPLOAD_ERR_CANT_WRITE:
        throw new sfValidatorError($this, 'cant_write');
      case UPLOAD_ERR_EXTENSION:
        throw new sfValidatorError($this, 'extension');
    }

    // check file size
    if($this->hasOption('max_size') && $this->getOption('max_size') < (int) $value['size'])
    {
      throw new sfValidatorError($this, 'max_size', array('max_size' => $this->getOption('max_size'), 'size' => (int) $value['size']));
    }

    $tempName = (string) $value['tmp_name'];
    $mimeType = null;
    if($tempName)
    {
      $mimeType = $this->getMimeType($tempName, (string) $value['type']);
    }

    // check mime type
    if($this->hasOption('mime_types'))
    {
      $mimeTypes = is_array($this->getOption('mime_types')) ? $this->getOption('mime_types') : $this->getMimeTypesFromCategory($this->getOption('mime_types'));
      if(!in_array($mimeType, array_map('strtolower', $mimeTypes)))
      {
        throw new sfValidatorError($this, 'mime_types', array('mime_types' => $mimeTypes, 'mime_type' => $mimeType));
      }
    }

    $class = $this->getOption('uploaded_file_class');

    return new $class($value['name'], $mimeType, $value['tmp_name'], $value['size'], $this->getOption('path'));
  }

  /**
   * Returns the mime type of a file.
   *
   * This methods call each mime_type_guessers option callables to
   * guess the mime type.
   *
   * This method always returns a lower-cased string as mime types are case-insensitive
   * as per the RFC 2616 (http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.7).
   *
   * @param  string $file      The absolute path of a file
   * @param  string $fallback  The default mime type to return if not guessable
   *
   * @return string The mime type of the file (fallback is returned if not guessable)
   */
  protected function getMimeType($file, $fallback)
  {
    return sfMimeType::getTypeFromFile($file, $fallback, $originalFileName = null);
  }

  protected function getMimeTypesFromCategory($category)
  {
    $categories = $this->getOption('mime_categories');

    if(!isset($categories[$category]))
    {
      throw new InvalidArgumentException(sprintf('Invalid mime type category "%s".', $category));
    }

    return $categories[$category];
  }

  /**
   * @see sfValidatorBase
   */
  protected function isEmpty($value)
  {
    // empty if the value is not an array
    // or if the value comes from PHP with an error of UPLOAD_ERR_NO_FILE
    return
            (!is_array($value)) ||
            (is_array($value) && isset($value['error']) && UPLOAD_ERR_NO_FILE === $value['error']);
  }

  /**
   * @see sfValidatorBase
   */
  public function getActiveMessages()
  {
    return array_merge(parent::getActiveMessages(), array(
        $this->getMessage('max_size'),
        $this->getMessage('mime_types'),
        $this->getMessage('partial'),
        $this->getMessage('no_tmp_dir'),
        $this->getMessage('cant_write'),
        $this->getMessage('extension')
    ));
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    if($maxSize = $this->getOption('max_size'))
    {
      $rules[sfFormJavascriptValidation::FILE_SIZE] = $maxSize;
    }

    if($mime_types = $this->getOption('mime_types'))
    {
      $mimeTypes = is_array($mime_types) ? $mime_types : $this->getMimeTypesFromCategory($mime_types);
      $rules[sfFormJavascriptValidation::FILE_EXTENSION] = join(',', $mimeTypes);
    }

    return $rules;
  }

}
