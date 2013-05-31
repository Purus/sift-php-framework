<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts messages from generator.yml files
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nYamlGeneratorExtractor extends sfI18nYamlExtractor {

  /**
   * Extracted strings
   *
   * @var array
   */
  protected $strings = array();

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
      'contexts' => array(
        'list', 'create', 'edit', 'show', 'export', 'quick_edit'
      ),
      'excluded_strings' => array(
        'list', 'delete', 'create', 'edit', 'save', 'save and add'
      )
  );

  /**
   * Extract i18n strings for the given content.
   *
   * @param  string The content
   *
   * @return array An array of i18n strings
   */
  public function extract($content)
  {
    $this->strings = array();

    $config = sfYaml::load($content);

    if(!isset($config['generator']['param']))
    {
      return array();
    }

    $params = $config['generator']['param'];

    // context less strings
    // names and help messages
    if(isset($params['fields']))
    {
      $this->getFromFields($params['fields']);
    }

    // global title
    if(isset($params['title']))
    {
      $this->strings[] = $params['title'];
    }

    // extract all contexts
    foreach($this->getOption('contexts', array()) as $context)
    {
      // titles
      if(isset($params[$context]['title']))
      {
        $this->strings[] = $params[$context]['title'];
      }

      // fields
      if(isset($params[$context]['fields']))
      {
        $this->getFromFields($params[$context]['fields']);
      }

      // batch actions (only valid for list context, but leave it here)
      if(isset($params[$context]['batch_actions']))
      {
        $this->getFromActions($params[$context]['batch_actions']);
      }

      // object actions (only valid for list context, but leave it here)
      if(isset($params[$context]['object_actions']))
      {
        $this->getFromActions($params[$context]['object_actions']);
      }

      // actions
      if(isset($params[$context]['actions']))
      {
        $this->getFromActions($params[$context]['actions']);
      }

      // display categories
      if(isset($params[$context]['display']) && !isset($params[$context]['display'][0]))
      {
        foreach(array_keys($params[$context]['display']) as $string)
        {
          if('NONE' == $string)
          {
            continue;
          }
          $this->strings[] = $string;
        }
      }
    }

    return $this->toExport($this->strings);
  }

  /**
   * Prepares the strings to export.
   *
   * @param array $strings
   * @return array
   */
  protected function toExport($strings)
  {
    $strings = array_unique($strings);
    $result = array();
    $excluded = $this->getOption('excluded_strings');
    foreach($strings as $string)
    {
      if(in_array($string, $excluded))
      {
        continue;
      }

      $result[] = $string;
    }
    return $result;
  }


  /**
   * Extact strings from array of actions
   *
   * @param array $actions
   */
  protected function getFromActions($actions)
  {
    foreach((array)$actions as $field => $options)
    {
      // this is a default action, but with custom name
      if($field[0] == '_' && isset($options['name']) && !empty($options['name']))
      {
        $this->strings[] = $options['name'];
      }
      elseif(isset($options['name']) && !empty($options['name']))
      {
        $this->strings[] = $options['name'];
      }
      // skip action names like _list, _delete
      elseif($field[0] != '_')
      {
        $this->strings[] = $field;
      }
    }
  }

  /**
   * Extract strings from fields definitions
   *
   * @param array $fields
   */
  protected function getFromFields($fields)
  {
    foreach($fields as $field => $options)
    {
      // not associative array
      if(is_numeric($field))
      {
        $field = $options;
        $options = array();
      }

      if(isset($options['name']) && !empty($options['name']))
      {
        $this->strings[] = $options['name'];
      }
      // name is missing will use the $field as name
      else
      {
        $name = sfUtf8::ucfirst(str_replace(
                array('_id', '_'), array('', ' '), $field));
        if(!empty($name))
        {
          $this->strings[] = $name;
        }
      }

      if(isset($options['help']) && !empty($options['help']))
      {
        $this->strings[] = $options['help'];
      }

      if(isset($options['widget']['options']))
      {
        foreach($options['widget']['options'] as $optionName => $value)
        {
          // this is label option
          if(strpos($optionName, 'label') !== false)
          {
            if(!empty($value))
            {
              $this->strings[] = $value;
            }
          }
        }
      }

      if(isset($options['editable']['title']))
      {
        $this->strings[] = $options['editable']['title'];
      }
    }
  }

}
