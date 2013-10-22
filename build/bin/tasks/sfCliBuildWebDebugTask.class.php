<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds web debug assets
 *
 * @package Sift
 * @subpackage build
 */
class sfCliBuildWebDebugTask extends sfCliBaseBuildTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = '';
    $this->name = 'web-debug';
    $this->briefDescription = 'Builds web debug, dumper and exception assets';

    $this->detailedDescription = <<<EOF
The [web-debug|INFO] task builds web debug assets

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->build();
  }

  /**
   * Builds all
   *
   */
  protected function build()
  {
    $this->buildIcons();
    $this->buildDump();
    $this->buildException();
    $this->buildDebug();
  }

  /**
   * Builds icons
   *
   */
  protected function buildIcons()
  {
    $this->logSection($this->getFullName(), 'Building icons...');

    $iconClassFile = $this->environment->get('sf_sift_lib_dir') . '/debug/sfWebDebugIcon.class.php';
    $sourceDir = realpath($this->environment->get('build_data_dir') . '/web_debug/images/icons');
    $files = sfFinder::type('file')->name('/\.png$/')->in($sourceDir);

    $icons = array();
    foreach($files as $file)
    {
      $icon = new sfDataUri('image/png');
      $icon->setData(file_get_contents($file), sfDataUri::ENCODING_BASE64);
      $icons[sfFilesystem::getFilename(basename($file))] = $icon->toString();
    }

    asort($icons);

    $php = '';
    foreach($icons as $name => $src)
    {
      $php .= sprintf("      '%s' => '%s',\n", strtolower($name), $src);
    }

    $content = preg_replace('/protected static \$icons = array *\(.*?\);/s', sprintf("protected static \$icons = array(\n%s  );", $php), file_get_contents($iconClassFile));
    file_put_contents($iconClassFile, $content);
  }

  protected function buildDebug()
  {
    $filesystem = $this->getFilesystem();

    $targetDir = realpath($this->environment->get('sf_sift_data_dir') . '/web_debug');
    $sourceDir = realpath($this->environment->get('build_data_dir') . '/web_debug');

    $files = sfFinder::type('file')->name('web_debug.min.js', 'web_debug.min.css')->in($targetDir);
    $filesystem->remove($files);

    $tmp = sys_get_temp_dir() . '/web_debug';
    mkdir($tmp);

    // copy all to temp directory
    $filesystem->mirror($sourceDir, $tmp, sfFinder::type('any'));

    $logo = new sfDataUri('image/png', file_get_contents($sourceDir.'/images/logo.png'), sfDataUri::ENCODING_BASE64);
    $loader = new sfDataUri('image/gif', file_get_contents($sourceDir.'/images/loader.gif'), sfDataUri::ENCODING_BASE64);
    $toolbar = new sfDataUri('image/gif', file_get_contents($sourceDir.'/images/toolbar.gif'), sfDataUri::ENCODING_BASE64);

    $this->getFilesystem()->replaceTokens(sfFinder::type('file')->in($tmp), '//~//', '//~//', array(
      'BASE64' => file_get_contents($sourceDir . '/js/base64.js'),
      'COOKIE' => file_get_contents($sourceDir . '/js/cookie.js'),
      'LOCAL_STORAGE' => file_get_contents($sourceDir . '/js/local_storage.js'),
      'HIGHLIGHTER' => file_get_contents($sourceDir . '/js/highlighter.js'),
      'LOGO_SRC' => $logo->toString(),
      'LOADER_SRC' => $loader->toString(),
      'TOOLBAR_SRC' => $toolbar->toString(),
    ));

    $compiler = new sfLessCompiler(new sfEventDispatcher(), array(
      'cache_dir' => sys_get_temp_dir(),
      'web_cache_dir' => sys_get_temp_dir()
    ));

    $compiler->addImportDir($tmp);
    $compiler->addImportDir($tmp . '/less');

    $compiled = $compiler->compile(file_get_contents($tmp . '/web_debug.less'));
    file_put_contents($tmp. '/web_debug.min.css', $compiled);

    passthru(sprintf('compiler --js %s/web_debug.js > %s/web_debug.min.js', $tmp, $targetDir));

    $filesystem->mirror($tmp, $targetDir,
        sfFinder::type('file')->name(
            'web_debug.min.js',
            'web_debug.min.css'
        )
    );

    sfToolkit::clearDirectory($tmp);
    rmdir($tmp);
  }

  protected function buildException()
  {
    $exceptionTargetDir = realpath($this->environment->get('sf_sift_data_dir') . '/web_debug/exception');
    $sourceDir = realpath($this->environment->get('build_data_dir') . '/web_debug/exception');

    $compiler = new sfLessCompiler(new sfEventDispatcher(), array(
      'cache_dir' => sys_get_temp_dir(),
      'web_cache_dir' => sys_get_temp_dir()
    ));

    $compiler->addImportDir($sourceDir. '/../less');

    $compiled = $compiler->compile(file_get_contents($sourceDir . '/exception.less'));
    file_put_contents($exceptionTargetDir. '/exception.min.css', $compiled);

    passthru(sprintf('compiler --js %s/exception.js > %s/exception.min.js', $sourceDir, $exceptionTargetDir));
  }

  /**
   * Builds assets for sfDebugDumper::dump()
   *
   */
  protected function buildDump()
  {
    $this->logSection($this->getFullName(), 'Building dump...');

    $sourceDir = realpath($this->environment->get('build_data_dir') . '/web_debug/dump');

    $compiler = new sfLessCompiler(new sfEventDispatcher(), array(
      'cache_dir' => sys_get_temp_dir(),
      'web_cache_dir' => sys_get_temp_dir()
    ));

    $targetDir = realpath($this->environment->get('sf_sift_data_dir') . '/web_debug/dump');

    $compiler->addImportDir($sourceDir);
    $compiler->addImportDir($sourceDir . '/../less');

    $compiled = $compiler->compile(file_get_contents($sourceDir . '/dump.less'));
    file_put_contents($targetDir. '/dump.min.css', $compiled);

    $jsFile = $sourceDir . '/dump.js';
    $jsTargetFile = $targetDir . '/dump.min.js';

    passthru(sprintf('compiler --js %s > %s', $jsFile, $jsTargetFile));
  }

}
