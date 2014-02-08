<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWizardComponents class. Usefull for form wizards using steps.
 *
 * @package    Sift
 * @subpackage action
 */
class sfWizardComponents extends myComponents
{
  /**
   * Storage namespace mask (should be the same in action class.)
   *
   * @var string
   */
  protected $formNameMask = 'myFormStep%s';

  /**
   * Returns storage namespace
   *
   * @return string
   */
  protected function getWizardStorageNamespace()
  {
    return myWizardForm::getStorageNamespace($this->formNameMask);
  }

  /**
   * Returns stored values for the $step
   *
   * @param integer $step
   * @return array
   */
  protected function getStepValues($step)
  {
    return $this->getUser()->getAttributeHolder()->getAll(
      self::getWizardStorageNamespace(). '/'.$step);
  }

}
