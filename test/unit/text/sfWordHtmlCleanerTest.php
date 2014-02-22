<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3, new lime_output_color());

class myHtmlPurifier extends sfHtmlPurifier {

    protected function loadSettings()
    {
        return array(
            'strict' => array(
                'Cache.SerializerPath' => sys_get_temp_dir(),
                'HTML.AllowedElements' => array()
            ),
            'word' => array(
                'Cache.SerializerPath' => sys_get_temp_dir(),
                'HTML.Trusted' => false,
                'AutoFormat.RemoveEmpty.RemoveNbsp' =>  true,
                'AutoFormat.RemoveSpansWithoutAttributes' =>  true,
                'HTML.TidyLevel' => 'heavy',
                'HTML.ForbiddenElements' =>  array('div', 'col'),
                'HTML.ForbiddenAttributes' => array('style', 'class'),
                'Output.TidyFormat' => true,
                'AutoFormat.RemoveEmpty' => true,
                'AutoFormat.AutoParagraph' => true,
                'Core.NormalizeNewlines' => false
            )
        );
    }
}

class mySanitizer extends sfSanitizer {

    public static function getHtmlPurifier($type = 'strict')
    {
        return new myHtmlPurifier($type);
    }

    // we support 5.2, so cannot use static:: inside the purifier
    public static function xssClean($value, $type = 'strict')
    {
        return self::getHtmlPurifier($type)->purify($value);
    }

    public static function sanitize($value, $type = 'strict')
    {
        return self::xssClean($value, $type);
    }
}

class myWordHtmlCleaner extends sfWordHtmlCleaner {

    /**
     * Cleans up word html, also convert to utf8 (second argument)
     *
     * @param string $html
     * @param boolean $convertToUtf8
     * @return string
     */
    public static function clean($html, $convertToUtf8 = true)
    {
        if($convertToUtf8)
        {
            $html = self::convertToUtf8($html);
        }

        return self::fixNewLines(
            mySanitizer::sanitize($html, 'word')
        );
    }
}

$fixturesDir = dirname(__FILE__) . '/fixtures';

$html = file_get_contents($fixturesDir . '/word_11.txt');

$cleaned = myWordHtmlCleaner::clean($html);

$t->isa_ok($cleaned, 'string', '->clean() returns string');
// no garbage found in the cleaned string
$t->ok(strpos($cleaned, 'text-indent:-.2in') === false, '->clean() cleans up ugly html correctly');

$html = file_get_contents($fixturesDir . '/openoffice.txt');
$t->ok(strpos(myWordHtmlCleaner::clean($html), 'TD WIDTH=50%') === false, '->clean() cleans up ugly html correctly');
