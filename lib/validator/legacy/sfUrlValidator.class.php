<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfUrlValidator verifies a parameter contains a value that qualifies as a valid URL.
 *
 * @package    Sift
 * @subpackage validator_legacy
 * @deprecated
 */
class sfUrlValidator extends sfValidator {

 const REGEX_URL_FORMAT = '~^
      (%s)://                                 # protocol
      (
        ([a-z0-9-]+\.)+[a-z]{2,6}             # a domain name
          |                                   #  or
        \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}    # a IP address
      )
      (:[0-9]+)?                              # a port (optional)
      (/?|/\S+)                               # a /, nothing or a / with something
    $~ix';

  /**
   * Executes this validator.
   *
   * @param mixed A file or parameter value/array
   * @param error An error message reference
   *
   * @return bool true, if this validator executes successfully, otherwise false
   */
  public function execute(&$value, &$error)
  {
    if(!$this->isValidUrl($value))
    {
      $error = $this->getParameterHolder()->get('url_error');
      return false;
    }

    return true;
  }

  /**
   * Initializes this validator.
   *
   * @param sfContext The current application context
   * @param array   An associative array of initialization parameters
   *
   * @return bool true, if initialization completes successfully, otherwise false
   */
  public function initialize($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context);

    // set defaults
    $this->getParameterHolder()->set('url_error', 'Invalid input');

    $this->getParameterHolder()->set('protocols', array('http', 'https', 'ftp', 'ftps'));

    $this->getParameterHolder()->add($parameters);

    return true;
  }

  /**
   * Does regex match
   *
   * @param string $to_validate
   * @return boolean
   */
  private function isValidUrl($to_validate)
  {
    $exp = sprintf(self::REGEX_URL_FORMAT, implode('|', $this->getParameter('protocols')));
    return preg_match($exp, $to_validate);
  }

}
