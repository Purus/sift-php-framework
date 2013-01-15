<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * CRUD generator.
 *
 * This class generates a basic CRUD module.
 *
 * @package    Sift
 * @subpackage generator
 */
abstract class sfCrudGenerator extends sfGenerator
{
  protected
    $singularName  = '',
    $pluralName    = '',
    $tableMap      = null,
    $primaryKey    = array(),
    $className     = '',
    $params        = array();

  /**
   * Generates classes and templates in cache.
   *
   * @param array The parameters
   *
   * @return string The data to put in configuration cache
   */
  public function generate($params = array())
  {
    $this->params = $params;

    $required_parameters = array('model_class', 'moduleName');
    foreach ($required_parameters as $entry)
    {
      if (!isset($this->params[$entry]))
      {
        $error = 'You must specify a "%s"';
        $error = sprintf($error, $entry);

        throw new sfParseException($error);
      }
    }

    $modelClass = $this->params['model_class'];

    if (!class_exists($modelClass))
    {
      $error = 'Unable to scaffold unexistant model "%s"';
      $error = sprintf($error, $modelClass);

      throw new sfInitializationException($error);
    }

    $this->setScaffoldingClassName($modelClass);

    // generated module name
    $this->setGeneratedModuleName('auto'.ucfirst($this->params['moduleName']));
    $this->setModuleName($this->params['moduleName']);

    // configure the model
    $this->configure();

    // load primary keys
    $this->loadPrimaryKeys();

    // theme exists?
    $theme = isset($this->params['theme']) ? $this->params['theme'] : 'default';
    $themeDir = sfLoader::getGeneratorTemplate($this->getGeneratorClass(), $theme, '');
    if (!is_dir($themeDir))
    {
      $error = 'The theme "%s" does not exist.';
      $error = sprintf($error, $theme);
      throw new sfConfigurationException($error);
    }

    $this->setTheme($theme);
    $templateFiles = sfFinder::type('file')->ignore_version_control()->name('*.php')->relative()->in($themeDir.'/templates');
    $configFiles = sfFinder::type('file')->ignore_version_control()->name('*.yml')->relative()->in($themeDir.'/config');

    $this->generatePhpFiles($this->generatedModuleName, $templateFiles, $configFiles);

    // require generated action class
    $data  = "require_once(sfConfig::get('sf_module_cache_dir').'/".$this->generatedModuleName."/actions/actions.class.php');\n";

    return $data;
  }

  /**
   * Returns PHP code for primary keys parameters.
   *
   * @param integer The indentation value
   *
   * @return string The PHP code
   */
  public function getRetrieveByPkParamsForAction($indent = 0)
  {
    $params = array();
    foreach($this->getPrimaryKey() as $pk)
    {
      // $params[] = "\$this->getRequestParameter('".sfInflector::underscore($pk->getPhpName())."')";
      $params[] = "\$this->getRequestParameter('".$pk->getPhpName()."')";
    }

    return implode(",\n".str_repeat(' ', max(0, $indent - strlen($this->singularName.$this->className))), $params);
  }

  /**
   * Returns PHP code for getOrCreate() parameters.
   *
   * @return string The PHP code
   */
  public function getMethodParamsForGetOrCreate()
  {
    $method_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      // $fieldName       = sfInflector::underscore($pk->getPhpName());
      $fieldName       = $pk->getPhpName();
      $method_params[] = "\$$fieldName = '$fieldName'";
    }

