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
class sfEmailLogger
{
  protected
    $emails     = array(),
    $subject,
    $body,
    $log        = false,
    $type       = 'Sift',
    $format     = '%time% %type% [%priority%] %message%',
    $timeFormat = '%b %d %H:%M:%S';

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
    if(!isset($options['emails']))
    {
      $emails = sfConfig::get('sf_webmaster_email');
      if(!is_array($emails))
      {
        $emails = array($emails);
      }
      $options['emails'] = $emails;  
    }

    $this->emails = $options['emails'];

    if (isset($options['format']))
    {
      $this->format = $options['format'];
    }

    if (isset($options['time_format']))
    {
      $this->timeFormat = $options['time_format'];
    }

    if (isset($options['type']))
    {
      $this->type = $options['type'];
    }

    if (isset($options['subject']))
    {
      $this->subject = $options['subject'];
    }
    else
    {
      $this->subject = @$_SERVER['SERVER_NAME'] . ' ~ '  . sfConfig::get('sf_app') . ' :: Log';
    }

  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param string $priority  Message priority
   */
  public function log($message, $priority, $priorityName)
  {
    $this->log = true;
    return $this->doLog($message, $priority, $priorityName);
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param string $priority  Message priority
   */
  protected function doLog($message, $priority, $priorityName)
  {
    $this->body .= strtr($this->format, array(
      '%type%'     => $this->type,
      '%message%'  => $message,
      '%time%'     => strftime($this->timeFormat),
      '%priority%' => $priorityName
    ));
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
    if(!$this->log)
    {
      return;
    }
    
    ini_set('sendmail_from', sfConfig::get('app_mail_robot'));
    $headers = '';

    $body   = array();
    $body[] = '----------------------------------------';
    $body[] = 'System error message';
    $body[] = '----------------------------------------';
    $body[] = $this->body;
    $body[] = "\n";
    $body[] = sprintf('IP: %s', @$_SERVER['REMOTE_ADDR']);
    $body[] = sprintf('URI: %s', $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
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
    $body   = join(PHP_EOL, $body);

    foreach($this->emails as $email)
    {
      mail($email, $this->subject, $body, $headers);
    }

    ini_restore('sendmail_from');
  }

}