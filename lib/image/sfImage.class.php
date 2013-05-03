<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * sfImage class.
 *
 * A container class for the image resource.
 *
 * This class allows the manipulation of sfImage using sub classes of the abstract sfImageTranform class.
 *
 * Example 1 Chaining
 *
 * <code>
 * <?php
 * $img = new sfImage('image1.jpg', 'image/png', 'GD');
 * $response = $this->getResponse();
 * $response->setContentType($img->getMIMEType());
 * $response->setContent($img->resize(1000,null)->overlay(sfImage('logo.png','','')));
 * ?>
 * </code>
 *
 * Example 2 Standalone
 * <code>

 * $img = new sfImage('image1.jpg', 'image/jpg', 'ImageMagick');
 * $t = new sfImageScale(0.5);
 * $img = $t->execute($img);
 * $img->save('image2.jpg', 'image/jpg');
 * </code>
 *
 * @package Sift
 * @subpackage image
 */
class sfImage {

  /**
   * Empty gif string
   *
   * @link http://emptygif.com/2010/10/29/php-echo/
   */
  const EMPTY_GIF = "\107\111\106\70\71\141\001\000\001\000\360\001\000\377\377\377\000\000\000\041\371\004\001\012\000\000\000\054\000\000\000\000\001\000\001\000\000\002\002\104\001\000\073";

  /**
   * The adapter class.
   * @access protected
   * @var object
   */
  protected $adapter;

  /*
   * MIME type map and their associated file extension(s)
   * @var array
   */
  protected $types = array(
      'image/gif' => array('gif'),
      'image/jpeg' => array('jpg', 'jpeg'),
      'image/png' => array('png'),
      'image/svg' => array('svg'),
      'image/tiff' => array('tiff')
  );

  /**
   * Prevents multiple tries of orientation fix based on EXIF data
   *
   * @var boolean
   * @see fixOrientation()
   */
  protected $orientationFixed = false;

  /**
   * Construct an sfImage object.
   * @access public
   * @param string Filename of the image to be loaded
   * @param string File MIME type
   * @param string Name of a supported adapter
   * @param boolen Fix orientation?
   */
  public function __construct($filename = '', $mime = '', $adapter = '', $fixOrientation = true)
  {
    $this->setAdapter($this->createAdapter($adapter));

    // Set the Image source if passed
    if($filename !== '')
    {
      $this->load($filename, $mime, $fixOrientation);
    }
    // Otherwise create a new blank image
    else
    {
      $this->create(null, null, null, $mime);
    }
  }

  /**
   * Gets the image library adapter object
   * @access public
   * @param object
   */
  public function getAdapter()
  {
    return $this->adapter;
  }

  /**
   * Sets the adapter to be used, i.e. GD or ImageMagick
   *
   * @access public
   * @param object $adapter Instance of adapter object to be used
   */
  public function setAdapter($adapter)
  {
    if(is_object($adapter))
    {
      $this->adapter = $adapter;

      return true;
    }
    return false;
  }

  /**
   * Creates a blank image
   *
   * Default is GD but the adapter can be set by app_sfImageTransformPlugin_adapter
   *
   * @access public
   * @param integer Width of image
   * @param integer Height of image
   * @param string Backfground color of image
   */
  public function create($x = null, $y = null, $color = null)
  {
    $defaults = sfConfig::get('sf_image_default_image',
            array('filename' => 'untitled.png',
                  'mime_type' => 'image/png',
                  'width' => 100, 'height' => 100)
            );

    // Get default width
    if(!is_numeric($x))
    {
      $x = 1;
      if(isset($defaults['width']))
      {
        $x = (int) $defaults['width'];
      }
    }

    // Get default height
    if(!is_numeric($y))
    {
      $y = 1;
      if(isset($defaults['height']))
      {
        $y = (int) $defaults['height'];
      }
    }

    $this->getAdapter()->create($x, $y);
    $this->getAdapter()->setFilename($defaults['filename']);

    if(isset($defaults['mime_type']))
    {
      $this->setMIMEType($defaults['mime_type']);
    }

    // Set the image color if set
    if(is_null($color))
    {
      $color = '#ffffff';
      if(isset($defaults['color']))
      {
        $color = $defaults['color'];
      }
    }

    $this->fill(0, 0, $color);

    return $this;
  }

