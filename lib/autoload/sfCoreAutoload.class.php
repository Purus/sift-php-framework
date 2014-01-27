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
 */
class sfCoreAutoload {

  /**
   * Registered flag
   *
   * @var boolean
   */
  protected static $registered = false;

  /**
   * Instance holder
   *
   * @var sfCoreAutoload
   */
  protected static $instance = null;

  /**
   * Base directory
   *
   * @var string
   */
  protected $baseDir;

  /**
   * Constructor
   *
   */
  protected function __construct()
  {
    $this->baseDir = realpath(dirname(__FILE__) . '/..');
  }

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfCoreAutoload A sfCoreAutoload implementation instance.
   */
  public static function getInstance()
  {
    if(!isset(self::$instance))
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
  public static function register()
  {
    if(self::$registered)
    {
      return;
    }

    if(false === spl_autoload_register(array(self::getInstance(), 'autoload')))
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
  public static function unregister()
  {
    spl_autoload_unregister(array(self::getInstance(), 'autoload'));
    self::$registered = false;
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    if(!isset($this->classes[$class]))
    {
      return;
    }

    if($path = $this->getClassPath($class))
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
    if(!isset($this->classes[$class]))
    {
      return null;
    }

    return $this->baseDir . '/' . $this->classes[$class];
  }

  /**
   * Returns the base directory this autoloader is working on.
   *
   * @return string The path to the Sift core lib directory
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
    $libDir = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));
    require_once $libDir . '/util/sfFinder.class.php';
    require_once $libDir . '/util/sfGlobToRegex.class.php';
    require_once $libDir . '/util/sfNumberCompare.class.php';
    require_once $libDir . '/util/sfToolkit.class.php';

    $files = sfFinder::type('file')
        ->prune('plugins')
        ->prune('vendor')
        ->prune('skeleton')
        ->prune('default')
        ->prune('helper')
        ->name('*.php')
        ->in($libDir);

    sort($files, SORT_STRING);

    $classes = array();
    foreach($files as $file)
    {
      $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
      foreach(sfToolkit::extractClasses($file) as $class)
      {
        $classes[$class] = $file;
      }
    }

    asort($classes);

    $php = '';
    foreach($classes as $class => $file)
    {
      $php .= sprintf("      '%s' => '%s',\n", $class, substr(str_replace($libDir, '', $file), 1));
    }

    $content = preg_replace('/protected \$classes = array *\(.*?\);/s', sprintf("protected \$classes = array(\n%s  );", $php), file_get_contents(__FILE__));
    file_put_contents(__FILE__, $content);
  }

  /**
   * Don't edit this property by hand.
   * To update it, use sfCoreAutoload::make()
   *
   * @var array
   */
  protected $classes = array(
      'myAction' => 'action/myAction.class.php',
      'myActions' => 'action/myActions.class.php',
      'myComponents' => 'action/myComponents.class.php',
      'myWizardActions' => 'action/myWizardActions.class.php',
      'myWizardComponents' => 'action/myWizardComponents.class.php',
      'sfAction' => 'action/sfAction.class.php',
      'sfActionStack' => 'action/sfActionStack.class.php',
      'sfActionStackEntry' => 'action/sfActionStackEntry.class.php',
      'sfActions' => 'action/sfActions.class.php',
      'sfComponent' => 'action/sfComponent.class.php',
      'sfComponents' => 'action/sfComponents.class.php',
      'sfWizardActions' => 'action/sfWizardActions.class.php',
      'sfWizardComponents' => 'action/sfWizardComponents.class.php',
      'sfGoogleAnalytics' => 'analytics/sfGoogleAnalytics.class.php',
      'sfAntivirusDriverClamav' => 'antivirus/driver/sfAntivirusDriverClamav.class.php',
      'sfAntivirusDriverClamavSocket' => 'antivirus/driver/sfAntivirusDriverClamavSocket.class.php',
      'sfAntivirus' => 'antivirus/sfAntivirus.class.php',
      'sfIAntivirus' => 'antivirus/sfIAntivirus.interface.php',
      'sfZipArchive' => 'archive/sfZipArchive.class.php',
      'sfClassLoader' => 'autoload/sfClassLoader.class.php',
      'sfCoreAutoload' => 'autoload/sfCoreAutoload.class.php',
      'sfSimpleAutoload' => 'autoload/sfSimpleAutoload.class.php',
      'myBreadcrumbs' => 'breadcrumbs/myBreadcrumbs.class.php',
      'sfBreadcrumbs' => 'breadcrumbs/sfBreadcrumbs.class.php',
      'sfIWebBrowserDriver' => 'browser/sfIWebBrowserDriver.interface.php',
      'sfWebBrowser' => 'browser/sfWebBrowser.class.php',
      'sfWebBrowserDriverCurl' => 'browser/sfWebBrowserDriverCurl.class.php',
      'sfWebBrowserDriverFopen' => 'browser/sfWebBrowserDriverFopen.class.php',
      'sfWebBrowserDriverSockets' => 'browser/sfWebBrowserDriverSockets.class.php',
      'sfCache' => 'cache/sfCache.class.php',
      'sfFileCache' => 'cache/sfFileCache.class.php',
      'sfICache' => 'cache/sfICache.interface.php',
      'sfICacheAware' => 'cache/sfICacheAware.interface.php',
      'sfNoCache' => 'cache/sfNoCache.class.php',
      'sfProcessCache' => 'cache/sfProcessCache.class.php',
      'sfSQLiteCache' => 'cache/sfSQLiteCache.class.php',
      'sfCalendar' => 'calendar/sfCalendar.class.php',
      'sfCalendarEvent' => 'calendar/sfCalendarEvent.class.php',
      'sfCalendarRenderer' => 'calendar/sfCalendarRenderer.class.php',
      'sfCalendarRendererHtml' => 'calendar/sfCalendarRendererHtml.class.php',
      'sfCalendarRendererICal' => 'calendar/sfCalendarRendererICal.class.php',
      'sfICalendarEvent' => 'calendar/sfICalendarEvent.interface.php',
      'sfICalendarRenderer' => 'calendar/sfICalendarRenderer.interface.php',
      'sfCliAnsiColorFormatter' => 'cli/sfCliAnsiColorFormatter.class.php',
      'sfCliCommandApplication' => 'cli/sfCliCommandApplication.class.php',
      'sfCliCommandArgument' => 'cli/sfCliCommandArgument.class.php',
      'sfCliCommandArgumentSet' => 'cli/sfCliCommandArgumentSet.class.php',
      'sfCliCommandManager' => 'cli/sfCliCommandManager.class.php',
      'sfCliCommandOption' => 'cli/sfCliCommandOption.class.php',
      'sfCliCommandOptionSet' => 'cli/sfCliCommandOptionSet.class.php',
      'sfCliFormatter' => 'cli/sfCliFormatter.class.php',
      'sfCliRootCommandApplication' => 'cli/sfCliRootCommandApplication.class.php',
      'sfCliTaskEnvironment' => 'cli/sfCliTaskEnvironment.class.php',
      'sfCliCacheClearTask' => 'cli/task/cache/sfCliCacheClearTask.class.php',
      'sfCliConfigureDatabaseTask' => 'cli/task/configure/sfCliConfigureDatabaseTask.class.php',
      'sfCliGenerateAppTask' => 'cli/task/generate/sfCliGenerateAppTask.class.php',
      'sfCliGenerateControllerTask' => 'cli/task/generate/sfCliGenerateControllerTask.class.php',
      'sfCliGenerateFormTask' => 'cli/task/generate/sfCliGenerateFormTask.class.php',
      'sfCliGenerateModuleTask' => 'cli/task/generate/sfCliGenerateModuleTask.class.php',
      'sfCliGeneratePluginModuleTask' => 'cli/task/generate/sfCliGeneratePluginModuleTask.class.php',
      'sfCliGeneratePluginTask' => 'cli/task/generate/sfCliGeneratePluginTask.class.php',
      'sfCliGenerateProjectTask' => 'cli/task/generate/sfCliGenerateProjectTask.class.php',
      'sfGenerateTaskTask' => 'cli/task/generate/sfCliGenerateTaskTask.class.php',
      'sfCliGenerateTestTask' => 'cli/task/generate/sfCliGenerateTestTask.class.php',
      'sfCliGeneratorBaseTask' => 'cli/task/generate/sfCliGeneratorBaseTask.class.php',
      'sfCliHelpTask' => 'cli/task/help/sfCliHelpTask.class.php',
      'sfCliI18nBaseTask' => 'cli/task/i18n/sfCliI18nBaseTask.class.php',
      'sfCliI18nBundleTask' => 'cli/task/i18n/sfCliI18nBundleTask.class.php',
      'sfCliI18nExtractFormTask' => 'cli/task/i18n/sfCliI18nExtractFormTask.class.php',
      'sfCliI18nExtractFormsTask' => 'cli/task/i18n/sfCliI18nExtractFormsTask.class.php',
      'sfCliI18nExtractTask' => 'cli/task/i18n/sfCliI18nExtractTask.class.php',
      'sfCliI18nFindTask' => 'cli/task/i18n/sfCliI18nFindTask.class.php',
      'sfCliListTask' => 'cli/task/list/sfCliListTask.class.php',
      'sfCliLogClearTask' => 'cli/task/log/sfCliLogClearTask.class.php',
      'sfCliLogRotateTask' => 'cli/task/log/sfCliLogRotateTask.class.php',
      'sfProjectSendEmailsTask' => 'cli/task/mailer/sfCliProjectSendEmailsTask.class.php',
      'sfCliPluginAddChannelTask' => 'cli/task/plugin/sfCliPluginAddChannelTask.class.php',
      'sfCliPluginBaseTask' => 'cli/task/plugin/sfCliPluginBaseTask.class.php',
      'sfCliPluginDisableTask' => 'cli/task/plugin/sfCliPluginDisableTask.class.php',
      'sfCliPluginEnableTask' => 'cli/task/plugin/sfCliPluginEnableTask.class.php',
      'sfCliPluginInstallTask' => 'cli/task/plugin/sfCliPluginInstallTask.class.php',
      'sfCliPluginListChannelsTask' => 'cli/task/plugin/sfCliPluginListChannelsTask.class.php',
      'sfCliPluginListTask' => 'cli/task/plugin/sfCliPluginListTask.class.php',
      'sfCliPluginPackageTask' => 'cli/task/plugin/sfCliPluginPackageTask.class.php',
      'sfCliPluginRemoveChannelTask' => 'cli/task/plugin/sfCliPluginRemoveChannelTask.class.php',
      'sfCliPluginRunInstallerTask' => 'cli/task/plugin/sfCliPluginRunInstallerTask.class.php',
      'sfCliPluginUninstallTask' => 'cli/task/plugin/sfCliPluginUninstallTask.class.php',
      'sfCliPluginUpgradeTask' => 'cli/task/plugin/sfCliPluginUpgradeTask.class.php',
      'sfCliProjectClearControllersTask' => 'cli/task/project/sfCliProjectClearControllersTask.class.php',
      'sfCliProjectDeployTask' => 'cli/task/project/sfCliProjectDeployTask.class.php',
      'sfCliProjectDisableTask' => 'cli/task/project/sfCliProjectDisableTask.class.php',
      'sfCliProjectEnableTask' => 'cli/task/project/sfCliProjectEnableTask.class.php',
      'sfCliProjectFreezeTask' => 'cli/task/project/sfCliProjectFreezeTask.class.php',
      'sfCliProjectPermissionsTask' => 'cli/task/project/sfCliProjectPermissionsTask.class.php',
      'sfCliProjectPrefetchTask' => 'cli/task/project/sfCliProjectPrefetchTask.class.php',
      'sfCliProjectUnfreezeTask' => 'cli/task/project/sfCliProjectUnfreezeTask.class.php',
      'sfCliGenerateCryptKeyTask' => 'cli/task/security/sfCliGenerateCryptKeyTask.class.php',
      'sfCliBaseTask' => 'cli/task/sfCliBaseTask.class.php',
      'sfCliCommandApplicationTask' => 'cli/task/sfCliCommandApplicationTask.class.php',
      'sfCliTask' => 'cli/task/sfCliTask.class.php',
      'sfCliTestAllTask' => 'cli/task/test/sfCliTestAllTask.class.php',
      'sfCliTestBaseTask' => 'cli/task/test/sfCliTestBaseTask.class.php',
      'sfCliTestCoverageTask' => 'cli/task/test/sfCliTestCoverageTask.class.php',
      'sfCliTestFunctionalTask' => 'cli/task/test/sfCliTestFunctionalTask.class.php',
      'sfCliTestUnitTask' => 'cli/task/test/sfCliTestUnitTask.class.php',
      'sfLimeHarness' => 'cli/task/test/sfLimeHarness.class.php',
      'sfCollection' => 'collection/sfCollection.class.php',
      'sfCollectionSorter' => 'collection/sfCollectionSorter.class.php',
      'sfCollectionSorterStrategyCallback' => 'collection/sfCollectionSorterStrategyCallback.class.php',
      'sfICollectionSorterStrategy' => 'collection/sfICollectionSorterStrategy.interface.php',
      'myColorPalette' => 'color/myColorPalette.class.php',
      'sfColor' => 'color/sfColor.class.php',
      'sfColorPalette' => 'color/sfColorPalette.class.php',
      'sfIColorPallete' => 'color/sfIColorPalette.interface.php',
      'sfAssetPackagesConfigHandler' => 'config/sfAssetPackagesConfigHandler.class.php',
      'sfAutoloadConfigHandler' => 'config/sfAutoloadConfigHandler.class.php',
      'sfCacheConfigHandler' => 'config/sfCacheConfigHandler.class.php',
      'sfCompileConfigHandler' => 'config/sfCompileConfigHandler.class.php',
      'sfConfig' => 'config/sfConfig.class.php',
      'sfConfigCache' => 'config/sfConfigCache.class.php',
      'sfConfigHandler' => 'config/sfConfigHandler.class.php',
      'sfConfigurable' => 'config/sfConfigurable.class.php',
      'sfDatabaseConfigHandler' => 'config/sfDatabaseConfigHandler.class.php',
      'sfDefineEnvironmentConfigHandler' => 'config/sfDefineEnvironmentConfigHandler.class.php',
      'sfDimensionsConfigHandler' => 'config/sfDimensionsConfigHandler.class.php',
      'sfFactoryConfigHandler' => 'config/sfFactoryConfigHandler.class.php',
      'sfFilterConfigHandler' => 'config/sfFilterConfigHandler.class.php',
      'sfGeneratorConfigHandler' => 'config/sfGeneratorConfigHandler.class.php',
      'sfI18nConfigHandler' => 'config/sfI18nConfigHandler.class.php',
      'sfIConfigurable' => 'config/sfIConfigurable.interface.php',
      'sfLoggingConfigHandler' => 'config/sfLoggingConfigHandler.class.php',
      'sfMailConfigHandler' => 'config/sfMailConfigHandler.class.php',
      'sfModulesConfigHandler' => 'config/sfModulesConfigHandler.class.php',
      'sfPhpConfigHandler' => 'config/sfPhpConfigHandler.class.php',
      'sfPluginsConfigHandler' => 'config/sfPluginsConfigHandler.class.php',
      'sfRichEditorConfigHandler' => 'config/sfRichEditorConfigHandler.class.php',
      'sfRootConfigHandler' => 'config/sfRootConfigHandler.class.php',
      'sfRoutingConfigHandler' => 'config/sfRoutingConfigHandler.class.php',
      'sfSanitizeConfigHandler' => 'config/sfSanitizeConfigHandler.class.php',
      'sfSecurityConfigHandler' => 'config/sfSecurityConfigHandler.class.php',
      'sfSimpleYamlConfigHandler' => 'config/sfSimpleYamlConfigHandler.class.php',
      'sfTextFiltersConfigHandler' => 'config/sfTextFiltersConfigHandler.class.php',
      'sfViewConfigHandler' => 'config/sfViewConfigHandler.class.php',
      'sfYamlConfigHandler' => 'config/sfYamlConfigHandler.class.php',
      'sfController' => 'controller/sfController.class.php',
      'sfFrontWebController' => 'controller/sfFrontWebController.class.php',
      'sfIController' => 'controller/sfIController.interface.php',
      'sfWebController' => 'controller/sfWebController.class.php',
      'sfCore' => 'core/sfCore.class.php',
      'sfDimensions' => 'core/sfDimensions.class.php',
      'sfLoader' => 'core/sfLoader.class.php',
      'sfPDO' => 'database/pdo/sfPDO.class.php',
      'sfPDOStatement' => 'database/pdo/sfPDOStatement.class.php',
      'sfDatabase' => 'database/sfDatabase.class.php',
      'sfDatabaseManager' => 'database/sfDatabaseManager.class.php',
      'sfIDataRetriever' => 'database/sfIDataRetriever.interface.php',
      'sfMockDatabase' => 'database/sfMockDatabase.class.php',
      'sfPDODatabase' => 'database/sfPDODatabase.class.php',
      'sfCapturingDateFormatRegexGenerator' => 'date/sfCapturingDateFormatRegexGenerator.class.php',
      'sfDate' => 'date/sfDate.class.php',
      'sfDateFormatRegexGenerator' => 'date/sfDateFormatRegexGenerator.class.php',
      'sfDateTimeToolkit' => 'date/sfDateTimeToolkit.class.php',
      'sfDateTimeZone' => 'date/sfDateTimeZone.class.php',
      'sfTime' => 'date/sfTime.class.php',
      'sfDebugBacktrace' => 'debug/backtrace/sfDebugBacktrace.class.php',
      'sfDebugBacktraceDecorator' => 'debug/backtrace/sfDebugBacktraceDecorator.class.php',
      'sfDebugBacktraceHtmlDecorator' => 'debug/backtrace/sfDebugBacktraceHtmlDecorator.class.php',
      'sfDebugBacktraceLogDecorator' => 'debug/backtrace/sfDebugBacktraceLogDecorator.class.php',
      'sfIDebugBacktraceDecorator' => 'debug/backtrace/sfIDebugBacktraceDecorator.interface.php',
      'sfSyntaxHighlighter' => 'debug/highlighter/sfSyntaxHighlighter.class.php',
      'sfSyntaxHighlighterGeneric' => 'debug/highlighter/sfSyntaxHighlighterGeneric.class.php',
      'sfSyntaxHighlighterHtml' => 'debug/highlighter/sfSyntaxHighlighterHtml.class.php',
      'sfSyntaxHighlighterPhp' => 'debug/highlighter/sfSyntaxHighlighterPhp.class.php',
      'sfSyntaxHighlighterSql' => 'debug/highlighter/sfSyntaxHighlighterSql.class.php',
      'sfWebDebugPanel' => 'debug/panel/sfWebDebugPanel.class.php',
      'sfWebDebugPanelCache' => 'debug/panel/sfWebDebugPanelCache.class.php',
      'sfWebDebugPanelCurrentRoute' => 'debug/panel/sfWebDebugPanelCurrentRoute.class.php',
      'sfWebDebugPanelDatabase' => 'debug/panel/sfWebDebugPanelDatabase.class.php',
      'sfWebDebugPanelDocumentation' => 'debug/panel/sfWebDebugPanelDocumentation.class.php',
      'sfWebDebugPanelEnvironment' => 'debug/panel/sfWebDebugPanelEnvironment.class.php',
      'sfWebDebugPanelHtmlValidate' => 'debug/panel/sfWebDebugPanelHtmlValidate.class.php',
      'sfWebDebugPanelLogs' => 'debug/panel/sfWebDebugPanelLogs.class.php',
      'sfWebDebugPanelMailer' => 'debug/panel/sfWebDebugPanelMailer.class.php',
      'sfWebDebugPanelMemory' => 'debug/panel/sfWebDebugPanelMemory.class.php',
      'sfWebDebugPanelResponse' => 'debug/panel/sfWebDebugPanelResponse.class.php',
      'sfWebDebugPanelTimer' => 'debug/panel/sfWebDebugPanelTimer.class.php',
      'sfWebDebugPanelUser' => 'debug/panel/sfWebDebugPanelUser.class.php',
      'sfDebug' => 'debug/sfDebug.class.php',
      'sfDebugDumper' => 'debug/sfDebugDumper.class.php',
      'sfTimer' => 'debug/sfTimer.class.php',
      'sfTimerManager' => 'debug/sfTimerManager.class.php',
      'sfWebDebug' => 'debug/sfWebDebug.class.php',
      'sfWebDebugIcon' => 'debug/sfWebDebugIcon.class.php',
      'sfDependencyInjectionMapBuilder' => 'dependency_injection/map/builder/sfDependencyInjectionMapBuilder.class.php',
      'sfDependencyInjectionMapBuilderClass' => 'dependency_injection/map/builder/sfDependencyInjectionMapBuilderClass.class.php',
      'sfDependencyInjectionMap' => 'dependency_injection/map/sfDependencyInjectionMap.class.php',
      'sfDependencyInjectionMapItem' => 'dependency_injection/map/sfDependencyInjectionMapItem.class.php',
      'sfDependencyInjectionBuilder' => 'dependency_injection/sfDependencyInjectionBuilder.class.php',
      'sfDependencyInjectionDependencies' => 'dependency_injection/sfDependencyInjectionDependencies.class.php',
      'sfDependencyInjectionInjectCommandParser' => 'dependency_injection/sfDependencyInjectionInjectCommandParser.class.php',
      'sfDependencyInjectionMaps' => 'dependency_injection/sfDependencyInjectionMaps.class.php',
      'sfEvent' => 'event/sfEvent.class.php',
      'sfEventDispatcher' => 'event/sfEventDispatcher.class.php',
      'sfIEventDispatcherAware' => 'event/sfIEventDispatcherAware.interface.php',
      'sfCacheException' => 'exception/sfCacheException.class.php',
      'sfCalendarException' => 'exception/sfCalendarException.class.php',
      'sfCliCommandArgumentsException' => 'exception/sfCliCommandArgumentsException.class.php',
      'sfCliCommandException' => 'exception/sfCliCommandException.class.php',
      'sfConfigurationException' => 'exception/sfConfigurationException.class.php',
      'sfControllerException' => 'exception/sfControllerException.class.php',
      'sfDatabaseException' => 'exception/sfDatabaseException.class.php',
      'sfDateTimeException' => 'exception/sfDateTimeException.class.php',
      'sfError404Exception' => 'exception/sfError404Exception.class.php',
      'sfException' => 'exception/sfException.class.php',
      'sfFactoryException' => 'exception/sfFactoryException.class.php',
      'sfFileException' => 'exception/sfFileException.class.php',
      'sfForwardException' => 'exception/sfForwardException.class.php',
      'sfHttpDownloadException' => 'exception/sfHttpDownloadException.class.php',
      'sfImageTransformException' => 'exception/sfImageTransformException.class.php',
      'sfInitializationException' => 'exception/sfInitializationException.class.php',
      'sfLessCompilerException' => 'exception/sfLessCompilerException.class.php',
      'sfParseException' => 'exception/sfParseException.class.php',
      'sfPhpErrorException' => 'exception/sfPhpErrorException.class.php',
      'sfPluginDependencyException' => 'exception/sfPluginDependencyException.class.php',
      'sfPluginException' => 'exception/sfPluginException.class.php',
      'sfPluginRecursiveDependencyException' => 'exception/sfPluginRecursiveDependencyException.class.php',
      'sfPluginRestException' => 'exception/sfPluginRestException.class.php',
      'sfRegexpException' => 'exception/sfRegexpException.class.php',
      'sfRenderException' => 'exception/sfRenderException.class.php',
      'sfSecurityException' => 'exception/sfSecurityException.class.php',
      'sfStopException' => 'exception/sfStopException.class.php',
      'sfStorageException' => 'exception/sfStorageException.class.php',
      'sfViewException' => 'exception/sfViewException.class.php',
      'sfWebBrowserInvalidResponseException' => 'exception/sfWebBrowserInvalidResponseException.class.php',
      'sfAtom1Feed' => 'feed/sfAtom1Feed.class.php',
      'sfFeed' => 'feed/sfFeed.class.php',
      'sfFeedEnclosure' => 'feed/sfFeedEnclosure.class.php',
      'sfFeedImage' => 'feed/sfFeedImage.class.php',
      'sfFeedItem' => 'feed/sfFeedItem.class.php',
      'sfFeedPeer' => 'feed/sfFeedPeer.class.php',
      'sfRss091Feed' => 'feed/sfRss091Feed.class.php',
      'sfRss10Feed' => 'feed/sfRss10Feed.class.php',
      'sfRss201Feed' => 'feed/sfRss201Feed.class.php',
      'sfRssFeed' => 'feed/sfRssFeed.class.php',
      'sfFilesystem' => 'file/sfFilesystem.class.php',
      'sfAssetPackagerFilter' => 'filter/sfAssetPackagerFilter.class.php',
      'sfBasicSecurityFilter' => 'filter/sfBasicSecurityFilter.class.php',
      'sfCacheFilter' => 'filter/sfCacheFilter.class.php',
      'sfCommonFilter' => 'filter/sfCommonFilter.class.php',
      'sfExecutionFilter' => 'filter/sfExecutionFilter.class.php',
      'sfFilter' => 'filter/sfFilter.class.php',
      'sfFilterChain' => 'filter/sfFilterChain.class.php',
      'sfIFilter' => 'filter/sfIFilter.interface.php',
      'sfISecurityFilter' => 'filter/sfISecurityFilter.interface.php',
      'sfRenderingFilter' => 'filter/sfRenderingFilter.class.php',
      'sfSecurityFilter' => 'filter/sfSecurityFilter.class.php',
      'sfTestRenderingFilter' => 'filter/sfTestRenderingFilter.class.php',
      'myFormEnhancer' => 'form/enhancer/myFormEnhancer.class.php',
      'sfFormEnhancer' => 'form/enhancer/sfFormEnhancer.class.php',
      'sfFormEnhancerRich' => 'form/enhancer/sfFormEnhancerRich.class.php',
      'sfIFormEnhancer' => 'form/enhancer/sfIFormEnhancer.interface.php',
      'sfWidgetFormSchemaDecorator' => 'form/formatter/sfWidgetFormSchemaDecorator.class.php',
      'sfWidgetFormSchemaFormatter' => 'form/formatter/sfWidgetFormSchemaFormatter.class.php',
      'sfWidgetFormSchemaFormatterAdvanced' => 'form/formatter/sfWidgetFormSchemaFormatterAdvanced.class.php',
      'sfWidgetFormSchemaFormatterDiv' => 'form/formatter/sfWidgetFormSchemaFormatterDiv.class.php',
      'sfWidgetFormSchemaFormatterList' => 'form/formatter/sfWidgetFormSchemaFormatterList.class.php',
      'sfWidgetFormSchemaFormatterMyList' => 'form/formatter/sfWidgetFormSchemaFormatterMyList.class.php',
      'sfWidgetFormSchemaFormatterPlain' => 'form/formatter/sfWidgetFormSchemaFormatterPlain.class.php',
      'sfWidgetFormSchemaFormatterTable' => 'form/formatter/sfWidgetFormSchemaFormatterTable.class.php',
      'sfWidgetFormSchemaFormatterUnorderedList' => 'form/formatter/sfWidgetFormSchemaFormatterUnorderedList.class.php',
      'sfFormJavascriptValidation' => 'form/javascript/sfFormJavascriptValidation.class.php',
      'sfFormJavascriptValidationFieldMessages' => 'form/javascript/sfFormJavascriptValidationFieldMessages.class.php',
      'sfFormJavascriptValidationFieldRules' => 'form/javascript/sfFormJavascriptValidationFieldRules.class.php',
      'sfFormJavascriptValidationMessage' => 'form/javascript/sfFormJavascriptValidationMessage.class.php',
      'sfFormJavascriptValidationMessagesCollection' => 'form/javascript/sfFormJavascriptValidationMessagesCollection.class.php',
      'sfFormJavascriptValidationRulesCollection' => 'form/javascript/sfFormJavascriptValidationRulesCollection.class.php',
      'myForm' => 'form/myForm.class.php',
      'myFormBase' => 'form/myFormBase.class.php',
      'sfFormObject' => 'form/object/sfFormObject.class.php',
      'sfForm' => 'form/sfForm.class.php',
      'sfFormCulture' => 'form/sfFormCulture.class.php',
      'sfFormField' => 'form/sfFormField.class.php',
      'sfFormFieldGroup' => 'form/sfFormFieldGroup.class.php',
      'sfFormFieldSchema' => 'form/sfFormFieldSchema.class.php',
      'sfFormManager' => 'form/sfFormManager.class.php',
      'sfWidgetFormChoice' => 'form/widget/choice/sfWidgetFormChoice.class.php',
      'sfWidgetFormChoiceBase' => 'form/widget/choice/sfWidgetFormChoiceBase.class.php',
      'sfWidgetFormChoiceMany' => 'form/widget/choice/sfWidgetFormChoiceMany.class.php',
      'sfWidgetFormDualList' => 'form/widget/choice/sfWidgetFormDualList.class.php',
      'sfWidgetFormInputCheckbox' => 'form/widget/choice/sfWidgetFormInputCheckbox.class.php',
      'sfWidgetFormInputRadio' => 'form/widget/choice/sfWidgetFormInputRadio.class.php',
      'sfWidgetFormSelect' => 'form/widget/choice/sfWidgetFormSelect.class.php',
      'sfWidgetFormSelectCheckbox' => 'form/widget/choice/sfWidgetFormSelectCheckbox.class.php',
      'sfWidgetFormSelectMany' => 'form/widget/choice/sfWidgetFormSelectMany.class.php',
      'sfWidgetFormSelectRadio' => 'form/widget/choice/sfWidgetFormSelectRadio.class.php',
      'sfWidgetFormTreeChoice' => 'form/widget/choice/sfWidgetFormTreeChoice.class.php',
      'sfWidgetFormTreeSelectCheckbox' => 'form/widget/choice/sfWidgetFormTreeSelectCheckbox.class.php',
      'sfWidgetFormTreeSelectRadio' => 'form/widget/choice/sfWidgetFormTreeSelectRadio.class.php',
      'sfWidgetFormTrilean' => 'form/widget/choice/sfWidgetFormTrilean.class.php',
      'sfWidgetFormDate' => 'form/widget/date/sfWidgetFormDate.class.php',
      'sfWidgetFormDateRange' => 'form/widget/date/sfWidgetFormDateRange.class.php',
      'sfWidgetFormDateTime' => 'form/widget/date/sfWidgetFormDateTime.class.php',
      'sfWidgetFormDateTimeRange' => 'form/widget/date/sfWidgetFormDateTimeRange.class.php',
      'sfWidgetFormFilterDate' => 'form/widget/date/sfWidgetFormFilterDate.class.php',
      'sfWidgetFormFilterDateTime' => 'form/widget/date/sfWidgetFormFilterDateTime.class.php',
      'sfWidgetFormTime' => 'form/widget/date/sfWidgetFormTime.class.php',
      'sfWidgetFormInputFile' => 'form/widget/file/sfWidgetFormInputFile.class.php',
      'sfWidgetFormInputFileEditable' => 'form/widget/file/sfWidgetFormInputFileEditable.class.php',
      'sfWidgetFormInputHidden' => 'form/widget/hidden/sfWidgetFormInputHidden.class.php',
      'sfWidgetFormI18nAggregate' => 'form/widget/i18n/sfWidgetFormI18nAggregate.class.php',
      'sfWidgetFormI18nChoiceCountry' => 'form/widget/i18n/sfWidgetFormI18nChoiceCountry.class.php',
      'sfWidgetFormI18nChoiceCurrency' => 'form/widget/i18n/sfWidgetFormI18nChoiceCurrency.class.php',
      'sfWidgetFormI18nChoiceEnabledLanguages' => 'form/widget/i18n/sfWidgetFormI18nChoiceEnabledLanguages.class.php',
      'sfWidgetFormI18nChoiceLanguage' => 'form/widget/i18n/sfWidgetFormI18nChoiceLanguage.class.php',
      'sfWidgetFormI18nChoiceTimezone' => 'form/widget/i18n/sfWidgetFormI18nChoiceTimezone.class.php',
      'sfWidgetFormI18nNumber' => 'form/widget/i18n/sfWidgetFormI18nNumber.php',
      'sfWidgetFormI18nSelectCountry' => 'form/widget/i18n/sfWidgetFormI18nSelectCountry.class.php',
      'sfWidgetFormI18nSelectCurrency' => 'form/widget/i18n/sfWidgetFormI18nSelectCurrency.class.php',
      'sfWidgetFormI18nSelectLanguage' => 'form/widget/i18n/sfWidgetFormI18nSelectLanguage.class.php',
      'sfWidgetFormInteger' => 'form/widget/number/sfWidgetFormInteger.class.php',
      'sfWidgetFormNumber' => 'form/widget/number/sfWidgetFormNumber.class.php',
      'sfWidgetFormPrice' => 'form/widget/number/sfWidgetFormPrice.class.php',
      'sfWidgetFormComponent' => 'form/widget/other/sfWidgetFormComponent.class.php',
      'sfWidgetFormIpAddress' => 'form/widget/other/sfWidgetFormIpAddress.class.php',
      'sfWidgetFormNoInput' => 'form/widget/other/sfWidgetFormNoInput.class.php',
      'sfWidgetFormPartial' => 'form/widget/other/sfWidgetFormPartial.class.php',
      'sfWidgetFormMetaTitleModeChoice' => 'form/widget/seo/sfWidgetFormMetaTitleModeChoice.class.php',
      'sfWidget' => 'form/widget/sfWidget.class.php',
      'sfWidgetForm' => 'form/widget/sfWidgetForm.class.php',
      'sfWidgetFormSchema' => 'form/widget/sfWidgetFormSchema.class.php',
      'sfWidgetFormSchemaForEach' => 'form/widget/sfWidgetFormSchemaForEach.class.php',
      'sfWidgetFormReCaptcha' => 'form/widget/spam_protect/sfWidgetFormReCaptcha.class.php',
      'sfWidgetFormSpamProtectTimer' => 'form/widget/spam_protect/sfWidgetFormSpamProtectTimer.class.php',
      'sfWidgetFormFilterInput' => 'form/widget/text/sfWidgetFormFilterInput.class.php',
      'sfWidgetFormInput' => 'form/widget/text/sfWidgetFormInput.class.php',
      'sfWidgetFormInputPassword' => 'form/widget/text/sfWidgetFormInputPassword.class.php',
      'sfWidgetFormInputText' => 'form/widget/text/sfWidgetFormInputText.class.php',
      'sfWidgetFormTextarea' => 'form/widget/text/sfWidgetFormTextarea.class.php',
      'myWizardForm' => 'form/wizard/myWizardForm.class.php',
      'sfWizardForm' => 'form/wizard/sfWizardForm.class.php',
      'sfGenerator' => 'generator/sfGenerator.class.php',
      'sfGeneratorField' => 'generator/sfGeneratorField.class.php',
      'sfGeneratorFormBuilder' => 'generator/sfGeneratorFormBuilder.class.php',
      'sfGeneratorManager' => 'generator/sfGeneratorManager.class.php',
      'sfIGenerator' => 'generator/sfIGenerator.interface.php',
      'sfIGeneratorField' => 'generator/sfIGeneratorField.interface.php',
      'sfBrowseHistory' => 'history/sfBrowseHistory.class.php',
      'sfBrowseHistoryItem' => 'history/sfBrowseHistoryItem.class.php',
      'sfCultureExport' => 'i18n/export/sfCultureExport.class.php',
      'sfCultureExportGlobalize' => 'i18n/export/sfI18nExportGlobalize.class.php',
      'sfICultureExport' => 'i18n/export/sfICultureExport.interface.php',
      'sfI18nApplicationExtract' => 'i18n/extract/sfI18nApplicationExtract.class.php',
      'sfI18nExtract' => 'i18n/extract/sfI18nExtract.class.php',
      'sfI18nExtractAnonymousUser' => 'i18n/extract/sfI18nExtractAnonymousUser.class.php',
      'sfI18nExtractLoggedInUser' => 'i18n/extract/sfI18nExtractLoggedInUser.class.php',
      'sfI18nExtractUser' => 'i18n/extract/sfI18nExtractUser.class.php',
      'sfI18nFormExtract' => 'i18n/extract/sfI18nFormExtract.class.php',
      'sfI18nJavascriptExtractor' => 'i18n/extract/sfI18nJavascriptExtractor.class.php',
      'sfI18nModelExtractor' => 'i18n/extract/sfI18nModelExtractor.class.php',
      'sfI18nModuleExtract' => 'i18n/extract/sfI18nModuleExtract.class.php',
      'sfI18nPhpExtractor' => 'i18n/extract/sfI18nPhpExtractor.class.php',
      'sfI18nPlainTextExtractor' => 'i18n/extract/sfI18nPlainTextExtractor.class.php',
      'sfI18nYamlDasboardWidgetsExtractor' => 'i18n/extract/sfI18nYamlDashboardWidgetsExtractor.class.php',
      'sfI18nYamlExtractor' => 'i18n/extract/sfI18nYamlExtractor.class.php',
      'sfI18nYamlGeneratorExtractor' => 'i18n/extract/sfI18nYamlGeneratorExtractor.class.php',
      'sfI18nYamlMenuExtractor' => 'i18n/extract/sfI18nYamlMenuExtractor.class.php',
      'sfI18nYamlValidateExtractor' => 'i18n/extract/sfI18nYamlValidateExtractor.class.php',
      'sfII18nExtractableForm' => 'i18n/extract/sfII18nExtractableForm.interface.php',
      'sfII18nExtractor' => 'i18n/extract/sfII18nExtractor.interface.php',
      'sfI18nDateTimeFormat' => 'i18n/format/sfI18nDateTimeFormat.class.php',
      'sfI18nNumberFormat' => 'i18n/format/sfI18nNumberFormat.class.php',
      'sfI18nChoiceFormatter' => 'i18n/formatter/sfI18nChoiceFormatter.class.php',
      'sfI18nDateFormatter' => 'i18n/formatter/sfI18nDateFormatter.class.php',
      'sfI18nNumberFormatter' => 'i18n/formatter/sfI18nNumberFormatter.class.php',
      'sfI18nPhoneNumberCultureFormatterCS' => 'i18n/formatter/sfI18nPhoneNumberCultureFormatterCS.php',
      'sfI18nPhoneNumberFormatter' => 'i18n/formatter/sfI18nPhoneNumberFormatter.class.php',
      'sfII18nPhoneNumberCultureFormatter' => 'i18n/formatter/sfII18nPhoneNumberFormatterCulture.interface.php',
      'sfISO3166' => 'i18n/iso/sfISO3166.class.php',
      'sfISO4217' => 'i18n/iso/sfISO4217.class.php',
      'sfISO639' => 'i18n/iso/sfISO639.class.php',
      'sfI18nMessageFormatter' => 'i18n/message/formatter/sfI18nMessageFormatter.class.php',
      'sfI18nMessageSource' => 'i18n/message/sfI18nMessageSource.class.php',
      'sfII18nMessageSource' => 'i18n/message/sfII18nMessageSource.interface.php',
      'sfI18nGettext' => 'i18n/message/source/gettext/sfI18nGettext.class.php',
      'sfI18nGettextMo' => 'i18n/message/source/gettext/sfI18nGettextMo.class.php',
      'sfI18nGettextPo' => 'i18n/message/source/gettext/sfI18nGettextPo.class.php',
      'sfI18nMessageSourceAggregate' => 'i18n/message/source/sfI18nMessageSourceAggregate.class.php',
      'sfI18nMessageSourceGettext' => 'i18n/message/source/sfI18nMessageSourceGettext.class.php',
      'sfI18nMessageSourceGettextSingleCatalogue' => 'i18n/message/source/sfI18nMessageSourceGettextSingleCatalogue.class.php',
      'sfI18nMessageSourceXliff' => 'i18n/message/source/sfI18nMessageSourceXliff.class.php',
      'sfI18nMessageSourceXliffSingleCatalogue' => 'i18n/message/source/sfI18nMessageSourceXliffSingleCatalogue.class.php',
      'sfCollator' => 'i18n/sfCollator.class.php',
      'sfCulture' => 'i18n/sfCulture.class.php',
      'sfI18n' => 'i18n/sfI18n.class.php',
      'sfExifAdapter' => 'image/adapters/sfExifAdapter.class.php',
      'sfExifAdapterExifTool' => 'image/adapters/sfExifAdapterExifTool.class.php',
      'sfExifAdapterNative' => 'image/adapters/sfExifAdapterNative.class.php',
      'sfImageTransformAdapterAbstract' => 'image/adapters/sfImageTransformAdapterAbstract.class.php',
      'sfImageTransformGDAdapter' => 'image/adapters/sfImageTransformGDAdapter.class.php',
      'sfImageTransformImageMagickAdapter' => 'image/adapters/sfImageTransformImageMagickAdapter.class.php',
      'sfExif' => 'image/sfExif.class.php',
      'sfImage' => 'image/sfImage.class.php',
      'sfImageAlphaMaskGD' => 'image/transforms/GD/sfImageAlphaMaskGD.class.php',
      'sfImageArcGD' => 'image/transforms/GD/sfImageArcGD.class.php',
      'sfImageBrightnessGD' => 'image/transforms/GD/sfImageBrightnessGD.class.php',
      'sfImageColorizeGD' => 'image/transforms/GD/sfImageColorizeGD.class.php',
      'sfImageContrastGD' => 'image/transforms/GD/sfImageContrastGD.class.php',
      'sfImageCropGD' => 'image/transforms/GD/sfImageCropGD.class.php',
      'sfImageEdgeDetectGD' => 'image/transforms/GD/sfImageEdgeDetectGD.class.php',
      'sfImageEllipseGD' => 'image/transforms/GD/sfImageEllipseGD.class.php',
      'sfImageEmbossGD' => 'image/transforms/GD/sfImageEmbossGD.class.php',
      'sfImageFillGD' => 'image/transforms/GD/sfImageFillGD.class.php',
      'sfImageFlipGD' => 'image/transforms/GD/sfImageFlipGD.class.php',
      'sfImageGammaGD' => 'image/transforms/GD/sfImageGammaGD.class.php',
      'sfImageGaussianBlurGD' => 'image/transforms/GD/sfImageGaussianBlurGD.class.php',
      'sfImageGreyscaleGD' => 'image/transforms/GD/sfImageGreyscaleGD.class.php',
      'sfImageLineGD' => 'image/transforms/GD/sfImageLineGD.class.php',
      'sfImageMergeGD' => 'image/transforms/GD/sfImageMergeGD.class.php',
      'sfImageMirrorGD' => 'image/transforms/GD/sfImageMirrorGD.class.php',
      'sfImageNegateGD' => 'image/transforms/GD/sfImageNegateGD.class.php',
      'sfImageNoiseGD' => 'image/transforms/GD/sfImageNoiseGD.class.php',
      'sfImageOpacityGD' => 'image/transforms/GD/sfImageOpacityGD.class.php',
      'sfImageOverlayGD' => 'image/transforms/GD/sfImageOverlayGD.class.php',
      'sfImagePixelBlurGD' => 'image/transforms/GD/sfImagePixelBlurGD.class.php',
      'sfImagePixelizeGD' => 'image/transforms/GD/sfImagePixelizeGD.class.php',
      'sfImagePolygonGD' => 'image/transforms/GD/sfImagePolygonGD.class.php',
      'sfImageRectangleGD' => 'image/transforms/GD/sfImageRectangleGD.class.php',
      'sfImageReflectionGD' => 'image/transforms/GD/sfImageReflectionGD.class.php',
      'sfImageResizeSimpleGD' => 'image/transforms/GD/sfImageResizeSimpleGD.class.php',
      'sfImageRotateGD' => 'image/transforms/GD/sfImageRotateGD.class.php',
      'sfImageRoundedCornersGD' => 'image/transforms/GD/sfImageRoundedCornersGD.class.php',
      'sfImageScaleGD' => 'image/transforms/GD/sfImageScaleGD.class.php',
      'sfImageScatterGD' => 'image/transforms/GD/sfImageScatterGD.class.php',
      'sfImageSelectiveBlurGD' => 'image/transforms/GD/sfImageSelectiveBlurGD.class.php',
      'sfImageSketchyGD' => 'image/transforms/GD/sfImageSketchyGD.class.php',
      'sfImageSmoothGD' => 'image/transforms/GD/sfImageSmoothGD.class.php',
      'sfImageTextGD' => 'image/transforms/GD/sfImageTextGD.class.php',
      'sfImageTransparencyGD' => 'image/transforms/GD/sfImageTransparencyGD.class.php',
      'sfImageUnsharpMaskGD' => 'image/transforms/GD/sfImageUnsharpMaskGD.class.php',
      'sfImageBorderGeneric' => 'image/transforms/Generic/sfImageBorderGeneric.php',
      'sfImageCallbackGeneric' => 'image/transforms/Generic/sfImageCallback.class.php',
      'sfImageResizeGeneric' => 'image/transforms/Generic/sfImageResizeGeneric.php',
      'sfImageThumbnailGeneric' => 'image/transforms/Generic/sfImageThumbnailGeneric.php',
      'sfImageBrightnessImageMagick' => 'image/transforms/ImageMagick/sfImageBrightnessImageMagick.class.php',
      'sfImageColorizeImageMagick' => 'image/transforms/ImageMagick/sfImageColorizeImageMagick.class.php',
      'sfImageCropImageMagick' => 'image/transforms/ImageMagick/sfImageCropImageMagick.class.php',
      'sfImageFillImageMagick' => 'image/transforms/ImageMagick/sfImageFillImageMagick.class.php',
      'sfImageFlipImageMagick' => 'image/transforms/ImageMagick/sfImageFlipImageMagick.class.php',
      'sfImageGreyscaleImageMagick' => 'image/transforms/ImageMagick/sfImageGreyscaleImageMagick.class.php',
      'sfImageLineImageMagick' => 'image/transforms/ImageMagick/sfImageLineImageMagick.class.php',
      'sfImageMirrorImageMagick' => 'image/transforms/ImageMagick/sfImageMirrorImageMagick.class.php',
      'sfImageOpacityImageMagick' => 'image/transforms/ImageMagick/sfImageOpacityImageMagick.class.php',
      'sfImageOverlayImageMagick' => 'image/transforms/ImageMagick/sfImageOverlayImageMagick.class.php',
      'sfImagePrettyThumbnailImageMagick' => 'image/transforms/ImageMagick/sfImagePrettyThumbnailImageMagick.class.php',
      'sfImageRectangleImageMagick' => 'image/transforms/ImageMagick/sfImageRectangleImageMagick.class.php',
      'sfImageResizeSimpleImageMagick' => 'image/transforms/ImageMagick/sfImageResizeSimpleImageMagick.class.php',
      'sfImageRotateImageMagick' => 'image/transforms/ImageMagick/sfImageRotateImageMagick.class.php',
      'sfImageScaleImageMagick' => 'image/transforms/ImageMagick/sfImageScaleImageMagick.class.php',
      'sfImageTextImageMagick' => 'image/transforms/ImageMagick/sfImageTextImageMagick.class.php',
      'sfImageTrimImageMagick' => 'image/transforms/ImageMagick/sfImageTrimImageMagick.class.php',
      'sfImageUnsharpMaskImageMagick' => 'image/transforms/ImageMagick/sfImageUnsharpMaskImageMagick.class.php',
      'sfImageTransformAbstract' => 'image/transforms/sfImageTransformAbstract.class.php',
      'sfIp2CountryDriverGeoIp' => 'ip2country/driver/sfIp2CountryDriverGeoIp.class.php',
      'sfIp2Country' => 'ip2country/sfIp2Country.class.php',
      'sfIIp2Country' => 'ip2country/sfIp2Country.interface.php',
      'sfIJsonSerializable' => 'json/sfIJsonSerializable.interface.php',
      'sfJson' => 'json/sfJson.class.php',
      'sfJsonExpression' => 'json/sfJsonExpression.class.php',
      'sfLessCompiler' => 'less/sfLessCompiler.class.php',
      'sfConsoleLogger' => 'log/sfConsoleLogger.class.php',
      'sfEmailLogger' => 'log/sfEmailLogger.class.php',
      'sfFileLogger' => 'log/sfFileLogger.class.php',
      'sfILogger' => 'log/sfILogger.interface.php',
      'sfILoggerAware' => 'log/sfILoggerAware.interface.php',
      'sfLogAnalyzer' => 'log/sfLogAnalyzer.class.php',
      'sfLogAnalyzerMessage' => 'log/sfLogAnalyzerMessage.class.php',
      'sfLogManager' => 'log/sfLogManager.class.php',
      'sfLogger' => 'log/sfLogger.class.php',
      'sfLoggerBase' => 'log/sfLoggerBase.class.php',
      'sfNoLogger' => 'log/sfNoLogger.class.php',
      'sfStreamLogger' => 'log/sfStreamLogger.class.php',
      'sfVarLogger' => 'log/sfVarLogger.class.php',
      'sfWebDebugLogger' => 'log/sfWebDebugLogger.class.php',
      'sfMailerMessage' => 'mailer/message/sfMailerMessage.class.php',
      'sfMailerBlackholePlugin' => 'mailer/plugin/sfMailerBlackholePlugin.class.php',
      'sfMailerHtml2TextPlugin' => 'mailer/plugin/sfMailerHtml2TextPlugin.class.php',
      'sfMailerLoggerPlugin' => 'mailer/plugin/sfMailerLoggerPlugin.class.php',
      'sfMailerNotificationPlugin' => 'mailer/plugin/sfMailerNotificationPlugin.class.php',
      'sfMailerPlugin' => 'mailer/plugin/sfMailerPlugin.class.php',
      'sfMailer' => 'mailer/sfMailer.class.php',
      'sfMailerSpool' => 'mailer/spool/sfMailerSpool.class.php',
      'sfMath' => 'math/sfMath.class.php',
      'sfRounding' => 'math/sfRounding.class.php',
      'myMenu' => 'menu/myMenu.class.php',
      'sfMenu' => 'menu/sfMenu.class.php',
      'sfMinifierDriverCssSimple' => 'minifier/css/sfMinifierDriverCssSimple.class.php',
      'sfMinifierDriverGoogleClosure' => 'minifier/javascript/sfMinifierDriverGoogleClosure.class.php',
      'sfMinifierDriverGoogleClosureApi' => 'minifier/javascript/sfMinifierDriverGoogleClosureApi.class.php',
      'sfMinifierDriverJsMin' => 'minifier/javascript/sfMinifierDriverJsMin.class.php',
      'sfMinifierDriverUglifyApi' => 'minifier/javascript/sfMinifierDriverUglifyApi.class.php',
      'sfIMinifier' => 'minifier/sfIMinifier.interface.php',
      'sfMinifier' => 'minifier/sfMinifier.class.php',
      'sfMinifierDriverDummy' => 'minifier/sfMinifierDriverDummy.class.php',
      'sfMoneyTaxCalculatorDriverCsCoefficient' => 'money/calculator/sfMoneyTaxCalculatorDriverCsCoefficient.class.php',
      'sfMoneyCurrencyCZK' => 'money/currency/sfMoneyCurrencyCZK.class.php',
      'sfMoneyCurrencyEUR' => 'money/currency/sfMoneyCurrencyEUR.class.php',
      'sfIMoneyCurrencyValue' => 'money/sfIMoneyCurrencyValue.interface.php',
      'sfIMoneyTaxCalculator' => 'money/sfIMoneyTaxCalculator.interface.php',
      'sfMoneyCurrency' => 'money/sfMoneyCurrency.class.php',
      'sfMoneyCurrencyConverter' => 'money/sfMoneyCurrencyConverter.class.php',
      'sfMoneyCurrencyValue' => 'money/sfMoneyCurrencyValue.class.php',
      'sfMoneyTaxCalculator' => 'money/sfMoneyTaxCalculator.class.php',
      'sfPearConfig' => 'plugin/pear/sfPearConfig.class.php',
      'sfPearDownloader' => 'plugin/pear/sfPearDownloader.class.php',
      'sfPearEnvironment' => 'plugin/pear/sfPearEnvironment.class.php',
      'sfPearFrontendPlugin' => 'plugin/pear/sfPearFrontendPlugin.class.php',
      'sfPearPackager' => 'plugin/pear/sfPearPackager.class.php',
      'sfPearPluginManager' => 'plugin/pear/sfPearPluginManager.class.php',
      'sfPearRest' => 'plugin/pear/sfPearRest.class.php',
      'sfPearRest10' => 'plugin/pear/sfPearRest10.class.php',
      'sfPearRest11' => 'plugin/pear/sfPearRest11.class.php',
      'sfPearRest13' => 'plugin/pear/sfPearRest13.class.php',
      'sfPearRestPlugin' => 'plugin/pear/sfPearRestPlugin.class.php',
      'sfIPluginInstaller' => 'plugin/sfIPluginInstaller.interface.php',
      'sfPluginInstaller' => 'plugin/sfPluginInstaller.class.php',
      'sfPluginManager' => 'plugin/sfPluginManager.class.php',
      'sfApplication' => 'project/sfApplication.class.php',
      'sfGenericApplication' => 'project/sfGenericApplication.class.php',
      'sfGenericPlugin' => 'project/sfGenericPlugin.class.php',
      'sfGenericProject' => 'project/sfGenericProject.class.php',
      'sfPlugin' => 'project/sfPlugin.class.php',
      'sfProject' => 'project/sfProject.class.php',
      'sfIRequest' => 'request/sfIRequest.interface.php',
      'sfRequest' => 'request/sfRequest.class.php',
      'sfRequestFiltersHolder' => 'request/sfRequestFiltersHolder.class.php',
      'sfUploadedFile' => 'request/sfUploadedFile.class.php',
      'sfWebRequest' => 'request/sfWebRequest.class.php',
      'sfAjaxResult' => 'response/sfAjaxResult.class.php',
      'sfHttpDownload' => 'response/sfHttpDownload.class.php',
      'sfIResponse' => 'response/sfIResponse.interface.php',
      'sfIResponseAware' => 'response/sfIResponseAware.interface.php',
      'sfResponse' => 'response/sfResponse.class.php',
      'sfWebResponse' => 'response/sfWebResponse.class.php',
      'sfInternalRoute' => 'routing/sfInternalRoute.class.php',
      'sfRouting' => 'routing/sfRouting.class.php',
      'sfISearchQueryBuilder' => 'search/builder/sfISearchQueryBuilder.interface.php',
      'sfSearchQueryBuilder' => 'search/builder/sfSearchQueryBuilder.class.php',
      'sfSearchQueryBuilderMysqlFulltext' => 'search/builder/sfSearchQueryBuilderMysqlFulltext.class.php',
      'sfSearchQueryBuilderPgsqlFulltext' => 'search/builder/sfSearchQueryBuilderPgsqlFulltext.class.php',
      'sfISearchQueryLexer' => 'search/parser/sfISearchQueryLexer.interface.php',
      'sfISearchQueryParser' => 'search/parser/sfISearchQueryParser.interface.php',
      'sfSearchQueryExpression' => 'search/parser/sfSearchQueryExpression.class.php',
      'sfSearchQueryLexer' => 'search/parser/sfSearchQueryLexer.class.php',
      'sfSearchQueryParser' => 'search/parser/sfSearchQueryParser.class.php',
      'sfSearchQueryPhrase' => 'search/parser/sfSearchQueryPhrase.class.php',
      'sfSearchQueryToken' => 'search/parser/sfSearchQueryToken.class.php',
      'sfCrypt' => 'security/sfCrypt.class.php',
      'sfHtmlPurifier' => 'security/sfHtmlPurifier.class.php',
      'sfInputFilters' => 'security/sfInputFilters.class.php',
      'sfSanitizer' => 'security/sfSanitizer.class.php',
      'sfSecurity' => 'security/sfSecurity.class.php',
      'sfSecurityCheckResult' => 'security/sfSecurityCheckResult.class.php',
      'sfIServiceContainerAware' => 'service/sfISerfviceContainerAware.interface.php',
      'sfIService' => 'service/sfIService.interface.php',
      'sfServiceContainer' => 'service/sfServiceContainer.class.php',
      'sfServiceDefinition' => 'service/sfServiceDefinition.class.php',
      'sfServiceReference' => 'service/sfServiceReference.php',
      'sfIStorage' => 'storage/sfIStorage.interface.php',
      'sfIStorageAware' => 'storage/sfIStorageAware.interface.php',
      'sfNoStorage' => 'storage/sfNoStorage.class.php',
      'sfPDOSessionStorage' => 'storage/sfPDOSessionStorage.class.php',
      'sfSessionStorage' => 'storage/sfSessionStorage.class.php',
      'sfSessionTestStorage' => 'storage/sfSessionTestStorage.class.php',
      'sfStorage' => 'storage/sfStorage.class.php',
      'sfTestBrowser' => 'test/sfTestBrowser.class.php',
      'sfTestFunctional' => 'test/sfTestFunctional.class.php',
      'sfTestFunctionalBase' => 'test/sfTestFunctionalBase.class.php',
      'sfTester' => 'test/sfTester.class.php',
      'sfTesterForm' => 'test/sfTesterForm.class.php',
      'sfTesterMailer' => 'test/sfTesterMailer.class.php',
      'sfTesterRequest' => 'test/sfTesterRequest.class.php',
      'sfTesterResponse' => 'test/sfTesterResponse.class.php',
      'sfTesterUser' => 'test/sfTesterUser.class.php',
      'sfTesterViewCache' => 'test/sfTesterViewCache.class.php',
      'sfRichTextEditorDriverCKEditor' => 'text/editor/driver/sfRichTextEditorDriverCKEditor.class.php',
      'sfIRichTextEditor' => 'text/editor/sfIRichTextEditor.interface.php',
      'sfRichTextEditor' => 'text/editor/sfRichTextEditor.class.php',
      'sfITextFilter' => 'text/filter/sfITextFilter.interface.php',
      'sfMacroTextFilter' => 'text/filter/sfMacroTextFilter.class.php',
      'sfMarkdownTextFilter' => 'text/filter/sfMarkdownTextFilter.class.php',
      'sfTextFilter' => 'text/filter/sfTextFilter.class.php',
      'sfTextFilterCallbackDefinition' => 'text/filter/sfTextFilterCallbackDefinition.class.php',
      'sfTextFilterContent' => 'text/filter/sfTextFilterContent.class.php',
      'sfTextFilterRegistry' => 'text/filter/sfTextFilterRegistry.class.php',
      'sfITextMacroFilter' => 'text/macro/sfITextMacroFilter.interface.php',
      'sfITextMacroWidget' => 'text/macro/sfITextMacroWidget.interface.php',
      'sfTextMacroCallbackDefinition' => 'text/macro/sfTextMacroCallbackDefinition.class.php',
      'sfTextMacroRegistry' => 'text/macro/sfTextMacroRegistry.class.php',
      'sfTextMacroWidget' => 'text/macro/sfTextMacroWidget.class.php',
      'myText' => 'text/myText.class.php',
      'sfHtml2Text' => 'text/sfHtml2Text.class.php',
      'sfMarkdownParser' => 'text/sfMarkdownParser.class.php',
      'sfPlainTextTable' => 'text/sfPlainTextTable.php',
      'sfText' => 'text/sfText.class.php',
      'sfTypography' => 'text/sfTypography.class.php',
      'sfWordHtmlCleaner' => 'text/sfWordHtmlCleaner.class.php',
      'sfBasicSecurityUser' => 'user/sfBasicSecurityUser.class.php',
      'sfISecurityUser' => 'user/sfISecurityUser.interface.php',
      'sfIUser' => 'user/sfIUser.interface.php',
      'sfIUserAware' => 'user/sfIUserAware.interface.php',
      'sfUser' => 'user/sfUser.class.php',
      'sfUserAgentDetector' => 'user/sfUserAgentDetector.class.php',
      'sfUserFlashMessage' => 'user/sfUserFlashMessage.class.php',
      'sfUtf8' => 'utf8/sfUtf8.class.php',
      'sfDataUri' => 'util/data_uri/sfDataUri.class.php',
      'sfIDataUriConvertable' => 'util/data_uri/sfIDataUriConvertable.interface.php',
      'sfArray' => 'util/sfArray.class.php',
      'sfAssetPackage' => 'util/sfAssetPackage.class.php',
      'sfBitwise' => 'util/sfBitwise.class.php',
      'sfBrowser' => 'util/sfBrowser.class.php',
      'sfBrowserBase' => 'util/sfBrowserBase.class.php',
      'sfCallable' => 'util/sfCallable.class.php',
      'sfCallbackDefinition' => 'util/sfCallbackDefinition.class.php',
      'sfClassManipulator' => 'util/sfClassManipulator.class.php',
      'sfContext' => 'util/sfContext.class.php',
      'sfDomCssSelector' => 'util/sfDomCssSelector.class.php',
      'sfFinder' => 'util/sfFinder.class.php',
      'sfFlatParameterHolder' => 'util/sfFlatParameterHolder.class.php',
      'sfGlob' => 'util/sfGlob.class.php',
      'sfGlobToRegex' => 'util/sfGlobToRegex.class.php',
      'sfHtml' => 'util/sfHtml.class.php',
      'sfIArrayAccessByReference' => 'util/sfIArrayAccessByReference.interface.php',
      'sfInflector' => 'util/sfInflector.class.php',
      'sfIntegerEncoder' => 'util/sfIntegerEncoder.class.php',
      'sfLimitedScope' => 'util/sfLimitedScope.class.php',
      'sfMimeType' => 'util/sfMimeType.class.php',
      'sfNumberCompare' => 'util/sfNumberCompare.class.php',
      'sfObjectCallbackDefinition' => 'util/sfObjectCallbackDefinition.class.php',
      'sfParameterHolder' => 'util/sfParameterHolder.class.php',
      'sfPasswordTools' => 'util/sfPasswordTools.class.php',
      'sfPhpExpression' => 'util/sfPhpExpression.class.php',
      'sfPrefetchBrowser' => 'util/sfPrefetchBrowser.class.php',
      'sfReflectionClass' => 'util/sfReflectionClass.class.php',
      'sfSafeUrl' => 'util/sfSafeUrl.class.php',
      'sfShutdownScheduler' => 'util/sfShutdownScheduler.class.php',
      'sfToolkit' => 'util/sfToolkit.class.php',
      'sfUuid' => 'util/sfUuid.class.php',
      'sfFileSafeStreamWrapper' => 'util/stream/sfFileSafeStream.class.php',
      'sfIStreamWrapper' => 'util/stream/sfIStreamWrapper.interface.php',
      'sfStreamWrapper' => 'util/stream/sfStreamWrapper.class.php',
      'sfStringStreamWrapper' => 'util/stream/sfStringStreamWrapper.class.php',
      'sfValidatorBlacklist' => 'validator/blacklist/sfValidatorBlacklist.class.php',
      'sfValidatorBlacklistRegex' => 'validator/blacklist/sfValidatorBlacklistRegex.class.php',
      'sfValidatorChoice' => 'validator/choice/sfValidatorChoice.class.php',
      'sfValidatorChoiceMany' => 'validator/choice/sfValidatorChoiceMany.class.php',
      'sfValidatorTrilean' => 'validator/choice/sfValidatorTrilean.class.php',
      'sfValidatorDate' => 'validator/date/sfValidatorDate.class.php',
      'sfValidatorDateRange' => 'validator/date/sfValidatorDateRange.class.php',
      'sfValidatorDateTime' => 'validator/date/sfValidatorDateTime.class.php',
      'sfValidatorDateTimeRange' => 'validator/date/sfValidatorDateTimeRange.class.php',
      'sfValidatorSchemaTimeInterval' => 'validator/date/sfValidatorSchemaTimeInterval.class.php',
      'sfValidatorTime' => 'validator/date/sfValidatorTime.class.php',
      'sfValidatorFile' => 'validator/file/sfValidatorFile.class.php',
      'sfValidatorImage' => 'validator/file/sfValidatorImage.class.php',
      'sfValidatorI18nAggregate' => 'validator/i18n/sfValidatorI18nAggregate.class.php',
      'sfValidatorI18nChoiceCountry' => 'validator/i18n/sfValidatorI18nChoiceCountry.class.php',
      'sfValidatorI18nChoiceCurrency' => 'validator/i18n/sfValidatorI18nChoiceCurrency.class.php',
      'sfValidatorI18nChoiceEnabledLanguages' => 'validator/i18n/sfValidatorI18nChoiceEnabledLanguages.class.php',
      'sfValidatorI18nChoiceLanguage' => 'validator/i18n/sfValidatorI18nChoiceLanguage.class.php',
      'sfValidatorI18nChoiceTimezone' => 'validator/i18n/sfValidatorI18nChoiceTimezone.class.php',
      'sfValidatorI18nNumber' => 'validator/i18n/sfValidatorI18nNumber.php',
      'sfValidatorBirthNumber' => 'validator/number/sfValidatorBirthNumber.class.php',
      'sfValidatorCompanyIn' => 'validator/number/sfValidatorCompanyIn.class.php',
      'sfValidatorCompanyInDriverAres' => 'validator/number/sfValidatorCompanyInDriverAres.class.php',
      'sfValidatorInteger' => 'validator/number/sfValidatorInteger.class.php',
      'sfValidatorIpAddress' => 'validator/number/sfValidatorIpAddress.class.php',
      'sfValidatorNumber' => 'validator/number/sfValidatorNumber.class.php',
      'sfValidatorPrice' => 'validator/number/sfValidatorPrice.class.php',
      'sfValidatorAnd' => 'validator/other/sfValidatorAnd.class.php',
      'sfValidatorBoolean' => 'validator/other/sfValidatorBoolean.class.php',
      'sfValidatorCSRFToken' => 'validator/other/sfValidatorCSRFToken.class.php',
      'sfValidatorCallback' => 'validator/other/sfValidatorCallback.class.php',
      'sfValidatorClass' => 'validator/other/sfValidatorClass.class.php',
      'sfValidatorCssClassName' => 'validator/other/sfValidatorCssClassName.class.php',
      'sfValidatorDefault' => 'validator/other/sfValidatorDefault.class.php',
      'sfValidatorFDTokenLeftBracket' => 'validator/other/sfValidatorFromDescription.class.php',
      'sfValidatorFDTokenRightBracket' => 'validator/other/sfValidatorFromDescription.class.php',
      'sfValidatorFDTokenOperator' => 'validator/other/sfValidatorFromDescription.class.php',
      'sfValidatorFromDescription' => 'validator/other/sfValidatorFromDescription.class.php',
      'sfValidatorFDTokenFilter' => 'validator/other/sfValidatorFromDescription.class.php',
      'sfValidatorFDToken' => 'validator/other/sfValidatorFromDescription.class.php',
      'sfValidatorOr' => 'validator/other/sfValidatorOr.class.php',
      'sfValidatorPass' => 'validator/other/sfValidatorPass.class.php',
      'sfValidatorPhoneNumber' => 'validator/other/sfValidatorPhoneNumber.class.php',
      'sfValidatorRegex' => 'validator/other/sfValidatorRegex.class.php',
      'sfValidatorSkype' => 'validator/other/sfValidatorSkype.class.php',
      'sfValidatorUrl' => 'validator/other/sfValidatorUrl.class.php',
      'sfValidatorZip' => 'validator/other/sfValidatorZip.class.php',
      'sfValidatorSchema' => 'validator/schema/sfValidatorSchema.class.php',
      'sfValidatorSchemaCompare' => 'validator/schema/sfValidatorSchemaCompare.class.php',
      'sfValidatorSchemaFilter' => 'validator/schema/sfValidatorSchemaFilter.class.php',
      'sfValidatorSchemaForEach' => 'validator/schema/sfValidatorSchemaForEach.class.php',
      'sfValidatormMetaTitleMode' => 'validator/seo/sfValidatorMetaTitleModeChoice.class.php',
      'sfValidatorBase' => 'validator/sfValidatorBase.class.php',
      'sfValidatorDecorator' => 'validator/sfValidatorDecorator.class.php',
      'sfValidatorError' => 'validator/sfValidatorError.class.php',
      'sfValidatorErrorSchema' => 'validator/sfValidatorErrorSchema.class.php',
      'sfValidatorTools' => 'validator/sfValidatorTools.class.php',
      'sfValidatorReCaptcha' => 'validator/spam_protect/sfValidatorReCaptcha.class.php',
      'sfValidatorSpamProtectTimer' => 'validator/spam_protect/sfValidatorSpamProtectTimer.class.php',
      'sfValidatorCompanyVat' => 'validator/text/sfValidatorCompanyVat.class.php',
      'sfValidatorEmail' => 'validator/text/sfValidatorEmail.class.php',
      'sfValidatorFirstName' => 'validator/text/sfValidatorFirstName.class.php',
      'sfValidatorHtml' => 'validator/text/sfValidatorHtml.class.php',
      'sfValidatorLastName' => 'validator/text/sfValidatorLastName.class.php',
      'sfValidatorPassword' => 'validator/text/sfValidatorPassword.class.php',
      'sfValidatorPhone' => 'validator/text/sfValidatorPhone.class.php',
      'sfValidatorSeparatedTextValues' => 'validator/text/sfValidatorSeparatedTextValues.class.php',
      'sfValidatorSlug' => 'validator/text/sfValidatorSlug.class.php',
      'sfValidatorString' => 'validator/text/sfValidatorString.class.php',
      'sfValidatorYaml' => 'validator/text/sfValidatorYaml.class.php',
      'sfOutputEscaper' => 'view/escaper/sfOutputEscaper.class.php',
      'sfOutputEscaperArrayDecorator' => 'view/escaper/sfOutputEscaperArrayDecorator.class.php',
      'sfOutputEscaperGetterDecorator' => 'view/escaper/sfOutputEscaperGetterDecorator.class.php',
      'sfOutputEscaperIteratorDecorator' => 'view/escaper/sfOutputEscaperIteratorDecorator.class.php',
      'sfOutputEscaperObjectDecorator' => 'view/escaper/sfOutputEscaperObjectDecorator.class.php',
      'sfOutputEscaperSafe' => 'view/escaper/sfOutputEscaperSafe.class.php',
      'sfJavascriptTemplateCompilerDriverHandlebars' => 'view/javascript_template/sfJavascriptTemplateCompilerDriverHandlebars.class.php',
      'sfIJavascriptTemplateCompiler' => 'view/sfIJavascriptTemplateCompiler.interface.php',
      'sfIPartialView' => 'view/sfIPartialView.interface.php',
      'sfIView' => 'view/sfIView.interface.php',
      'sfJavascriptPartialView' => 'view/sfJavascriptPartialView.class.php',
      'sfJavascriptTemplateCompiler' => 'view/sfJavascriptTemplateCompiler.class.php',
      'sfJavascriptView' => 'view/sfJavascriptView.class.php',
      'sfPHPView' => 'view/sfPHPView.class.php',
      'sfPartialMailView' => 'view/sfPartialMailView.class.php',
      'sfPartialView' => 'view/sfPartialView.class.php',
      'sfView' => 'view/sfView.class.php',
      'sfViewCacheManager' => 'view/sfViewCacheManager.class.php',
      'sfXmlElement' => 'xml/sfXmlElement.class.php',
      'sfYaml' => 'yaml/sfYaml.class.php',
      'sfYamlDumper' => 'yaml/sfYamlDumper.class.php',
      'sfYamlInline' => 'yaml/sfYamlInline.class.php',
      'sfYamlParser' => 'yaml/sfYamlParser.class.php',
  );

}
