<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18nGettext - GNU gettext file reader and writer.
 *
 * @package    Sift
 * @subpackage i18n
 */
class sfI18nGettext
{
    /**
     * strings
     *
     * associative array with all [msgid => msgstr] entries
     *
     * @access  protected
     * @var     array
     */
    protected $strings = array();

    /**
     * meta
     *
     * associative array containing meta
     * information like project name or content type
     *
     * @access  protected
     * @var     array
     */
    protected $meta = array();

    /**
     * file path
     *
     * @access  protected
     * @var     string
     */
    protected $file = '';

    /**
     * Factory
     *
     * @static
     * @access  public
     * @return  sfI18nGettextPo|sfI18nGettextmo Returns sfI18nGettextPo or sfI18nGettextMO on success
     *                  or throws an sfException on failure
     *
     * @param   string $format mo or po
     * @param   string $file   path to GNU gettext file
     */
    public static function factory($format, $file = '')
    {
        $className = sprintf('sfI18nGettext%s', ucfirst(strtolower($format)));
        if (!class_exists($className)) {
            throw new sfException(sprintf('Class "%s" not found', $className));
        }

        return new $className($file);
    }

    /**
     * poFile2moFile
     *
     * That's a simple fake of the 'msgfmt' console command.  It reads the
     * contents of a GNU PO file and saves them to a GNU MO file.
     *
     * @static
     * @access  public
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     *
     * @param   string $pofile path to GNU PO file
     * @param   string $mofile path to GNU MO file
     */
    public function poFile2moFile($pofile, $mofile)
    {
        if (!is_file($pofile)) {
            throw new sfException(sprintf('File "%s" doesn\'t exist.', $pofile));
        }

        $PO = new sfI18nGettextPo($pofile);
        if (true !== ($e = $PO->load())) {
            return $e;
        }

        $MO = $PO->toMO();
        if (true !== ($e = $MO->save($mofile))) {
            return $e;
        }
        unset($PO, $MO);

        return true;
    }

    /**
     * prepare
     *
     * @static
     * @access  protected
     * @return  string
     *
     * @param   string $string
     * @param   bool   $reverse
     */
    protected function prepare($string, $reverse = false)
    {
        if ($reverse) {
            $smap = array('"', "\n", "\t", "\r");
            $rmap = array('\"', '\\n"' . "\n" . '"', '\\t', '\\r');

            return (string)str_replace($smap, $rmap, $string);
        } else {
            $string = preg_replace('/"\s+"/', '', $string);
            $smap = array('\\n', '\\r', '\\t', '\"');
            $rmap = array("\n", "\r", "\t", '"');

            return (string)str_replace($smap, $rmap, $string);
        }
    }

    /**
     * meta2array
     *
     * @static
     * @access  public
     * @return  array
     *
     * @param   string $meta
     */
    public function meta2array($meta)
    {
        $array = array();
        foreach (explode("\n", $meta) as $info) {
            if ($info = trim($info)) {
                list($key, $value) = explode(':', $info, 2);
                $array[trim($key)] = trim($value);
            }
        }

        return $array;
    }

    /**
     * toArray
     *
     * Returns meta info and strings as an array of a structure like that:
     * <code>
     *   array(
     *       'meta' => array(
     *           'Content-Type'      => 'text/plain; charset=iso-8859-1',
     *           'Last-Translator'   => 'Michael Wallner <mike@iworks.at>',
     *           'PO-Revision-Date'  => '2004-07-21 17:03+0200',
     *           'Language-Team'     => 'German <mail@example.com>',
     *       ),
     *       'strings' => array(
     *           'All rights reserved'   => 'Alle Rechte vorbehalten',
     *           'Welcome'               => 'Willkommen',
     *           // ...
     *       )
     *   )
     * </code>
     *
     * @see     fromArray()
     * @access  protected
     * @return  array
     */
    public function toArray()
    {
        return array('meta' => $this->meta, 'strings' => $this->strings);
    }

    /**
     * fromArray
     *
     * Assigns meta info and strings from an array of a structure like that:
     * <code>
     *   array(
     *       'meta' => array(
     *           'Content-Type'      => 'text/plain; charset=iso-8859-1',
     *           'Last-Translator'   => 'Michael Wallner <mike@iworks.at>',
     *           'PO-Revision-Date'  => date('Y-m-d H:iO'),
     *           'Language-Team'     => 'German <mail@example.com>',
     *       ),
     *       'strings' => array(
     *           'All rights reserved'   => 'Alle Rechte vorbehalten',
     *           'Welcome'               => 'Willkommen',
     *           // ...
     *       )
     *   )
     * </code>
     *
     * @see     toArray()
     * @access  protected
     * @return  bool
     *
     * @param   array $array
     */
    public function fromArray($array)
    {
        if (!array_key_exists('strings', $array)) {
            if (count($array) != 2) {
                return false;
            } else {
                list($this->meta, $this->strings) = $array;
            }
        } else {
            $this->meta = @$array['meta'];
            $this->strings = @$array['strings'];
        }

        return true;
    }

    /**
     * toMO
     *
     * @access  protected
     * @return  object  sfGettextMO
     */
    public function toMO()
    {
        $mo = new sfI18nGettextMo();
        $mo->fromArray($this->toArray());

        return $mo;
    }

    /**
     * toPO
     *
     * @access  protected
     * @return  object      sfGettextPO
     */
    public function toPO()
    {
        $po = new sfI18nGettextPo();
        $po->fromArray($this->toArray());

        return $po;
    }

}