  /**
   * Loads image from disk
   *
   * Loads an image of specified MIME type from the filesystem
   *
   * @access public
   * @param string Name of image file
   * @param string MIME type of image
   * @param boolean Fix orientation based on EXIF data?
   * @return sfImage
   */
  public function load($filename, $mime = '', $fixOrientation = true)
  {
    if(file_exists($filename) && is_readable($filename))
    {
      if('' == $mime)
      {
        $mime = $this->autoDetectMIMETypeFromFile($filename);
      }
      else
      {
        $mime = $this->fixMIMEType($mime);
      }

      if('' == $mime)
      {
        throw new sfImageTransformException(sprintf('Mime type of the file "%s" not specified nor detected.', $filename));
      }

      $this->getAdapter()->load($filename, $mime);

      if($fixOrientation)
      {
        $this->fixOrientation();
      }

      return $this;
    }

    throw new sfImageTransformException(sprintf('Unable to load %s. File does not exist or is unreadable', $filename));
  }

  /**
   * Loads image from a string
   *
   * Loads the image from a string
   *
   * @access public
   * @param string Image string
   * @preturn sfImage
   */
  public function loadString($string)
  {
    $this->getAdapter()->loadString($string);

    return $this;
  }

  /**
   * Saves the image to disk
   *
   * Saves the image to the filesystem
   *
   * @access public
   * @param string
   * @return boolean
   */
  public function save()
  {
    return $this->getAdapter()->save();
  }

  /**
   * Saves the image to the specified filename
   *
   * Allows the saving to a different filename
   *
   * @access public
   * @param string Filename
   * @param string MIME type
   * @return sfImage
   */
  public function saveAs($filename, $mime = '')
  {
    if('' === $mime)
    {
      // $mime = $this->autoDetectMIMETypeFromFilename($filename);
      $mime = $this->getMIMEType();
    }

    if(!$mime)
    {
      throw new sfImageTransformException(sprintf('Unsupported file %s', $filename));
    }

    $copy = $this->copy();

    $copy->getAdapter()->saveAs($filename, $mime);

    return $copy;
  }

  /**
   * Copies the image object and returns it
   *
   * Returns a copy of the sfImage object
   *
   * @access public
   * @return sfImage
   */
  public function copy()
  {
    $copy = clone $this;
    $copy->setAdapter($this->getAdapter()->copy());

    return $copy;
  }

  /**
   * Magic method. Converts the image to a string
   *
   * Returns the image as a string
   *
   * @access public
   * @return string
   */
  public function __toString()
  {
    return $this->toString();
  }

  /**
   * Converts the image to a string
   *
   * Returns the image as a string
   *
   * @access public
   * @return string
   */
  public function toString()
  {
    return (string) $this->getAdapter();
  }

  /**
   * Tries to fix image orientation based on EXIF data
   *
   * @link http://www.impulseadventure.com/photo/exif-orientation.html
   * @link http://stackoverflow.com/questions/4266656/how-to-stop-php-imagick-auto-rotating-images-based-on-exif-orientation-data
   * @link http://recursive-design.com/blog/2012/07/28/exif-orientation-handling-is-a-ghetto/
   */
  public function fixOrientation()
  {
    // orientation already fixed
    if($this->orientationFixed)
    {
      return $this;
    }

    if(!($src = $this->getFilename()))
    {
      return $this;
    }

    // we will use exif
    try
    {
      $exif = new sfExif();
      $data = $exif->getData($src);

      // do nothing, we don't have any data
      if(!isset($data['Orientation']))
      {
        // throw exception which will be catched later
        throw new Exception('No orientation data present.');
      }
      $orientation = $data['Orientation'];
    }
    catch(Exception $e)
    {
      // set to prevent multiple tries
      $this->orientationFixed = true;
      return;
    }

    switch($orientation)
    {
      case 1:
        // 1 is ok!
        break;

      case 2:
        $result = $this->mirror();
        break;

      case 3:
        $result = $this->flip()->mirror();
        break;

      case 4:
        $result = $this->flip();
        break;

      case 5:
        $result = $this->mirror()->rotate(90);
        break;

      case 6: // rotate 90 degrees CCW
        $result = $this->rotate(-90);
        break;

      case 7:
        $result = $this->flip()->rotate(90);
        break;

      case 8: // rotate 90 degrees CW
        $result = $this->rotate(90);
        break;
    }

    // prevents multiple tries to fix
    $this->orientationFixed = true;

    return isset($result) ? $result : $this;
  }

