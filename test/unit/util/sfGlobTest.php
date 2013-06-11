<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(20, new lime_output_color());

// create test directory
$tmpDir = (sys_get_temp_dir() . '/_glob_' . md5(uniqid(rand(), true)));

mkdir($tmpDir);

chdir($tmpDir);

// create test contents
touch('abc.php');
touch('abcd.php');
touch('abc.jpg');
touch('abe.jpg');
touch('aba.jpg');
touch('abd.jpg');
touch('abcd.jpg');
touch('abcc.jpg');
touch('abce.jpg');
touch('abc.png');
touch('abcd.png');
touch('abc.exe');
touch('abcd.exe');
// DOES not work on windows
// touch('foo\\?bar');

mkdir('foo', 0777);
chdir('foo');
    touch('abc.php');
    touch('abcd.php');
    touch('abc.jpg');
    touch('abcd.jpg');
    touch('abc.png');
    touch('abcd.png');
    touch('abc.exe');
    touch('abcd.exe');

    mkdir('bar', 0777);
    chdir('bar');
        touch('abc.php');
        touch('abcd.php');
        touch('abc.jpg');
        touch('abcd.jpg');
        touch('abc.png');
        touch('abcd.png');
        touch('abc.exe');
        touch('abcd.exe');
        chdir('..');

    mkdir('baz', 0777);
    chdir('baz');
        touch('abc.php');
        touch('abcd.php');
        touch('abc.jpg');
        touch('abcd.jpg');
        chdir('../..');

mkdir('baz', 0777);
chdir('baz');
    touch('abc.php');
    touch('abcd.php');
    touch('abc.jpg');
    touch('abcd.jpg');

    mkdir('bar', 0777);
    chdir('bar');
        touch('abc.php');
        touch('abcd.php');
        touch('abc.jpg');
        touch('abcd.jpg');
        chdir('../..');

mkdir('cat', 0777);
chdir('cat');
    touch('abc.php');
    touch('abcd.php');
    touch('abc.jpg');
    touch('abcd.jpg');

    mkdir('bar', 0777);
    chdir('bar');
        touch('abc.php');
        touch('abcd.php');
        touch('abc.jpg');
        touch('abcd.jpg');

// test patterns
$tests = array(
    0 => array(
        'none',
        'foo',
        'ab[cd].jpg',
        'foo*',
        '???/*',
        '*foo*',
        '*/abc.*',
        'foo/*/abc.*',
        'foo/*/*'
    ),
    GLOB_BRACE => array(
        'GLOB_BRACE',
        'a*.{php,jpg}',
        'foo/a*.{php,jpg}',
        'foo/a*.{p{hp,ng},jpg}'
    ),
    (GLOB_BRACE | GLOB_NOSORT) => array(
        'GLOB_BRACE | GLOB_NOSORT',
        'a*.{php,jpg}',
        'foo/a*.{php,jpg}',
        'foo/a*.{p{hp,ng},jpg}'
    ),
    GLOB_NOSORT => array(
        'GLOB_NOSORT',
        '*/*'
    ),
    GLOB_ONLYDIR => array(
        'GLOB_ONLYDIR',
        '*',
        'foo/*'
    ),
    GLOB_MARK => array(
        'GLOB_MARK',
        'foo/*'
    ),
    GLOB_NOESCAPE => array(
        'GLOB_NOESCAPE',
        'foo\\?bar'
    ),
    GLOB_NOCHECK => array(
        'GLOB_NOCHECK',
        'foo/khsgkhgjhgla'
    )
);

