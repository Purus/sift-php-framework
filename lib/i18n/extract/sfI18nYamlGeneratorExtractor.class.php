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
      'excluded_strings' => array(
          'Basic', 'list', 'delete', 'create', 'edit', 'save', 'save and add'
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

    // titles
    if(isset($params['list']['title']) && !in_array($params['list']['title'], $this->getOption('excluded_strings')))
    {
      $this->strings[] = $params['list']['title'];
    }

    if(isset($params['edit']['title']) && !in_array($params['edit']['title'], $this->getOption('excluded_strings')))
    {
      $this->strings[] = $params['edit']['title'];
    }

    if(isset($params['create']['title']) && !in_array($params['create']['title'], $this->getOption('excluded_strings')))
    {
      $this->strings[] = $params['create']['title'];
    }

    if(isset($params['show']['title']) && !in_array($params['show']['title'], $this->getOption('excluded_strings')))
    {
      $this->strings[] = $params['show']['title'];
    }

    if(isset($params['export']['title']) && !in_array($params['export']['title'], $this->getOption('excluded_strings')))
    {
      $this->strings[] = $params['export']['title'];
    }

    // names and help messages
    if(isset($params['fields']))
    {
      $this->getFromFields($params['fields']);
    }

    if(isset($params['list']['fields']))
    {
      $this->getFromFields($params['list']['fields']);
    }

    if(isset($params['edit']['fields']))
    {
      $this->getFromFields($params['edit']['fields']);
    }

    if(isset($params['create']['fields']))
    {
      $this->getFromFields($params['create']['fields']);
    }

    if(isset($params['show']['fields']))
    {
      $this->getFromFields($params['show']['fields']);
    }

    if(isset($params['list']['batch_actions']))
    {
      foreach($params['list']['batch_actions'] as $field => $options)
      {
        if(isset($options['name']) && !in_array($options['name'], $this->getOption('excluded_strings')))
        {
          $this->strings[] = $options['name'];
        }
        else
        {
          $this->strings[] = $field;
        }
      }
    }

    if(isset($params['list']['object_actions']))
    {
      foreach($params['list']['object_actions'] as $field => $options)
      {
        if(isset($options['name']) && !in_array($options['name'], $this->getOption('excluded_strings')))
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

    // edit categories
    if(isset($params['edit']['display']) && !isset($params['edit']['display'][0]))
    {
      foreach(array_keys($params['edit']['display']) as $string)
      {
        if('NONE' == $string || in_array($string, $this->getOption('excluded_strings')))
        {
          continue;
        }

        $this->strings[] = $string;
      }
    }

    // create categories
    if(isset($params['create']['display']) && !isset($params['create']['display'][0]))
    {
      foreach(array_keys($params['create']['display']) as $string)
      {
        if('NONE' == $string || in_array($string, $this->getOption('excluded_strings')))
        {
          continue;
        }

        $this->strings[] = $string;
      }
    }

    if(isset($params['show']['display']) && !isset($params['show']['display'][0]))
    {
      foreach(array_keys($params['show']['display']) as $string)
      {
        if('NONE' == $string || in_array($string, $this->getOption('excluded_strings')))
        {
          continue;
        }

        $this->strings[] = $string;
      }
    }

    return $this->strings;
  }

  protected function getFromFields($fields)
  {
    foreach($fields as $field => $options)
    {
      if(isset($options['name']) && !in_array($options['name'], $this->getOption('excluded_strings')))
      {
        $this->strings[] = $options['name'];
      }

      if(isset($options['help']) && !in_array($options['help'], $this->getOption('excluded_strings')))
      {
        $this->strings[] = $options['help'];
      }
    }
  }

}
