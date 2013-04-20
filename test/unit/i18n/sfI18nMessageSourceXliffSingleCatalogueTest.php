<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$fixturesDir = dirname(__FILE__) . '/fixtures/i18n';

$source = new sfI18nMessageSourceXliffSingleCatalogue($fixturesDir, 'base_form');

$source->setCulture('cs_CZ')
       ->load();

$messages = $source->getMessages();

$t->is_deeply($messages, array (
  'cs_CZ/base_form.xml' =>
  array (
    'Get thee hence, Satan: for it is written, Thou shalt worship the Lord thy God, and him only shalt thou serve.' =>
    array (
      0 => 'Odejdi, Satane. Vždyť je napsáno: Pánu, svému Bohu, se budeš klanět a jeho jediného uctívat.',
      1 => '1', // message ID
      2 => '',
    ),
  ),
), '->read() returns messages from source');


// try saving and updating, we do it outside of the fixtures dir not to cause mess
$tmpDir = sys_get_temp_dir().'/i18n_Xliff_single_test';
$filesystem = new sfFilesystem();
$filesystem->mkdirs($tmpDir);
$filesystem->mirror($fixturesDir, $tmpDir, new sfFinder());

$source = new sfI18nMessageSourceXliffSingleCatalogue($tmpDir, 'base_form');

$source->setCulture('cs_CZ')
       ->load();

$t->diag('->getMessages()');

$expected = array(
  'Odejdi, Satane. Vždyť je napsáno: Pánu, svému Bohu, se budeš klanět a jeho jediného uctívat. AMEN',
  'Ježíš je Pán'
);

// update the existing message
$source->update('Get thee hence, Satan: for it is written, Thou shalt worship the Lord thy God, and him only shalt thou serve.',
                'Odejdi, Satane. Vždyť je napsáno: Pánu, svému Bohu, se budeš klanět a jeho jediného uctívat. AMEN');

$source->append('Jesus is LORD');
$source->save();

$source->update('Jesus is LORD', 'Ježíš je Pán');
$source->save();
$source->load();

$allMessages = $source->getMessages();

foreach($allMessages as $catalogue => $messages)
{
  $i = 0;
  foreach($messages as $message)
  {
    $t->is($message[0], $expected[$i], 'returned value is ok');
    $i++;
  }
}

$source = new sfI18nMessageSourceXliffSingleCatalogue($tmpDir, 'base_form');

$source->setCulture('cs_CZ')
       ->load();

$result = null;
// delete
foreach($allMessages as $catalogue => $messages)
{
  foreach($messages as $message => $translated)
  {
    $t->diag(sprintf('->delete() message: ', $message));
    $result = $source->delete($message);
    break;
  }
}

$t->is($result, true, 'delete() returned true');

$source->save();
$allMessages = $source->load()->getMessages();

foreach($allMessages as $catalogue => $messages)
{
  $t->is(count($messages), 1, 'There is only one message left');
  foreach($messages as $message)
  {
    $t->is($message[0], 'Ježíš je Pán', 'first message have been sucessfully deletes from source');
    break;
  }
}

sfToolkit::clearDirectory($tmpDir);
$filesystem->remove($tmpDir);