<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGenerator is the abstract base class for all generators.
 *
 * @package    Sift
 * @subpackage generator
 */
abstract class sfGenerator
{
  protected
    $generatorClass      = '',
    $generatorManager    = null,
    $generatedModuleName = '',
    $theme               = 'default',
    $moduleName          = '',
    $table               = null,
    // has translation?
    $isI18n              = false,
    // field used for culture
    // "lang" or "culture"
    $i18nField           = null,
    // translations in separated table
    // tags schema does not use separate table
    $hasI18nRelation     = false,
    // has nested set feature?
    $isTree              = false,
    
    $hasTags             = false,
    $tagsClassName       = null,
    $hasTagsI18n         = false,
    // field for lang
    $tagsI18nCultureField = null,
    
    // sortable behaviour
    $actsAsSortable      = false,

    // taggable behaviour
    $actsAsTaggable      = false,

    $actsAsDataStorage   = false,

    // Doctrine_Template_AttachedFile
    $actsAsAttachedFile  = false,
    $actsAsAttachedFileColumn = null;


  /**
   * Initializes the current sfGenerator instance.
   *
   * @param sfGeneratorManager A sfGeneratorManager instance
   */
  public function initialize(sfGeneratorManager $generatorManager)
  {
    $this->generatorManager = $generatorManager;
  }

  /**
   * Configures the generator
   *
   */
  public function configure()
  {
    $this->table  = Doctrine::getTable($this->getClassName());

    // FIXME: refactor method names!
    // detect common behaviours
    $this->detectTranslation();
    $this->detectTagsRelation();
    $this->detectNestedSet();
    $this->detectSortableBehaviour();
    $this->detectDataStorage();
    $this->detectAttachedFileBehaviour();
  }

  protected function detectAttachedFileBehaviour()
  {
    $this->actsAsAttachedFile = $this->table->hasTemplate('Doctrine_Template_AttachedFile');
    if($this->actsAsAttachedFile)
    {
      $this->actsAsAttachedFileColumn = $this->table->getTemplate('Doctrine_Template_AttachedFile')
        ->getOption('field');
    }
  }

  protected function detectDataStorage()
  {
    $this->actsAsDataStorage = $this->table->hasTemplate('Doctrine_Template_DataStorage');
  }

  protected function detectSortableBehaviour()
  {
    // sortable?
    $this->actsAsSortable = $this->table->hasTemplate('Sortable');
  }

  protected function detectNestedSet()
  {
    // nested set
    $this->isTree = $this->table->getOption('treeImpl') == 'NestedSet';
  }

  protected function detectTranslation()
  {
    // translation detection
    if($this->table->hasRelation('Translation'))
    {
      $this->isI18n                 = true;
      $this->hasI18nRelation        = true;

      if($this->table->getRelation('Translation')->getTable()->hasColumn('lang'))
      {
        $this->i18nField = 'lang';
      }
      elseif($this->table->getRelation('Translation')->getTable()->hasColumn('culture'))
      {
        $this->i18nField = 'culture';
      }
      else
      {
        throw new sfInitializationException(sprintf('{sfGenerator} Cannot detect culture field for "%s".', $this->table->getName()));
      }
    }
    elseif($this->table->hasColumn('lang'))
    {
      $this->isI18n                 = true;
      $this->hasI18nRelation        = false;
      $this->i18nField              = 'lang';
    }
    elseif($this->table->hasColumn('culture'))
    {
      $this->i18nField              = 'culture';
      $this->isI18n                 = true;
    }
  }

  protected function detectTagsRelation()
  {
    // has tags
    if($this->table->hasRelation('Tags'))
    {
      $this->hasTags       = true;
      $relationTable       = $this->table->getRelation('Tags')->getTable();
      $this->tagsClassName = $relationTable->getOption('name');
      if($relationTable->hasColumn('lang'))
      {
        $this->hasTagsI18n = true;
        $this->tagsI18nCultureField = 'lang';
      }
      elseif($relationTable->hasColumn('culture')) // BC compatibility
      {
        $this->hasTagsI18n = true;
        $this->tagsI18nCultureField = 'culture';
      }
    }
  }

  public function hasTags()
  {
    return $this->hasTags;
  }

  public function hasTagsI18n()
  {
    return $this->hasTagsI18n;
  }

  public function getTagsClassName()
  {
    return $this->tagsClassName;
  }

  public function getTagsI18nCultureField()
  {
    return $this->tagsI18nCultureField;
  }

  protected function getTable()
  {
    return $this->table;
  }

  /**
   * Has this table translation?
   * 
   * @return <type> boolean
   */
  protected function isI18n()
  {
    return $this->isI18n;
  }

  /**
   * Has this table translation?
   *
   * @return <type> boolean
   */
  protected function hasI18nRelation()
  {
    return $this->hasI18nRelation;
  }

  /**
   * Has this table translation?
   *
   * @return <type> boolean
   */
  protected function getI18nField()
  {
    return $this->i18nField;
  }

  /**
   * Has this table nested set?
   *
   * @return <type> boolean
   */
  protected function isTree()
  {
    return $this->isTree;
  }