    return implode(', ', $method_params);
  }

  /**
   * Returns PHP code for getOrCreate() promary keys condition.
   *
   * @param boolean true if we pass the field name as an argument, false otherwise
   *
   * @return string The PHP code
   */
  public function getTestPksForGetOrCreate($fieldNameAsArgument = true)
  {
    $test_pks = array();
    foreach($this->getPrimaryKey() as $pk)
    {
      //$fieldName  = sfInflector::underscore($pk->getPhpName());
      $fieldName  = $pk->getPhpName();
      $test_pks[] = sprintf("!\$this->getRequestParameter(%s)", $fieldNameAsArgument ? "\$$fieldName" : "'".$fieldName."'");
    }

    return implode("\n     || ", $test_pks);
  }

  /**
   * Returns PHP code for primary keys parameters used in getOrCreate() method.
   *
   * @return string The PHP code
   */
  public function getRetrieveByPkParamsForGetOrCreate()
  {
    $retrieve_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      // $fieldName         = sfInflector::underscore($pk->getPhpName());
      $fieldName         = $pk->getPhpName();
      $retrieve_params[] = "\$this->getRequestParameter(\$$fieldName)";
    }

    return implode(",\n".str_repeat(' ', max(0, 45 - strlen($this->singularName.$this->className))), $retrieve_params);
  }

  /**
   * Sets the class name to use for scaffolding
   *
   * @param  string class name
   */
  protected function setScaffoldingClassName($className)
  {
    $this->singularName  = sfInflector::underscore($className);
    $this->pluralName    = $this->singularName.'s';
    $this->className     = $className;
  }

  /**
   * Gets the singular name for current scaffolding class.
   *
   * @return string
   */
  public function getSingularName()
  {
    return $this->singularName;
  }

  /**
   * Gets the plural name for current scaffolding class.
   *
   * @return string
   */
  public function getPluralName()
  {
    return $this->pluralName;
  }

  /**
   * Gets the class name for current scaffolding class.
   *
   * @return string
   */
  public function getClassName()
  {
    return $this->className;
  }
 
  /**
   * Gets PHP code for primary key condition.
   *
   * @param string The prefix value
   *
   * @return string PHP code
   */
  public function getPrimaryKeyIsSet($prefix = '')
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = $this->getColumnGetter($pk, true, $prefix);
    }

    return implode(' && ', $params);
  }

  /**
   * Gets object tag parameters.
   *
   * @param array An array of parameters
   * @param array An array of default parameters
   *
   * @return string PHP code
   */
  protected function getObjectTagParams($params, $default_params = array())
  {
    return var_export(array_merge($default_params, $params), true);
  }

  /**
   * Returns HTML code for a column in list mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getColumnListTag($column, $params = array())
  {
    $type         = $column->getType();
    $columnGetter = $this->getColumnGetter($column, true);

    if($type == 'timestamp') // CreoleTypes::TIMESTAMP
    {
      return "format_date($columnGetter, 'f')";
    }
    elseif ($type == 'date') // CreoleTypes::DATE
    {
      return "format_date($columnGetter, 'D')";
    }
    else
    {
      return "$columnGetter";
    }
  }

 /**
   * Returns PHP code to add to a URL for primary keys.
   *
   * @param string $prefix The prefix value
   *
   * @return string PHP code
   */
  public function getPrimaryKeyUrlParams($prefix = '', $full = false)
  {
    $params = array();
    foreach($this->getPrimaryKey() as $pk)
    {
      $fieldName = sfInflector::underscore($pk->getPhpName());
      if($full)
      {
        $params[] = sprintf("%s='.%s->%s()", $fieldName, $prefix, $this->getColumnGetter($pk, false));
      }
      else
      {
        $params[] = sprintf("%s='.%s", $fieldName, $this->getColumnGetter($pk, true, $prefix));
      }
    }

    return implode(".'&", $params);
  }

  /**
   * Gets the primary key name.
   *
   * @param Boolean $firstOne Whether to return the first PK or not
   *
   * @return array An array of primary keys
   */
  public function getPrimaryKey($firstOne = false)
  {
    return $firstOne ? $this->primaryKey[0] : $this->primaryKey;
  }
  
  /**
   * Returns HTML code for a column in edit mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getCrudColumnEditTag($column, $params = array())
  {
    $type = $column->getType();
    
    if($column->isForeignKey() || $column->isRelationAlias())
    {
      if(!$column->isNotNull() && !isset($params['include_blank']))
      {
        $params['include_blank'] = true;
      }

      // detect many to many relations
      if($column->isManyToMany())
      {
        // no blank on many to many!
        $params['include_blank'] = false;
        if($column->isTree())
        {
          $module = $this->getModuleName();
          if(isset($params['module']))
          {
            $module = $params['module'];
          }
          $component = $column->getName();
          if(isset($params['component']))
          {
            $component = $params['component'];
          }

          return "get_component('".$module."', '".$component."', array('type' => 'edit'))";

          // return $this->getPHPObjectHelper('admin_tree_list', $column, $params, array('related_class' => $column->getForeignClassName(), 'relation_name' => $column->getPhpName()));
        }
        else
        {
          return $this->getPHPObjectHelper('admin_double_list', $column, $params, array('related_class' => $column->getForeignClassName(), 'relation_name' => $column->getPhpName(), 'class' => $column->getForeignClassName()));
        }
      }
      else
      {
        return $this->getPHPObjectHelper('select_tag', $column, $params, array('related_class' => $column->getForeignClassName()));
      }

    }
    else if($type == 'date') // CreoleTypes::DATE
    {
      // rich=false not yet implemented
      return $this->getPHPObjectHelper('input_date_tag', $column, $params, array('rich' => true, 'class' => 'calendar', 'size' => 20));
    }
    else if ($type == 'timestamp') // CreoleTypes::TIMESTAMP
    {
      // rich=false not yet implemented
      return $this->getPHPObjectHelper('input_date_tag', $column, $params, array('rich' => true, 'withtime' => true, 'class' => 'calendar', 'size' => 20));
    }
    else if($type == 'boolean') // CreoleTypes::BOOLEAN
    {
      return $this->getPHPObjectHelper('checkbox_tag', $column, $params);
    }
    // else if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR)
    elseif($type == 'string' || $type == 'clob')
    {
      if($column->getSize() <= 255 && $type != 'clob')
      {
        $size = ($column->getSize() > 20 ? ($column->getSize() < 50 ? $column->getSize() : 50) : 20);
        return $this->getPHPObjectHelper('input_tag', $column, $params, array('size' => $size));
      }
      else
      {
        return $this->getPHPObjectHelper('textarea_tag', $column, $params, array('size' => '60x5'));
      }
    }
    // else if ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT)
    else if($type == 'integer')
    {
      return $this->getPHPObjectHelper('input_tag', $column, $params, array('size' => 7));
    }
    //else if ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL)
    else if($type == 'float' || $type == 'double' || $type == 'decimal')
    {
      return $this->getPHPObjectHelper('input_tag', $column, $params, array('size' => 10));
    }    
    else
    {
      return $this->getPHPObjectHelper('input_tag', $column, $params, array('disabled' => true));
    }
  }

  /**
   * Loads primary keys.
   *
   * This method is ORM dependant.
   *
   * @throws sfException
   */
  abstract protected function loadPrimaryKeys();

  /**
   * Generates a PHP call to an object helper.
   *
   * This method is ORM dependant.
   *
   * @param string The helper name
   * @param string The column name
   * @param array  An array of parameters
   * @param array  An array of local parameters
   *
   * @return string PHP code
   */
  abstract function getPHPObjectHelper($helperName, $column, $params, $localParams = array());

  /**
   * Returns the getter either non-developped: 'getFoo' or developped: '$class->getFoo()'.
   *
   * This method is ORM dependant.
   *
   * @param string  The column name
   * @param boolean true if you want developped method names, false otherwise
   * @param string The prefix value
   *
   * @return string PHP code
   */
  abstract function getColumnGetter($column, $developed = false , $prefix = '');

}
