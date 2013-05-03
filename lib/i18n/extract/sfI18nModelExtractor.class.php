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

    // we have an abstract class, which cannot be instantiated
    // we will look for a class which extends this
    if($reflection->isAbstract())
    {
      // check if its a plugin model
      if(preg_match('/^Plugin/', $reflection->getName()))
      {
        $className = preg_replace('/^(Plugin)+/', '', $reflection->getName());
        if(class_exists($className))
        {
          // create new
          $reflection = new sfReflectionClass($className);
        }
        else
        {
          throw new InvalidArgumentException(sprintf('Model "%s" cannot be extracted.', $this->getOption('model')));
        }
      }
      else
      {
        throw new InvalidArgumentException(sprintf('Abstract model "%s" cannot be extracted.', $this->getOption('model')));
      }
    }

    // we have a valid class
    if($reflection->isSubclassOf($this->getOption('model_subclass')))
    {
      // we have to check the method and where is defined!
      $method = $reflection->getMethod('setupValidatorSchema');

      // setup validator schema is defined in the class
      if($method->getDeclaringClass()->getName() == $this->getOption('model'))
      {
        // we can extract
        $record = $reflection->newInstance();
        $validatorSchema = $record->getValidatorSchema();
        $fields = $validatorSchema->getFields();
        foreach($fields as $name => $validator)
        {
          $this->getStringsFromValidator($validator);
        }
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

  /**
   * Extract messages from validator
   *
   * @param sfValidatorBase $validator
   */
  protected function getStringsFromValidator($validator)
  {
    if(method_exists($validator, 'getMessages') && method_exists($validator, 'getValidators'))
    {
      $this->strings = array_merge($this->strings, $validator->getActiveMessages());

      foreach($validator->getValidators() as $f)
      {
        $this->getStringsFromValidator($f);
      }
    }
    elseif(method_exists($validator, 'getMessages'))
    {
      $this->strings = array_merge($this->strings, $validator->getActiveMessages());
    }
  }

}