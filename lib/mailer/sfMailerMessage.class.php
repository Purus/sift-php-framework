<?php
/*
 * This file is part of the Sift PHP framework
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerMessage class provides extensions for Swift_Message
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailerMessage extends Swift_Message {

  protected 
    $isQueued = false;
  
  public function  __construct($subject = null, $body = null, $contentType = null, $charset = null)
  {
    parent::__construct($subject, $body, $contentType, $charset);
    $this->initialize();
  }

  public function initialize()
  {
    // load configuration    
    $config = include(sfConfigCache::getInstance()->checkConfig('config/mail.yml'));
    
    $encode = isset($config['encode']) ? $config['encode'] : '8-bit';
    
    switch($encode)
    {
      case 'qp': // Quoted Printable
      case 'quoted_printable':
      case 'quoted-printable':
       $this->setEncoder(Swift_Encoding::getQpEncoding());
      break;

      case 'base_64':
      case 'base-64':
      case 'base64':
        $this->setEncoder(Swift_Encoding::getBase64Encoding());
      break;

      case '7_bit':
      case '7-bit':
      case '7bit':
        $this->setEncoder(Swift_Encoding::get7BitEncoding());
      break;

      // Fast!
      // see: http://forums.devnetwork.net/viewtopic.php?f=52&t=96933
      case '8_bit':
      case '8-bit':
      case '8bit':
        $this->setEncoder(Swift_Encoding::get8BitEncoding());
      break;
    }

    $charset = isset($config['charset']) ? $config['charset'] : sfConfig::get('sf_charset');
    // setup character set,
    // use the one set in mail.yml or symfony's default
    $this->setCharset($charset);

    // set maximum line length
    // Dont allow more than 1000 characters according to RFC 2822.
    // Doing so could have unspecified side-effects such as truncating
    // parts of your message when it is transported between SMTP servers.
    $lineLength = isset($config['line_length']) ? $config['line_length'] : 80;
    if($lineLength > 1000)
    {
      throw new sfConfigurationException(sprintf('{sfMailer} line_length setting has been misconfigured. Maximum is set to 1000. "%s" given', $lineLength));
    }
    $this->setMaxLineLength($lineLength);
    
    // allow to modify the message
    sfCore::getEventDispatcher()->notify(new sfEvent('mailer.message.configure', 
            array('message' => &$this)));    
  }

  /**
   * Create a new Attachment from a filesystem path.
   *
   * @param string $path
   * @param string $filename
   * @param string $contentType optional
   * @return Swift_Mime_Attachment
   */
  public function attachFromPath($path, $filename = null, $contentType = null, $disposition = null)
  {
    if(!sfToolkit::isPathAbsolute($path))
    {
      $path = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR
              . 'email' . DIRECTORY_SEPARATOR . 'files' .
              DIRECTORY_SEPARATOR . $path;
    }

    $attachment = Swift_Attachment::fromPath($path, $contentType);
    if($filename)
    {
      $attachment->setFilename($filename);
    }
    if($disposition)
    {
      $attachment->setDisposition($disposition);
    }
    return $this->attach($attachment);
  }

  /**
   * Attaches data 
   *
   * @param string $data
   * @param string $filename
   * @param string $mime
   * @return sfMailerMessage
   */
  public function attachData($data, $filename, $mime)
  {
    // create the attachment with your data
    $attachment = Swift_Attachment::newInstance($data, $filename, $mime);
    // attach it to the message
    return $this->attach($attachment);
  }

  /**
   * Alias method for embed()
   *
   * @param string $path
   * @return string CID of the embeded image
   */
  public function embedImage($path)
  {
    if(!sfToolkit::isPathAbsolute($path))
    {
      $path = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR
              . 'email' . DIRECTORY_SEPARATOR . 'images' . 
              DIRECTORY_SEPARATOR . $path;
    }
    
    if(!is_readable($path))
    {
      throw new sfException(sprintf('Invalid path "%s" given. Image is not readable or does not exist', $path));
    }

    return $this->embed(Swift_Image::fromPath($path));
  }

  /**
   * Attach data from $data and return it's CID source.
   * This method should be used when embedding images or other data in a message.
   *
   * @param $path
   * @return string
   */
  public function embedData($data, $filename = 'image.jpg', $mime = 'image/jpeg')
  {
    return $this->embed(Swift_Image::newInstance($data, $filename, $mime));
  }

  /**
   * Returns text body used for email part (plain or html)
   *
   * This methods renders a partial with the sfPartialMailView class.
   *
   * @return sfMailerMessage
   */
  public function setBodyFromPartial($partial, $vars = null, $type = 'plain')
  {
    // validate email type
    if(!in_array($type, array('plain', 'html')))
    {
      throw new sfConfigurationException(sprintf('Invalid email type passed ("%s"). Valid types are "plain" or "html".', $type));
    }

    if(is_null($vars))
    {
      $vars = array();
    }

    $vars['sf_email_type']      = $type;
    $vars['sf_mailer_message']  = &$this;
    
    sfLoader::loadHelpers('Partial');
    
    $body = get_partial($partial, $vars, 'sfPartialMail');

    return $this->addPart($body, $this->typeToContentType($type));
  }
  
  /**
   * Converts internal type "text" or "html" to 
   * content type "text/plain" and "text/html".
   * 
   * @param string
   * @return string
   */
  protected function typeToContentType($type)
  {
    $map = array(
      'plain' => 'text/plain',
      'html'  => 'text/html'  
    );    
    return isset($map[$type]) ? $map[$type] : $type;
  }
  
  /**
   * Gets the plaintext body
   * 
   * @return string
   */
  public function getPlaintextBody()
  {
    $children = $this->getChildren();
    
    foreach($children as $child)
    {
      if ($child->getContentType() == 'text/plain')
      {
        return $child->getBody();
      }
    }
  }
  
  /**
   * Gets the html body
   * 
   * It is the main body per our convention
   * 
   * @return string
   */
  public function getHtmlBody()
  {
    $children = $this->getChildren();    
    foreach($children as $child)
    {
      if ($child->getContentType() == 'text/html')
      {
        return $child->getBody();
      }
    }
    return false;
  }
  
  /**
   * Set plaintext body
   * 
   * @param string $body
   * 
   * @return sfMailerMessage
   */
  public function setPlaintextBody($body)
  {
    $children = $this->getChildren();
    
    $isFound = false;
    
    foreach($children as $child)
    {
      if ($child->getContentType() == 'text/plain')
      {
        $child->setBody($body);
        $isFound = true;
        
        break;
      }
    }    
    
    if (!$isFound)
    {
      $this->addPart($body, 'text/plain');
    }
    
    return $this;
  }  
  
  /**
   * Sets the html body
   * 
   * @param string $body
   * 
   * @return self
   */
  public function setHtmlBody($body)
  {
    $this->setBody($body, 'text/html');    
    return $this;
  }
  
  /**
   * 
   * @param boolean $boolean
   * @return self
   */
  public function setIsQueued($boolean)
  {
    $this->isQueued = (boolean) $boolean;
    
    return $this;
  }

  /**
   * 
   * @return boolean
   */
  public function getIsQueued()
  {
    return (boolean) $this->isQueued;
  }
  
  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = sfCore::getEventDispatcher()->notifyUntil(
                    new sfEvent('mailer.message.method_not_found', array(
                        'method'      => $method,
                        'arguments'   => $arguments,
                        'message'     => $this)));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
  
}
