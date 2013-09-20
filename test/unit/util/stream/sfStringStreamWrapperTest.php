<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

sfStringStreamWrapper::register();

$fp = fopen("string://", "r+");

for($i = 1; $i < 5; $i++)
{
  fwrite($fp, "line$i\n");
}

rewind($fp);

$i = 1;
while(!feof($fp))
{
  $line = fgets($fp);
  $t->is($line, "line$i\n", "fgets() returns written data");
  $i++;
}

rewind($fp);

$t->is_deeply(stream_get_contents($fp), "line1\nline2\nline3\nline4\n", "stream_get_contents() works");

fseek($fp, 6);

$t->is_deeply(stream_get_contents($fp), "line2\nline3\nline4\n", "fseek() works");

fclose($fp);