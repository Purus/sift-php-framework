<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfPostCodeValidator allows you to validate post codes for given countries
 * or using custom regular expression
 *
 * @package    Sift
 * @subpackage validator_legacy
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfPostCodeValidator extends sfValidator {

  /**
   * Executes this validator.
   *
   * @param string A parameter value
   * @param string An error message reference
   *
   * @return bool true, if this validator executes successfully, otherwise false
   */
  public function execute(&$value, &$error)
  {
    $countries = $this->getParameterHolder()->get('countries', array());

    if($countries != 'all')
    {
      // upper case countries
      $countries = array_map('strtoupper', $countries);
    }

    if(!$this->getParameterHolder()->get('strict'))
    {
      $value = trim(preg_replace('/\s+/', '', $value));
    }

    $patterns = array();
    if($countries)
    {
      if($countries == 'all')
      {
        $patterns = sfCulture::getInstance()->getPostCodes();
      }
      else
      {
        $patterns = sfCulture::getInstance()->getPostCodes($countries);
      }
    }

    $match = false;

    // we will first validate countries
    foreach($patterns as $country => $pattern)
    {
      if(preg_match('/^' . $pattern . '$/', $value))
      {
        $match = true;
        break;
      }
    }

    // we have found something
    if($match)
    {
      return true;
    }

    // lets try custom pattern if set
    $pattern = $this->getParameterHolder()->get('pattern', false);
    if($pattern && preg_match($pattern, $value))
    {
      $match = true;
    }

    if(!$match)
    {
      $error = $this->getParameterHolder()->get('post_code_error');
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

    $this->getParameterHolder()->set('post_code_error', 'Invalid post code');

    // strict mode? when non strict, value is before validation trimed of white space
    $this->getParameterHolder()->set('strict', true);

    // regular pattern
    $this->getParameterHolder()->set('pattern', null);

    // territories used for validatin post codes
    $this->getParameterHolder()->set('countries', array());

    $this->getParameterHolder()->add($parameters);

    // check parameters
    if($this->getParameterHolder()->get('pattern') == null &&
            !($this->getParameterHolder()->get('countries')))
    {
      throw new sfValidatorException('Please specify an array of countries or PCRE regular expression pattern for your registered PostCodeValidator');
    }

    return true;
  }

}
