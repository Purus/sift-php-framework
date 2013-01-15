<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Admin generator.
 *
 * This class generates an admin module.
 *
 * @package    Sift
 * @subpackage generator
 * @todo       Needs refactoring!
 */
class sfAdminGenerator extends sfCrudGenerator
{

  /**
   * Initialize the generator
   * 
   * @param sfGeneratorManager $generatorManager
   */
  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);
    $this->setGeneratorClass('sfAdmin');
  }


  /**
   * Returns the getter either non-developped: 'getFoo' or developped: '$class->getFoo()'.
   *
   * @param string  $column     The column name
   * @param boolean $developed  true if you want developped method names, false otherwise
   * @param string  $prefix     The prefix value
   *
   * @return string PHP code
   */
  public function getColumnGetter($column, $developed = false, $prefix = '', $params = array())
  {
    if($column instanceof sfAdminColumn)
    {
      if($column->isI18n())
      {
        if(isset($params['culture']))
        {
          $getter = sprintf('getTranslationForCulture(\'%s\', %s)',$column->getPhpName(), $params['culture']);
          if($developed)
          {
            $getter = sprintf('$%s%s->%s', $prefix, $this->getSingularName(), $getter);
          }
        }
        else
        {
          $getter = sprintf('get%s', sfInflector::camelize($column->getPhpName()));
          if($developed)
          {
            $getter = sprintf('$%s%s->%s()', $prefix, $this->getSingularName(), $getter);
          }
        }
        return $getter;
      }
      else
      {
        $getter = sprintf('get%s', sfInflector::camelize($column->getPhpName()));
      }      
    }
    else // $column is string
    {
      if(isset($params['culture']))
      {
        $getter = sprintf('getTranslationForCulture(\'%s\', %s)',$column, $params['culture']);
        if($developed)
        {
          $getter = sprintf('$%s%s->%s', $prefix, $this->getSingularName(), $getter);
        }
      }
      else
      {
        $getter = sprintf('get%s', sfInflector::camelize($column));
        if($developed)
        {
          $getter = sprintf('$%s%s->%s()', $prefix, $this->getSingularName(), $getter);
        }
      }      
    }
    
    if($developed)
    {
      $getter = sprintf('$%s%s->%s()', $prefix, $this->getSingularName(), $getter);
      return $getter;
    }

    return array($getter, $params);
  }

  function getColumnSetter($column, $value, $singleQuotes = false, $prefix = 'this->')
  {
    if($singleQuotes)
    {
      $value = sprintf("'%s'", $value);
    }
    return sprintf('$%s%s->set(\'%s\', %s)', $prefix, $this->getSingularName(), $column->getName(), $value);
  }

  /**
   * Return object helper for given helper name and column
   *
   * @param <type> $helperName
   * @param <type> $column
   * @param <type> $params
   * @param <type> $localParams
   * @return <type> string
   */
  function getPHPObjectHelper($helperName, $column, $params, $localParams = array())
  {
    if(isset($params['culture']))
    {
      $culture = $params['culture'];
      unset($params['culture']);
    }
      
    $params = $this->getObjectTagParams($params, $localParams);

    /*
    return sprintf('object_%s($%s, %s, %s)', $helperName, $this->getSingularName(), var_export($this->getColumnGetter($column, false, '', $localParams), true), $params);
    */
  
    // i18n handling
    if($column->isI18n())
    {
      if(!isset($culture))
      {
        $culture = sfContext::getInstance()->getUser()->getCulture();
      }
      // getter is different
      $getter = array('getTranslationForCulture', array($column->getName(), $culture));
      return sprintf('object_%s($%s, %s, %s)', $helperName, $this->getSingularName(), var_export($getter, true), $params);
    }
    else if($column->isRelationAlias() && $column->getPhpName() == "Tags")
    {
      // tags relation table
      $relationTable = $column->getTable()->getRelation('Tags')->getTable();
      if($relationTable->hasI18n())
      {
        $getter = array('getTagsForCulture', array($culture));
        return sprintf('object_%s($%s, %s, %s)', $helperName, $this->getSingularName(), var_export($getter, true), $params);
      }
      else
      {
        $getter = array('getTags', array());
        return sprintf('object_%s($%s, %s, %s)', $helperName, $this->getSingularName(), var_export($getter, true), $params);
      }

    }
    else
    {
      return sprintf('object_%s($%s, %s, %s)', $helperName, $this->getSingularName(), var_export($this->getColumnGetter($column, false, '', $localParams), true), $params);
    }
  
  }

  /**
   * Returns HTML code for a column in filter mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getColumnFilterTag($column, $params = array())
  {
    $user_params = $this->getParameterValue('list.fields.'.$column->getPhpName().'.params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params      = $user_params ? array_merge($params, $user_params) : $params;

    if($column->isComponent())
    {
      return "get_component('".$this->getModuleName()."', '".$column->getName()."', array('type' => 'filter'))";
    }
    else if($column->isPartial())
    {
      return "get_partial('".$column->getName()."', array('type' => 'filter', 'filters' => \$filters))";
    }

    $type = $column->getType();

    $default_value = "isset(\$filters['".$column->getName()."']) ? \$filters['".$column->getName()."'] : null";
    $unquotedName = 'filters['.$column->getName().']';
    $name = "'$unquotedName'";

    // foreign key or relation alias
    if($column->isForeignKey() || $column->isRelationAlias())
    {
      $params = $this->getObjectTagParams($params, array('include_blank' => true, 'related_class'=> $column->getForeignClassName(), 'text_method' => '__toString', 'control_name' => $unquotedName));
      // FIXME: HACKISH?! why is this here?
      if($column->isI18n())
      {
        return "";
      }
      else
      {      
      }
      return "object_select_tag($default_value, null, $params)";
    }
    elseif($type == 'date') // CreoleTypes::DATE
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'class' => 'calendar'));
      return "input_date_range_tag($name, $default_value, $params)";
    }
    else if ($type == 'timestamp') // CreoleTypes::TIMESTAMP
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'withtime' => true, 'class' => 'calendar'));
      return "input_date_range_tag($name, $default_value, $params)";
    }
    else if($type == 'boolean') // CreoleTypes::BOOLEAN
    {
      $defaultIncludeCustom = '__("yes or no", array(), "myAdmin/controls")';
      //$defaultIncludeCustom = '__("yes or no")';

      $option_params = $this->getObjectTagParams($params, array('include_custom' => $defaultIncludeCustom));
      $params = $this->getObjectTagParams($params);

      
      // little hack
      // $option_params = preg_replace("/'".preg_quote($defaultIncludeCustom)."'/", $defaultIncludeCustom, $option_params);
      $option_params = @preg_replace("/'".preg_quote($defaultIncludeCustom, '/')."'/", $defaultIncludeCustom, $option_params);
      

      $options = "options_for_select(array(1 => __('yes', array(), 'myAdmin/controls'), 0 => __('no', array(), 'myAdmin/controls')), $default_value, $option_params)";

      return "select_tag($name, $options, $params)";
    }    
    else if($type == 'string')
    {
      $size = ($column->getSize() < 15 ? $column->getSize() : 15);
      $params = $this->getObjectTagParams($params, array('size' => $size));
      return "input_tag($name, $default_value, $params)";
    }
    else if($type == 'integer')
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "input_tag($name, $default_value, $params)";
    }    
    else if($type == 'float' || $type == 'double' || $type == 'decimal')
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "input_tag($name, $default_value, $params)";
    }
    else
    {
      $params = $this->getObjectTagParams($params, array('disabled' => true));
      return "input_tag($name, $default_value, $params)";
    }
  }

  public function getColumnShowTag($column, $params = array())
  {
    return $this->getColumnListTag($column, $params, 'show');
  }
  
  public function getColumnCreateTag($column, $params = array())
  {
    return $this->getColumnEditTag($column, $params, 'create');
  }
  
  /**
   * Returns HTML code for a column in edit mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getColumnEditTag($column, $params = array(), $type = 'edit')
  {
    // user defined parameters
    $user_params = $this->getParameterValue($type.'.fields.'.$column->getPhpName().'.params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params      = $user_params ? array_merge($params, $user_params) : $params;

    // component
    if($column->isComponent())
    {
      return "get_component('".$this->getModuleName()."', '".$column->getPhpName()."', array('type' => '".$type."', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }
    elseif($column->isPartial())
    {
      return "get_partial('".$column->getPhpName()."', array('type' => '".$type."', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }    

    // default parameter values
    $column_type = $column->getType();
    // this is used by javascript validation
    if($column_type)
    {
      $params['class'] = $column_type;
    }


    $params = array_merge(array(
      'control_name' => $this->getSingularName().'['.$column->getPhpName().']'), $params);

    $getter = $this->getColumnGetter($column, false, '', $params);
    
    $inputType = $this->getParameterValue($type.'.fields.'.$column->getPhpName().'.type');
    
    // user sets a specific tag to use
    if($inputType)
    {
      if($inputType == 'plain')
      {
        return $this->getColumnListTag($column, $params);
      }
      else
      {
        return $this->getPHPObjectHelper($inputType, $column, $params);
      }
    }

    if($column_type == 'enum')
    {
      return 'enum!';
    }

    // guess the best tag to use with column type
    return parent::getCrudColumnEditTag($column, $params);
  }

  /**
   * Returns HTML code for a column in list mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getColumnListTag($column, $params = array(), $type = 'list')
  {
    $user_params = $this->getParameterValue($type . '.fields.'.$column->getName().'.params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params      = $user_params ? array_merge($params, $user_params) : $params;

    $cType        = $column->getType();

    $columnGetter = $this->getColumnGetter($column, true, '', $params);

    if($column->isComponent())
    {
      return "get_component('".$this->getModuleName()."', '".$column->getName()."', array('type' => '{$type}', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }
    elseif($column->isPartial())
    {
      return "get_partial('".$column->getName()."', array('type' => '{$type}', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }    
    elseif($cType == 'date' || $cType == 'timestamp')
    {
      $format = isset($params['date_format']) ? $params['date_format'] : ($cType == 'date' ? 'D' : 'f');
      return "($columnGetter !== null && $columnGetter !== '') ? format_date($columnGetter, \"$format\") : ''";
    }
    elseif($cType == 'boolean')
    {
      return "$columnGetter ? image_tag(sfConfig::get('sf_admin_web_dir').'/images/tick-small.png') : image_tag(sfConfig::get('sf_admin_web_dir').'/images/cross-small.png')";
    }
    elseif($column->isSortable())
    {
      $helperName = 'admin_object_sortable_list';
      $params['module'] = $this->getModuleName();
      
      $params = $this->getObjectTagParams($params);

      $columnGetter = $this->getColumnGetter($column);
 
      return sprintf('%s($%s, %s, %s)', $helperName, $this->getSingularName(), var_export($columnGetter, true), $params);
    }
    elseif(!$column->isPrimaryKey() && $column->isForeignKey() && !$column->isRelationAlias())
    {
      list($method, $params) = $this->getColumnGetter($column->getForeignClassName(), false, '', $params);
      return sprintf('$%s->%s()', $this->getSingularName(), $method);
    }
    else
    {
      if($column->isI18n())
      {
        return $columnGetter;
      }
      else
      {
        return "$columnGetter";
      }
    }
  }

  /**
   * Get option to action (used by batch action select)
   *
   * @param <type> $actionName
   * @param <type> $params
   * @return <type> string
   */
  function getOptionToAction($actionName, $params = array())
  {
    if(isset($params['name']))
    {
      $name = $params['name'];
    }
    elseif($actionName[0] == '_')
    {
      $name = substr($actionName, 1);
    }
    else
    {
      $name = $actionName;
    }
    return sprintf('<option value="%s">%s</option>', $this->getBatchActionRequestName($actionName, $params), sprintf("[?php echo __('%s', array(), 'myAdmin/messages'); ?]", $this->escapeString($name)));
  }

  /**
   * Returns table colspan value for given list table
   *
   * @return <type> integer
   */
  public function getListTableColspan()
  {
    $count  = count($this->getColumns('list.display'));
    if($this->getParameterValue('list.object_actions'))
    {
      $count += 1;
    }

    $batchActions = $this->getParameterValue('list.batch_actions');
    if($batchActions)
    {
      $count += count($batchActions);
    }

    return $count;
  }

  /**
   * Returns batch request name for given action name
   *
   * @param <type> $actionName action name
   * @return <type> string
   */
  public function getBatchActionRequestName($actionName)
  {
    return str_replace('_', '', sfInflector::classify(sfInflector::tableize($actionName)));
  }

  /**
   * Returns HTML code for a help text.
   *
   * @param string The column name
   * @param string The field type (list, edit)
   *
   * @return string HTML code
   */
  public function getHelp($column, $type = '')
  {
    $help = $this->getParameterValue($type.'.fields.'.$column->getPhpName().'.help');
    if($help)
    {
      return sprintf('<div class="help">[?php echo __(\'%s\'); ?]</div>', $this->escapeString($help));
    }
    return '';
  }

  /**
   * Returns HTML code for a help icon.
   *
   * @param string The column name
   * @param string The field type (list, edit)
   *
   * @return string HTML code
   */
  public function getHelpAsIcon($column, $type = '')
  {
    $help = $this->getParameterValue($type.'.fields.'.$column->getPhpName().'.help');
    if($help)
    {
      return "[?php echo image_tag(sfConfig::get('sf_admin_web_dir').'/images/question.png', array('alt' => __('".$this->escapeString($help)."'), 'title' => __('".$this->escapeString($help)."'))) ?]";
    }
    return '';
  }

  /**
   * Returns HTML code for an action link.
   *
   * @param string  The action name
   * @param array   The parameters
   * @param boolean Whether to add a primary key link or not
   *
   * @return string HTML code
   */
  public function getLinkToAction($actionName, $params, $pk_link = false)
  {
    $options = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();

    $class = array();

    // default values
    if($actionName[0] == '_')
    {
      $actionName = substr($actionName, 1);
      $name       = $actionName;
      $action     = $actionName;
      if($actionName == 'delete')
      {
        if(!isset($options['confirm']))
        {
          $class[] = 'confirm';
        }
      }
    }
    else
    {
      $name = isset($params['name']) ? $params['name'] : $actionName;
      if(isset($params['icon']) && sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->err('{sfAdminGenerator} Usage of icons is deprecated. Specify action icon by using css.');
      }
      $action = isset($params['action']) ? $params['action'] : sfInflector::camelize($actionName);
    }
    
    if(preg_match('|delete|i', $action))
    {
      $class[] = 'delete';
    }

    // deleteAllSimilar => delete-all-similar
    $class[] = str_replace('_', '-', sfInflector::underscore($action));

    if(!empty($options['class']))
    {
      $class[] = $options['class'];
    }
    
    // put new class
    $options['class'] = join(' ', array_unique($class));

    if(isset($params['pk_identifier']))
    {
      $pk         = $this->getPrimaryKey(true);
      $value      = $this->getColumnGetter($pk, true);
      $pk_params  = sprintf('%s=\'.%s', $params['pk_identifier'], $value);
      $url_params = $pk_link ? '?'.$pk_params : '\'';
    }
    else
    {
      $url_params = $pk_link ? '?'.$this->getPrimaryKeyUrlParams() : '\'';
    }

    $module = $this->getModuleName();
    if(isset($params['module']))
    {
      $module = $params['module'];
    }
    $title  = isset($options['title']) ? $options['title'] : $name;
    $url    = sprintf('%s/%s%s', $module, $action, $url_params);

    $html = '';

    if(isset($params['condition']))
    {
      $html .= $this->buildCondition($params['condition']);
    }

    $html .= '<li>'  . "\n";
    $html .= '<a href="[?php echo url_for(\''.$url.'); ?]" title="[?php echo __(\''.$title.'\', array(), \'myAdmin/messages\'); ?]" class="'.$options['class'].'"><span>[?php echo __(\''.$name.'\', array(), \'myAdmin/messages\'); ?]</span></a>';
    $html .= '</li>'  . "\n";

    if(isset($params['condition']))
    {
      $html .= '[?php endif; ?]' . "\n";
    }
    
    return $html;
  }
  
  /**
   * Is field type labelable?
   *
   * @param <type> $type
   * @return <type> boolean
   */
  public function isLabelableType($column, $type, $action = 'edit')
  {
    // check the settings
    $parameters = $this->getFieldParameterValue($column->getPhpName(), $action);
    if($parameters)
    {
      if(isset($parameters['is_labelable']))
      {
        return (boolean)$parameters['is_labelable'];
      }
    }

    if($column->isI18n() || $column->isRelationAlias()
      || $column->isPartial() || $column->isComponent())
    {
      return false;
    }
    
    switch($type)
    {
      // case 'admin_double_select_list':
      case 'admin_double_list':
      case 'plain':
        return false;
      break;
    }
    return true;
  }

  /**
   * Hide label?
   *
   * @param <type> $column
   * @param <type> $type
   * @return <type> boolean
   */
  public function hideLabel($column, $type = '')
  {
    $hide = $this->getParameterValue('fields.'.$column->getName().'.hide_label');
    if(is_null($hide) && $type)
    {
      $hide = $this->getParameterValue($type.'.fields.'.$column->getName().'.hide_label');
    }
    if($hide)
    {
      return true;
    }  
    return false;
  }

  /**
   * Returns css class for given column
   * 
   * @param <type> $column
   * @return <type> string
   */
  public function getCssClass($column)
  {
    $class = array();

    if($column->isPrimaryKey())
    {
      $class[] = 'primary-key';
    }

    if($column->isLink())
    {
      $class[] = 'link';
    }

    if(!$column->isPrimaryKey())
    {
      $class[] = $column->getType();
    }

    return join(' ', $class);
  }

  /**
   * Returns tab href
   *
   * @param <type> $name
   * @return <type> string
   */
  public function getTabHref($name)
  {
    if($name == 'NONE')
    {
      $name = 'basic';
    }
    return 's-' . Doctrine_Inflector::urlize($name);
  }

  /**
   *
   * @param <type> $name
   * @return <type> string
   */
  public function getTabName($name)
  {
    if($name == 'NONE')
    {
      $name = 'Basic';
    }
    return $name;
  }

  /**
   * Returns HTML code for an action button.
   *
   * @param string  The action name
   * @param array   The parameters
   * @param boolean Whether to add a primary key link or not
   *
   * @return string HTML code
   */
  public function getButtonToAction($actionName, $params, $pk_link = false)
  {
    $params   = (array) $params;
    $options  = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();
    $method   = 'link_to';
    $only_for = isset($params['only_for']) ? $params['only_for'] : null;

    $default_class = 'action-'.str_replace('_', '-', sfInflector::tableize($actionName));

    // default values
    if($actionName[0] == '_')
    {
      $actionName     = substr($actionName, 1);
      $default_name   = strtr($actionName, '_', ' ');
      $default_action = $actionName;
      $default_class  = 'action-'.str_replace('_', '-', sfInflector::tableize($actionName));

      if(in_array($actionName, array('save', 'save_and_add', 'save_and_list')))
      {
        $method           = 'submit_tag';
        $options['name']  = $actionName;
        $options['value'] = $actionName;
      }
      elseif(in_array($actionName, array('list', 'create', 'export')))
      {
        $options['class'] = $default_class . ' button';
      }
      elseif(in_array($actionName, array('delete')))
      {       
        $options['class'] = $default_class . ' button confirm';
        $pk_link          = true;
      }      

      elseif(in_array($actionName, array('edit')))
      {
        $options['class'] = $default_class . ' button';
        $pk_link          = true;
      }
      else
      {
        throw new sfConfigurationException(sprintf('Invalid default action "_%s"', $actionName));
      }
    }
    else
    {
      $default_name     = strtr($actionName, '_', ' ');
      $default_action   = sfInflector::camelize($actionName);
      $options['class'] = isset($params['class']) ? ('action-'.$params['class'] . ' button') : 'button';

      if(preg_match('|delete|i', $actionName))
      {
        $options['class'] = $default_class . ' action-delete button';
      }
    }

    $name   = isset($params['name']) ? $params['name'] : $default_name;
    $action = isset($params['action']) ? $params['action'] : $default_action;
    $url_params = $pk_link ? '?'.$this->getPrimaryKeyUrlParams() : '\'';

    if(isset($options['confirm']) && $options['confirm'])
    {
      $options['class'] = isset($options['class']) ? $options['class'] . ' confirm' : 'confirm';
      unset($options['confirm']);
    }

    if(!isset($options['class']))
    {
      if($default_class)
      {
        $options['class'] = $default_class;
      }
    }
    
    $html = '';

    if(isset($params['condition']))
    {
      $html .= $this->buildCondition($params['condition']);
    }

    if($only_for == 'edit')
    {
      $html .= '[?php if('.$this->getPrimaryKeyIsSet().'): ?]'."\n";
    }
    elseif($only_for == 'create')
    {
      $html .= '[?php if(!'.$this->getPrimaryKeyIsSet().'): ?]'."\n";
    }
    else if($only_for !== null)
    {
      throw new sfConfigurationException(sprintf('The "only_for" parameter can only takes "create" or "edit" as argument ("%s")', $only_for));
    }

    $html .= '<li>';

    if($method == 'submit_tag')
    {
      $html .= '[?php echo submit_tag(__(\''.$name.'\', array(), \'myAdmin/messages\'), '.$this->asPhp($options).') ?]';
    }
    elseif($method == 'link_to')
    {
      $html .= '[?php echo link_to(\'<span>\'.__(\''.$name.'\', array(), \'myAdmin/messages\').\'</span>\', \''. $this->getModuleName().'/'.$action.$url_params.', '.$this->asPhp($options).'); ?]';
    }
    else
    {
      $html .= '[?php echo button_to(__(\''.$name.'\', array(), \'myAdmin/messages\'), \''.$this->getModuleName().'/'.$action.$url_params.', '.$this->asPhp($options).') ?]';
    }

    $html .= '</li>'."\n";

    if($only_for !== null)
    {
      $html .= '[?php endif; ?]'."\n";
    }

    if(isset($params['condition']))
    {
      $html .= '[?php endif; ?]'."\n";
    }

    return $html;
  }

  public function buildCondition($params)
  {
    if(!isset($params['function']))
    {
      throw new sfConfigurationException('Generator condition parameters required "function" to be set.');
    }

    // $this->object->func(
    $condition =  '$'.$this->getSingularName().'->'.$params['function'].'(';

    // function parameters
    if(isset($params['params']))
    {
      $condition .= $params['params'];
    }

    // close bracket
    $condition .= ')';

    $html      = '[?php if(';

    if(isset($params['invert']))
    {
      $html .= '!';
    }

    $html .= $condition;

    if(isset($params['equal']))
    {
      $html .= sprintf('== %s', $params['equal']);
    }
    elseif(isset($params['not_equal']))
    {
      $html .= sprintf('!= %s', $params['not_equal']);
    }

    // if ending
    $html .= '): ?]' . "\n";
    return $html;
  }

  public function getExportFields()
  {
    $fields = $this->getParameterValue('export.fields');
    if($fields === null)
    {
      return array();
    }

    $f = array();
    
    foreach($fields as $field)
    {
      $f[] = $this->getAdminColumnForField($field);
    }
    
    return $f;
  }

  public function getExportFilename()
  {
    $filename = $this->getParameterValue('export.filename');
    if($filename !== null)
    {
      return sfToolkit::replaceConstants($filename);
    }
    else
    {
      $filename = sfInflector::underscore($this->getSingularName());
    }
    return $filename;
  }

  /**
   * Wraps a content for I18N.
   *
   * @param string The key name
   * @param string The default value
   *
   * @return string HTML code
   */
  public function getI18NString($key, $default = null, $withEcho = true)
  {
    $value = $this->escapeString($this->getParameterValue($key, $default));

    // find %%xx%% strings
    preg_match_all('/%%([^%]+)%%/', $value, $matches, PREG_PATTERN_ORDER);
    $this->params['tmp']['display'] = array();
    foreach($matches[1] as $name)
    {
      $this->params['tmp']['display'][] = $name;
    }

    $vars = array();
    foreach($this->getColumns('tmp.display') as $column)
    {
      if($column->isLink())
      {
        $vars[] = '\'%%'.$column->getName().'%%\' => link_to('.$this->getColumnListTag($column).', \''.$this->getModuleName().'/edit?'.$this->getPrimaryKeyUrlParams().')';
      }
      elseif($column->isPartial())
      {
        $vars[] = '\'%%_'.$column->getName().'%%\' => '.$this->getColumnListTag($column);
      }
      else if($column->isComponent())
      {
        $vars[] = '\'%%~'.$column->getName().'%%\' => '.$this->getColumnListTag($column);
      }
      else
      {
        $vars[] = '\'%%'.$column->getName().'%%\' => '.$this->getColumnListTag($column);
      }
    }

    // strip all = signs
    $value = preg_replace('/%%=([^%]+)%%/', '%%$1%%', $value);

    $i18n = '__(\''.$value.'\', '."\n".'array('.implode(",\n", $vars).'))';

    return $withEcho ? '[?php echo '.$i18n.' ?]' : $i18n;
  }
  

  /**
   * Loads primary keys.
   *
   * @throws sfException
   */
  protected function loadPrimaryKeys()
  {
    $identifier = $this->table->getIdentifier();
    if(is_array($identifier))
    {
      foreach($identifier as $_key)
      {
        $this->primaryKey[] = new sfAdminColumn($_key, $this->table);
      }
    }
    else
    {
      $this->primaryKey[]   = new sfAdminColumn($identifier, $this->table);
    }

    if(!count($this->primaryKey))
    {
      throw new sfException(sprintf('Cannot generate a module for a model without a primary key (%s)', $this->getClassName()));
    }
  }

  function getAllColumns($withRelations = false)
  {
    $cols = $this->getTable()->getColumns();

    if($withRelations)
    {
      $rels = $this->getTable()->getRelations();
    }
    
    $columns = array();
    foreach ($cols as $name => $col)
    {
      // we set out to replace the foreign key to their corresponding aliases
      $found = null;
      if(isset($rels) && is_array($rels))
      {
        foreach($rels as $alias=>$rel)
        {
          $relType = $rel->getType();
          if ($rel->getLocal() == $name && $relType != Doctrine_Relation::MANY)
            $found = $alias;
        }
        if ($found)
        {
          $name = $found;
        }
      }
      $columns[] = new sfAdminColumn($name, $this->table);
    }
    return $columns;
  }

  function getAdminColumnForField($field, $flag = null)
  {
    return new sfAdminColumn($field, $this->table, $flag);
  }

  /**
   * Gets sfAdminColumn objects for a given category.
   *
   * @param string The parameter name
   *
   * @return array sfAdminColumn array
   */
  public function getColumns($paramName, $category = 'NONE')
  {
    $phpNames = array();

    // user has set a personnalized list of fields?
    $fields = $this->getParameterValue($paramName);
    
    if(is_array($fields))
    {
      // categories?
      if(isset($fields[0]))
      {
        // simulate a default one
        $fields = array('NONE' => $fields);
      }
      
      if(!$fields)
      {
        return array();
      }

      foreach($fields[$category] as $field)
      {
        list($field, $flags) = $this->splitFlag($field);
        $phpNames[] = $this->getAdminColumnForField($field, $flags);
      }
    }
    else
    {
      // no, just return the full list of columns in table
      return $this->getAllColumns();
    }

    return $phpNames;
  }

  /**
   * Returns all column categories.
   *
   * @param string  The parameter name
   *
   * @return array The column categories
   */
  public function getColumnCategories($paramName)
  {
    if(is_array($this->getParameterValue($paramName)))
    {
      $fields = $this->getParameterValue($paramName);
      // do we have categories?
      if(!isset($fields[0]))
      {
        return array_keys($fields);
      }
    }    
    return array('NONE');
  }
  
  /**
   * Gets a field parameter value.
   *
   * @param string The key name
   * @param string The type (list, edit, create, show)
   * @param mixed  The default value
   *
   * @return mixed The parameter value
   */
  protected function getFieldParameterValue($key, $type = '', $default = null)
  {
    $retval = $this->getValueFromKey($type.'.fields.'.$key, $default);
    if($retval !== null)
    {
      return $retval;
    }

    $retval = $this->getValueFromKey('fields.'.$key, $default);
    if($retval !== null)
    {
      return $retval;
    }

    if(preg_match('/\.name$/', $key))
    {
      // default field.name
      return sfInflector::humanize(($pos = strpos($key, '.')) ? substr($key, 0, $pos) : $key);
    }
    else
    {
      return null;
    }
  }

  /**
   * Gets a parameter value.
   *
   * @param string The key name
   * @param mixed  The default value
   *
   * @return mixed The parameter value
   */
  public function getParameterValue($key, $default = null)
  {
    $filter = false;
    
    if(in_array($key, array(
       // list of keys which are filtered using event system
      'list.batch_actions',
      'list.actions',
      'list.object_actions'
    )))
    {
      $filter    = true;
      $eventName = sprintf('admin.%s.%s', sfInflector::underscore($this->getClassName()), $key);      
    }
            
    if(preg_match('/^([^\.]+)\.fields\.(.+)$/', $key, $matches))
    {
      $result = $this->getFieldParameterValue($matches[2], $matches[1], $default);
    }
    else
    {
      $result = $this->getValueFromKey($key, $default);
    }

    return $filter ? sfCore::filterByEventListeners($result, $eventName) : $result;    
  }

  /**
   * Wraps content with a credential condition.
   *
   * @param string  The content
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function addCredentialCondition($content, $params = array())
  {
    if(isset($params['credentials']))
    {
      $credentials = $this->asPhp($params['credentials']);

      return <<<EOF
[?php if(\$sf_user->hasCredential($credentials)): ?]
$content
[?php endif; ?]
EOF;
    }
    else
    {
      return $content;
    }
  }

  /**
   * Gets modifier flags from a column name.
   *
   * @param string The column name
   *
   * @return array An array of detected flags
   */
  public function splitFlag($text)
  {
    $flags = array();
    while(in_array($text[0], array('=', '-', '+', '_', '~')))
    {
      $flags[] = $text[0];
      $text = substr($text, 1);
    }
    return array($text, $flags);
  }

  /**
   * Gets the value for a given key.
   *
   * @param string The key name
   * @param mixed  The default value
   *
   * @return mixed The key value
   */
  protected function getValueFromKey($key, $default = null)
  {
    $ref   =& $this->params;
    $parts =  explode('.', $key);
    $count =  count($parts);
    for($i = 0; $i < $count; $i++)
    {
      $partKey = $parts[$i];
      if(!isset($ref[$partKey]))
      {
        return $default;
      }
      if($count == $i + 1)
      {
        return $ref[$partKey];
      }
      else
      {
        $ref =& $ref[$partKey];
      }
    }
    return $default;
  }

  /**
   * Escapes a string.
   *
   * @param string
   *
   * @param string
   */
  protected function escapeString($string)
  {
    return preg_replace('/\'/', '\\\'', $string);
  }

  public function asPhp($variable)
  {
    return str_replace(array("\n", 'array (', 'array(  '), array('', 'array(', 'array('), var_export($variable, true));
  }

  public function asEvaluatedPhp($variable)
  {
    return $this->asPhp($variable);
    
    if (is_array($var)) {
        $code = 'array(';
        foreach ($var as $key => $value) {
            $code .= "'$key'=>".$this->asEvaluatedPhp($value).',';
        }
        $code = chop($code, ','); //remove unnecessary coma
        $code .= ')';
        return $code;
    } else {
        if (is_string($var)) {
            return "'".$var."'";
        } elseif (is_bool($code)) {
            return ($code ? 'TRUE' : 'FALSE');
        } else {
            return 'NULL';
        }
    }
    // return preg_replace(array("|^'|i", "|'$|i"), '', $this->asPhp($variable));
  }
  
 
}
