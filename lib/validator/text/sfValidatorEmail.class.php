<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorEmail validates emails.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorEmail extends sfValidatorRegex
{
  /* Cal Henderson: http://iamcal.com/publish/articles/php/parsing_email/pdf/
   * The long regular expression below is made by the following code
   * fragment:
   *
   *   $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
   *   $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
   *   $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'
   *         . '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
   *   $quoted_pair = '\\x5c\\x00-\\x7f';
   *   $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
   *   $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
   *   $domain_ref = $atom;
   *   $sub_domain = "($domain_ref|$domain_literal)";
   *   $word = "($atom|$quoted_string)";
   *   $domain = "$sub_domain(\\x2e$sub_domain)*";
   *   $local_part = "$word(\\x2e$word)*";
   *   $addr_spec = "$local_part\\x40$domain";
   */
  const REGEX_EMAIL = '/^([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80-\\xff]|\\x5c\\x00-\\x7f)*\\x22)(\\x2e([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80-\\xff]|\\x5c\\x00-\\x7f)*\\x22))*\\x40([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x5b([^\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*\\x5d)(\\x2e([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x5b([^\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*\\x5d))*$/';

  const REGEX_EMAIL_STRICT = '/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i';

  /**
   * @see sfValidatorRegex
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->setMessage('required', 'Email is required.');
    $this->setMessage('invalid', 'Email is invalid.');
    $this->addMessage('mx_fail', 'Email "%value%" does not exist.');
    $this->addOption('check_mx', sfConfig::get('sf_validator_email_check_mx', false));
    $this->addOption('strict', sfConfig::get('sf_validator_email_strict_pattern', true));

    if ($this->getOption('strict')) {
      $this->setOption('pattern', self::REGEX_EMAIL_STRICT);
    } else {
      $this->setOption('pattern', self::REGEX_EMAIL);
    }
  }

  protected function doClean($value)
  {
    $value = parent::doClean($value);

    if ($this->getOption('check_mx')) {
      $this->log(sprintf('Checking MX records for "%s"', $value));
      $parts = explode('@', $value);
      if (isset($parts[1]) && $parts[1] && !$this->checkMX($parts[1])) {
        throw new sfValidatorError($this, 'mx_fail', array('value' => $value));
      }
    }

    return $value;
  }

  /**
    * Check DNA Records for MX type
    *
    * @param string $host Host name
    * @return boolean
    */
  private function checkMX($host)
  {
    // We have different behavior here depending of OS and PHP version
    if (strtolower(substr(PHP_OS, 0, 3)) == 'win' && version_compare(PHP_VERSION, '5.3.0', '<')) {
      $output = array();
      exec('nslookup -type=MX '.escapeshellcmd($host) . ' 2>&1', $output);
      if (empty($output)) {
        throw new sfException('Unable to execute DNS lookup. Are you sure PHP can call exec()?');
      }
       foreach ($output as $line) {
        if (preg_match('/^'.$host.'/', $line)) {
          return true;
        }
      }

      return false;
    } elseif (function_exists('checkdnsrr')) {
      return checkdnsrr($host, 'MX');
    }

    throw new sfException('Could not retrieve DNS record information. Remove check_mx = true to prevent this warning');
  }

  public function getJavascriptValidationMessages()
  {
    $messages = parent::getJavascriptValidationMessages();

    $messages[sfFormJavascriptValidation::EMAIL] =
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');

    return $messages;
  }

  /**
   * Returns active messages (based on active options). This is usefull for
   * i18n extract task.
   *
   * @return array
   */
  public function getActiveMessages()
  {
    $messages = parent::getActiveMessages();
    $messages[] = $this->getMessage('required');
    $messages[] = $this->getMessage('mx_fail');

    return $messages;
  }
}
