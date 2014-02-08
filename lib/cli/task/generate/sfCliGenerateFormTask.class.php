<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new module.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliGenerateFormTask extends sfCliGeneratorBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('form', sfCliCommandArgument::REQUIRED, 'The form name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('dir', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The directory to create the form in', 'lib/form'),
      new sfCliCommandOption('wizard', null, sfCliCommandOption::PARAMETER_NONE, 'Will the form be wizard form?'),
      new sfCliCommandOption('base-class', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'What is the base class for the form?', 'myForm'),
    ));

    $this->namespace = 'generate';
    $this->name = 'form';

    $this->briefDescription = 'Generates a new form';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [generate:form|INFO] task creates new form in /lib/form directory
for an existing project:

  [{$scriptName} generate:form myLoginForm|INFO]

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $form = $arguments['form'];

    $this->validateFormClass($form);

    $formClass = $form;
    // form name does not end with "Form"
    if (!preg_match('/Form$/i', $form)) {
      $formClass .= 'Form';
    }

    if ($options['wizard'] && $formClass != 'myWizardForm') {
      $formBaseClass = 'myWizardForm';
    } elseif ($options['base-class']) {
      $formBaseClass = $options['base-class'];
      $this->validateFormClass($formBaseClass);
    }

    if (class_exists($formClass)) {
      throw new sfCliCommandException(sprintf('Form "%s" already exists', $formClass));
    }

    $this->logSection($this->getFullName(), sprintf('Creating form "%s".', $form));

    $constants = array(
      'PROJECT_NAME' => $this->getProjectProperty('name', 'Your name here'),
      'FORM_CLASS_NAME' => $formClass,
      'FORM_UNDERSCORED_NAME' => sfInflector::underscore($formClass),
      'FORM_BASE_CLASS_NAME' => $formBaseClass,
    );

    if (is_readable($this->environment->get('sf_data_dir').'/skeleton/form/form_simple.php')) {
      $skeleton= $this->environment->get('sf_data_dir').'/skeleton/form/form_simple.php';
    } else {
      $skeleton = $this->environment->get('sf_sift_data_dir').'/skeleton/form/form_simple.php';
    }

    $formFile = $this->environment->get('sf_root_dir').'/'.$options['dir'].'/'.$formClass.'.class.php';
    if (is_readable($formFile)) {
      throw new sfCliCommandException(sprintf('A "%s" form already exists in "%s".', $formClass, $formFile));
    }

    $this->getFilesystem()->copy($skeleton, $formFile);
    $this->getFilesystem()->replaceTokens($formFile, '##', '##', $constants);

    $this->logSection($this->getFullName(), 'Done.');
  }

  /**
   * Validates form class name
   *
   * @param string $class
   * @return boolean
   * @throws sfCliCommandException
   */
  protected function validateFormClass($class)
  {
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class)) {
      throw new sfCliCommandException(sprintf('The form class name "%s" is invalid.', $class));
    }

    return true;
  }

}
