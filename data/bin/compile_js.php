<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Compiles javascript using Google closure compiler into package
 *
 * Usage: php compile_js globalize
 *
 * @package    Sift
 * @subpackage cli
 */

$siftDataDir = realpath(dirname(__FILE__) . '/../');
$jsDataDir   = realpath($siftDataDir . '/web/sf/js');
$siftLibDir  = realpath(dirname(__FILE__) . '/../../lib');

require_once $siftLibDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

if(!isset($argv[1]))
{
  usage();
  exit;
}

$what = $argv[1];
$compiler = new sfJsCompilerCommand();
$method = sprintf('compile%s', $what);

$options = array(
  'js_data_dir' => $jsDataDir,
  'sift_data_dir' => $siftDataDir
);

if(!method_exists($compiler, $method))
{
  echo sprintf('Invalid package "%s" given.' . "\n", $what);

  $valid = array();
  foreach(get_class_methods($compiler) as $method)
  {
    if(preg_match('/^compile/', $method))
    {
      $valid[] = preg_replace('/^(compile)+/', '', $method);
    }
  }

  echo "Valid packages: \n";

  foreach($valid as $valid)
  {
    echo sprintf("  %s\n", $valid);
  }

  echo "\n";

  exit;
}

echo sprintf("Compiling %s\n", $what);

class sfJsCompilerCommand {

  protected function callCompiler($command)
  {
    passthru(sprintf('compiler %s', ($command)));
  }

  public function compileCore($options = array())
  {
    $command = array();

//    $files = sfFinder::type('file')
//              ->name('*.js')
//              ->maxdepth(0)
//              ->discard('*.min.js')
//              ->in($options['js_data_dir'] . '/core');

    $files = array(
       'config.js',
        'cookie.js',
        'plugins.js',
        'application.js',
        'api.js',
        'forms.js',
        'bootstrap.js',
        'i18n.js',
        'globalize.js',
        'logger.js',
        'tools.js'
    );


    $tmpFile = tempnam(sys_get_temp_dir(), 'core_compile');

    $fileContents = array();
    foreach($files as $file)
    {
      $fileContents[] = file_get_contents($options['js_data_dir']  . '/core/'.$file);
    }

    file_put_contents($tmpFile, join("\n\n", $fileContents));

    $command[] = sprintf('--js %s', $tmpFile);

    // output file
    $command[] = sprintf('> %s', $options['js_data_dir'] . '/core/core.min.js');
    $command = join(' ', $command);
    $this->callCompiler($command);

    // we need to compile translations too
    $source = sfI18nMessageSource::factory('gettext', $options['sift_data_dir'] . '/i18n/catalogues');

    foreach(array('cs_CZ', 'sk_SK', 'en_GB', 'de_DE') as $culture)
    {
      // we are building gettext -> json format
      $source->setCulture($culture);
      $source->load('enhanced_form');

      $_messages = $source->read();

      if(count($_messages))
      {
        $_messages = current($_messages);
      }

      $messages = array();
      foreach($_messages as $original => $translatedProperties)
      {
        $messages[$original] = $translatedProperties[0];
      }

      $i18n = $this->getI18n($culture, $options);
      // FIXME: implement
      $baseCulture = 'default';

      $content = sprintf(
'/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is automatically generated by a build task for culture "%s".
 * Do not modify it manually. See the compiler script for more information.
 *
 * @link https://bitbucket.org/mishal/sift-php-framework/wiki/Javascript
 */

// add translation strings
%s

// add culture information information
Globalize.addCultureInfo(\'%s\', \'%s\', %s);

', $culture, (count($messages) ? sprintf('I18n.addTranslation(%s);', sfJson::encode($messages)) : '// nothing'),
        $baseCulture, $culture, sfJson::encode($i18n));

    $cultureFile = sprintf($options['js_data_dir'].'/core/i18n/%s.js', $culture);
    $cultureMinFile = sprintf($options['js_data_dir'].'/core/i18n/%s.min.js', $culture);

    file_put_contents($cultureFile, $content);

    $command = array();
    $command[] = sprintf('--js %s', $cultureFile);
    $command[] = sprintf('> %s', $cultureMinFile);
    $command = join(' ', $command);
    $this->callCompiler($command);
    }
  }

  protected function getI18n($culture, $options)
  {
    // FIXME: HACK to make it work!
    sfConfig::set('sf_sift_data_dir', $options['sift_data_dir']);
    $export = sfCultureExport::factory('globalize', $culture);
    return $export->export();
  }

  public function compileGlobalize($options = array())
  {
    $command = array();

    $files = sfFinder::type('file')
              ->name('*.js')
              ->discard('*.min.js')
              ->in($options['js_data_dir'] . '/globalize');

    $files = array_reverse($files);

    foreach($files as $file)
    {
      $command[] = sprintf('--js %s', $file);
    }

    // $command[] = '--compilation_level WHITESPACE_ONLY';
    // SIMPLE_OPTIMIZATIONS | ADVANCED_OPTIMIZATIONS]

    // $command[] = sprintf('--js_output_file %s', escapeshellarg($options['js_data_dir'] . '/globalize/globalize.min.js'));
    // output file
    $command[] = sprintf('> %s', $options['js_data_dir'] . '/globalize/globalize.min.js');

    $command = join(' ', $command);

    $this->callCompiler($command);
  }

  public function compileBootstrap($options = array())
  {
    $command = array();

    $files = array(
      'bootstrap-transition.js',
      'bootstrap-alert.js',
      'bootstrap-button.js',
      'bootstrap-carousel.js',
      'bootstrap-collapse.js',
      'bootstrap-dropdown.js',
      'bootstrap-modal.js',
      'bootstrap-tooltip.js',
      'bootstrap-popover.js',
      'bootstrap-scrollspy.js',
      'bootstrap-tab.js',
      'bootstrap-typeahead.js',
      'bootstrap-inputmask.js',
      'bootstrap-rowlink.js',
      'bootstrap-fileupload.js',
      'bootstrap-affix.js'
    );

//    $files = sfFinder::type('file')
//              ->name($files)
//              ->in($options['js_data_dir'] . '/bootstrap');
//
    $tmpFile = tempnam(sys_get_temp_dir(), 'btstrp_compile');

    $fileContents = array();
    foreach($files as $file)
    {
      $fileContents[] = file_get_contents($options['js_data_dir'] . '/bootstrap/' . $file);
    }

    file_put_contents($tmpFile, join("\n\n", $fileContents));

    $command[] = sprintf('--js %s', $tmpFile);

    // $command[] = '--compilation_level WHITESPACE_ONLY';
    // SIMPLE_OPTIMIZATIONS | ADVANCED_OPTIMIZATIONS]

    // $command[] = sprintf('--js_output_file %s', escapeshellarg($options['js_data_dir'] . '/globalize/globalize.min.js'));
    // output file
    $command[] = sprintf('> %s', $options['js_data_dir'] . '/bootstrap/bootstrap.min.js');

    $command = join(' ', $command);

    $this->callCompiler($command);



  }


}

$compiler->$method($options);

echo "Done.\n";

function usage()
{
  echo sprintf("%s what\n", basename($_SERVER['SCRIPT_NAME']));
  echo "\n";
}