  /**
   * Magic method. This allows the calling of execute tranform methods on sfImageTranform objects.
   *
   * @method
   * @param string $name the name of the transform, sfImage<NAME>
   * @param array Arguments for the transform class execute method
   * @return sfImage
   */
  public function __call($name, $arguments)
  {
    $class_generic = 'sfImage' . ucfirst($name) . 'Generic';
    $class_adapter = 'sfImage' . ucfirst($name) . $this->getAdapter()->getAdapterName();

    $class = null;

    // Make sure a transform class exists, either generic or adapter specific, otherwise throw an exception
    // Defaults to adapter transform
    if(class_exists($class_adapter, true))
    {
      $class = $class_adapter;
    }

    // No adapter specific transform so look for a generic transform
    elseif(class_exists($class_generic, true))
    {
      $class = $class_generic;
    }

    // Cannot find the transform class so throw an exception
    else
    {
      throw new sfImageTransformException(sprintf('Unsupported transform %s. Cannot find %s adapter or generic transform class', $name, $this->getAdapter()->getAdapterName()));
    }

    $reflectionObj = new ReflectionClass($class);
    if(is_array($arguments) && count($arguments) > 0)
    {
      $transform = $reflectionObj->newInstanceArgs($arguments);
    }
    else
    {
      $transform = $reflectionObj->newInstance();
    }

    $transform->execute($this);

    // Tidy up
    unset($transform);

    // So we can chain transforms return the reference to itself
    return $this;
  }

  /**
   * Sets the image filename
   * @param string
   * @return boolean
   */
  public function setFilename($filename)
  {
    return $this->getAdapter()->setFilename($filename);
  }

  /**
   * Returns the image full filename
   * @return string The filename of the image
   *
   */
  public function getFilename()
  {
    return $this->getAdapter()->getFilename();
  }

  /**
   * Returns the image pixel width
   * @return integer
   *
   */
  public function getWidth()
  {
    return $this->getAdapter()->getWidth();
  }

  /**
   * Returns the image height
   * @return integer
   *
   */
  public function getHeight()
  {
    return $this->getAdapter()->getHeight();
  }

  /**
   * Sets the MIME type
   * @param string
   *
   */
  public function setMIMEType($mime)
  {
    $this->getAdapter()->setMIMEType($mime);
  }

  /**
   * Returns the MIME type
   * @return string
   *
   */
  public function getMIMEType()
  {
    return $this->getAdapter()->getMIMEType();
  }

  /**
   * Sets the image quality
   *
   * @param integer Valid range is from 0 (worst) to 100 (best)
   * @return sfImage
   */
  public function setQuality($quality)
  {
    $this->getAdapter()->setQuality($quality);
    return $this;
  }

  /**
   * Returns the image quality
   * @return string
   *
   */
  public function getQuality()
  {
    return $this->getAdapter()->getQuality();
  }

  /**
   * Returns mime type from the actual file using a detection library
   * @access protected
   * @return string or boolean
   */
  protected function autoDetectMIMETypeFromFile($filename)
  {
    return sfMimeType::getTypeFromFile($filename);
  }

  /**
   * Fixes mimetype
   *
   * @param string $mimeType
   */
  protected function fixMIMEType($mimeType)
  {
    switch($mimeType)
    {
      case 'image/pjpeg': return 'image/jpeg';
    }

    return $mimeType;
  }

  /**
   * Returns a adapter class of the specified type
   * @access protected
   * @return string or boolean
   */
  protected function createAdapter($name)
  {
    // No adapter set so use default
    if($name == '')
    {
      $name = sfConfig::get('sf_image_default_adapter', 'GD');
    }

    $adapter_class = 'sfImageTransform' . $name . 'Adapter';

    if(class_exists($adapter_class))
    {
      $adapter = new $adapter_class;
    }

    // Cannot find the adapter class so throw an exception
    else
    {
      throw new sfImageTransformException(sprintf('Unsupported adapter: %s', $adapter_class));
    }

    return $adapter;
  }

