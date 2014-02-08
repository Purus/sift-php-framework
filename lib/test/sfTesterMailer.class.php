<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTesterMailer implements tests for the Sift mailer object.
 *
 * @package    Sift
 * @subpackage test
 */
class sfTesterMailer extends sfTester {

  protected $logger = null,
      $message = null;

  /**
   * Prepares the tester.
   */
  public function prepare()
  {
  }

  /**
   * Initializes the tester.
   */
  public function initialize()
  {
    $this->logger = $this->browser->getContext()->getMailer()->getLogger();

    if($this->logger->countMessages())
    {
      $messages = $this->logger->getMessages();

      $this->message = $messages[0];
    }
  }

  /**
   * Tests if message was send and optional how many.
   *
   * @param int $nb number of messages
   *
   * @return sfTestFunctionalBase|sfTester
   */
  public function hasSent($nb = null)
  {
    if(null === $nb)
    {
      $this->tester->ok($this->logger->countMessages() > 0, 'mailer sent some email(s).');
    }
    else
    {
      $this->tester->is($this->logger->countMessages(), $nb, sprintf('mailer sent %s email(s).', $nb));
    }

    return $this->getObjectToReturn();
  }

  /**
   * Outputs some debug information about mails sent during the current request.
   */
  public function debug()
  {
    foreach($this->logger->getMessages() as $message)
    {
      echo $message->toString() . "\n\n";
    }

    return $this->getObjectToReturn();
  }

  /**
   * Changes the context to use the email corresponding to the given criteria.
   *
   * @param string|array $to       the email or array(email => alias)
   * @param int          $position address position
   *
   * @return sfTestFunctionalBase|sfTester
   */
  public function withMessage($to, $position = 1)
  {
    $messageEmail = $to;
    if(is_array($to))
    {
      $alias = current($to);
      $to = key($to);
      $messageEmail = sprintf('%s <%s>', $alias, $to);
    }

    $matches = 0;
    foreach($this->logger->getMessages() as $message)
    {
      $email = $message->getTo();
      if($to == key($email))
      {
        $matches++;

        if($matches == $position)
        {
          $this->message = $message;

          if(isset($alias) AND $alias != current($email))
          {
            break;
          }

          $this->tester->pass(sprintf('switch context to the message number "%s" sent to "%s"', $position, $messageEmail));

          return $this;
        }
      }
    }

    $this->tester->fail(sprintf('unable to find a message sent to "%s"', $messageEmail));

    return $this;
  }

  /**
   * Tests for a mail message body.
   *
   * @param string $value regular expression or value
   * @return sfTestFunctionalBase|sfTester
   */
  public function checkBody($value, $type = 'plain')
  {
    if(!$this->message)
    {
      $this->tester->fail('unable to test as no email were sent');
    }

    switch($type)
    {
      case 'plain':
        $this->checkBodyPart($value, $this->message->getPlainTextBody());
      break;

      case 'html':
        $this->checkBodyPart($value, $this->message->getHtmlBody());
      break;

      default:
        throw new InvalidArgumentException(sprintf('Invalid body type "%s" given', $type));
      break;
    }

    return $this->getObjectToReturn();
  }

  /**
   * Checks the body (within multiparts of the email message)
   *
   * @param string $value regular expression or value
   * @param string $contentType The content type to search
   * @param boolean $matchFirst If the test should be performed on the first multipart with given content type
   * @return sfTestFunctionalBase|sfTester
   */
  public function checkBodyMultipart($value, $contentType = 'text/plain', $matchFirst = true)
  {
    $tested = false;
    foreach($this->message->getChildren() as $key => $child)
    {
      if($contentType === $child->getHeaders()->get('content-type')->getValue())
      {
        $tested = true;

        $this->checkBodyPart($value, $child->getBody());
        if($matchFirst)
        {
          break;
        }
      }
    }

    if(!$tested)
    {
      $this->tester->fail(sprintf('there is no multipart for given contentType "%s" which should match "%s"', $contentType, $value));
    }

    return $this->getObjectToReturn();
  }

