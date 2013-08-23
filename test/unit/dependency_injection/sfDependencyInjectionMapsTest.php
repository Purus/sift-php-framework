<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

$maps = new sfDependencyInjectionMaps();
$item = new sfDependencyInjectionMapItem();

$map = new sfDependencyInjectionMap(array(
   $item
));

$maps->set('newMap', $map);

$getMap = $maps->get('newMap');

$t->is($getMap, $map, 'get() returns the map');

$getMap = $maps->get('notFound');

$t->is($getMap->count(), 0, 'get returns an empty map for invalid key');
