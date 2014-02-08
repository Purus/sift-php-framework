<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Javascript extractor extracts messages from javascript files. Based on the class
 * from jsgettext project.
 *
 * @package    Sift
 * @subpackage i18n_extract
 * @link       http://code.google.com/p/jsgettext/
 */
class sfI18nJavascriptExtractor extends sfConfigurable implements sfII18nExtractor
{
  /**
   * Regular expression holder
   *
   * @var array
   */
  protected $regs = array();

  /**
   * Expression counter
   * @var int
   */
  protected $regsCounter = 0;

  /**
   * Extracted string
   *
   * @var array
   */
  protected $strings = array();

  /**
   * String counter
   *
   * @var int
   */
  protected $stringsCounter = 0;

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'functions' => array(
      '__'
    )
  );

  /**
   * Extracts i18n strings.
   *
   * @param string $content Content of the file
   */
  public function extract($content)
  {
    return $this->findMessages($this->getOption('functions'), $content);
  }

  /**
   * Resets the state of the extractor
   *
   */
  protected function reset()
  {
    $this->strings = array();
    $this->regsCounter = 0;
    $this->stringsCounter = 0;
  }

  /**
   * Returns array of extracted messages
   *
   * @param array $functions Array of functions
   * @param string $content Content to search for
   */
  protected function findMessages($functions, $content)
  {
    // reset the
    $this->reset();

    $content = htmlspecialchars($content, ENT_NOQUOTES);

    // extract reg exps
    $content = preg_replace_callback(
      '# ( / (?: (?>[^/\\\\]++) | \\\\\\\\ | (?<!\\\\)\\\\(?!\\\\) | \\\\/ )+ (?<!\\\\)/ ) [a-z]* \b #ix', array($this, 'extractRegs'), $content
    );

    // handle special cases
    // special cases
    $content = str_replace('\'"\'', '\'\'\'', $content);

    // extract strings
    $content = preg_replace_callback(
      array(
      '# " ( (?: (?>[^"\\\\]++) | \\\\\\\\ | (?<!\\\\)\\\\(?!\\\\) | \\\\" )* ) (?<!\\\\)" #ix',
      "# ' ( (?: (?>[^'\\\\]++) | \\\\\\\\ | (?<!\\\\)\\\\(?!\\\\) | \\\\' )* ) (?<!\\\\)' #ix"
      ), array($this, 'extractStrings'), $content
    );

    // delete line comments
    $content = preg_replace("#(//.*?)$#m", '', $content);
    // delete multiline comments
    $content = preg_replace('#/\*(.*?)\*/#is', '', $content);

    // replace
    $content = preg_replace_callback("#<<s(\d+)>>#", array($this, 'replaceStrings'), $content);

    $keywords = implode('|', $functions);

    // extracted messages
    $messages = array();

    // extract func calls
    // @see http://stackoverflow.com/questions/15762060/regular-expression-to-extract-javascript-method-calls
    preg_match_all(
      '# (?:' . $keywords . ') \(\\ *" ( (?: (?>[^"\\\\]++) | \\\\\\\\ | (?<!\\\\)\\\\(?!\\\\) | \\\\" )* ) (?<!\\\\)"\\ *(?:\)|,) #ix', $content, $matches, PREG_SET_ORDER
    );

    foreach ($matches as $m) {
      $messages[] = stripslashes($m[1]);
    }

    $matches = array();
    // @see http://stackoverflow.com/questions/15762060/regular-expression-to-extract-javascript-method-calls
    preg_match_all(
      "# (?:$keywords) \(\\ *' ( (?: (?>[^'\\\\]++) | \\\\\\\\ | (?<!\\\\)\\\\(?!\\\\) | \\\\' )* ) (?<!\\\\)'\\ *(?:\)|,) #ix", $content, $matches, PREG_SET_ORDER
    );

    foreach ($matches as $m) {
      $messages[] = stripslashes($m[1]);
    }

    // make the array unique
    // http://www.php.net/manual/en/function.array-unique.php#77743
    return array_keys(array_flip($messages));
  }

  protected function replaceStrings($match)
  {
    return $this->strings[$match[1]];
  }

  protected function extractRegs($match)
  {
    $this->regs[$this->regsCounter] = $match[1];
    $id = "<<reg{$this->regsCounter}>>";
    $this->regsCounter++;

    return $id;
  }

  protected function extractStrings($match)
  {
    $this->strings[$this->stringsCounter] = $this->importRegExps($match[0]);
    $id = "<<s{$this->stringsCounter}>>";
    $this->stringsCounter++;

    return $id;
  }

  protected function importRegExps($input)
  {
    return preg_replace_callback("#<<reg(\d+)>>#", array($this, 'importRegExpsCallback'), $input);
  }

  protected function importRegExpsCallback($match)
  {
    return $this->regs[$match[1]];
  }

}
