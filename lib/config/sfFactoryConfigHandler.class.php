<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFactoryConfigHandler allows you to specify which factory implementation the
 * system will use.
 *
 * @package    Sift
 * @subpackage config
 */
class sfFactoryConfigHandler extends sfYamlConfigHandler {

  /**
   * Executes this configuration handler.
   *
   * @param array An array of absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   */
  public function execute($configFiles)
  {
    // parse the yaml
    $myConfig = $this->parseYamls($configFiles);

    $myConfig = sfToolkit::arrayDeepMerge(
        isset($myConfig['default']) && is_array($myConfig['default']) ? $myConfig['default'] : array(),
        isset($myConfig['all']) && is_array($myConfig['all']) ? $myConfig['all'] : array(),
        isset($myConfig[sfConfig::get('sf_environment')]) && is_array($myConfig[sfConfig::get('sf_environment')]) ? $myConfig[sfConfig::get('sf_environment')] : array()
    );

    // required services
    $requiredServices = array(
      'controller',
      'request',
      'response',
      'storage',
      'user',
      'view_cache',
      'i18n',
      'database_manager',
      'mailer'
    );

    // init our data and includes arrays
    $includes = array();
    $inits = array();
    $instances = array();

    // first check the required
    foreach($requiredServices as $required)
    {
      if(!isset($myConfig[$required]))
      {
        throw new sfParseException(sprintf('Configuration file "%s" is missing required service "%s" definition.', $configFiles[0], $required));
      }
      elseif(!isset($myConfig[$required]['class']))
      {
        throw new sfParseException(sprintf('Configuration file "%s" specifies category "%s" with missing class key', $configFiles[0], $required));
      }
    }

    //
    // let's do our fancy work
    foreach($myConfig as $serviceName => $definition)
    {

      // append new data
      switch($serviceName)
      {
        /*
        case 'controller':
          // append instance creation
          // $instances[] = sprintf("  \$this->controller = sfController::newInstance(sfConfig::get('sf_factory_controller', '%s'));", $class);
          // append instance initialization
          // $inits[] = "  \$this->controller->initialize(\$this);";
          break;

        case 'request':
          // append instance creation
          // $instances[] = sprintf("  \$this->request = sfRequest::newInstance(sfConfig::get('sf_factory_request', '%s'));", $class);
          // append instance initialization
          // $inits[] = sprintf("  \$this->request->initialize(\$this, sfConfig::get('sf_factory_request_parameters', %s), sfConfig::get('sf_factory_request_attributes', array()));", $parameters);
          break;

        case 'response':
          // append instance creation
          // $instances[] = sprintf("  \$this->response = sfResponse::newInstance(sfConfig::get('sf_factory_response', '%s'));", $class);
          // append instance initialization
          // $inits[] = sprintf("  \$this->response->initialize(\$this, sfConfig::get('sf_factory_response_parameters', %s));", $parameters);
          break;

        case 'storage':

          $inits[] = sprintf("\$this->registerService('%s', %s);\n", $serviceName, $this->varExport($definition));

          // append instance creation
          // $instances[] = sprintf("  \$this->storage = sfStorage::newInstance(sfConfig::get('sf_factory_storage', '%s'));", $class);
          // append instance initialization
          // $inits[] = sprintf("  \$this->storage->initialize(\$this, sfConfig::get('sf_factory_storage_parameters', %s));", $parameters);
          break;

        case 'user':
          // append instance creation
          // $instances[] = sprintf("  \$this->user = sfUser::newInstance(sfConfig::get('sf_factory_user', '%s'));", $class);
          // append instance initialization
          // $inits[] = sprintf("  \$this->user->initialize(\$this, sfConfig::get('sf_factory_user_parameters', %s));", $parameters);
          break;

        case 'view_cache':
          // append view cache class name

          $inits[] = sprintf("\n  if(sfConfig::get('sf_cache'))\n  {\n" .
              "    \$this->viewCacheManager = new sfViewCacheManager(\$this, sfCache::factory(sfConfig::get('sf_factory_view_cache', '%s'), (array)sfConfig::get('sf_factory_view_cache_parameters', %s)));\n" .
//                             "    \$this->viewCacheManager->initialize(, sfConfig::get('sf_factory_view_cache', '%s'), sfConfig::get('sf_factory_view_cache_parameters', %s));\n".
              " }\n", $class, $parameters);
           *

          break;

        case 'i18n':


          $inits[] = sprintf("\n  if (sfConfig::get('sf_i18n'))\n  {\n" .
              "    \$class = sfConfig::get('sf_factory_i18n', '%s');\n" .
              "    \$this->i18n = new \$class(\$this, sfConfig::get('sf_i18n_param', array()));\n" .
              "    sfWidgetFormSchemaFormatter::setTranslationCallable(array(\$this->i18n, '__'));\n" .
              "  }\n"
              , $class);
           *

          break;
          */

        // other  services
        default:
          $inits[] = sprintf("\$this->registerService('%s', %s);\n", $serviceName, $this->varExport($definition));
        break;

      }
    }

    // compile data
    $retval = sprintf("<?php\n" .
        "// auto-generated by sfFactoryConfigHandler\n" .
        "// date: %s\n%s\n%s\n%s\n", date('Y/m/d H:i:s'), implode("\n", $includes), implode("\n", $instances), implode("\n", $inits));

    return $retval;
  }

}
