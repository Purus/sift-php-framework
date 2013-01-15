<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/text/sfHtml2Text.class.php');

$t = new lime_test(1, new lime_output_color());

$html = '<h1>Nadpis</h1><p>Toto je zpráva, obsahující tagy a diakritiku.</p>';

$txt = "*** Nadpis ***\n\nToto je zpráva, obsahující tagy a diakritiku.";

$t->is(sfHtml2Text::convert($html), fix_linebreaks($txt), 'convert() works ok for simple html');