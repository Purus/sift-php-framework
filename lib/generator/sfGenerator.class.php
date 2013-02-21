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
abstract class sfGenerator implements sfIGenerator {

  /**
   * Generator class
   *
   * @var string
   */
  protected $generatorClass;

  /**
   * sfGeneratorManager holder
   *
   * @var sfGeneratorManager
   */
  protected $generatorManager;

  /**
   * Generated module name
   *
   * @var string
   */
  protected $generatedModuleName;

  /**
   * Module name
   *
   * @var string
   */
  protected $moduleName;

  /**
   * Theme name
   *
   * @var string
   */
  protected $theme;

  /**
   * Constructs the generator
   *
   * @param sfGeneratorManager $generatorManager
   * @param array $options
   */
  public function __construct(sfGeneratorManager $generatorManager)
  {
    $this->generatorManager = $generatorManager;
  }

  /**
   * Configures the generator
   *
   */
  public function configure()
  {

  }

  /**
   * Generates classes and templates.
   *
   * @param array An array of parameters
   *
   * @return string The cache for the configuration file
   */
  // abstract public function generate($params = array());

  /**
   * Generates PHP files for a given module name.
   *
   * @param string $generatedModuleName The name of module name to generate
   * @param array  $files               A list of template files to generate
   */
  protected function generatePhpFiles($generatedModuleName, $files = array())
  {
    foreach($files as $file)
    {
      $this->getGeneratorManager()->save($generatedModuleName . '/' . $file, $this->evalTemplate($file));
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
    return $this->replacePhpMarks($content);
  }

  /**
   * Replaces Sift constants in the $content
   *
   * @param string $content
   * @return string
   */
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
   * @param string $generatorClass The generator class
   */
  public function setGeneratorClass($generatorClass)
  {
    $this->generatorClass = $generatorClass;
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
   * @param string $moduleName The module name
   */
  public function setGeneratedModuleName($moduleName)
  {
    $this->generatedModuleName = $moduleName;
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
   * @param string $name
   * @return string
   */
  public function underscore($name)
  {
    $name = str_replace('\\', '_', $name);
    return sfInflector::underscore($name);
  }

  /**
   * Camelizes name
   *
   * @param string $name
   * @return string
   */
  public function camelize($name)
  {
    $name = str_replace('\\', '_', $name);
    $name = sfInflector::camelize($name);
    return strtolower($name[0]) . substr($name, 1);
  }

  /**
   * Array export. Export array to formatted php code
   *
   * @param array $values
   * @return string $php
   */
  protected function arrayExport($values)
  {
    $php = var_export($values, true);
    $php = str_replace("\n", '', $php);
    $php = str_replace('array (  ', 'array(', $php);
    $php = str_replace(',)', ')', $php);
    $php = str_replace('  ', ' ', $php);
    return $php;
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
        'method' => $method,
        'arguments' => $arguments,
        'generator' => $this)));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

}
