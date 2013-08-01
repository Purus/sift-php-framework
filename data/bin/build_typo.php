<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Converts typographic information from Tex format to Sift internal format
 *
 * @package    Sift
 * @subpackage cli
 */

$libDir = dirname(__FILE__) . '/../../lib';
$dataSourceDir = dirname(__FILE__) . '/../../build/typo';
$i18nDir = dirname(__FILE__) . '/../../data/i18n/typo';

require_once $libDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$files = glob($dataSourceDir . '/*.tex');

// invalid!
$invalidLocales = array();
foreach($files as $file)
{
  $fileName = basename($file);

  preg_match('/hyph-([a-z_-]+)\.tex/i', $fileName, $matches);
  list(, $locale) = $matches;

  $subLocale = explode('_', str_replace('-', '_', $locale));
  if(isset($subLocale[1]))
  {
    $locale = $subLocale[0] . '_' . strtoupper($subLocale[1]);
  }

  if(in_array($locale, $invalidLocales))
  {
    continue;
  }

  echo sprintf('Parsing text file "%s" for locale "%s"', basename($file), $locale) . "\n";

  $parser = new TexParser();
  $parser->parseTexFile($file);

  $data = array();

  $data = array_merge($data, array(
    'patterns' => $parser->patterns,
    'hyphenation' => $parser->hyphenation,
  ));

  $array = array();

  if(is_readable($dataSourceDir . '/' . $locale . '.php'))
  {
    $array = include $dataSourceDir . '/' . $locale . '.php';
  }

  if(!isset($array['abbreviations']))
  {
    $array['abbreviations'] = array();
  }

  if(!isset($array['prepositions']))
  {
    $array['prepositions'] = array();
  }

  if(!isset($array['conjunctions']))
  {
    $array['conjunctions'] = array();
  }

  $data = array_merge($data, $array);

  file_put_contents($i18nDir . '/' . $locale . '.dat', serialize($data));
}

echo ('Done.') . "\n";

class TexParser {

  public $patterns = array();
  public $hyphens = array();

  /**
   * This method parses a TEX-Hyphenation file and creates the appropriate
   * PHP-Hyphenation file
   *
   * @param string $file       The original TEX-File
   *
   * @return boolean
   */
  public function parseTexFile($file)
  {
    $tex = file($file);

    // parser state
    $command = false;
    $braces = false;

    foreach($tex as $line)
    {
      $offset = 0;
      while($offset < strlen($line))
      {
        // %comment
        if($line[$offset] == '%')
        {
          break; // ignore rest of line
        }

        // \command
        if(preg_match('~^\\\\([[:alpha:]]+)~', substr($line, $offset), $m) === 1)
        {
          $command = $m[1];
          $offset += strlen($m[0]);
          continue; // next token
        }

        // {
        if($line[$offset] == '{')
        {
          $braces = TRUE;
          ++$offset;
          continue; // next token
        }

        // content
        if($braces)
        {
          switch($command)
          {
            case 'patterns':
              if(preg_match('~^(\pL\pM*|\pN|\.)+~u', substr($line, $offset), $m) === 1)
              {
                $numbers = '';
                preg_match_all('~(?:(\d)\D?)|\D~', $m[0], $matches, PREG_PATTERN_ORDER);
                foreach($matches[1] as $score)
                {
                  $numbers .= is_numeric($score) ? $score : 0;
                }
                $this->patterns[preg_replace('~\d~', '', $m[0])] = $numbers;
                $offset += strlen($m[0]);
              }
              continue; // next token
              break;

            case 'hyphenation':
              if(preg_match('~^\pL\pM*(-|\pL\pM*)+\pL\pM*~u', substr($line, $offset), $m) === 1)
              {
                $this->hyphenation[preg_replace('~\-~', '', $m[0])] = $m[0];
                $offset += strlen($m[0]);
              }
              continue; // next token
              break;
          }
        }

        // }
        if($line[$offset] == '}')
        {
          $braces = FALSE;
          $command = FALSE;
          ++$offset;
          continue; // next token
        }

        // ignorable content, skip one char
        ++$offset;
      }
    }
  }

}