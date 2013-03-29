<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCoreAutoload class.
 *
 * @package    Sift
 * @subpackage autoload
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfCoreAutoload
{
  static protected
    $registered = false,
    $instance   = null;

  protected
    $baseDir = null;

  protected function __construct()
  {
    $this->baseDir = realpath(dirname(__FILE__).'/..');
  }

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfCoreAutoload A sfCoreAutoload implementation instance.
   */
  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfCoreAutoload();
    }

    return self::$instance;
  }

  /**
   * Register sfCoreAutoload in spl autoloader.
   *
   * @return void
   */
  static public function register()
  {
    if (self::$registered)
    {
      return;
    }

    ini_set('unserialize_callback_func', 'spl_autoload_call');
    if (false === spl_autoload_register(array(self::getInstance(), 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
    }

    self::$registered = true;
  }

  /**
   * Unregister sfCoreAutoload from spl autoloader.
   *
   * @return void
   */
  static public function unregister()
  {
    spl_autoload_unregister(array(self::getInstance(), 'autoload'));
    self::$registered = false;
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    if ($path = $this->getClassPath($class))
    {
      require $path;

      return true;
    }

    return false;
  }

  /**
   * Returns the filename of the supplied class.
   *
   * @param  string $class The class name (case insensitive)
   *
   * @return string|null An absolute path or null
   */
  public function getClassPath($class)
  {
    $class = strtolower($class);

    if (!isset($this->classes[$class]))
    {
      return null;
    }

    return $this->baseDir.'/'.$this->classes[$class];
  }

  /**
   * Returns the base directory this autoloader is working on.
   *
   * @return string The path to the symfony core lib directory
   */
  public function getBaseDir()
  {
    return $this->baseDir;
  }

  /**
   * Returns an array of class mappings
   * 
   * @return array
   */
  public function getClassMap()
  {
    return $this->classes;
  }
  
  /**
   * Rebuilds the association array between class names and paths.
   *
   * This method overrides this file (__FILE__)
   */
  static public function make()
  {
    $libDir = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'));
    require_once $libDir.'/util/sfFinder.class.php';
    require_once $libDir.'/util/sfGlobToRegex.class.php';
    require_once $libDir.'/util/sfNumberCompare.class.php';
    require_once $libDir.'/util/sfToolkit.class.php';

    $files = sfFinder::type('file')
      ->prune('plugins')
      ->prune('vendor')
      ->prune('skeleton')
      ->prune('default')
      ->prune('helper')
      ->name('*.php')
      ->in($libDir);

    sort($files, SORT_STRING);

    $classes = '';
    foreach ($files as $file)
    {
      $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
      $_classes = sfToolkit::extractClasses($file);
      foreach($_classes as $class)
      {
        $classes .= sprintf("    '%s' => '%s',\n", strtolower($class), substr(str_replace($libDir, '', $file), 1));
      }       
    }

    $content = preg_replace('/protected \$classes = array *\(.*?\);/s', sprintf("protected \$classes = array(\n%s  );", $classes), file_get_contents(__FILE__));
    file_put_contents(__FILE__, $content);
  }

  // Don't edit this property by hand.
  // To update it, use sfCoreAutoload::make()
  protected $classes = array(
    'myaction' => 'action/myAction.class.php',
    'myactions' => 'action/myActions.class.php',
    'mycomponents' => 'action/myComponents.class.php',
    'mywizardactions' => 'action/myWizardActions.class.php',
    'mywizardcomponents' => 'action/myWizardComponents.class.php',
    'sfaction' => 'action/sfAction.class.php',
    'sfactionstack' => 'action/sfActionStack.class.php',
    'sfactionstackentry' => 'action/sfActionStackEntry.class.php',
    'sfactions' => 'action/sfActions.class.php',
    'sfcomponent' => 'action/sfComponent.class.php',
    'sfcomponents' => 'action/sfComponents.class.php',
    'sfwizardactions' => 'action/sfWizardActions.class.php',
    'sfwizardcomponents' => 'action/sfWizardComponents.class.php',
    'sfgoogleanalytics' => 'analytics/sfGoogleAnalytics.class.php',
    'sfziparchive' => 'archive/sfZipArchive.class.php',
    'sfclassloader' => 'autoload/sfClassLoader.class.php',
    'sfcoreautoload' => 'autoload/sfCoreAutoload.class.php',
    'sfsimpleautoload' => 'autoload/sfSimpleAutoload.class.php',
    'mybreadcrumbs' => 'breadcrumbs/myBreadcrumbs.class.php',
    'sfbreadcrumbs' => 'breadcrumbs/sfBreadcrumbs.class.php',
    'sfiwebbrowserdriver' => 'browser/sfIWebBrowserDriver.interface.php',
    'sfwebbrowser' => 'browser/sfWebBrowser.class.php',
    'sfwebbrowserdrivercurl' => 'browser/sfWebBrowserDriverCurl.class.php',
    'sfwebbrowserdriverfopen' => 'browser/sfWebBrowserDriverFopen.class.php',
    'sfwebbrowserdriversockets' => 'browser/sfWebBrowserDriverSockets.class.php',
    'sfcache' => 'cache/sfCache.class.php',
    'sffilecache' => 'cache/sfFileCache.class.php',
    'sfnocache' => 'cache/sfNoCache.class.php',
    'sfprocesscache' => 'cache/sfProcessCache.class.php',
    'sfsqlitecache' => 'cache/sfSQLiteCache.class.php',
    'sfcalendar' => 'calendar/sfCalendar.class.php',
    'sfcalendarevent' => 'calendar/sfCalendarEvent.class.php',
    'sfcalendarrenderer' => 'calendar/sfCalendarRenderer.class.php',
    'sfcalendarrendererhtml' => 'calendar/sfCalendarRendererHtml.class.php',
    'sfcalendarrendererical' => 'calendar/sfCalendarRendererICal.class.php',
    'sficalendarevent' => 'calendar/sfICalendarEvent.interface.php',
    'sficalendarrenderer' => 'calendar/sfICalendarRenderer.interface.php',
    'sfcliansicolorformatter' => 'cli/sfCliAnsiColorFormatter.class.php',
    'sfclicommandapplication' => 'cli/sfCliCommandApplication.class.php',
    'sfclicommandargument' => 'cli/sfCliCommandArgument.class.php',
    'sfclicommandargumentset' => 'cli/sfCliCommandArgumentSet.class.php',
    'sfclicommandargumentsexception' => 'cli/sfCliCommandArgumentsException.class.php',
    'sfclicommandexception' => 'cli/sfCliCommandException.class.php',
    'sfclicommandmanager' => 'cli/sfCliCommandManager.class.php',
    'sfclicommandoption' => 'cli/sfCliCommandOption.class.php',
    'sfclicommandoptionset' => 'cli/sfCliCommandOptionSet.class.php',
    'sfcliformatter' => 'cli/sfCliFormatter.class.php',
    'sfclirootcommandapplication' => 'cli/sfCliRootCommandApplication.class.php',
    'sfclitaskenvironment' => 'cli/sfCliTaskEnvironment.class.php',
    'sfclicachecleartask' => 'cli/task/cache/sfCliCacheClearTask.class.php',
    'sfcliconfiguredatabasetask' => 'cli/task/configure/sfCliConfigureDatabaseTask.class.php',
    'sfcligenerateapptask' => 'cli/task/generate/sfCliGenerateAppTask.class.php',
    'sfcligeneratecontrollertask' => 'cli/task/generate/sfCliGenerateControllerTask.class.php',
    'sfcligenerateformtask' => 'cli/task/generate/sfCliGenerateFormTask.class.php',
    'sfcligeneratemoduletask' => 'cli/task/generate/sfCliGenerateModuleTask.class.php',
    'sfcligeneratepluginmoduletask' => 'cli/task/generate/sfCliGeneratePluginModuleTask.class.php',
    'sfcligenerateplugintask' => 'cli/task/generate/sfCliGeneratePluginTask.class.php',
    'sfcligenerateprojecttask' => 'cli/task/generate/sfCliGenerateProjectTask.class.php',
    'sfgeneratetasktask' => 'cli/task/generate/sfCliGenerateTaskTask.class.php',
    'sfcligeneratetesttask' => 'cli/task/generate/sfCliGenerateTestTask.class.php',
    'sfcligeneratorbasetask' => 'cli/task/generate/sfCliGeneratorBaseTask.class.php',
    'sfclihelptask' => 'cli/task/help/sfCliHelpTask.class.php',
    'sfclii18nextractformtask' => 'cli/task/i18n/sfCliI18nExtractFormTask.class.php',
    'sfclii18nextractformstask' => 'cli/task/i18n/sfCliI18nExtractFormsTask.class.php',
    'sfclii18nextracttask' => 'cli/task/i18n/sfCliI18nExtractTask.class.php',
    'sfclii18nfindtask' => 'cli/task/i18n/sfCliI18nFindTask.class.php',
    'sfclilisttask' => 'cli/task/list/sfCliListTask.class.php',
    'sfclilogcleartask' => 'cli/task/log/sfCliLogClearTask.class.php',
    'sfclilogrotatetask' => 'cli/task/log/sfCliLogRotateTask.class.php',
    'sfprojectsendemailstask' => 'cli/task/mailer/sfCliProjectSendEmailsTask.class.php',
    'sfclipluginaddchanneltask' => 'cli/task/plugin/sfCliPluginAddChannelTask.class.php',
    'sfclipluginbasetask' => 'cli/task/plugin/sfCliPluginBaseTask.class.php',
    'sfcliplugininstalltask' => 'cli/task/plugin/sfCliPluginInstallTask.class.php',
    'sfclipluginlistchannelstask' => 'cli/task/plugin/sfCliPluginListChannelsTask.class.php',
    'sfclipluginlisttask' => 'cli/task/plugin/sfCliPluginListTask.class.php',
    'sfclipluginpackagetask' => 'cli/task/plugin/sfCliPluginPackageTask.class.php',
    'sfclipluginpublishassetstask' => 'cli/task/plugin/sfCliPluginPublishAssetsTask.class.php',
    'sfclipluginremovechanneltask' => 'cli/task/plugin/sfCliPluginRemoveChannelTask.class.php',
    'sfclipluginruninstallertask' => 'cli/task/plugin/sfCliPluginRunInstallerTask.class.php',
    'sfclipluginuninstalltask' => 'cli/task/plugin/sfCliPluginUninstallTask.class.php',
    'sfclipluginupgradetask' => 'cli/task/plugin/sfCliPluginUpgradeTask.class.php',
    'sfcliprojectclearcontrollerstask' => 'cli/task/project/sfCliProjectClearControllersTask.class.php',
    'sfcliprojectdeploytask' => 'cli/task/project/sfCliProjectDeployTask.class.php',
    'sfcliprojectdisabletask' => 'cli/task/project/sfCliProjectDisableTask.class.php',
    'sfcliprojectenabletask' => 'cli/task/project/sfCliProjectEnableTask.class.php',
    'sfcliprojectfreezetask' => 'cli/task/project/sfCliProjectFreezeTask.class.php',
    'sfcliprojectpermissionstask' => 'cli/task/project/sfCliProjectPermissionsTask.class.php',
    'sfcliprojectprefetchtask' => 'cli/task/project/sfCliProjectPrefetchTask.class.php',
    'sfcliprojectunfreezetask' => 'cli/task/project/sfCliProjectUnfreezeTask.class.php',
    'sfcligeneratecryptkeytask' => 'cli/task/security/sfCliGenerateCryptKeyTask.class.php',
    'sfclibasetask' => 'cli/task/sfCliBaseTask.class.php',
    'sfclicommandapplicationtask' => 'cli/task/sfCliCommandApplicationTask.class.php',
    'sfclitask' => 'cli/task/sfCliTask.class.php',
    'mycolorpalette' => 'color/myColorPalette.class.php',
    'sfcolor' => 'color/sfColor.class.php',
    'sfcolorpalette' => 'color/sfColorPalette.class.php',
    'sficolorpallete' => 'color/sfIColorPalette.interface.php',
    'sfassetpackagesconfighandler' => 'config/sfAssetPackagesConfigHandler.class.php',
    'sfautoloadconfighandler' => 'config/sfAutoloadConfigHandler.class.php',
    'sfcacheconfighandler' => 'config/sfCacheConfigHandler.class.php',
    'sfcompileconfighandler' => 'config/sfCompileConfigHandler.class.php',
    'sfconfig' => 'config/sfConfig.class.php',
    'sfconfigcache' => 'config/sfConfigCache.class.php',
    'sfconfighandler' => 'config/sfConfigHandler.class.php',
    'sfconfigurable' => 'config/sfConfigurable.class.php',
    'sfdatabaseconfighandler' => 'config/sfDatabaseConfigHandler.class.php',
    'sfdefineenvironmentconfighandler' => 'config/sfDefineEnvironmentConfigHandler.class.php',
    'sfdimensionsconfighandler' => 'config/sfDimensionsConfigHandler.class.php',
    'sffactoryconfighandler' => 'config/sfFactoryConfigHandler.class.php',
    'sffilterconfighandler' => 'config/sfFilterConfigHandler.class.php',
    'sfgeneratorconfighandler' => 'config/sfGeneratorConfigHandler.class.php',
    'sfi18nconfighandler' => 'config/sfI18nConfigHandler.class.php',
    'sficonfigurable' => 'config/sfIConfigurable.interface.php',
    'sfloggingconfighandler' => 'config/sfLoggingConfigHandler.class.php',
    'sfmailconfighandler' => 'config/sfMailConfigHandler.class.php',
    'sfmodulesconfighandler' => 'config/sfModulesConfigHandler.class.php',
    'sfphpconfighandler' => 'config/sfPhpConfigHandler.class.php',
    'sfpluginsconfighandler' => 'config/sfPluginsConfigHandler.class.php',
    'sfrootconfighandler' => 'config/sfRootConfigHandler.class.php',
    'sfroutingconfighandler' => 'config/sfRoutingConfigHandler.class.php',
    'sfsanitizeconfighandler' => 'config/sfSanitizeConfigHandler.class.php',
    'sfsearchsourceconfighandler' => 'config/sfSearchSourcesConfigHandler.class.php',
    'sfsecurityconfighandler' => 'config/sfSecurityConfigHandler.class.php',
    'sfsimpleyamlconfighandler' => 'config/sfSimpleYamlConfigHandler.class.php',
    'sftextmacrosconfighandler' => 'config/sfTextMacrosConfigHandler.class.php',
    'sfvalidatorconfighandler' => 'config/sfValidatorConfigHandler.class.php',
    'sfviewconfighandler' => 'config/sfViewConfigHandler.class.php',
    'sfyamlconfighandler' => 'config/sfYamlConfigHandler.class.php',
    'sfconsolecontroller' => 'controller/sfConsoleController.class.php',
    'sfcontroller' => 'controller/sfController.class.php',
    'sffrontwebcontroller' => 'controller/sfFrontWebController.class.php',
    'sfwebcontroller' => 'controller/sfWebController.class.php',
    'sfcore' => 'core/sfCore.class.php',
    'sfdimensions' => 'core/sfDimensions.class.php',
    'sfloader' => 'core/sfLoader.class.php',
    'sfdata' => 'data/sfData.class.php',
    'sfpdo' => 'database/pdo/sfPDO.class.php',
    'sfpdostatement' => 'database/pdo/sfPDOStatement.class.php',
    'sfdatabase' => 'database/sfDatabase.class.php',
    'sfdatabasemanager' => 'database/sfDatabaseManager.class.php',
    'sfpdodatabase' => 'database/sfPDODatabase.class.php',
    'sfdate' => 'date/sfDate.class.php',
    'sfdatetimetoolkit' => 'date/sfDateTimeToolkit.class.php',
    'sftime' => 'date/sfTime.class.php',
    'sfwebdebugpanel' => 'debug/panel/sfWebDebugPanel.class.php',
    'sfwebdebugpanelcache' => 'debug/panel/sfWebDebugPanelCache.class.php',
    'sfwebdebugpanelconfig' => 'debug/panel/sfWebDebugPanelConfig.class.php',
    'sfwebdebugpanelcurrentroute' => 'debug/panel/sfWebDebugPanelCurrentRoute.class.php',
    'sfwebdebugpaneldatabase' => 'debug/panel/sfWebDebugPanelDatabase.class.php',
    'sfwebdebugpaneldocumentation' => 'debug/panel/sfWebDebugPanelDocumentation.class.php',
    'sfwebdebugpanellesscompiler' => 'debug/panel/sfWebDebugPanelLessCompiler.class.php',
    'sfwebdebugpanellogs' => 'debug/panel/sfWebDebugPanelLogs.class.php',
    'sfwebdebugpanelmailer' => 'debug/panel/sfWebDebugPanelMailer.class.php',
    'sfwebdebugpanelmemory' => 'debug/panel/sfWebDebugPanelMemory.class.php',
    'sfwebdebugpanelresponse' => 'debug/panel/sfWebDebugPanelResponse.class.php',
    'sfwebdebugpanelsiftversion' => 'debug/panel/sfWebDebugPanelSiftVersion.class.php',
    'sfwebdebugpaneltimer' => 'debug/panel/sfWebDebugPanelTimer.class.php',
    'sfwebdebugpaneluser' => 'debug/panel/sfWebDebugPanelUser.class.php',
    'sfdebug' => 'debug/sfDebug.class.php',
    'sftimer' => 'debug/sfTimer.class.php',
    'sftimermanager' => 'debug/sfTimerManager.class.php',
    'sfwebdebug' => 'debug/sfWebDebug.class.php',
    'sfevent' => 'event/sfEvent.class.php',
    'sfeventdispatcher' => 'event/sfEventDispatcher.class.php',
    'sfactionexception' => 'exception/sfActionException.class.php',
    'sfautoloadexception' => 'exception/sfAutoloadException.class.php',
    'sfcacheexception' => 'exception/sfCacheException.class.php',
    'sfcalendarexception' => 'exception/sfCalendarException.class.php',
    'sfconfigurationexception' => 'exception/sfConfigurationException.class.php',
    'sfcontextexception' => 'exception/sfContextException.class.php',
    'sfcontrollerexception' => 'exception/sfControllerException.class.php',
    'sfdatabaseexception' => 'exception/sfDatabaseException.class.php',
    'sfdatetimeexception' => 'exception/sfDateTimeException.class.php',
    'sferror404exception' => 'exception/sfError404Exception.class.php',
    'sfexception' => 'exception/sfException.class.php',
    'sffactoryexception' => 'exception/sfFactoryException.class.php',
    'sffileexception' => 'exception/sfFileException.class.php',
    'sffilterexception' => 'exception/sfFilterException.class.php',
    'sfforwardexception' => 'exception/sfForwardException.class.php',
    'sfhttpdownloadexception' => 'exception/sfHttpDownloadException.class.php',
    'sfimagetransformexception' => 'exception/sfImageTransformException.class.php',
    'sfinitializationexception' => 'exception/sfInitializationException.class.php',
    'sflesscompilerexception' => 'exception/sfLessCompilerException.class.php',
    'sfparseexception' => 'exception/sfParseException.class.php',
    'sfphperrorexception' => 'exception/sfPhpErrorException.class.php',
    'sfrenderexception' => 'exception/sfRenderException.class.php',
    'sfsecurityexception' => 'exception/sfSecurityException.class.php',
    'sfstopexception' => 'exception/sfStopException.class.php',
    'sfstorageexception' => 'exception/sfStorageException.class.php',
    'sfvalidatorexception' => 'exception/sfValidatorException.class.php',
    'sfviewexception' => 'exception/sfViewException.class.php',
    'sfwebbrowserinvalidresponseexception' => 'exception/sfWebBrowserInvalidResponseException.class.php',
    'sfatom1feed' => 'feed/sfAtom1Feed.class.php',
    'sffeed' => 'feed/sfFeed.class.php',
    'sffeedenclosure' => 'feed/sfFeedEnclosure.class.php',
    'sffeedimage' => 'feed/sfFeedImage.class.php',
    'sffeeditem' => 'feed/sfFeedItem.class.php',
    'sffeedpeer' => 'feed/sfFeedPeer.class.php',
    'sfrss091feed' => 'feed/sfRss091Feed.class.php',
    'sfrss10feed' => 'feed/sfRss10Feed.class.php',
    'sfrss201feed' => 'feed/sfRss201Feed.class.php',
    'sfrssfeed' => 'feed/sfRssFeed.class.php',
    'sffilesystem' => 'file/sfFilesystem.class.php',
    'sfassetpackagerfilter' => 'filter/sfAssetPackagerFilter.class.php',
    'sfbasicsecurityfilter' => 'filter/sfBasicSecurityFilter.class.php',
    'sfcachefilter' => 'filter/sfCacheFilter.class.php',
    'sfcommonfilter' => 'filter/sfCommonFilter.class.php',
    'sfcompressoutputfilter' => 'filter/sfCompressOutputFilter.class.php',
    'sfexecutionfilter' => 'filter/sfExecutionFilter.class.php',
    'sffillinformfilter' => 'filter/sfFillInFormFilter.class.php',
    'sffilter' => 'filter/sfFilter.class.php',
    'sffilterchain' => 'filter/sfFilterChain.class.php',
    'sfflashfilter' => 'filter/sfFlashFilter.class.php',
    'sfi18nfilter' => 'filter/sfI18nFilter.class.php',
    'sflesscompilerfilter' => 'filter/sfLessCompilerFilter.class.php',
    'sfrenderingfilter' => 'filter/sfRenderingFilter.class.php',
    'sfsecurityfilter' => 'filter/sfSecurityFilter.class.php',
    'sfwebdebugfilter' => 'filter/sfWebDebugFilter.class.php',
    'myformenhancer' => 'form/enhancer/myFormEnhancer.class.php',
    'sfformenhancer' => 'form/enhancer/sfFormEnhancer.class.php',
    'sfformenhancerrich' => 'form/enhancer/sfFormEnhancerRich.class.php',
    'sffillinform' => 'form/fillin/sfFillInForm.class.php',
    'sfwidgetformschemadecorator' => 'form/formatter/sfWidgetFormSchemaDecorator.class.php',
    'sfwidgetformschemaformatter' => 'form/formatter/sfWidgetFormSchemaFormatter.class.php',
    'sfwidgetformschemaformatteradvanced' => 'form/formatter/sfWidgetFormSchemaFormatterAdvanced.class.php',
    'sfwidgetformschemaformatterdiv' => 'form/formatter/sfWidgetFormSchemaFormatterDiv.class.php',
    'sfwidgetformschemaformatterlist' => 'form/formatter/sfWidgetFormSchemaFormatterList.class.php',
    'sfwidgetformschemaformattermylist' => 'form/formatter/sfWidgetFormSchemaFormatterMyList.class.php',
    'sfwidgetformschemaformatterplain' => 'form/formatter/sfWidgetFormSchemaFormatterPlain.class.php',
    'sfwidgetformschemaformattertable' => 'form/formatter/sfWidgetFormSchemaFormatterTable.class.php',
    'sfwidgetformschemaformatterunorderedlist' => 'form/formatter/sfWidgetFormSchemaFormatterUnorderedList.class.php',
    'sfformjavascriptfixedvalidationmessage' => 'form/javascript/sfFormJavascriptFixedValidationMessage.class.php',
    'sfformjavascriptvalidation' => 'form/javascript/sfFormJavascriptValidation.class.php',
    'myform' => 'form/myForm.class.php',
    'myformbase' => 'form/myFormBase.class.php',
    'sfformobject' => 'form/object/sfFormObject.class.php',
    'sfform' => 'form/sfForm.class.php',
    'sfformculture' => 'form/sfFormCulture.class.php',
    'sfformfield' => 'form/sfFormField.class.php',
    'sfformfieldgroup' => 'form/sfFormFieldGroup.class.php',
    'sfformfieldschema' => 'form/sfFormFieldSchema.class.php',
    'sfformmanager' => 'form/sfFormManager.class.php',
    'sfwidgetformchoice' => 'form/widget/choice/sfWidgetFormChoice.class.php',
    'sfwidgetformchoicebase' => 'form/widget/choice/sfWidgetFormChoiceBase.class.php',
    'sfwidgetformchoicemany' => 'form/widget/choice/sfWidgetFormChoiceMany.class.php',
    'sfwidgetforminputcheckbox' => 'form/widget/choice/sfWidgetFormInputCheckbox.class.php',
    'sfwidgetformselect' => 'form/widget/choice/sfWidgetFormSelect.class.php',
    'sfwidgetformselectcheckbox' => 'form/widget/choice/sfWidgetFormSelectCheckbox.class.php',
    'sfwidgetformselectmany' => 'form/widget/choice/sfWidgetFormSelectMany.class.php',
    'sfwidgetformselectradio' => 'form/widget/choice/sfWidgetFormSelectRadio.class.php',
    'sfwidgetformdate' => 'form/widget/date/sfWidgetFormDate.class.php',
    'sfwidgetformdaterange' => 'form/widget/date/sfWidgetFormDateRange.class.php',
    'sfwidgetformdatetime' => 'form/widget/date/sfWidgetFormDateTime.class.php',
    'sfwidgetformdatetimerange' => 'form/widget/date/sfWidgetFormDateTimeRange.class.php',
    'sfwidgetformfilterdate' => 'form/widget/date/sfWidgetFormFilterDate.class.php',
    'sfwidgetformfilterdatetime' => 'form/widget/date/sfWidgetFormFilterDateTime.class.php',
    'sfwidgetformtime' => 'form/widget/date/sfWidgetFormTime.class.php',
    'sfwidgetforminputfile' => 'form/widget/file/sfWidgetFormInputFile.class.php',
    'sfwidgetforminputfileeditable' => 'form/widget/file/sfWidgetFormInputFileEditable.class.php',
    'sfwidgetforminputhidden' => 'form/widget/hidden/sfWidgetFormInputHidden.class.php',
    'sfwidgetformi18nchoicecountry' => 'form/widget/i18n/sfWidgetFormI18nChoiceCountry.class.php',
    'sfwidgetformi18nchoicecurrency' => 'form/widget/i18n/sfWidgetFormI18nChoiceCurrency.class.php',
    'sfwidgetformi18nchoicelanguage' => 'form/widget/i18n/sfWidgetFormI18nChoiceLanguage.class.php',
    'sfwidgetformi18nchoicetimezone' => 'form/widget/i18n/sfWidgetFormI18nChoiceTimezone.class.php',
    'sfwidgetformi18nselectcountry' => 'form/widget/i18n/sfWidgetFormI18nSelectCountry.class.php',
    'sfwidgetformi18nselectcurrency' => 'form/widget/i18n/sfWidgetFormI18nSelectCurrency.class.php',
    'sfwidgetformi18nselectlanguage' => 'form/widget/i18n/sfWidgetFormI18nSelectLanguage.class.php',
    'sfwidgetforminteger' => 'form/widget/number/sfWidgetFormInteger.class.php',
    'sfwidgetformnumber' => 'form/widget/number/sfWidgetFormNumber.class.php',
    'sfwidgetformprice' => 'form/widget/number/sfWidgetFormPrice.class.php',
    'sfwidgetformcomponent' => 'form/widget/other/sfWidgetFormComponent.class.php',
    'sfwidgetformnoinput' => 'form/widget/other/sfWidgetFormNoInput.class.php',
    'sfwidgetformpartial' => 'form/widget/other/sfWidgetFormPartial.class.php',
    'sfwidget' => 'form/widget/sfWidget.class.php',
    'sfwidgetform' => 'form/widget/sfWidgetForm.class.php',
    'sfwidgetformschema' => 'form/widget/sfWidgetFormSchema.class.php',
    'sfwidgetformschemaforeach' => 'form/widget/sfWidgetFormSchemaForEach.class.php',
    'sfwidgetformrecaptcha' => 'form/widget/spam_protect/sfWidgetFormReCaptcha.class.php',
    'sfwidgetformspamprotecttimer' => 'form/widget/spam_protect/sfWidgetFormSpamProtectTimer.class.php',
    'sfwidgetformfilterinput' => 'form/widget/text/sfWidgetFormFilterInput.class.php',
    'sfwidgetforminput' => 'form/widget/text/sfWidgetFormInput.class.php',
    'sfwidgetforminputpassword' => 'form/widget/text/sfWidgetFormInputPassword.class.php',
    'sfwidgetforminputtext' => 'form/widget/text/sfWidgetFormInputText.class.php',
    'sfwidgetformtextarea' => 'form/widget/text/sfWidgetFormTextarea.class.php',
    'mywizardform' => 'form/wizard/myWizardForm.class.php',
    'sfwizardform' => 'form/wizard/sfWizardForm.class.php',
    'sfgeneratorformbuilder' => 'generator/builder/sfGeneratorFormBuilder.class.php',
    'sfgenerator' => 'generator/sfGenerator.class.php',
    'sfgeneratorfield' => 'generator/sfGeneratorField.class.php',
    'sfgeneratormanager' => 'generator/sfGeneratorManager.class.php',
    'sfigenerator' => 'generator/sfIGenerator.interface.php',
    'sfigeneratorfield' => 'generator/sfIGeneratorField.interface.php',
    'sfbrowsehistory' => 'history/sfBrowseHistory.class.php',
    'sfbrowsehistoryitem' => 'history/sfBrowseHistoryItem.class.php',
    'sfcultureexport' => 'i18n/export/sfCultureExport.class.php',
    'sfcultureexportglobalize' => 'i18n/export/sfI18nExportGlobalize.class.php',
    'sficultureexport' => 'i18n/export/sfICultureExport.interface.php',
    'sfi18napplicationextract' => 'i18n/extract/sfI18nApplicationExtract.class.php',
    'sfi18nextract' => 'i18n/extract/sfI18nExtract.class.php',
    'sfi18nextractanonymoususer' => 'i18n/extract/sfI18nExtractAnonymousUser.class.php',
    'sfi18nextractloggedinuser' => 'i18n/extract/sfI18nExtractLoggedInUser.class.php',
    'sfi18nformextract' => 'i18n/extract/sfI18nFormExtract.class.php',
    'sfi18njavascriptextractor' => 'i18n/extract/sfI18nJavascriptExtractor.class.php',
    'sfi18nmoduleextract' => 'i18n/extract/sfI18nModuleExtract.class.php',
    'sfi18nphpextractor' => 'i18n/extract/sfI18nPhpExtractor.class.php',
    'sfi18nyamlextractor' => 'i18n/extract/sfI18nYamlExtractor.class.php',
    'sfi18nyamlgeneratorextractor' => 'i18n/extract/sfI18nYamlGeneratorExtractor.class.php',
    'sfi18nyamlmenuextractor' => 'i18n/extract/sfI18nYamlMenuExtractor.class.php',
    'sfi18nyamlvalidateextractor' => 'i18n/extract/sfI18nYamlValidateExtractor.class.php',
    'sfii18nextractor' => 'i18n/extract/sfII18nExtractor.interface.php',
    'sfi18ndatetimeformat' => 'i18n/format/sfI18nDateTimeFormat.class.php',
    'sfi18nnumberformat' => 'i18n/format/sfI18nNumberFormat.class.php',
    'sfi18nchoiceformatter' => 'i18n/formatter/sfI18nChoiceFormatter.class.php',
    'sfi18ndateformatter' => 'i18n/formatter/sfI18nDateFormatter.class.php',
    'sfi18nnumberformatter' => 'i18n/formatter/sfI18nNumberFormatter.class.php',
    'sfiso639' => 'i18n/iso/sfISO639.class.php',
    'sfi18nmessageformatter' => 'i18n/message/formatter/sfI18nMessageFormatter.class.php',
    'sfi18nmessagesource' => 'i18n/message/sfI18nMessageSource.class.php',
    'sfii18nmessagesource' => 'i18n/message/sfII18nMessageSource.interface.php',
    'sfi18ngettext' => 'i18n/message/source/gettext/sfI18nGettext.class.php',
    'sfi18ngettextmo' => 'i18n/message/source/gettext/sfI18nGettextMo.class.php',
    'sfi18ngettextpo' => 'i18n/message/source/gettext/sfI18nGettextPo.class.php',
    'sfi18nmessagesourcegettext' => 'i18n/message/source/sfI18nMessageSourceGettext.class.php',
    'sfi18nmessagesourcexliff' => 'i18n/message/source/sfI18nMessageSourceXliff.class.php',
    'sfcollator' => 'i18n/sfCollator.class.php',
    'sfculture' => 'i18n/sfCulture.class.php',
    'sfi18n' => 'i18n/sfI18n.class.php',
    'sfexifadapter' => 'image/adapters/sfExifAdapter.class.php',
    'sfexifadapterexiftool' => 'image/adapters/sfExifAdapterExifTool.class.php',
    'sfexifadapternative' => 'image/adapters/sfExifAdapterNative.class.php',
    'sfimagetransformadapterabstract' => 'image/adapters/sfImageTransformAdapterAbstract.class.php',
    'sfimagetransformgdadapter' => 'image/adapters/sfImageTransformGDAdapter.class.php',
    'sfimagetransformimagemagickadapter' => 'image/adapters/sfImageTransformImageMagickAdapter.class.php',
    'sfexif' => 'image/sfExif.class.php',
    'sfimage' => 'image/sfImage.class.php',
    'sfimagealphamaskgd' => 'image/transforms/GD/sfImageAlphaMaskGD.class.php',
    'sfimagearcgd' => 'image/transforms/GD/sfImageArcGD.class.php',
    'sfimagebrightnessgd' => 'image/transforms/GD/sfImageBrightnessGD.class.php',
    'sfimagecolorizegd' => 'image/transforms/GD/sfImageColorizeGD.class.php',
    'sfimagecontrastgd' => 'image/transforms/GD/sfImageContrastGD.class.php',
    'sfimagecropgd' => 'image/transforms/GD/sfImageCropGD.class.php',
    'sfimageedgedetectgd' => 'image/transforms/GD/sfImageEdgeDetectGD.class.php',
    'sfimageellipsegd' => 'image/transforms/GD/sfImageEllipseGD.class.php',
    'sfimageembossgd' => 'image/transforms/GD/sfImageEmbossGD.class.php',
    'sfimagefillgd' => 'image/transforms/GD/sfImageFillGD.class.php',
    'sfimageflipgd' => 'image/transforms/GD/sfImageFlipGD.class.php',
    'sfimagegammagd' => 'image/transforms/GD/sfImageGammaGD.class.php',
    'sfimagegaussianblurgd' => 'image/transforms/GD/sfImageGaussianBlurGD.class.php',
    'sfimagegreyscalegd' => 'image/transforms/GD/sfImageGreyscaleGD.class.php',
    'sfimagelinegd' => 'image/transforms/GD/sfImageLineGD.class.php',
    'sfimagemergegd' => 'image/transforms/GD/sfImageMergeGD.class.php',
    'sfimagemirrorgd' => 'image/transforms/GD/sfImageMirrorGD.class.php',
    'sfimagenegategd' => 'image/transforms/GD/sfImageNegateGD.class.php',
    'sfimagenoisegd' => 'image/transforms/GD/sfImageNoiseGD.class.php',
    'sfimageopacitygd' => 'image/transforms/GD/sfImageOpacityGD.class.php',
    'sfimageoverlaygd' => 'image/transforms/GD/sfImageOverlayGD.class.php',
    'sfimagepixelblurgd' => 'image/transforms/GD/sfImagePixelBlurGD.class.php',
    'sfimagepixelizegd' => 'image/transforms/GD/sfImagePixelizeGD.class.php',
    'sfimagepolygongd' => 'image/transforms/GD/sfImagePolygonGD.class.php',
    'sfimagerectanglegd' => 'image/transforms/GD/sfImageRectangleGD.class.php',
    'sfimagereflectiongd' => 'image/transforms/GD/sfImageReflectionGD.class.php',
    'sfimageresizesimplegd' => 'image/transforms/GD/sfImageResizeSimpleGD.class.php',
    'sfimagerotategd' => 'image/transforms/GD/sfImageRotateGD.class.php',
    'sfimageroundedcornersgd' => 'image/transforms/GD/sfImageRoundedCornersGD.class.php',
    'sfimagescalegd' => 'image/transforms/GD/sfImageScaleGD.class.php',
    'sfimagescattergd' => 'image/transforms/GD/sfImageScatterGD.class.php',
    'sfimageselectiveblurgd' => 'image/transforms/GD/sfImageSelectiveBlurGD.class.php',
    'sfimagesketchygd' => 'image/transforms/GD/sfImageSketchyGD.class.php',
    'sfimagesmoothgd' => 'image/transforms/GD/sfImageSmoothGD.class.php',
    'sfimagetextgd' => 'image/transforms/GD/sfImageTextGD.class.php',
    'sfimagetransparencygd' => 'image/transforms/GD/sfImageTransparencyGD.class.php',
    'sfimageunsharpmaskgd' => 'image/transforms/GD/sfImageUnsharpMaskGD.class.php',
    'sfimagebordergeneric' => 'image/transforms/Generic/sfImageBorderGeneric.php',
    'sfimagecallbackgeneric' => 'image/transforms/Generic/sfImageCallback.class.php',
    'sfimageresizegeneric' => 'image/transforms/Generic/sfImageResizeGeneric.php',
    'sfimagethumbnailgeneric' => 'image/transforms/Generic/sfImageThumbnailGeneric.php',
    'sfimagebrightnessimagemagick' => 'image/transforms/ImageMagick/sfImageBrightnessImageMagick.class.php',
    'sfimagecolorizeimagemagick' => 'image/transforms/ImageMagick/sfImageColorizeImageMagick.class.php',
    'sfimagecropimagemagick' => 'image/transforms/ImageMagick/sfImageCropImageMagick.class.php',
    'sfimagefillimagemagick' => 'image/transforms/ImageMagick/sfImageFillImageMagick.class.php',
    'sfimageflipimagemagick' => 'image/transforms/ImageMagick/sfImageFlipImageMagick.class.php',
    'sfimagegreyscaleimagemagick' => 'image/transforms/ImageMagick/sfImageGreyscaleImageMagick.class.php',
    'sfimagelineimagemagick' => 'image/transforms/ImageMagick/sfImageLineImageMagick.class.php',
    'sfimagemirrorimagemagick' => 'image/transforms/ImageMagick/sfImageMirrorImageMagick.class.php',
    'sfimageopacityimagemagick' => 'image/transforms/ImageMagick/sfImageOpacityImageMagick.class.php',
    'sfimageoverlayimagemagick' => 'image/transforms/ImageMagick/sfImageOverlayImageMagick.class.php',
    'sfimageprettythumbnailimagemagick' => 'image/transforms/ImageMagick/sfImagePrettyThumbnailImageMagick.class.php',
    'sfimagerectangleimagemagick' => 'image/transforms/ImageMagick/sfImageRectangleImageMagick.class.php',
    'sfimageresizesimpleimagemagick' => 'image/transforms/ImageMagick/sfImageResizeSimpleImageMagick.class.php',
    'sfimagerotateimagemagick' => 'image/transforms/ImageMagick/sfImageRotateImageMagick.class.php',
    'sfimagescaleimagemagick' => 'image/transforms/ImageMagick/sfImageScaleImageMagick.class.php',
    'sfimagetextimagemagick' => 'image/transforms/ImageMagick/sfImageTextImageMagick.class.php',
    'sfimagetrimimagemagick' => 'image/transforms/ImageMagick/sfImageTrimImageMagick.class.php',
    'sfimageunsharpmaskimagemagick' => 'image/transforms/ImageMagick/sfImageUnsharpMaskImageMagick.class.php',
    'sfimagetransformabstract' => 'image/transforms/sfImageTransformAbstract.class.php',
    'sfjson' => 'json/sfJson.class.php',
    'sfjsonexpression' => 'json/sfJsonExpression.class.php',
    'sflesscompiler' => 'less/sfLessCompiler.class.php',
    'sfconsolelogger' => 'log/sfConsoleLogger.class.php',
    'sfemaillogger' => 'log/sfEmailLogger.class.php',
    'sffilelogger' => 'log/sfFileLogger.class.php',
    'sfilogger' => 'log/sfILogger.interface.php',
    'sflogmanager' => 'log/sfLogManager.class.php',
    'sflogger' => 'log/sfLogger.class.php',
    'sfnologger' => 'log/sfNoLogger.class.php',
    'sfstreamlogger' => 'log/sfStreamLogger.class.php',
    'sfvarlogger' => 'log/sfVarLogger.class.php',
    'sfwebdebuglogger' => 'log/sfWebDebugLogger.class.php',
    'mymailer' => 'mailer/myMailer.class.php',
    'mymailermessage' => 'mailer/myMailerMessage.class.php',
    'sfmailer' => 'mailer/sfMailer.class.php',
    'sfmailerblackholeplugin' => 'mailer/sfMailerBlackholePlugin.class.php',
    'sfmailerhtml2textplugin' => 'mailer/sfMailerHtml2TextPlugin.class.php',
    'sfmailerlogger' => 'mailer/sfMailerLogger.class.php',
    'sfmailermessage' => 'mailer/sfMailerMessage.class.php',
    'mymenu' => 'menu/myMenu.class.php',
    'sfmenu' => 'menu/sfMenu.class.php',
    'sfplugindependencyexception' => 'plugin/exception/sfPluginDependencyException.class.php',
    'sfpluginexception' => 'plugin/exception/sfPluginException.class.php',
    'sfpluginrecursivedependencyexception' => 'plugin/exception/sfPluginRecursiveDependencyException.class.php',
    'sfpluginrestexception' => 'plugin/exception/sfPluginRestException.class.php',
    'sfpearconfig' => 'plugin/pear/sfPearConfig.class.php',
    'sfpeardownloader' => 'plugin/pear/sfPearDownloader.class.php',
    'sfpearenvironment' => 'plugin/pear/sfPearEnvironment.class.php',
    'sfpearfrontendplugin' => 'plugin/pear/sfPearFrontendPlugin.class.php',
    'sfpearpackager' => 'plugin/pear/sfPearPackager.class.php',
    'sfpearpluginmanager' => 'plugin/pear/sfPearPluginManager.class.php',
    'sfpearrest' => 'plugin/pear/sfPearRest.class.php',
    'sfpearrest10' => 'plugin/pear/sfPearRest10.class.php',
    'sfpearrest11' => 'plugin/pear/sfPearRest11.class.php',
    'sfpearrest13' => 'plugin/pear/sfPearRest13.class.php',
    'sfpearrestplugin' => 'plugin/pear/sfPearRestPlugin.class.php',
    'sfiplugininstaller' => 'plugin/sfIPluginInstaller.interface.php',
    'sfplugininstaller' => 'plugin/sfPluginInstaller.class.php',
    'sfpluginmanager' => 'plugin/sfPluginManager.class.php',
    'sfapplication' => 'project/sfApplication.class.php',
    'sfgenericapplication' => 'project/sfGenericApplication.class.php',
    'sfgenericplugin' => 'project/sfGenericPlugin.class.php',
    'sfgenericproject' => 'project/sfGenericProject.class.php',
    'sfplugin' => 'project/sfPlugin.class.php',
    'sfproject' => 'project/sfProject.class.php',
    'sfconsolerequest' => 'request/sfConsoleRequest.class.php',
    'sfrequest' => 'request/sfRequest.class.php',
    'sfrequestfiltersholder' => 'request/sfRequestFiltersHolder.class.php',
    'sfuploadedfile' => 'request/sfUploadedFile.class.php',
    'sfwebrequest' => 'request/sfWebRequest.class.php',
    'sfajaxresult' => 'response/sfAjaxResult.class.php',
    'sfconsoleresponse' => 'response/sfConsoleResponse.class.php',
    'sfhttpdownload' => 'response/sfHttpDownload.class.php',
    'sfresponse' => 'response/sfResponse.class.php',
    'sfwebresponse' => 'response/sfWebResponse.class.php',
    'sfinternalroute' => 'routing/sfInternalRoute.class.php',
    'sfrouting' => 'routing/sfRouting.class.php',
    'sfisearchquerybuilder' => 'search/builder/sfISearchQueryBuilder.interface.php',
    'sfsearchquerybuilderabstract' => 'search/builder/sfSearchQueryBuilderAbstract.class.php',
    'sfsearchquerybuildermysqlfulltext' => 'search/builder/sfSearchQueryBuilderMysqlFulltext.class.php',
    'sfsearchquerybuilderpgsqlfulltext' => 'search/builder/sfSearchQueryBuilderPgsqlFulltext.class.php',
    'mysearchtools' => 'search/mySearchTools.class.php',
    'sfisearchquerylexer' => 'search/parser/sfISearchQueryLexer.interface.php',
    'sfisearchqueryparser' => 'search/parser/sfISearchQueryParser.interface.php',
    'sfsearchqueryexpression' => 'search/parser/sfSearchQueryExpression.class.php',
    'sfsearchquerylexer' => 'search/parser/sfSearchQueryLexer.class.php',
    'sfsearchqueryparser' => 'search/parser/sfSearchQueryParser.class.php',
    'sfsearchqueryphrase' => 'search/parser/sfSearchQueryPhrase.class.php',
    'sfsearchquerytoken' => 'search/parser/sfSearchQueryToken.class.php',
    'sfsearchtools' => 'search/sfSearchTools.class.php',
    'mysearchresult' => 'search/source/mySearchResult.class.php',
    'sfisearchsource' => 'search/source/sfISearchSource.interface.php',
    'sfsearchresult' => 'search/source/sfSearchResult.class.php',
    'sfsearchresultcollection' => 'search/source/sfSearchResultCollection.class.php',
    'sfsearchresults' => 'search/source/sfSearchResults.class.php',
    'sfsearchsourceabstract' => 'search/source/sfSearchSourceAbstract.class.php',
    'sfsearchsourcecollection' => 'search/source/sfSearchSourceCollection.class.php',
    'sfcrypt' => 'security/sfCrypt.class.php',
    'sfinputfilters' => 'security/sfInputFilters.class.php',
    'sfsanitizer' => 'security/sfSanitizer.class.php',
    'sfsecurity' => 'security/sfSecurity.class.php',
    'sfsecuritycheckresult' => 'security/sfSecurityCheckResult.class.php',
    'sfnostorage' => 'storage/sfNoStorage.class.php',
    'sfpdosessionstorage' => 'storage/sfPDOSessionStorage.class.php',
    'sfsessionstorage' => 'storage/sfSessionStorage.class.php',
    'sfsessionteststorage' => 'storage/sfSessionTestStorage.class.php',
    'sfstorage' => 'storage/sfStorage.class.php',
    'sftestbrowser' => 'test/sfTestBrowser.class.php',
    'sftestfunctional' => 'test/sfTestFunctional.class.php',
    'sftestfunctionalbase' => 'test/sfTestFunctionalBase.class.php',
    'sftester' => 'test/sfTester.class.php',
    'sftesterform' => 'test/sfTesterForm.class.php',
    'sftestermailer' => 'test/sfTesterMailer.class.php',
    'sftesterrequest' => 'test/sfTesterRequest.class.php',
    'sftesterresponse' => 'test/sfTesterResponse.class.php',
    'sftesteruser' => 'test/sfTesterUser.class.php',
    'sftesterviewcache' => 'test/sfTesterViewCache.class.php',
    'sfrichtexteditor' => 'text/editor/sfRichTextEditor.class.php',
    'sfcoretextfilter' => 'text/filter/sfCoreTextFilter.class.php',
    'sftextfilter' => 'text/filter/sfTextFilter.interface.php',
    'sftextmacroregistry' => 'text/macro/sfTextMacroRegistry.class.php',
    'sftextmacrowidget' => 'text/macro/sfTextMacroWidget.interface.php',
    'sftextmacrowidgetbase' => 'text/macro/sfTextMacroWidgetBase.class.php',
    'mytext' => 'text/myText.class.php',
    'sfhtml2text' => 'text/sfHtml2Text.class.php',
    'sfmarkdownparser' => 'text/sfMarkdownParser.class.php',
    'sfplaintexttable' => 'text/sfPlainTextTable.php',
    'sftext' => 'text/sfText.class.php',
    'sftypography' => 'text/sfTypography.class.php',
    'sfwordhtmlcleaner' => 'text/sfWordHtmlCleaner.class.php',
    'sfbasicsecurityuser' => 'user/sfBasicSecurityUser.class.php',
    'sfsecurityuser' => 'user/sfSecurityUser.class.php',
    'sfuser' => 'user/sfUser.class.php',
    'sfuseragentdetector' => 'user/sfUserAgentDetector.class.php',
    'sfutf8' => 'utf8/sfUtf8.class.php',
    'sfarray' => 'util/sfArray.class.php',
    'sfassetpackage' => 'util/sfAssetPackage.class.php',
    'sfbitwise' => 'util/sfBitwise.class.php',
    'sfbrowser' => 'util/sfBrowser.class.php',
    'sffakerenderingfilter' => 'util/sfBrowser.class.php',
    'sfbrowserbase' => 'util/sfBrowserBase.class.php',
    'sfcallable' => 'util/sfCallable.class.php',
    'sfcontext' => 'util/sfContext.class.php',
    'sfdomcssselector' => 'util/sfDomCssSelector.class.php',
    'sffinder' => 'util/sfFinder.class.php',
    'sfglobtoregex' => 'util/sfGlobToRegex.class.php',
    'sfhtml' => 'util/sfHtml.class.php',
    'sfinflector' => 'util/sfInflector.class.php',
    'sfintegerencoder' => 'util/sfIntegerEncoder.class.php',
    'sfmimetype' => 'util/sfMimeType.class.php',
    'sfnumbercompare' => 'util/sfNumberCompare.class.php',
    'sfparameterholder' => 'util/sfParameterHolder.class.php',
    'sfpasswordtools' => 'util/sfPasswordTools.class.php',
    'sfphpexpression' => 'util/sfPhpExpression.class.php',
    'sfprefetchbrowser' => 'util/sfPrefetchBrowser.class.php',
    'sfsafeurl' => 'util/sfSafeUrl.class.php',
    'sfshutdownscheduler' => 'util/sfShutdownScheduler.class.php',
    'sftoolkit' => 'util/sfToolkit.class.php',
    'sfuuid' => 'util/sfUuid.class.php',
    'sfvalidatori18nchoicecountry' => 'validator/i18n/sfValidatorI18nChoiceCountry.class.php',
    'sfvalidatori18nchoicelanguage' => 'validator/i18n/sfValidatorI18nChoiceLanguage.class.php',
    'sfvalidatori18nchoicetimezone' => 'validator/i18n/sfValidatorI18nChoiceTimezone.class.php',
    'sfcallbackvalidator' => 'validator/legacy/sfCallbackValidator.class.php',
    'sfcomparevalidator' => 'validator/legacy/sfCompareValidator.class.php',
    'sfdatevalidator' => 'validator/legacy/sfDateValidator.class.php',
    'sfemailvalidator' => 'validator/legacy/sfEmailValidator.class.php',
    'sffilevalidator' => 'validator/legacy/sfFileValidator.class.php',
    'sfhtmlvalidator' => 'validator/legacy/sfHtmlValidator.class.php',
    'sfnumbervalidator' => 'validator/legacy/sfNumberValidator.class.php',
    'sfphonenumbervalidator' => 'validator/legacy/sfPhoneNumberValidator.class.php',
    'sfpostcodevalidator' => 'validator/legacy/sfPostCodeValidator.class.php',
    'sfregexvalidator' => 'validator/legacy/sfRegexValidator.class.php',
    'sfslugvalidator' => 'validator/legacy/sfSlugValidator.class.php',
    'sfstringvalidator' => 'validator/legacy/sfStringValidator.class.php',
    'sfurlvalidator' => 'validator/legacy/sfUrlValidator.class.php',
    'sfvalidator' => 'validator/legacy/sfValidator.class.php',
    'sfvalidatormanager' => 'validator/legacy/sfValidatorManager.class.php',
    'sfvalidatorand' => 'validator/sfValidatorAnd.class.php',
    'sfvalidatorbase' => 'validator/sfValidatorBase.class.php',
    'sfvalidatorbirthnumber' => 'validator/sfValidatorBirthNumber.class.php',
    'sfvalidatorblacklist' => 'validator/sfValidatorBlacklist.class.php',
    'sfvalidatorblacklistregex' => 'validator/sfValidatorBlacklistRegex.class.php',
    'sfvalidatorboolean' => 'validator/sfValidatorBoolean.class.php',
    'sfvalidatorcsrftoken' => 'validator/sfValidatorCSRFToken.class.php',
    'sfvalidatorcallback' => 'validator/sfValidatorCallback.class.php',
    'sfvalidatorchoice' => 'validator/sfValidatorChoice.class.php',
    'sfvalidatorchoicemany' => 'validator/sfValidatorChoiceMany.class.php',
    'sfvalidatorcompanyin' => 'validator/sfValidatorCompanyIn.class.php',
    'sfvalidatorcompanyindriverares' => 'validator/sfValidatorCompanyInDriverAres.class.php',
    'sfvalidatorcompanyvat' => 'validator/sfValidatorCompanyVat.class.php',
    'sfvalidatordate' => 'validator/sfValidatorDate.class.php',
    'sfvalidatordaterange' => 'validator/sfValidatorDateRange.class.php',
    'sfvalidatordatetime' => 'validator/sfValidatorDateTime.class.php',
    'sfvalidatordatetimerange' => 'validator/sfValidatorDateTimeRange.class.php',
    'sfvalidatordecorator' => 'validator/sfValidatorDecorator.class.php',
    'sfvalidatordefault' => 'validator/sfValidatorDefault.class.php',
    'sfvalidatoremail' => 'validator/sfValidatorEmail.class.php',
    'sfvalidatorerror' => 'validator/sfValidatorError.class.php',
    'sfvalidatorerrorschema' => 'validator/sfValidatorErrorSchema.class.php',
    'sfvalidatorfile' => 'validator/sfValidatorFile.class.php',
    'sfvalidatorfirstname' => 'validator/sfValidatorFirstName.class.php',
    'sfvalidatorfromdescription' => 'validator/sfValidatorFromDescription.class.php',
    'sfvalidatorfdtoken' => 'validator/sfValidatorFromDescription.class.php',
    'sfvalidatorfdtokenfilter' => 'validator/sfValidatorFromDescription.class.php',
    'sfvalidatorfdtokenoperator' => 'validator/sfValidatorFromDescription.class.php',
    'sfvalidatorfdtokenleftbracket' => 'validator/sfValidatorFromDescription.class.php',
    'sfvalidatorfdtokenrightbracket' => 'validator/sfValidatorFromDescription.class.php',
    'sfvalidatorhtml' => 'validator/sfValidatorHtml.class.php',
    'sfvalidatorimage' => 'validator/sfValidatorImage.class.php',
    'sfvalidatorinteger' => 'validator/sfValidatorInteger.class.php',
    'sfvalidatorlastname' => 'validator/sfValidatorLastName.class.php',
    'sfvalidatornumber' => 'validator/sfValidatorNumber.class.php',
    'sfvalidatoror' => 'validator/sfValidatorOr.class.php',
    'sfvalidatorpass' => 'validator/sfValidatorPass.class.php',
    'sfvalidatorpassword' => 'validator/sfValidatorPassword.class.php',
    'sfvalidatorphone' => 'validator/sfValidatorPhone.class.php',
    'sfvalidatorrecaptcha' => 'validator/sfValidatorReCaptcha.class.php',
    'sfvalidatorregex' => 'validator/sfValidatorRegex.class.php',
    'sfvalidatorschema' => 'validator/sfValidatorSchema.class.php',
    'sfvalidatorschemacompare' => 'validator/sfValidatorSchemaCompare.class.php',
    'sfvalidatorschemafilter' => 'validator/sfValidatorSchemaFilter.class.php',
    'sfvalidatorschemaforeach' => 'validator/sfValidatorSchemaForEach.class.php',
    'sfvalidatorschematimeinterval' => 'validator/sfValidatorSchemaTimeInterval.class.php',
    'sfvalidatorseparatedtextvalues' => 'validator/sfValidatorSeparatedTextValues.class.php',
    'sfvalidatorspamprotecttimer' => 'validator/sfValidatorSpamProtectTimer.class.php',
    'sfvalidatorstring' => 'validator/sfValidatorString.class.php',
    'sfvalidatortime' => 'validator/sfValidatorTime.class.php',
    'sfvalidatortools' => 'validator/sfValidatorTools.class.php',
    'sfvalidatorurl' => 'validator/sfValidatorUrl.class.php',
    'sfvalidatorzip' => 'validator/sfValidatorZip.class.php',
    'sfoutputescaper' => 'view/escaper/sfOutputEscaper.class.php',
    'sfoutputescaperarraydecorator' => 'view/escaper/sfOutputEscaperArrayDecorator.class.php',
    'sfoutputescapergetterdecorator' => 'view/escaper/sfOutputEscaperGetterDecorator.class.php',
    'sfoutputescaperiteratordecorator' => 'view/escaper/sfOutputEscaperIteratorDecorator.class.php',
    'sfoutputescaperobjectdecorator' => 'view/escaper/sfOutputEscaperObjectDecorator.class.php',
    'sfoutputescapersafe' => 'view/escaper/sfOutputEscaperSafe.class.php',
    'sfdimensionsview' => 'view/sfDimensionsView.class.php',
    'sfjavascriptpartialview' => 'view/sfJavascriptPartialView.class.php',
    'sfjavascriptview' => 'view/sfJavascriptView.class.php',
    'sfphpview' => 'view/sfPHPView.class.php',
    'sfpartialmailview' => 'view/sfPartialMailView.class.php',
    'sfpartialview' => 'view/sfPartialView.class.php',
    'sfview' => 'view/sfView.class.php',
    'sfviewcachemanager' => 'view/sfViewCacheManager.class.php',
    'sfxmlelement' => 'xml/sfXmlElement.class.php',
    'sfyaml' => 'yaml/sfYaml.class.php',
    'sfyamldumper' => 'yaml/sfYamlDumper.class.php',
    'sfyamlinline' => 'yaml/sfYamlInline.class.php',
    'sfyamlparser' => 'yaml/sfYamlParser.class.php',
  );

}
