<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/text/sfTypography.class.php');
require_once(dirname(__FILE__).'/../../../lib/utf8/sfUtf8.class.php');


$t = new lime_test(8, new lime_output_color());

sfConfig::set('sf_sift_data_dir', dirname(__FILE__).'/../../../data');

$tg = sfTypography::getInstance('cs_CZ')
      ->setHyphen('-')
      // Minimum 5 characters before the first hyphenation
      ->setHyphenateLeftMin(5)
      // Hyphenate only words with more than 4 characters
      ->setHyphenateWordMin(10)
      // Set some special characters
      // ->setSpecialChars ( 'Ă¤Ă¶ĂĽĂź' )
      // Only Hyphenate with the best quality
      ->setHyphenateQuality(sfTypography::HYPHENATE_QUALITY_HIGHEST)
      // Words that shall not be hyphenated have to start with this string
      ->setNoHyphenateMarker('nbr:')
      // Words that contain this string are custom hyphenated
      ->setCustomHyphen('--'); 

$text   = 'impresionismus';
$parsed = 'impre-si-o-nis-mus';

$t->is($tg->hyphenate($text), $parsed, 'parse() works ok for simple html');

// special cases
// koeficient
$text = 'řekni mi prosím tvůj koeficient Boží moci jestli je jeho úhlopříčka větší než moje';
// úhlopříčka
$parsed = 'řekni mi prosím tvůj koe-fi-ci-ent Boží moci jestli je jeho úhlo-příč-ka větší než moje';

$t->is($tg->hyphenate($text), $parsed, 'parse() works ok for simple html');

$html = '<p>řekni mi prosím tvůj koeficient Boží moci jestli je jeho úhlopříčka větší než moje</p>';
$parsed = '<p>řekni mi prosím tvůj koe-fi-ci-ent Boží moci jestli je jeho úhlo-příč-ka větší než moje</p>';

$t->is($tg->hyphenate($html, true), $parsed, 'parse() works ok for simple html');


$tg  // Minimum 5 characters before the first hyphenation
      ->setHyphenateLeftMin(2)
      // Hyphenate only words with more than 4 characters
      ->setHyphenateWordMin(10)
      ->setHyphenateQuality('normal')
      ->setHyphenateRightMin(2);

$text = 'dům administrativní';
// THIS IS NOT 100% should be: ad-mi-nis-tra-tiv-ní
$parsed = 'dům ad-mi-ni-stra-ti-vní'; 

$t->is($tg->hyphenate($text), $parsed, 'parse() works ok for simple html');

$text = 'Koeficient';
$parsed = 'Koe-fi-ci-ent';

$t->is($tg->hyphenate($text), $parsed, 'parse() works ok for simple html');

$text = 'KOEfICIENT';
$parsed = 'KOE-fI-CI-ENT';

$t->is($tg->hyphenate($text), $parsed, 'parse() works ok for simple html');

$text = 'dům předměstský';
// THIS IS NOT 100% should be před-měst-ský
$parsed = 'dům před-městský';

$t->is($tg->hyphenate($text), $parsed, 'parse() works ok for simple html');


$text = 'převelicedlouhýtextkterý někdo napsaldoguestbooku, co s tím?';
// THIS IS NOT 100% should be před-měst-ský
$parsed = 'převe-li-cedlouhý-t-extk-te-rý někdo na-psal-doguest-booku, co s tím?';

$t->is($tg->hyphenate($text), $parsed, 'parse() works ok for simple html');

//// fix
//$t->diag('->correct()');
//
//$tests = array();
//
//// 
//$tests[] = array( 
//  'test' => 'Ahoj,jak se máš ? Díky,dobře ...',
//  'result' => 'Ahoj, jak se máš? Díky, dobře...'
//);
//
//$tests[] = array( 
//  'test' => '<p>Ahoj, <strong>jak se máš</strong> ? Díky,dobře ...</p>',
//  'result' => '<p>Ahoj, <strong>jak se máš</strong>? Díky, dobře...</p>'
//);
//
//$tests[] = array( 
//  'test' => '<p>Ahoj, <strong>jak se máš</strong> ? Díky,dobře ...</p>',
//  'result' => '<p>Ahoj, <strong>jak se máš</strong>? Díky, dobře...</p>'
//);
//
//foreach($tests as $test)
//{
//  $t->is($tg->correct($test['test']), $test['result'], 'correct() works ok');
//}
//
//$tests = array();
//
//$tests[] = array( 
//  'test' => '<p>Ahoj, <strong>jak se máš</strong> ? Díky,dobře ... Co ten KOEfICIENT?</p>',
//  'result' => '<p>Ahoj, <strong>jak se máš</strong>? Díky, dobře... Co ten KOE-fI-CI-ENT?</p>'
//);
//
//$tests[] = array( 
//  'test' => '<p>Ahoj, <a href="/index.php/stranka/?a=foobar&zoom=5">jak se máš</a> ?</p>',
//  'result' => '<p>Ahoj, <a href="/index.php/stranka/?a=foobar&amp;zoom=5">jak se máš</a>?</p>'
//);
//
//
//foreach($tests as $test)
//{
//  $t->is($tg->correctAndHyphenate($test['test']), $test['result'], 'correct() works ok');
//}
//
//
//$t->diag('->wrapWords()');
//
//$tests = array();
//
//$tests[] = array( 
//  'test' => 'Co je ti po nás, Ježíši Synu Boha Nejvyššího? Přišel jsi z nebe?',
//  'result' => 'Co je ti po&nbsp;nás, Ježíši Synu Boha Nejvyššího? Přišel jsi z&nbsp;nebe?'
//);
//
//$tests[] = array( 
//  'test' => '<p>Co je ti po nás, <strong>Ježíši</strong> [ano i ne] Synu Boha Nejvyššího? Přišel jsi z nebe?</p><pre>z nebe</pre>',
//  'result' => '<p>Co je ti po&nbsp;nás, <strong>Ježíši</strong> [ano i ne] Synu Boha Nejvyššího? Přišel jsi z&nbsp;nebe?</p><pre>z nebe</pre>'
//);
//
//$tests[] = array( 
//  'test' => 'Toto je naše galerie. [gallery id="123" size="medium"] s velým obrázkem [caption class="headline"]My Caption s obrázkem[/caption]',
//  'result' => 'Toto je naše galerie. [gallery id="123" size="medium"] s&nbsp;velým obrázkem [caption class="headline"]My Caption s&nbsp;obrázkem[/caption]'
//);
//
//// numbers 
//$tests[] = array( 
//  'test' => '<p>Mám na honci 1 2 nebo tři?</p>',
//  'result' => '<p>Mám na honci 1&nbsp;2 nebo tři?</p>'
//);
//
//$tests[] = array( 
//  'test' => '"Mám na honci 1 2 nebo tři?"',
//  'result' => '""Mám na honci 1&nbsp;2 nebo tři?""'
//);
//
//foreach($tests as $test)
//{ 
//  $t->is($tg->correct($test['test']), $test['result'], 'wrapWords() works ok');
//}