$expected = array(
  'none'  => array(
    array("foo"),
    array(
      "abc.jpg",
      "abd.jpg"
    ),
    array(
      "foo",
      // does not work on windows
      // "foo\?bar"
    ),
   array(
    "baz/abc.jpg",
    "baz/abc.php",
    "baz/abcd.jpg",
    "baz/abcd.php",
    "baz/bar",
    "cat/abc.jpg",
    "cat/abc.php",
    "cat/abcd.jpg",
    "cat/abcd.php",
    "cat/bar",
    "foo/abc.exe",
    "foo/abc.jpg",
    "foo/abc.php",
    "foo/abc.png",
    "foo/abcd.exe",
    "foo/abcd.jpg",
    "foo/abcd.php",
    "foo/abcd.png",
    "foo/bar",
    "foo/baz",
  ),
  array(
    "foo",
    // "foo\?bar",
  ),
  array(
    "baz/abc.jpg",
    "baz/abc.php",
    "cat/abc.jpg",
    "cat/abc.php",
    "foo/abc.exe",
    "foo/abc.jpg",
    "foo/abc.php",
    "foo/abc.png",
  ),
  array(
    "foo/bar/abc.exe",
    "foo/bar/abc.jpg",
    "foo/bar/abc.php",
    "foo/bar/abc.png",
    "foo/baz/abc.jpg",
    "foo/baz/abc.php"
  ),
  array(
  "foo/bar/abc.exe",
  "foo/bar/abc.jpg",
  "foo/bar/abc.php",
  "foo/bar/abc.png",
  "foo/bar/abcd.exe",
  "foo/bar/abcd.jpg",
  "foo/bar/abcd.php",
  "foo/bar/abcd.png",
  "foo/baz/abc.jpg",
  "foo/baz/abc.php",
  "foo/baz/abcd.jpg",
  "foo/baz/abcd.php"
  )),

  'GLOB_BRACE' => array(
    array(
      "abc.php",
      "abcd.php",
      "aba.jpg",
      "abc.jpg",
      "abcc.jpg",
      "abcd.jpg",
      "abce.jpg",
      "abd.jpg",
      "abe.jpg"
    ),
    array(
      "foo/abc.php",
      "foo/abcd.php",
      "foo/abc.jpg",
      "foo/abcd.jpg"
    ),
    array(
    "foo/abc.php",
    "foo/abcd.php",
    "foo/abc.png",
    "foo/abcd.png",
    "foo/abc.jpg",
    "foo/abcd.jpg"
    )),

  'GLOB_BRACE | GLOB_NOSORT' => array(
    array(
    "aba.jpg",
    "abc.jpg",
    "abc.php",
    "abcc.jpg",
    "abcd.jpg",
    "abcd.php",
    "abce.jpg",
    "abd.jpg",
    "abe.jpg"
   ), array(
    "foo/abc.jpg",
    "foo/abc.php",
    "foo/abcd.jpg",
    "foo/abcd.php",
    ),
    array(
    "foo/abc.jpg",
    "foo/abc.php",
    "foo/abc.png",
    "foo/abcd.jpg",
    "foo/abcd.php",
    "foo/abcd.png"
    )),
    'GLOB_NOSORT' => array(
      array(
        "baz/abc.jpg",
        "baz/abc.php",
        "baz/abcd.jpg",
        "baz/abcd.php",
        "baz/bar",
        "cat/abc.jpg",
        "cat/abc.php",
        "cat/abcd.jpg",
        "cat/abcd.php",
        "cat/bar",
        "foo/abc.exe",
        "foo/abc.jpg",
        "foo/abc.php",
        "foo/abc.png",
        "foo/abcd.exe",
        "foo/abcd.jpg",
        "foo/abcd.php",
        "foo/abcd.png",
        "foo/bar",
        "foo/baz"
      )
  ),
  'GLOB_ONLYDIR' => array(
      array(
     "baz", "cat", "foo"
    ),
    array(
      "foo/bar",
      "foo/baz"
    )
  ),
  'GLOB_MARK' => array(
      array(
        "foo/abc.exe",
        "foo/abc.jpg",
        "foo/abc.php",
        "foo/abc.png",
        "foo/abcd.exe",
        "foo/abcd.jpg",
        "foo/abcd.php",
        "foo/abcd.png",
        "foo/bar/",
        "foo/baz/"
      )
  ),
  'GLOB_NOESCAPE' => array(
      array(
        // "foo\?bar" // DOES NOT WORK on WINDOWS
      )
  ),
  'GLOB_NOCHECK' => array(
    array(
      "foo/khsgkhgjhgla"
    )
  )
);


chdir($tmpDir);

foreach($tests as $flags => $patterns)
{
  $ftext = array_shift($patterns);
  $i = 0;
  foreach($patterns as $pattern)
  {
    $compat = sfGlob::find($pattern, $flags, true);
    if($flags & GLOB_NOSORT)
    {
      natsort($compat);
      $compat = array_values($compat);
    }

    if(!isset($expected[$ftext][$i]))
    {
      $t->skip('skipping', 1);
    }
    else
    {
      $t->is(array_map('fix_dir_separator', $compat), $expected[$ftext][$i], sprintf('find() work ok for flags "%s" and pattern: "%s"', $ftext, $pattern));
    }

    $i++;
  }
}

function fix_dir_separator($item)
{
  return str_replace(DIRECTORY_SEPARATOR, '/', $item);
}

sfToolkit::clearDirectory($tmpDir);
rmdir($tmpDir);