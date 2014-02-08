<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// we need it here so the unserialize works ok
require_once dirname(__FILE__).'/../../vendor/swift/swift_required.php';
require_once dirname(__FILE__).'/../../vendor/swift/swift_init.php';

/**
 * sfMailerMessage class provides extensions for Swift_Message
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailerMessage extends Swift_Message
{
  /**
   * Inline attachment
   */
  const DISPOSITION_INLINE = 'inline';

  /**
   * Attachment
   */
  const DISPOSITION_ATTACHMENT = 'attachment';

  /**
   * Plain type (text/plain)
   *
   */
  const TYPE_PLAIN = 'plain';

  /**
   * HTML type (text/html)
   *
   */
  const TYPE_HTML = 'html';

  /**
   * Type to content type map
   *
   * @var array
   */
  protected $typeToContentTypeMap = array(
    self::TYPE_PLAIN => 'text/plain',
    self::TYPE_HTML => 'text/html'
  );

  /**
   * Is the message queued?
   *
   * @var boolean
   */
  protected $isQueued = false;

  /**
   * Message encoding
   *
   * @var string
   */
  protected $encoding = '8-bit';

  /**
   * Line length
   *
   * @var integer
   */
  protected $lineLength = 80;

  /**
   * Event dispatcher
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * File data directory
   *
   * @var string
   */
  protected $fileDataPath;

  /**
   * Main content type
   *
   * @var string
   */
  protected $mainContentType;

  /**
   * Constructs the message
   *
   * @param sfEventDispatcher $dispatcher
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @param string $encoding
   * @param integer $maxLineLength
   * @throws InvalidArgumentException
   */
  public function __construct(sfEventDispatcher $dispatcher, $subject = null, $body = null,
      $contentType = null, $charset = null,
      $encoding = '8bit', $maxLineLength = 80, $fileDataPath = null)
  {
    // Don't allow more than 1000 characters according to RFC 2822.
    // Doing so could have unspecified side-effects such as truncating
    // parts of your message when it is transported between SMTP servers.
    if ($maxLineLength > 1000) {
      throw new InvalidArgumentException(sprintf('Maximum message line length is 1000. "%s" given', $maxLineLength));
    }

    $this->fileDataPath = $fileDataPath;
    $this->dispatcher = $dispatcher;

    $this->mainContentType = $contentType ? $contentType : 'text/plain';

    // pass to parent
    parent::__construct($subject, $body, $contentType, $charset);

    $this->setEncoding($encoding);
    $this->setMaxLineLength($maxLineLength);

    $this->dispatcher->notify(new sfEvent('mailer.message.configure',
        array('message' => $this)));
  }

  /**
   * Sets the message encoding (qp, base64, 7bit, 8bit)
   *
   * @param string $encoding
   * @return sfMailerMessage
   * @throws InvalidArgumentException If the encoding is not valid
   */
  public function setEncoding($encoding)
  {
    switch ($encoding) {
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

      default:
        throw new InvalidArgumentException(sprintf('Invalid encoding "%s" given.', $encoding));
      break;
    }

    $this->encoding = $encoding;

    return $this;
  }

  /**
   * Returns the encoding (from encoder)
   *
   * @return string
   */
  public function getEncoding()
  {
    return $this->getEncoder()->getName();
  }

  /**
   * Create a new Attachment from a filesystem path.
   *
   * @param string $path The path to the file (is relative, fileDataPath is searched for the file)
   * @param string $filename
   * @param string $contentType optional
   * @return Swift_Mime_Attachment
   */
  public function attachFromPath($path, $filename = null, $contentType = null, $disposition = null)
  {
    if (!sfToolkit::isPathAbsolute($path)) {
      $path = $this->fileDataPath . '/' . $path;
    }

    if (!is_readable($path)) {
      throw new sfException(sprintf('Invalid path "%s" given. Image is not readable or does not exist', $path));
    }

    $attachment = Swift_Attachment::fromPath($path, $contentType);

    if ($filename) {
      $attachment->setFilename($filename);
    }

    if ($disposition) {
      $attachment->setDisposition($disposition);
    }

    return $this->attach($attachment);
  }

  /**
   * Attaches data
   *
   * @param string|sfCallable $data The data to attach
   * @param string $filename
   * @param string $contentType
   * @param string $disposition The attachment disposition
   * @param string $description The attachment description
   * @return sfMailerMessage
   */
  public function attachData($data, $filename = null, $contentType = null,
      $disposition = self::DISPOSITION_ATTACHMENT, $description = null)
  {
    if ($data instanceof sfCallable) {
      $data = $data->call();
      if (is_array($data)) {
        $contentType = isset($data[1]) ? $data[1] : null;
        $data = $data[0];
      }
    }

    // create the attachment with your data
    $attachment = Swift_Attachment::newInstance($data, $filename, $contentType);

    if (!self::isValidDisposition($disposition)) {
      throw new InvalidArgumentException(sprintf('Invalid attachment disposition "%s" given.', $disposition));
    }

    $attachment->setDisposition($disposition);

    if ($description) {
      $attachment->setDescription($description);
    }

    // attach it to the message
    return $this->attach($attachment);
  }

  /**
   * Validates the disposition
   *
   * @param string $disposition
   * @return boolean
   */
  public static function isValidDisposition($disposition)
  {
    if(in_array(strtolower($disposition), array(
        self::DISPOSITION_ATTACHMENT,
        self::DISPOSITION_INLINE
    )))
    {
      return true;
    }

    return false;
  }

  /**
   * Set the body of this entity, either as a string, or as an instance of
   * {@link Swift_OutputByteStream}.
   *
   * @param mixed  $body
   * @param string $contentType optional
   * @param string $charset     optional
   *
   * @return sfMailerMessage
   */
  public function setBody($body, $contentType = null, $charset = null)
  {
    parent::setBody($body, $contentType, $charset);

    return $this;
  }

  /**
   * Alias method for embed()
   *
   * @param string $path
   * @return string CID of the embeded image
   */
  public function embedImage($path)
  {
    if (!sfToolkit::isPathAbsolute($path)) {
      $path = $this->fileDataPath . '/' . $path;
    }

    if (!is_readable($path)) {
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
  public function setBodyFromPartial($partial, $vars = null, $type = self::TYPE_PLAIN)
  {
    // validate email type
    if (!in_array($type, array(self::TYPE_PLAIN, self::TYPE_HTML))) {
      throw new sfConfigurationException(sprintf('Invalid email type passed ("%s"). Valid types are "plain" or "html".', $type));
    }

    if (is_null($vars)) {
      $vars = array();
    }

    $vars['sf_email_type'] = $type;
    $vars['sf_mailer_message'] = $this;

    $this->loadPartialHelpers();

    $body = get_partial($partial, $vars, 'sfPartialMail');

    switch ($type) {
      case self::TYPE_PLAIN:
        return $this->setPlaintextBody($body);

      case self::TYPE_HTML:
        return $this->setHtmlBody($body);
    }

    return $this;
  }

  /**
   * Loads partial helpers
   *
   * @return sfMailerMessage
   */
  protected function loadPartialHelpers()
  {
    sfLoader::loadHelpers(array('Partial'));

    return $this;
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
    return isset($this->typeToContentTypeMap[$type]) ? $this->typeToContentTypeMap[$type] : $type;
  }

  /**
   * Gets the plain text body
   *
   * @return string|null
   */
  public function getPlaintextBody()
  {
    // this checks if the object has been created with HTML type
    // main type is not plain, so we need to search the children
    if (!$this->mainContentType == 'text/plain') {
      foreach ($this->getChildren() as $child) {
        if ($child->getContentType() == 'text/plain') {
          return $child->getBody();
        }
      }
    } else {
      return $this->getBody();
    }
  }

  /**
   * Has the message plain text body?
   *
   * @return boolean
   */
  public function hasPlainTextBody()
  {
    return !!($this->getPlaintextBody());
  }

  /**
   * Gets the html body. It is the main body per our convention
   *
   * @return string
   */
  public function getHtmlBody()
  {
    if (!$this->mainContentType == 'text/plain') {
      return $this->getBody();
    }

    foreach ($this->getChildren() as $child) {
      if ($child->getContentType() == 'text/html') {
        return $child->getBody();
      }
    }
  }

  /**
   * Has this message HTML body?
   *
   * @return boolean
   */
  public function hasHtmlBody()
  {
    return !!$this->getHtmlBody();
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
    if (!$this->mainContentType == 'text/plain') {
      $found = false;
      foreach ($this->getChildren() as $child) {
        if ($child->getContentType() == 'text/plain') {
          $found = true;
          $child->setBody($body);
          break;
        }
      }
      if (!$found) {
        $this->addPart($body, 'text/plain');
      }
    } else {
      $this->setBody($body, 'text/plain');
    }

    return $this;
  }

  /**
   * Sets the html body
   *
   * @param string $body The html code
   * @return sfMailerMessage
   */
  public function setHtmlBody($body)
  {
    if ($this->mainContentType == 'text/plain') {
      $found = false;
      foreach ($this->getChildren() as $child) {
        if ($child->getContentType() == 'text/html') {
          $found = true;
          $child->setBody($body);
          break;
        }
      }
      if (!$found) {
        $this->addPart($body, 'text/html');
      }
    } else {
      $this->setBody($body, 'text/html');
    }

    return $this;
  }

  /**
   * Sets queued flag
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
   * Gets queued flag
   *
   * @return boolean
   */
  public function getIsQueued()
  {
    return (boolean) $this->isQueued;
  }

  /**
   * Returns the event dispatcher
   *
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
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
    $event = $this->dispatcher->notifyUntil(
                    new sfEvent('mailer.message.method_not_found', array(
                        'method'      => $method,
                        'arguments'   => $arguments,
                        'message'     => $this)));

    if (!$event->isProcessed()) {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}
