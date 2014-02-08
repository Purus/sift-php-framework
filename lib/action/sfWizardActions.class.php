<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWizardAction class. Usefull for form wizards using steps.
 *
 * @package    Sift
 * @subpackage action
 */
class sfWizardActions extends myActions {

  /**
   * Last step of the wizard
   * @var integer
   */
  public $lastStep     = 1;

  /**
   * Firts step of the wizard
   * @var integer
   */
  public $firstStep    = 1;

  /**
   * Form name mask "myFormStep%s". All forms of the steps should follow
   * this name mask. First step will be "myFormStep1Form".
   *
   * @var string
   */
  public $formNameMask = 'myFormStep%s';

  /**
   * Wizard route
   *
   * @var string
   */
  public $wizardRouteName   = '';

  /**
   *
   */
  public function preExecute()
  {
    $this->step = $this->getRequest()->getInt('step');

    try
    {
      $this->form = sfFormManager::getForm(sprintf($this->formNameMask, $this->step));
      // set session namespace where will be save data!
      myWizardForm::setStorageNamespace(sfInflector::tableize(get_class($this)),
              $this->formNameMask);
    }
    catch(sfException $e)
    {
      $this->forward404($e->getMessage());
    }

    if($this->step > $this->firstStep
        && !$this->isPreviousStepValidated())
    {
      // FIXME: only works with named route and no other params in the route
      // @route?step=1
      $route = current(explode('?', sfRouting::getInstance()->getCurrentInternalUri(true)));
      $validated = 1;
      // get last validated step!
      // range is backward
      foreach(range($this->lastStep, $this->firstStep) as $step)
      {
        if($this->stepIsValidated($step))
        {
          // next step after last validated step
          $validated = $step == $this->lastStep ? $this->lastStep : ($step + 1);
          break;
        }
      }

      return $this->redirect($route.'?step='.($validated));
    }
  }

  /**
   * Is previous step validated?
   *
   * @return boolean
   */
  protected function isPreviousStepValidated()
  {
    return $this->stepIsValidated($this->step - 1);
  }

  /**
   * Are we in last step?
   *
   * @return boolean
   */
  protected function isLastStep()
  {
    return $this->step == $this->lastStep;
  }

  /**
   * Are we in first step?
   *
   * @return boolean
   */
  protected function isFirstStep()
  {
    return $this->step == $this->firstStep;
  }

  /**
   * Returns wizard storage namespace
   *
   * @return string
   */
  public function getWizardStorageNamespace()
  {
    return myWizardForm::getStorageNamespace($this->formNameMask);
  }

  /**
   * Stores the $values into user attributes using storage namespace name.
   *
   * @param mixed $values
   * @param boolean $overwrite
   * @return void
   */
  protected function setStepValues($values, $overwrite = false)
  {
    if($overwrite)
    {
      $this->getUser()->getAttributeHolder()->removeNamespace($this->getWizardStorageNamespace().'/'.$this->step);

      return $this->getUser()->getAttributeHolder()->add($values, $this->getWizardStorageNamespace().'/'.$this->step);
    }
    else
    {
      return $this->getUser()->getAttributeHolder()->add($values, $this->getWizardStorageNamespace().'/'.$this->step);
    }
  }

  /**
   * Returns step data from $step
   *
   * @param integer $step
   * @return mixed
   */
  protected function getStepValues($step)
  {
    return $this->getUser()->getAttributeHolder()->getAll($this->getWizardStorageNamespace(). '/'.$step);
  }

  /**
   * Returns or sets validity of step
   *
   * @param integer $step
   * @param boolean $boolean
   * @return sfWizardForm sfWizardForm or boolean value
   */
  protected function stepIsValidated($step, $boolean = null)
  {
    // we are setting the value
    if(!is_null($boolean))
    {
      $this->getUser()->getAttributeHolder()->add(array('is_valid' => $boolean), $this->getWizardStorageNamespace().'/'.$step);

      return $this;
    }

    $valid = $this->getUser()->getAttribute('is_valid', false, $this->getWizardStorageNamespace().'/'.$step);

    return $valid;
  }

  /**
   * Redirects to next step
   *
   * @return void
   */
  protected function redirectToNextStep()
  {
    if(!$this->isLastStep())
    {
      return $this->redirect($this->getWizardRouteName().'?step='.($this->step+1));
    }

    return $this->redirect($this->getWizardRouteName().'?step=1');
  }

  /**
   * Redirects to previous step
   *
   * @return void
   */
  protected function redirectToPreviousStep()
  {
    if(!$this->isFirstStep())
    {
      return $this->redirect($this->getWizardRouteName().'?step='.($this->step-1));
    }

    return $this->redirect($this->getWizardRouteName().'?step=1');
  }

  /**
   * Redirects back to currect step.
   *
   * @return void
   */
  protected function redirectAgainToCurrentStep()
  {
    return $this->redirect($this->getWizardRouteName().'?step='.$this->step);
  }

  /**
   * Returns wizard route name
   *
   * @return string
   */
  protected function getWizardRouteName()
  {
    return $this->wizardRouteName;
  }

  /**
   * Returns an array of all data from all steps.
   *
   * @return array
   */
  protected function getAllStepsData()
  {
    $data = array();
    // cleanup
    foreach(range($this->firstStep, $this->lastStep) as $step)
    {
      $data[$step] = $this->getStepValues($step);
    }

    return $data;
  }

  /**
   * Cleans up the stored data.
   *
   * @return void
   */
  protected function cleanAllStepsData()
  {
    // cleanup
    foreach(range($this->firstStep, $this->lastStep) as $step)
    {
      $this->getUser()->getAttributeHolder()->removeNamespace($this->getWizardStorageNamespace().'/'.$step);
    }
  }

}
