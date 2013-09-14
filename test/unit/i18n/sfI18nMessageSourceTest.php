<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

$fixturesDir = dirname(__FILE__) . '/fixtures/i18n';

$source = new sfI18nMessageSourceGettext($fixturesDir);
$source->setCulture('cs_CZ')
       ->load('base_form');

$messages = $source->getMessages();

$t->is_deeply($messages, array (
  'cs_CZ/base_form.mo' =>
  array (
    'Get thee hence, Satan: for it is written, Thou shalt worship the Lord thy God, and him only shalt thou serve.' =>
    array (
      0 => 'Odejdi, Satane. Vždyť je napsáno: Pánu, svému Bohu, se budeš klanět a jeho jediného uctívat.',
      1 => 0,
      2 => '',
    ),
  ),
), '->read() returns messages from source');

// try saving and updating, we do it outside of the fixtures dir not to cause mess
$tmpDir = sys_get_temp_dir().'/i18n_gettext_single_test';

class myFileCache extends sfFileCache {

  public function getCacheData($path)
  {
    return $this->read($path, self::READ_DATA);
  }

}

$cache = new myFileCache(array('cache_dir' => $tmpDir));

$source = new sfI18nMessageSourceGettext($fixturesDir);
$source->setCache($cache);
$source->setCulture('cs_CZ');
$source->load('base_form');

$data = $source->getCache()->getCacheData($tmpDir . '/cs_CZ/base_form.mo'.'.cache');

$t->isa_ok($data, 'array', 'some data are written to cache');

$messages = unserialize($data[1]);
$t->isa_ok($messages, 'array', 'data contains some translation messages');

$t->is($messages, array(
  'Get thee hence, Satan: for it is written, Thou shalt worship the Lord thy God, and him only shalt thou serve.' =>
  array (
    'Odejdi, Satane. Vždyť je napsáno: Pánu, svému Bohu, se budeš klanět a jeho jediného uctívat.',
    0,
    '',
  ),
), 'data contains the right translation messages');

sfToolkit::clearDirectory($tmpDir);
@unlink($tmpDir);
