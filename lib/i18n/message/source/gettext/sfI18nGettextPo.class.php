<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18nGettextPo is GNU MO file reader and writer.
 *
 * @package    Sift
 * @subpackage i18n
 */
class sfI18nGettextPo extends sfI18nGettext {

  /**
   * Constructor
   *
   * @access  public
   * @return  object      File_Gettext_PO
   * @param   string      path to GNU PO file
   */
  public function __construct($file = '')
  {
    $this->file = $file;
  }

  /**
   * Load PO file
   *
   * @access  public
   * @return  mixed   Returns true on success or false on failure.
   * @param   string  $file
   */
  public function load($file = null)
  {
    if(!isset($file))
    {
      $file = $this->file;
    }

    // load file
    if(!$contents = @file($file))
    {
      return false;
    }

    $contents = implode('', $contents);

    $pattern =
            '/(msgid\s+("(.*)*?"\s*)+)\s+' .
            '(msgstr\s+("(.*)*?"\s*)+)/';


    // match all msgid/msgstr entries
    $matched = preg_match_all($pattern, $contents, $matches);
    unset($contents);

    if(!$matched)
    {
      return false;
    }

    // get all msgids and msgtrs
    for($i = 0; $i < $matched; $i++)
    {
      $msgid = preg_replace(
              '/\s*msgid\s*"(.*)"\s*/s', '\\1', $matches[1][$i]);
      $msgstr = preg_replace(
              '/\s*msgstr\s*"(.*)"\s*/s', '\\1', $matches[4][$i]);
      $this->strings[parent::prepare($msgid)] = parent::prepare($msgstr);
    }

    // check for meta info
    if(isset($this->strings['']))
    {
      $this->meta = parent::meta2array($this->strings['']);
      unset($this->strings['']);
    }

    return true;
  }

  /**
   * Save PO file
   *
   * @access  public
   * @return  mixed   Returns true on success or PEAR_Error on failure.
   * @param   string  $file
   */
  public function save($file = null)
  {
    if(!isset($file))
    {
      $file = $this->file;
    }

    // open PO file
    if(!is_resource($fh = @fopen($file, 'w')))
    {
      return false;
    }

    // lock PO file exclusively
    if(!flock($fh, LOCK_EX))
    {
      fclose($fh);
      return false;
    }
    // write meta info
    if(count($this->meta))
    {
      $meta = 'msgid ""' . "\nmsgstr " . '""' . "\n";
      foreach($this->meta as $k => $v)
      {
        $meta .= '"' . $k . ': ' . $v . '\n"' . "\n";
      }
      fwrite($fh, $meta . "\n");
    }

    // write strings
    foreach($this->strings as $o => $t)
    {
      fwrite($fh, 'msgid "' . parent::prepare($o, true) . '"' . "\n" .
              'msgstr "' . parent::prepare($t, true) . '"' . "\n\n"
      );
    }

    //done
    @flock($fh, LOCK_UN);
    @fclose($fh);
    return true;
  }

}
