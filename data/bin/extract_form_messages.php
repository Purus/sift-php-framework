<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts all default messages from validators
 *
 * Usage: php extract_form_messages
 *
 * @package    Sift
 * @subpackage cli
 */

$siftDataDir = realpath(dirname(__FILE__) . '/../');
$siftLibDir  = realpath(dirname(__FILE__) . '/../../lib');

require_once $siftLibDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

// setup the environment
sfConfig::set('sf_sift_data_dir', $siftDataDir);

$validators = sfFinder::type('file')
        ->name('sfValidator*.php')
        ->in($siftLibDir.'/validator');

// for which cultures?
$cultures = array_map('trim', explode("\n", file_get_contents(dirname(__FILE__).'/../../build/cultures.txt')));
// where are translation catalogues?
$sourceDir = $siftDataDir . '/i18n/catalogues';
$formCatalogueName = 'form';

$messages = array();

echo "Working, please wait...\n";

foreach($validators as $validator)
{
  $classes = sfToolkit::extractClasses($validator);
  foreach($classes as $class)
  {
    $reflection = new sfReflectionClass($class);

    if($reflection->isAbstract() || $reflection->isSubclassOfOrIsEqual(array(
          'sfValidatorSchemaFilter',
          'sfValidatorSchemaCompare',
          'sfValidatorSchemaForEach'
    ))
            )
    {
      // printf("Skipping abstract class: %s", $reflection->getName() . "\n");
      continue;
    }

    if(!$reflection->isSubclassOfOrIsEqual(array(
            'sfValidatorBase')))
    {
      // printf("Skipping class: %s", $reflection->getName() . "\n");
      continue;
    }

    // printf("Extracting: %s", $reflection->getName() . "\n");
    // handle special cases

    $arguments = array();

    if($reflection->isSubclassOfOrIsEqual(array('sfValidatorBlacklist')))
    {
      $arguments = array(
        // options
        array(
          'forbidden_values' => array()
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorChoice')))
    {
      $arguments = array(
        // options
        array(
          'choices' => array()
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorDateRange', 'sfValidatorDateTimeRange')))
    {
      $arguments = array(
          array(
          'from_date' => '',
          'to_date' => ''
          )
       );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorCallback')))
    {
      $arguments = array(
        array(
          'callback' => ''
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorCSRFToken')))
    {
      $arguments = array(
        array(
          'token' => ''
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorDefault')))
    {
      $arguments = array(
        array(
          'validator' => ''
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorRegex', 'sfValidatorZip')))
    {
      $arguments = array(
        array(
          'pattern' => ''
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorSchemaTimeInterval')))
    {
      $arguments = array(
        '', '',
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorReCaptcha')))
    {
      $arguments = array(
        array(
          'private_key' => ''
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorI18nAggregate')))
    {
      $arguments = array(
        array(),
        array('cultures' => array('en_GB'))
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfValidatorFromDescription')))
    {
      $arguments = array(
        '  String',
        array()
      );
    }

    try
    {
      $validator = $reflection->newInstanceArgs($arguments);
    }
    catch(Exception $e)
    {
      throw $e;
      continue;
    }

    $validatorMessages = $validator->getDefaultMessages();

    foreach($validatorMessages as $message)
    {
      if(empty($message))
      {
        continue;
      }
      $messages[] = $message;
    }
  }
}


// widgets
$widgets = sfFinder::type('file')->name('sfWidgetForm*.php')->in($siftLibDir.'/form');
foreach($widgets as $file)
{
  $classes = sfToolkit::extractClasses($file);

  foreach($classes as $class)
  {
    //
    $reflection = new sfReflectionClass($class);
    if($reflection->isAbstract())
    {
      continue;
    }

    // those which have empty_label
    if(!$reflection->isSubclassOfOrIsEqual(array(
                                                 'sfWidgetFormChoice',
                                                 'sfWidgetFormFilterInput',
                                                 'sfWidgetFormFilterDate',
                                                 'sfWidgetFormInputFileEditable',
                                                 'sfWidgetFormDualList')))
    {
      continue;
    }

    $arguments = array();

    // prepare arguments
    if($reflection->isSubclassOfOrIsEqual(array('sfWidgetFormFilterDate')))
    {
      $arguments = array(
        array(
          'from' => null,
          'to' => null
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfWidgetFormInputFileEditable')))
    {
      $arguments = array(
        array(
          'file_src' => ''
        )
      );
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfWidgetFormDualList', 'sfWidgetFormChoice')))
    {
      $arguments = array(
        array(
          'choices' => array()
        )
      );
    }

    try
    {
      $widget = $reflection->newInstanceArgs($arguments);
    }
    catch(Exception $e)
    {
      throw $e;
      continue;
    }

    $label = $widget->getOption('empty_label');

    if(!empty($label))
    {
      $messages[] = $label;
    }

    if($reflection->isSubclassOfOrIsEqual(array('sfWidgetFormDateRange')))
    {
      $template = $widget->getOption('template');
      if(!empty($template))
      {
        $messages[] = $template;
      }
    }
    elseif($reflection->isSubclassOfOrIsEqual(array('sfWidgetFormDualList')))
    {
      foreach(array('available', 'associated', 'select_all', 'unselect_all', 'inverse_selection', 'filter_placeholder') as $name)
      {
        if($label = $widget->getOption('label_'.$name))
        {
          $messages[] = $label;
        }
      }
    }

    if($reflection->isSubclassOfOrIsEqual(array('sfWidgetFormChoice')))
    {
      if($widget->getOption('translate_choices'))
      {
        $choices = $widget->getChoices();

        if(count($choices))
        {
          foreach($choices as $choice)
          {
            if(!empty($choice))
            {
              $messages[] = $choice;
            }
          }
        }
      }
    }

    $label = $widget->getOption('delete_label');
    if(!empty($label))
    {
      $messages[] = $label;
    }

  }
}

// validator extracted
$messages = array_unique(array_values($messages));

foreach($cultures as $culture)
{
  printf("Preparing culture %s\n", $culture);

  $source = new sfI18nMessageSourceGettext($sourceDir);
  $source->setCulture($culture);
  $source->load($formCatalogueName);

  $currentMessages = $source->getMessages();

  $translated = 0;
  $catched = array();
  foreach($currentMessages as $catalogue => $cMessages)
  {
    foreach($cMessages as $original => $cMessage)
    {
      $catched[] = $original;
      if(!empty($cMessage[0]))
      {
        $translated++;
      }
    }
  }

  $po = new sfI18nGettextPo($source->getOriginalSource().'/'.$culture.'/'.$formCatalogueName.'.po');
  $po->load();
  $array = $po->toArray();
  $total = count($array['strings']);

  $old = array_diff($catched, $messages);
  $new = array_diff($messages, $catched);

  printf("Found %s old messages\n", count($old));

  print(str_repeat('-', 20)."\n");
  printf("Statictics for %s\n", $culture);
  print(str_repeat('-', 20)."\n");
  printf("Total messages: %s\n", $total);
  printf("Translated messages: %s\n", $translated);

  if($total > 0)
  {
    printf("Percentage done: %s%%\n", round(($translated / $total) * 100, 2));
  }
  else
  {
    print("Percentage done: 0%");
  }

  print(str_repeat('-', 20)."\n");

  foreach($old as $oldMessage)
  {
    // printf("      %s\n", $oldMessage);
    $source->delete($oldMessage, $formCatalogueName);
  }

  printf("Found %s new messages\n", count($new));

  foreach($new as $newMessage)
  {
    if(empty($newMessage))
    {
      continue;
    }
    // printf("      %s\n", $newMessage);
    $source->append($newMessage);
  }

  $source->save($formCatalogueName);
}

echo "Done.\n";