  /**
   * Copies the image object and returns it
   *
   * Returns a copy of the sfImage object
   *
   * @return sfImage
   */
  public function __clone()
  {
    $this->adapter = $this->adapter->copy();
  }

  /**
   * Returns image colors
   *
   * @param integer $max
   * @param integer $threshold Percentage that colour needs to reach of total pixels for colour to be considered significant
   * @param sfColorPalette $palette Color pallete
   * @return array Array of colors
   */
  public function getColors($max = false, $threshold = 1, $granularity = 5, sfColorPalette $palette = null)
  {
    $colors = array();
    $width = $this->getWidth();
    $height = $this->getHeight();

    // image granularity
    $granularity = max(1, abs((int) $granularity));

    // loop through x axis
    for($x = 0; $x < $width; $x += $granularity)
    {
      // loop through y axis
      for($y = 0; $y < $height; $y += $granularity)
      {
        $color = $this->getAdapter()->getRGBFromPixel($x, $y);
        // we are using palette
        if($palette)
        {
          list($red, $green, $blue) = array_values($palette->getClosestColor($color)->toRgbInt());
        }
        else
        {
          list($red, $green, $blue) = $color;
          // rounds to color value
          $red = round(round(($red / 0x33)) * 0x33);
          $green = round(round(($green / 0x33)) * 0x33);
          $blue = round(round(($blue / 0x33)) * 0x33);
        }

        $colorHex = sprintf('%02x%02x%02x', $red, $green, $blue);

        isset($colors[$colorHex]) ? $colors[$colorHex]++ : $colors[$colorHex] = 0;
      }
    }

    arsort($colors);

    $result = array();
    $i = 0;

    // how many pixels have been checked? This is 100% value so the percentage is ok
    $pixels = $this->getTotalPixels() / pow($granularity, 2);

    // build the return array of the top results
    foreach($colors as $color => $count)
    {
      if(round($count / $pixels * 100, 2) > $threshold)
      {
        $result[$color] = round(($count / $pixels) * 100, 5);
        $i++;
      }
      if($max && $i >= $max)
      {
        break;
      }
    }

    // we have to filter our colors which are not grayscale
    // since we used closest color from palette
    if($palette && $this->isGrayscale())
    {
      foreach($result as $_color => $percentage)
      {
        $color = new sfColor($_color);
        if(!$color->isGrayscale())
        {
          // remove the color!
          unset($result[$_color]);
        }
      }
    }

    return $result;
  }

  /**
   * Returns number of pixels in the image.
   *
   * @return integer
   */
  public function getTotalPixels()
  {
    return $this->getWidth() * $this->getHeight();
  }

  /**
   * Checks if image is grayscale image by picking $toCheck pixels and checking
   * if those pixels colors are grayscale.
   *
   * @see http://www.autoitscript.com/forum/topic/120313-check-if-an-image-is-grayscale-or-not/
   * @param integer $toCheck Number of pixels to check (100 is default)
   * @return boolean true if image is considered as grayscale image, false otherwise
   */
  public function isGrayscale($toCheck = 100)
  {
    $totalPixels = $this->getTotalPixels();
    if($toCheck > $totalPixels)
    {
      $toCheck = $totalPixels;
    }

    $isGrayscale = true;
    $width = $this->getWidth();
    $height = $this->getHeight();
    // now check out the pixels
    for($i = 0; $i < $toCheck && $isGrayscale; $i++)
    {
      $randX = rand(0, $width - 1);
      $randY = rand(0, $height - 1);
      list($red, $green, $blue) = $this->getAdapter()->getRGBFromPixel($randX, $randY);
      // if one of the pixels isn't grayscale it breaks an you know this is a color picture
      if($red != $green || $green != $blue)
      {
        $isGrayscale = false;
        break;
      }
      else
      {
        $isGrayscale = true;
      }
    }
    return $isGrayscale;
  }

  /**
   * Returns average color of the image. Simply resizes the copy
   * of this image to 1x1 pixel and returns its color.
   *
   * @return sfColor
   */
  public function getAverageColor()
  {
    $copy = $this->copy()->resize(1, 1);
    list($red, $green, $blue) = $copy->getAdapter()->getRGBFromPixel(0, 0);
    return new sfColor(array($red, $green, $blue));
  }

}
