<?php

$abbreviations = array(
    'cca.',
    'č.',
    'čís.',
    'čj.',
    'čp.',
    'fa',
    'fě',
    'fy',
    'kupř.',
    'mj.',
    'např.',
    'p.',
    'pí',
    'popř.',
    'př.',
    'přib.',
    'přibl.',
    'sl.',
    'str.',
    'sv.',
    'tj.',
    'tzn.',
    'tzv.',
    'zvl.'
);

$prepositions = array(
    'k', 's', 'v', 'z',
);


$conjunctions = array(
    'a', 'i', 'o', 'u'
);

$result =  array(
  'abbreviations'  => $abbreviations,
  'prepositions' => $prepositions,
  'conjunctions' => $conjunctions
);

unset($abbreviations, $prepositions, $conjunctions);

return $result;