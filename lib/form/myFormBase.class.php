<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

sfWidgetFormSchema::setDefaultFormFormatterName('Div');

/**
 * myFormBase class provides basic setup for all myForms
 *
 * @package    Sift
 * @subpackage form
 */
class myFormBase extends sfForm {

  /**
   * Constructor.
   *
   * @param array  $defaults    An array of field default values
   * @param array  $options     An array of options
   * @param string $CSRFSecret  A CSRF secret
   */
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null)
  {
    if($CSRFSecret === null)
    {
      $CSRFSecret = sfConfig::get('sf_csrf_secret');
    }
    parent::__construct($defaults, $options, $CSRFSecret);
  }

  public function setup()
  {
    parent::setup();

    $this->setName(str_replace(array('sf_', 'my_'), '',
              sfInflector::underscore(get_class($this))));

    $this->setupDecorator();

    $this->validatorSchema->setOption('filter_extra_fields', false);
  }

  /**
   * Setups form decorator and i18n for it
   * if enabled
   *
   */
  protected function setupDecorator()
  {
    $decorator = new sfWidgetFormSchemaFormatterDiv($this->widgetSchema);
    $decorator->setValidatorSchema($this->getValidatorSchema());

    if(sfConfig::get('sf_i18n') && $this->translationCatalogue)
    {
      $decorator->setTranslationCallable('__');
      $decorator->setTranslationCatalogue($this->getTranslationCatalogue());
    }

    $this->widgetSchema->addFormFormatter('div', $decorator);
    $this->widgetSchema->setFormFormatterName('div');
  }

  /**
   * Renders global errors
   *
   * @param boolean $useGlobalPartial Use partial to render the errors
   * @return type
   */
  public function renderGlobalErrors($useGlobalPartial = true)
  {
    if(!$this->hasGlobalErrors())
    {
      return '';
    }

    if($useGlobalPartial &&
        is_readable(sfConfig::get('sf_app_template_dir') . '/_form_errors.php'))
    {
      sfLoader::loadHelpers('Partial');

      return get_partial('global/form_errors', array('form' => $this));
    }

    return parent::renderGlobalErrors();
  }

  /**
   * Renders the form
   *
   * @param array $attributes
   * @param boolean $useGlobalTemplate Use global template?
   * @return string
   */
  public function render($attributes = array())
  {
    if(!isset($attributes['global_template']))
    {
      $attributes['global_template'] = 'form';
    }

    if($attributes['global_template'] &&
       is_readable(sprintf('%s/_%s.php', sfConfig::get('sf_app_template_dir'),
                   $attributes['global_template'])))
    {
      sfLoader::loadHelpers('Partial');
      $template = sprintf('global/%s', $attributes['global_template']);
      $attributes['global_template'] = false;

      return get_partial($template,
              array('form' => $this, 'attributes' => $attributes));
    }

    return parent::render($attributes);
  }

  /**
   * Embeds a form like "mergeForm" does, but will still
   * save the input data.
   *
   * @param string $name
   * @param sfForm $form
   * @throws LogicException
   * @see http://itsmajax.com/2011/01/29/6-things-to-know-about-embedded-forms-in-symfony/
   */
  public function embedMergeForm($name, sfForm $form)
  {
    // This starts like sfForm::embedForm
    $name = (string) $name;
    if(true === $this->isBound() || true === $form->isBound())
    {
      throw new LogicException('A bound form cannot be merged');
    }
    $this->embeddedForms[$name] = $form;

    $form = clone $form;
    unset($form[self::$CSRFFieldName]);

    // But now, copy each widget instead of the while form into the current
    // form. Each widget ist named "formname|fieldname".
    foreach($form->getWidgetSchema()->getFields() as $field => $widget)
    {
      $widgetName = "$name-$field";
      if(isset($this->widgetSchema[$widgetName]))
      {
        throw new LogicException("The forms cannot be merged. A field name '$widgetName' already exists.");
      }

      $this->widgetSchema[$widgetName] = $widget;                           // Copy widget
      $this->validatorSchema[$widgetName] = $form->validatorSchema[$field]; // Copy schema
      $this->setDefault($widgetName, $form->getDefault($field));            // Copy default value

      if(!$widget->getLabel())
      {
        // Re-create label if not set (otherwise it would be named 'ucfirst($widgetName)')
        $label = $form->getWidgetSchema()->getFormFormatter()->generateLabelName($field);
        $this->getWidgetSchema()->setLabel($widgetName, $label);
      }
    }

    // And this is like in sfForm::embedForm
    $this->resetFormFields();
  }

}
