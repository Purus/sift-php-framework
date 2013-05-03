<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPhoneNumberValidator class.
 *
 * This class validates phone numbers.
 *
 * @package    Sift
 * @subpackage validator_legacy
 * @depracated
 */
class sfPhoneNumberValidator extends sfValidator {

  /**
   * Built in validation rules (support for czech and slovak numbers)
   *
   * @var array
   */
  protected $validationRules = array(
    // czech number
    'CZ' => "/^((\+420)|(00420))? ?\d{3} ?\d{3} ?\d{3}$/",
    // slovak number
    'SK' => "/^((\+421)|(00421))?(\(?0\)?)? ?\d{3} ?\d{3} ?\d{3}$/"
  );

  public function execute(&$value, &$error)
  {
    $_value = $value;

    if(!$this->getParameter('strict'))
    {
      $_value = str_replace(' ', '', trim($value));
    }

    // use custom regex or builin one
    $regexes = (array) $this->getParameter('regex', array_values($this->validationRules));

    $validated = false;
    foreach($regexes as $regex)
    {
      if(preg_match($regex, $_value))
      {
        $validated = true;
        break;
      }
    }

    if(!$validated)
    {
      $error = $this->getParameter('phone_error');
      return false;
    }

    return true;
  }

  public function initialize($context, $parameters = null)
  {
    // Initialize parent
    parent::initialize($context);

    $this->setParameter('phone_error', 'This is invalid phone number');
    // Custom pattern
    $this->setParameter('regex', null);
    // Strict mode
    $this->setParameter('strict', false);
    // Set parameters
    $this->getParameterHolder()->add($parameters);

    return true;
  }

}