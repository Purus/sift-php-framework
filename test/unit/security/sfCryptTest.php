<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

// setting algorithm works correctly
$crypt = new sfCrypt('$ecret');

$string = 'my secure message';

$t->diag('->encrypt()');
$encrypted = $crypt->encrypt($string);
$decrypted = $crypt->decrypt($encrypted);

$t->is($decrypted, $string, 'Decryption of string correct result');

// returns string
$t->isa_ok($encrypted, 'string', 'Encryption of string returns string');
$decrypted = $crypt->decrypt($encrypted);

// decrypt it back!
$t->isa_ok($encrypted, 'string', 'Encryption of string returns string');
$t->is($decrypted, $string, 'Decryption of string correct result');

// test utf-8!
$string = 'můj tajný kód (žížala řeže pilkou železo)';

$encrypted = $crypt->encrypt($string);
$decrypted = $crypt->decrypt($encrypted);

// returns string
$t->is($decrypted, $string, 'Decryption of string correct result for utf-8 string');

// encryption with onther key and algorithm

$crypt = new sfCrypt('my secure key 1123');
$string = 'my secured message with žužu';

$encrypted = $crypt->encrypt($string);
$decrypted = $crypt->decrypt($encrypted);

$t->is($decrypted, $string, 'Decryption of string correct result');


$crypt = new sfCrypt('my secure key 444');
$string = 'my secured message with žužula';

$encrypted = $crypt->encrypt($string);
$decrypted = $crypt->decrypt($encrypted);

$t->is($decrypted, $string, 'Decryption of string correct result');

$crypt = new sfCrypt('my secret key');
$string = 'my secured message with žužula';

$encrypted = $crypt->encrypt($string);
$decrypted = $crypt->decrypt($encrypted);

$t->is($decrypted, $string, 'Decryption of string correct result');

$crypt = new sfCrypt(dirname(__FILE__) . '/crypt.key');

$t->diag('Load key from external file');

$string = 'my secured message with žužula|1';

$encrypted = $crypt->encrypt($string, true);
$decrypted = $crypt->decrypt($encrypted, true);

$t->is($decrypted, $string, 'Decryption of string correct result');

