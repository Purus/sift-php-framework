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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfValidatorImage extends sfValidatorFile
{
  
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
   *  * validated_file_class: Name of the class that manages the cleaned uploaded file (optional)
   *
   * There are 3 built-in mime type guessers:
   *
   *  * guessFromFileinfo:        Uses the finfo_open() function (from the Fileinfo PECL extension)
   *  * guessFromMimeContentType: Uses the mime_content_type() function (deprecated)
   *  * guessFromFileBinary:      Uses the file binary (only works on *nix system)
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
    parent::configure($options, $messages);

    $this->addOption('persistent', isset($options['persistent']) ? $options['persistent'] : false);
    
    // we set "web images" category if nothing is provided in options array
    if(!$this->getOption('mime_types'))
    {
      $this->addOption('mime_types', 'web_images');
    }

    $this->addOption('min_width',  1024);
    $this->addOption('min_height', 768);

    // Canon EOS 50D max resolution
    $this->addOption('max_width',  4752);
    $this->addOption('max_height', 3168);

    // lifetime in seconds of old files, 14 days
    $this->addOption('persistent_upload_lifetime', 1209600);

    // image preview, only used with persistent feature
    $this->addOption('preview', true);
    
    // image thumbnail options, default to 100x100 square
    $this->addOption('create_preview_options', array(
      'width'   => 100,
      'height'  => 100,
      'method'  => 'left',
      'quality' => 80
    ));
    
    $this->addOption('create_preview_callable', array($this, 'createPreview'));

    $this->setMessage('required', isset($messages['required']) ? $messages['required'] : 'Select an image to be uploaded.');
    $this->setMessage('invalid', isset($messages['invalid']) ? $messages['invalid'] : 'Invalid file uploaded.');
    
    $this->addMessage('too_small', 'Image dimensions %width%x%height%px are too small (minimum is %min_width%x%min_height%px).');
    $this->addMessage('too_large', 'Image dimensions %width%x%height%px are too large (maximum is %max_width%x%max_height%px).');
  }

  /**
   * This validator always returns a sfValidatedFile object.
   *
   * The input value must be an array with the following keys:
   *
   *  * tmp_name: The absolute temporary path to the file
   *  * name:     The original file name (optional)
   *  * type:     The file content type (optional)
   *  * error:    The error code (optional)
   *  * size:     The file size in bytes (optional)
   *
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    // pass validation to file validator
    $result = parent::doClean($value);
    /* @var $result sfUploadedFile */

    // try our image validation
    $class = $this->getOption('validated_file_class');
    
    // we have a valid uploaded file, lets check if its an image
    // and its dimensions are ok!
    if($result instanceof $class)
    {
      $dimensions = getimagesize($result->getTempName());
      if($dimensions[0] < $this->getOption('min_width') ||
         $dimensions[1] < $this->getOption('min_height'))

      {
        throw new sfValidatorError($this, 'too_small', array(
            'width'      => $dimensions[0],
            'height'     => $dimensions[1],
            'min_width' => $this->getOption('min_width'),
            'min_height' => $this->getOption('min_height')));
      }
      elseif($dimensions[0] > $this->getOption('max_width') ||
         $dimensions[1] > $this->getOption('max_height'))

      {
        throw new sfValidatorError($this, 'too_large', array(
            'width'      => $dimensions[0],
            'height'     => $dimensions[1],
            'min_width'  => $this->getOption('max_width'),
            'min_height' => $this->getOption('max_height')));
      }
    }

    
    // image is valid
    // persistent
    if($this->getOption('persistent'))
    {
      $dir  = $this->getPersistentDir();

      // collect old uploads and delete them! probability of 10%
      if(mt_rand(0, 100) < 10)
      {
        $this->deleteOldUploads();
      }
      
      // we make unique filename in the directory
      do
      {
        $uuid                 = sfUuid::generate();
        $persistentUpload     = $dir . DS . $uuid. '.dat';
        $persistentUploadInfo = $dir . DS . $uuid. '.file';
      }
      while(file_exists($persistentUpload));

      $preview = $dimensions = false;

      // do we want to generate image preview?
      if($this->getOption('preview'))
      {
        $previewDir    = $this->getPreviewDir();
        $previewWebDir = $this->getPreviewWebDir();

        $previewCallable = $this->getOption('create_preview_callable');
        $filename        = $uuid . $result->getExtension();
        $previewTarget   = $previewDir . DS . $filename;
        
        // make thumbnail
        call_user_func($previewCallable, $result->getTempName(), $result->getType(), $previewTarget);
        $preview = $previewWebDir . '/' . $filename;

        $dimensions = @getimagesize($previewTarget);        
      }

      // we save this file
      $result->save($persistentUpload);

      // lets create an info of this file
      $info = array(
        'id'            => $uuid,
        'size'          => $result->getSize(),
        'original_name' => $result->getOriginalName(),
        'preview'       => $preview,
        'mime'          => $result->getType(),
        'saved_name'    => $result->getSavedName(),
        'extension'     => $result->getExtension(),
        'dimensions'    => $dimensions ? $dimensions[0] . 'x' . $dimensions[1] : false,
      );

      file_put_contents($persistentUploadInfo, serialize($info));

      // dispatch event with information
      sfCore::getEventDispatcher()->notify(
        new sfEvent('form.validator.image.persistent.post_save', array(
          'info' => $info, 'file' => $result, 'persistent_info' => $persistentUploadInfo
      )));
      
    }

    return $result;
  }

  /**
   * Returns persistent directory absolute path
   *
   * @return string
   */
  public function getPersistentDir()
  {
    $dir = sfConfig::get('sf_data_dir') . DS . 'persistent_upload';
    if(!file_exists($dir))
    {
      if(!@mkdir($dir))
      {
        throw new sfException(sprintf('Unable to create "$dir" check permissions of parent directory or create this directory manually and make sure it is writable by the web server', $dir));
      }
    }
    return $dir;
  }

  /**
   * Returns absolute path to preview directory (/sf_web_dir/cache/preview)
   *
   * @return string
   */
  public function getPreviewDir()
  {
    $dir = sfConfig::get('sf_web_dir') . DS . 'cache' . DS . 'preview';
    if(!file_exists($dir))
    {
      if(!@mkdir($dir))
      {
        throw new sfException(sprintf('Unable to create "$dir" check permissions of parent directory or create this directory manually and make sure it is writable by the web server', $dir));
      }
    }
    return $dir;
  }

  /**
   * Returns web path (used as URL) to preview directory (/cache/preview)
   *
   * @return string
   */
  public function getPreviewWebDir()
  {
    $dir          = '/cache/preview';
    $relativeRoot = sfContext::getInstance()->getRequest()->getRelativeUrlRoot();
    return $relativeRoot . $dir;
  }

  /**
   * Deletes old uploaded files stored in persistent directory
   * 
   * @return boolean 
   */
  protected function deleteOldUploads()
  {
    // clear data files
    $persistentDir  = $this->getPersistentDir();    
    $files = sfFinder::type('file')->ignore_version_control()
              ->name('*.dat')->name('*.file')->in($persistentDir);
    $now = time();
    
    foreach($files as $file)
    {
      if($now - filemtime($file) >= $this->getOption('persistent_upload_lifetime'))
      {
        @unlink($file);
      }
    }
    
    // clear previews
    $previewDir = $this->getPreviewDir();
    $files      = sfFinder::type('file')->ignore_version_control()
                    ->name('*')->in($previewDir);
    
    foreach($files as $file)
    {
      if($now - filemtime($file) > $this->getOption('persistent_upload_lifetime'))
      {
        @unlink($file);
      }
    }

    return true;
  }

  /**
   * Creates image preview of the given filename
   *
   * @param string $file Path to file
   * @param string $mime Mimetype of the file
   * @param string $target Absolute path to file to be saved
   */
  protected function createPreview($file, $mime, $target)
  {
    $options = $this->getOption('create_preview_options');
    $image = new sfImage($file, $mime);
    $image->thumbnail($options['width'], $options['height'], $options['method']);
    $image->setQuality(isset($options['quality']) ? $options['quality'] : 80);
    $image->saveAs($target);
  }

  /**
   * @see sfValidatorBase
   */
  public function getActiveMessages()
  {
    $messages = parent::getActiveMessages();    
    $messages[] = $this->getMessage('too_small');
    $messages[] = $this->getMessage('too_large');
    return $messages;
  }
  
}
