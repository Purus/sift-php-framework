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
 */
class sfEmailLogger extends sfVarLogger
{
  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'time_format' => 'D d.m.Y H:i:s',
    'subject' => 'Error notification - %host% / %app% (env: %env%)',
    'sender_email' => '',
    // we need the backtrace to be generated!
    'with_backtrace' => true,
    // what level to log?
    'min_level' => sfILogger::ERROR
  );

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'recipients', 'template'
  );

  /**
   * Log flag
   *
   * @var boolean
   */
  protected $log = false;

  /**
   * Initializes this logger.
   *
   * Available options:
   *
   * - emails:        The emails to be send the log messages
   * - subject:       The subject for the email
   * - include_level: Use this to get more detailed information
   * - format:        The log line format (default to %time% %type% [%level%] %message%%EOL%)
   * - time_format:   The log time strftime format (default to %b %d %H:%M:%S)
   *
   * @param  array             $options     An array of options.
   *
   * @return Boolean      true, if initialization completes successfully, otherwise false.
   */
  public function setup()
  {
    $this->setOption('xdebug_logging', false);

    if (!is_readable($this->getOption('template'))) {
      throw new sfConfigurationException(sprintf('The template "%s" is not readable or does not exist', $this->getOption('template')));
    }

    if (!$this->getOption('sender_email')) {
      $this->setOption('sender_email', sprintf('webmaster@%s', isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost'));
    }

    if (is_string($this->getOption('recipients'))) {
      $recipients = sfToolkit::replaceConstants($this->getOption('recipients'));
      $this->setOption('recipients', explode(',', $recipients));
    }
  }

  /**
   * @see sfILogger
   */
  public function log($message, $level = sfILogger::INFO, array $context = array())
  {
    // we need to check level
    if ($level > $this->getOption('min_level')) {
      return;
    }

    parent::log($message, $level, $context);

    $this->log = true;
  }

  /**
   * Get host
   *
   * @return string The host
   */
  protected function getHost()
  {
    $host = php_uname('n');
    foreach (array('HTTP_HOST', 'SERVER_NAME', 'HOSTNAME') as $item) {
      if (isset($_SERVER[$item])) {
        $host = $_SERVER[$item];
        break;
      }
    }

    return $host;
  }

  /**
   * Returns current application
   *
   * @return string
   */
  protected function getApplication()
  {
    $application = 'n/a';
    if (class_exists('sfConfig', false)) {
      $application = sfConfig::get('sf_app');
    }

    return $application;
  }

  /**
   * Returns current application environment
   *
   * @return string
   */
  protected function getEnvironment()
  {
    $env = 'n/a';
    if (class_exists('sfConfig', false)) {
      $env = sfConfig::get('sf_environment');
    }

    return $env;
  }

  /**
   * Creates body to be send by mail() function
   *
   * @return string
   */
  protected function getMailBody()
  {
    $decorator = new sfDebugBacktraceLogDecorator();

    foreach ($this->logs as &$log) {
      // debug backtrace from exception
      if (isset($log['context'][sfILogger::CONTEXT_EXTRA]['debug_backtrace'])) {
        // we need to modify the trace to remove absolute paths
        // for security reasons!
        $backtrace = ($log['context'][sfILogger::CONTEXT_EXTRA]['debug_backtrace']);

        if ($backtrace instanceof sfDebugBacktrace) {
          $backtrace = new sfDebugBacktrace($backtrace->get(), array(
              'shorten_file_paths' => true
          ));
        } elseif ($backtrace instanceof sfDebugBacktraceDecorator) {
          $backtrace = new sfDebugBacktrace($backtrace->getBacktrace()->get(), array(
              'shorten_file_paths' => true
          ));
        } else {
          // remove backtrace!
          $backtrace = 'Backtrace has been removed for security reasons. See the logs.';
        }

        $log['debug_backtrace'] = $backtrace;
      }

      $decorator->setBacktrace($log['debug_backtrace']);
      // remove sensitive information about file paths from the backtrace
      $log['debug_backtrace'] = $decorator->toString();
      $log['message_formatted'] = $this->removeFilePaths($log['message_formatted']);
    }

    $email = sfLimitedScope::render($this->getOption('template'), array(
      'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
      'ip' => @$_SERVER['REMOTE_ADDR'],
      'host' => $this->getHost(),
      'url' => @$_SERVER['SERVER_NAME'] . @$_SERVER['REQUEST_URI'],
      'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
      'app' => $this->getApplication(),
      'env' => $this->getEnvironment(),
      'logs' => $this->logs,
      'highest_level' => $this->getLevelName($this->getHighestLevel()),
      'now' => date($this->getOption('time_format')),
      'memory_usage' => sprintf('%s kB', round(memory_get_usage() / 1000, 2)),
      'time_format' => $this->getOption('time_format')
    ));

    return $email;
  }

  /**
   * Removes absolute file path from the string. Shortens the file paths
   *
   * @param string $string
   * @return string
   */
  protected function removeFilePaths($string)
  {
    if (preg_match('/\'|"([^\\"]+)"|\'/i', $string, $matches)) {
      $string = preg_replace_callback('/\'|"([^\\"]+)"|\'/i', array($this, 'removeFilePathsCallback'), $string);
    }
    // Error message in file /usr/share/php/Foobar.class.php, line
    elseif (preg_match('/in file ([^,]*)/i', $string, $matches, PREG_OFFSET_CAPTURE)) {
      $string = preg_replace_callback('/\'|"([^\\"]+)"|\'/i', array($this, 'removeFilePathsCallback'), $string);
    }

    return $string;
  }

  protected function removeFilePathsCallback($matches)
  {
    return sprintf('"%s"', sfDebug::shortenFilePath($matches[1]));
  }

  /**
   * Returns request dump as a string
   *
   * @return string
   */
  protected function getRequestDump()
  {
    return json_encode($this->getRequestDumpAsArray());
  }

  /**
   * Returns PHP globals variables as a sorted array.
   *
   * @return array PHP globals
   */
  protected function getRequestDumpAsArray()
  {
    $values = array();
    // do not include sever and env for security reason!
    // this is sent by mail!
    foreach (array('cookie', 'get', 'post', 'files') as $name) {
      if (!isset($GLOBALS['_' . strtoupper($name)])) {
        continue;
      }
      $values[$name] = array();
      foreach ($GLOBALS['_' . strtoupper($name)] as $key => $value) {
        $values[$name][$key] = $value;
      }
      ksort($values[$name]);
    }

    // modify the session id
    // if reporting E_PARSE or similar, and the session name
    // was not started, we do not want to send the session cookie name
    // FIXME: make it configurable?
    foreach(array(
      session_name(),
      'PHPSESSID',
      'sessionID',
      'sessionid',
      'SID',
      'sid'
    ) as $sessionName)
    {
      if (isset($values['cookie'][$sessionName])) {
        $values['cookie'][$sessionName] = substr($values['cookie'][$sessionName], 0, 3) . '-the-rest-removed-for-security-reasons';
      }
    }

    ksort($values);

    return $values;
  }

  /**
   * Rreturns mail subject
   *
   * @return string
   */
  protected function getMailSubject()
  {
    $subject = strtr($this->getOption('subject'), array(
      '%host%' => $this->getHost(),
      '%app%' => $this->getApplication(),
      '%env%' => $this->getEnvironment()
    ));

    return '=?UTF-8?B?' . base64_encode($subject) . '?=';
  }

  /**
   * Creates mail headers
   *
   * @return array
   */
  protected function getMailHeaders()
  {
    return array(
      'MIME-Version: 1.0',
      'X-level: 1 (Highest)',
      'X-MSMail-level: High',
      'Importance: High',
      'X-Mailer: Email logger'
    );
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
    $boundary = sprintf('--NOTIFICATION%s', md5(rand()));
    array_push($headers, sprintf('Content-Type: multipart/mixed; boundary="%s"', $boundary));

    // Make sure there are no bare linefeeds in the headers
    $headers = preg_replace('#(?<!\r)\n#si', "\r\n", join("\r\n", $headers));
    // Fix any bare linefeeds in the message to make it RFC821 Compliant.
    $body = preg_replace("#(?<!\r)\n#si", "\r\n", $body);

    $body = array(
        sprintf('--%s', $boundary),
        'Content-Type: text/plain; charset=utf8',
        'Content-Transfer-Encoding: quoted-printable',
        '',
        $this->quotedPrintableEncode($body),
        sprintf('--%s', $boundary),
        'Content-Type: text/plain; charset=utf8',
        'Content-Disposition: attachment; filename="request_dump.json"',
        "Content-Transfer-Encoding: base64",
        '',
        chunk_split(base64_encode($this->getRequestDump())),
        sprintf('--%s--', $boundary)
    );

    return mail($email, $subject, join("\r\n", $body), $headers);
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
    $recipients = $this->getOption('recipients');

    if(!$this->log ||
        !count($recipients))
    {
      return;
    }

    $senderEmail = $this->getOption('sender_email');
    if ($senderEmail) {
      ini_set('sendmail_from', $senderEmail);
    }

    $headers = $this->getMailHeaders();
    $body = $this->getMailBody();
    $subject = $this->getMailSubject();

    foreach ($recipients as $email) {
      $this->sendEmail($email, $subject, $body, $headers);
    }

    if ($senderEmail) {
      ini_restore('sendmail_from');
    }
  }

  /**
   * Returns the debug stack.
   *
   * @return array
   * @see debug_backtrace()
   */
  protected function getDebugBacktrace()
  {
    // remove first item, since its this function
    return new sfDebugBacktrace(array_slice(debug_backtrace(), 0), array(
        'shorten_file_paths' => true
    ));
  }

  /**
   * Quoted printable encode for php < 5.3.
   *
   * @param string $string
   * @return string
   */
  protected function quotedPrintableEncode($string)
  {
    if (function_exists('quoted_printable_encode')) {
      return quoted_printable_encode($string);
    }

    if (!defined('PHP_QPRINT_MAXL')) {
      define('PHP_QPRINT_MAXL', 75);
    }

    $lp = 0;
    $ret = '';
    $hex = '0123456789ABCDEF';
    $length = strlen($string);
    $str_index = 0;

    while ($length--) {
      if ((($c = $string[$str_index++]) == "\015") && ($string[$str_index] == "\012") && $length > 0) {
        $ret .= "\015";
        $ret .= $string[$str_index++];
        $length--;
        $lp = 0;
      } else {
        if (ctype_cntrl($c) || (ord($c) == 0x7f) || (ord($c) & 0x80) || ($c == '=') || (($c == ' ') && ($string[$str_index] == "\015"))) {
          if (($lp += 3) > PHP_QPRINT_MAXL) {
            $ret .= '=';
            $ret .= "\015";
            $ret .= "\012";
            $lp = 3;
          }
          $ret .= '=';
          $ret .= $hex[ord($c) >> 4];
          $ret .= $hex[ord($c) & 0xf];
        } else {
          if ((++$lp) > PHP_QPRINT_MAXL) {
            $ret .= '=';
            $ret .= "\015";
            $ret .= "\012";
            $lp = 1;
          }
          $ret .= $c;
        }
      }
    }

    return $ret;
  }
}