  /**
   * Does this model act as sortable?
   *
   * @return <type> boolean
   */
  protected function actsAsSortable()
  {
    return $this->actsAsSortable;
  }

  /**
   * Generates classes and templates.
   *
   * @param array An array of parameters
   *
   * @return string The cache for the configuration file
   */
  abstract public function generate($params = array());

  /**
   * Generates PHP files for a given module name.
   *
   * @param string The name of module name to generate
   * @param array  A list of template files to generate
   * @param array  A list of configuration files to generate
   */
  protected function generatePhpFiles($generatedModuleName, $templateFiles = array(), $configFiles = array())
  {
    // eval actions file
    $retval = $this->evalTemplate('actions/actions.class.php');

    // save actions class
    $this->getGeneratorManager()->getCache()->set('actions.class.php', $generatedModuleName.DIRECTORY_SEPARATOR.'actions', $retval);

    // eval components file
    $retval = $this->evalTemplate('actions/components.class.php');

    // save components class
    $this->getGeneratorManager()->getCache()->set('components.class.php', $generatedModuleName.DIRECTORY_SEPARATOR.'actions', $retval);

    // generate template files
    foreach ($templateFiles as $template)
    {
      // eval template file
      $retval = $this->evalTemplate('templates/'.$template);

      // save template file
      $this->getGeneratorManager()->getCache()->set($template, $generatedModuleName.DIRECTORY_SEPARATOR.'templates', $retval);
    }

    // generate config files
    foreach ($configFiles as $config)
    {
      // eval config file
      $retval = $this->evalTemplate('config/'.$config);

      // save config file
      $this->getGeneratorManager()->getCache()->set($config, $generatedModuleName.DIRECTORY_SEPARATOR.'config', $retval);
    }
  }

  /**
   * Evaluates a template file.
   *
   * @param string The template file path
   *
   * @return string The evaluated template
   */
  protected function evalTemplate($templateFile)
  {
    $templateFile = sfLoader::getGeneratorTemplate($this->getGeneratorClass(), $this->getTheme(), $templateFile);

    // eval template file
    ob_start();
    require($templateFile);
    $content = ob_get_clean();

    // replace [?php and ?]
    $content = $this->replacePhpMarks($content);

    $retval = "<?php\n".
              "// auto-generated by ".$this->getGeneratorClass()."\n".
              "// date: %s\n?>\n%s";
    $retval = sprintf($retval, date('Y/m/d H:i:s'), $content);

    return $retval;
  }

  protected function replaceConstants($content)
  {
    return sfToolkit::replaceConstants($content);
  }

  /**
   * Replaces PHP marks by <?php ?>.
   *
   * @param string The PHP code
   *
   * @return string The converted PHP code
   */
  protected function replacePhpMarks($text)
  {
    // replace [?php and ?]
    return str_replace(array('[?php', '[?=', '?]'), array('<?php', '<?php echo', '?>'), $text);
  }

  /**
   * Gets the generator class.
   *
   * @return string The generator class
   */
  public function getGeneratorClass()
  {
    return $this->generatorClass;
  }

  /**
   * Sets the generator class.
   *
   * @param string The generator class
   */
  public function setGeneratorClass($generator_class)
  {
    $this->generatorClass = $generator_class;
  }

  /**
   * Gets the sfGeneratorManager instance.
   *
   * @return string The sfGeneratorManager instance
   */
  protected function getGeneratorManager()
  {
    return $this->generatorManager;
  }

  /**
   * Gets the module name of the generated module.
   *
   * @return string The module name
   */
  public function getGeneratedModuleName()
  {
    return $this->generatedModuleName;
  }

  /**
   * Sets the module name of the generated module.
   *
   * @param string The module name
   */
  public function setGeneratedModuleName($module_name)
  {
    $this->generatedModuleName = $module_name;
  }

  /**
   * Gets the module name.
   *
   * @return string The module name
   */
  public function getModuleName()
  {
    return $this->moduleName;
  }

  /**
   * Sets the module name.
   *
   * @param string The module name
   */
  public function setModuleName($module_name)
  {
    $this->moduleName = $module_name;
  }

  /**
   * Gets the theme name.
   *
   * @return string The theme name
   */
  public function getTheme()
  {
    return $this->theme;
  }

  /**
   * Sets the theme name.
   *
   * @param string The theme name
   */
  public function setTheme($theme)
  {
    $this->theme = $theme;
  }

  /**
   * Underscores name
   * 
   * @param <type> $name
   * @return <type> string
   */
	public function underscore($name)
	{
		$name = str_replace('\\', '_', $name);
		return sfInflector::underscore($name);
	}

  /**
   * Camelizes name
   *
   * @param <type> $name
   * @return <type> string
   */
  public function camelize($name)
  {
    $name = str_replace('\\', '_', $name);
    $name = sfInflector::camelize($name);
    return strtolower($name[0]).substr($name, 1);
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = sfCore::getEventDispatcher()->notifyUntil(
      new sfEvent('generator.method_not_found', array(
          'method'    => $method,
          'arguments' => $arguments,
          'generator' => $this)));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
  
}