  /**
   * Checks if the mail message has the attachment
   *
   * @param string $attachmentName The attachment file name
   * @param string $contentType The content type
   * @return sfTestFunctionalBase|sfTester
   */
  public function hasAttachment($attachmentName, $contentType = null)
  {
    foreach($this->message->getChildren() as $child)
    {
      if(!$child instanceof Swift_Mime_Attachment)
      {
        continue;
      }

      if($child->getFilename() == $attachmentName)
      {
        if($contentType)
        {
          if($child->getHeaders()->get('content-type')->getValue() != $contentType)
          {
            $this->tester->fail(sprintf('attachment "%s" does not have the right content type "%s"', $attachmentName, $contentType));
          }
          else
          {
            $this->tester->pass(sprintf('attachment "%s" does have the right content type "%s"', $attachmentName, $contentType));
          }
        }
        else
        {
          $this->tester->pass(sprintf('attachment "%s" is attached to the message', $attachmentName));
        }
        return $this->getObjectToReturn();
      }
    }

    $this->tester->fail(sprintf('attachment "%s" is missing', $attachmentName));

    return $this->getObjectToReturn();
  }

  /**
   * Performs checks on the body
   *
   * @param string $value regular expression or value
   * @param string $body The body to search
   */
  protected function checkBodyPart($value, $body)
  {
    $ok = false;
    $regex = false;
    $mustMatch = true;
    if(preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $value, $match))
    {
      $regex = $value;
      if($match[1] == '!')
      {
        $mustMatch = false;
        $regex = substr($value, 1);
      }
    }

    if(false !== $regex)
    {
      if($mustMatch)
      {
        if(preg_match($regex, $body))
        {
          $ok = true;
          $this->tester->pass(sprintf('email body matches "%s"', $value));
        }
      }
      else
      {
        if(preg_match($regex, $body))
        {
          $ok = true;
          $this->tester->fail(sprintf('email body does not match "%s"', $value));
        }
      }
    }
    else if($body == $value)
    {
      $ok = true;
      $this->tester->pass(sprintf('email body is "%s"', $value));
    }

    if(!$ok)
    {
      if(!$mustMatch)
      {
        $this->tester->pass(sprintf('email body matches "%s"', $value));
      }
      else
      {
        $this->tester->fail(sprintf('email body matches "%s"', $value));
      }
    }
  }

  /**
   * Tests for a mail message header.
   *
   * @param string $key   entry to test
   * @param string $value regular expression or value
   *
   * @return sfTestFunctionalBase|sfTester
   */
  public function checkHeader($key, $value)
  {
    if(!$this->message)
    {
      $this->tester->fail('unable to test as no email were sent');
    }

    $headers = array();
    foreach($this->message->getHeaders()->getAll($key) as $header)
    {
      $headers[] = $header->getFieldBody();
    }
    $current = implode(', ', $headers);
    $ok = false;
    $regex = false;
    $mustMatch = true;
    if(preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $value, $match))
    {
      $regex = $value;
      if($match[1] == '!')
      {
        $mustMatch = false;
        $regex = substr($value, 1);
      }
    }

    foreach($headers as $header)
    {
      if(false !== $regex)
      {
        if($mustMatch)
        {
          if(preg_match($regex, $header))
          {
            $ok = true;
            $this->tester->pass(sprintf('email header "%s" matches "%s" (%s)', $key, $value, $current));
            break;
          }
        }
        else
        {
          if(preg_match($regex, $header))
          {
            $ok = true;
            $this->tester->fail(sprintf('email header "%s" does not match "%s" (%s)', $key, $value, $current));
            break;
          }
        }
      }
      else if($header == $value)
      {
        $ok = true;
        $this->tester->pass(sprintf('email header "%s" is "%s" (%s)', $key, $value, $current));
        break;
      }
    }

    if(!$ok)
    {
      if(!$mustMatch)
      {
        $this->tester->pass(sprintf('email header "%s" matches "%s" (%s)', $key, $value, $current));
      }
      else
      {
        $this->tester->fail(sprintf('email header "%s" matches "%s" (%s)', $key, $value, $current));
      }
    }

    return $this->getObjectToReturn();
  }

}
