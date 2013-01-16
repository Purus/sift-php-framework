<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfEmailLogger logs messages and send it via email.
 *
 * @package    Sift
 * @subpackage log
 * @author     Gordon Franke <gfranke@nevalon.de>
 * @link       http://www.nevalon.de
 */
class sfEmailLogger extends sfLogger
{
  /**
   * Default options
   * 
   * @var array 
   */
  protected $defaultOptions = array(
    'type' => 'Sift',
    'format' => "%time% %type% [%priority%] %message%\n",
    'time_format' => '%b %d %H:%M:%S',
    'subject'    => '',
    'recipients' => array(),
    'sender_email' => '',
  );
  
  /**
   * Log flag
   * 
   * @var boolean 
   */
  protected $log = false;
  
  /**
   * Mail body holder
   * 
   * @var string 
   */
  protected $body = '';

  /**
   * Initializes this logger.
   *
   * Available options:
   *
   * - emails:        The emails to be send the log messages
   * - subject:       The subject for the email
   * - include_level: Use this to get more detailed information
   * - format:        The log line format (default to %time% %type% [%priority%] %message%%EOL%)
   * - time_format:   The log time strftime format (default to %b %d %H:%M:%S)
   *
   * @param  array             $options     An array of options.
   *
   * @return Boolean      true, if initialization completes successfully, otherwise false.
   */
  public function initialize($options = array())
  {
    if(!$this->getOption('recipients'))
    {
      throw new sfConfigurationException('You must provide a "recipients" parameter for this logger.');
    }
    
    // subject is empty, construct some nice subject :)
    if(!$this->getOption('subject'))
    {
      $subject = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
      if(class_exists('sfConfig') && ($app = sfConfig::get('sf_app')))
      {
        $subject .= ' ' . $app;
      }
      
      $subject .= ' Error report';
      $this->setOption('subject', trim($subject));
    }
    
    if(!$this->getOption('sender_email'))
    {
      $this->setOption('sender_email', sprintf('webmaster@%s', 
              isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost'));
    }
    
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param string $priority  Message priority
   */
  public function log($message, $priority = SF_LOG_INFO)
  {
    $this->log = true;
    $this->body .= strtr($this->getOption('format'), array(
      '%type%'     => $this->getOption('type'),
      '%message%'  => $message,
      '%time%'     => strftime($this->getOption('time_format')),
      '%priority%' => $this->getPriorityName($priority)
    ));
  }

  /**
   * Creates mail headers
   * 
   * @return string
   */
  protected function getMailHeaders()
  {
    return 'MIME-Version: 1.0' . "\r\n" . 
           'Content-type: text/plain; charset=UTF-8' . "\r\n" .
           'X-Priority: 1 (Higuest)' . "\r\n" .
           'X-MSMail-Priority: High'. "\r\n" .
           'Importance: High' . "\r\n";
  }
  
  /**
   * Creates body to be send by mail() function
   * 
   * @return string
   */
  protected function getMailBody()
  {
    $body   = array();
    $body[] = '----------------------------------------';
    $body[] = 'System error message';
    $body[] = '----------------------------------------';
    $body[] = $this->body;
    $body[] = "\n";
    $body[] = sprintf('IP: %s', @$_SERVER['REMOTE_ADDR']);
    $body[] = sprintf('URI: %s', @$_SERVER['SERVER_NAME'] . @$_SERVER['REQUEST_URI']);
    $body[] = sprintf('Referer: %s', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']: 'n/a');
    $body[] = sprintf('User agent: %s', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']: 'n/a');
    $body[] = "\n\n\n";
    $body[] = '----------------------------------------';
    $body[] = '$_SERVER DUMP';
    $body[] = '----------------------------------------';
    $body[] = var_export($_SERVER, true);
    $body[] = '----------------------------------------';
    $body[] = '$_REQUEST DUMP';
    $body[] = '----------------------------------------';
    $body[] = var_export($_REQUEST, true);
    $body[] = '----------------------------------------';
    $body[] = 'FILES DUMP';
    $body[] = var_export($_FILES, true);
    $body[] = '----------------------------------------';
    
    return wordwrap(join("\r\n", $body), 70, "\r\n");
  }
  
  /**
   * Rreturns mail subject
   * 
   * @return string
   */
  protected function getMailSubject()
  {
    return '=?UTF-8?B?'.base64_encode($this->getOption('subject')).'?='; 
  }

  /**
   * Sends the mail
   * 
   * @param string $email Email address
   * @param string $subject Subject
   * @param string $body Mail body
   * @param string $headers Mail headers
   * @return boolean
   */
  protected function sendEmail($email, $subject, $body, $headers)
  {
    return mail($email, $subject, $body, $headers);    
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
    $recipients = (array)$this->getOption('recipients');
    
    if(!$this->log || 
      !count($recipients))
    {
      return;
    }
    
    $senderEmail = $this->getOption('sender_email');
    if($senderEmail)
    {
      ini_set('sendmail_from', $senderEmail);
    }
    
    $headers = $this->getMailHeaders();
    $body = $this->getMailBody();
    $subject = $this->getMailSubject();
    
    foreach($recipients as $email)
    {
      $this->sendEmail($email, $subject, $body, $headers);
    }

    if($senderEmail)
    {
      ini_restore('sendmail_from');
    }
    
  }


}