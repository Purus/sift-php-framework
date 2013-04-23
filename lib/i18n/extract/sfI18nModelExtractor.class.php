<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Model extractor extracts messages from model files. Currently only
 * Doctrine (via myDoctrine plugin) is supported.
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nModelExtractor extends sfConfigurable implements sfII18nExtractor {

  /**
   * Array of default options
   *
   * @var string
   */
  protected $defaultOptions = array(
    'model_subclass' => array(
        'sfDoctrineRecord'
    )
  );

  /**
   * Array of strings
   *
   * @var array
   */
  protected $strings = array();

  public function extract($content)
  {
    $reflection = new sfReflectionClass($this->getOption('model'));

    // we have a valid class
    if($reflection->isSubclassOf($this->getOption('model_subclass')))
    {
      $record = $reflection->newInstance();

      $validatorSchema = $record->getValidatorSchema();
      $fields = $validatorSchema->getFields();
      foreach($fields as $name => $field)
      {
        $this->getStringsFromValidator($field);
      }
    }

    $collected = array();
    foreach($this->strings as $string)
    {
      if(empty($string))
      {
        continue;
      }

      $collected[] = $string;
    }

    return array(
      sfI18nExtract::UNKNOWN_DOMAIN => $collected
    );

  }

  protected function getStringsFromValidator($field)
  {
    if(method_exists($field, 'getMessages') && method_exists($field, 'getValidators'))
    {
      $this->strings = array_merge($this->strings, $field->getActiveMessages());

      foreach($field->getValidators() as $f)
      {
        $this->getStringsFromValidator($f);
      }
    }
    elseif(method_exists($field, 'getMessages'))
    {
      $this->strings = array_merge($this->strings, $field->getActiveMessages());
    }
  }

}