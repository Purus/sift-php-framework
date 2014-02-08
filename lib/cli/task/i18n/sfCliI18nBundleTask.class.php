<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base task for i18n tasks
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliI18nBundleTask extends sfCliI18nBaseTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    parent::configure();

    $this->addArguments(array());

    $this->addOptions(array());

    $this->namespace = 'i18n';
    $this->name = 'bundle';
    $this->briefDescription = 'Bundles the translations to be sent to translators';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [i18n:bundle|INFO] task bundles the i18n catalogues to a bundle:

  [{$scriptName} i18n:bundle frontend|INFO]


  [{$scriptName} i18n:bundle myFooPlugin|INFO]

EOF;
  }

  /**
   * @see sfCliTask
   */
  public function execute($arguments = array(), $options = array())
  {
    list($application, $dir, $isPlugin) = $this->getApplicationOrPlugin($arguments['app']);

    if($isPlugin)
    {
      $this->logSection($this->getFullName(), sprintf('Preparing catalogues for plugin "%s"', $application));
    }
    else
    {
      $this->logSection($this->getFullName(), sprintf('Preparing catalogues for "%s"', $application));
    }

    $moduleNames = sfFinder::type('dir')->maxDepth(0)->ignoreVersionControl()->relative()->in(
      $dir . '/' . $this->environment->get('sf_app_module_dir_name')
    );

    $culture = $arguments['culture'];
    $filesystem = $this->getFileSystem();
    $tmpDir = sys_get_temp_dir() . '/i18n_bundle';
    $filesystem->mkdirs($tmpDir);

    $moduleDirName = $this->environment->get('sf_app_module_dir_name');
    $i18nDirName = $this->environment->get('sf_app_i18n_dir_name');

    // loop each module
    foreach($moduleNames as $module)
    {
      $i18nDir = $dir . '/' . $moduleDirName . '/' . $module . '/' . $i18nDirName . '/' . $culture;
      $filesystem->mirror($i18nDir, $tmpDir . '/' . $moduleDirName . '/' . $module . '/' . $i18nDirName .'/' . $culture,
              new sfFinder());
    }

    // find i18n
    $filesystem->mirror($dir . '/' . $i18nDirName . '/' . $culture,
            $tmpDir . '/' . $i18nDirName . '/' . $culture, new sfFinder());

    $zip = sprintf('%s/%s_%s.zip', getcwd(),
            $application, $culture);

    if(is_readable($zip))
    {
      $filesystem->remove($zip);
    }

    // create a zip archive
    $this->createArchiveFromDirectory($tmpDir, $zip, $application);

    sfToolkit::clearDirectory($tmpDir);
    $filesystem->remove($tmpDir);

    $this->logSection($this->getFullName(), 'Done.');
  }

}