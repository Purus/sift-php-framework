<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 *
 * @package    Sift
 * @subpackage view
 */
class sfJavascriptTemplateCompilerDriverHandlebars extends sfJavascriptTemplateCompiler {

  /**
   * Array of default options
   */
  protected $defaultOptions = array(
    'handlebars_bin' => '/usr/bin/handlebars'
  );

  /**
   * Setup the compiler
   *
   * @throws InvalidArgumentException
   */
  public function setup()
  {
    if(!sfToolkit::isCallable('exec'))
    {
      throw new InvalidArgumentException('Php cannot execute command line scripts. Enable "exec" in your php.ini.');
    }
  }

  /**
   * Compiles the
   *
   * @param array $options
   */
  public function compile($string, $options = array())
  {
    // create temporary file
    $tmpInFile = tempnam(sys_get_temp_dir(), 'template_javascript');
    file_put_contents($tmpInFile, $string);

    $tmpOutFile = sys_get_temp_dir().uniqid('template_javascript.js');

    // precompile the template
    $handlebarsCmd = sprintf('%s %s -f %s --simple',
            $this->getOption('handlebars_bin'),
            escapeshellarg($tmpInFile),
            escapeshellarg($tmpOutFile));

    @exec($handlebarsCmd, $output, $return_var);

    // cleanup
    unlink($tmpInFile);

    // something went wrong
    if($return_var)
    {
      throw new RuntimeException('Compilation of the template failed.');
    }

    $compiledJs = file_get_contents($tmpOutFile);

    // cleanup
    unlink($tmpOutFile);

    return $compiledJs;
  }

}
