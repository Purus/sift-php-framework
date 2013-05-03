<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSlugValidator class.
 *
 * This class validates slug.
 *
 * @package    Sift
 * @subpackage validator_legacy
 * @deprecated
 */
class sfSlugValidator extends sfValidator {

  public function initialize($context, $parameters = null)
  {
    // Initialize parent
    parent::initialize($context);

    $this->setParameter('slug_error', 'This is invalid slug');

    // Set parameters
    $this->getParameterHolder()->add($parameters);
    return true;
  }

  public function execute(&$value, &$error)
  {
    if(!$this->isValidSlug($value))
    {
      $error = $this->getParameter('slug_error');
      return false;
    }
    return true;
  }

  /**
   * Checks the validity of the slug
   *
   * @param string $slug
   * @return boolean
   */
  private function isValidSlug($slug)
  {
    if(trim($slug) == myTools::normalize($slug))
    {
      return true;
    }
    return false;
  }

}
