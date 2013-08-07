<?php

class sfTexParser {

  public $patterns = array();
  public $hyphenation = array();

  /**
   * This method parses a TEX-Hyphenation file and creates the appropriate
   * PHP-Hyphenation file
   *
   * @param string $file The original TEX-File
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