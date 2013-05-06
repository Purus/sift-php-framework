<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds mime type definitions
 *
 * Usage: php data/bin/mime_types.php
 *
 * @package    Sift
 * @subpackage cli
 */

$siftDataDir = realpath(dirname(__FILE__) . '/../');
$siftLibDir  = realpath(dirname(__FILE__) . '/../../lib');
$i18nDir  = realpath($siftDataDir . '/i18n/catalogues');

// for which cultures?
$cultures = array_map('trim', explode("\n", file_get_contents(dirname(__FILE__).'/../../build/cultures.txt')));

require_once $siftLibDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$definitions_data_dir = dirname(__FILE__) . '/../../build';

$file = $definitions_data_dir . '/freedesktop.org.xml';

print 'Building mime types definitions'. "\n";

$xml = simplexml_load_file($file);

$types = array();
$translations = array();
$messages = array();

foreach($xml as $t)
{
  $mimeType  = (string)$t['type'];

  $name = (string)$t->_comment;
  // comment: http://cz1.php.net/manual/en/class.simplexmlelement.php#106500
  $icon = (string)$t->{"generic-icon"}['name'];

  $globs = $t->glob;
  $mimeAliases = $t->alias;

  // mime type subclasses
  $mimeSubclasses = $t->{'sub-class-of'};

  // collection of file extensions
  $extensions = array();
  // collection of aliases
  $aliases     = array();
  foreach($globs as $glob)
  {
    $pattern = (string)$glob['pattern'];
    $extension = false;

    // we are skipping regular expressions, we need only
    // simple filenames
    if(preg_match('~\*\.([a-zA-Z0-9])+$~', $pattern))
    {
      $extension = str_replace('*.', '', $pattern);
      $extensions[] = strtolower($extension);
      foreach($mimeAliases as $alias)
      {
        $alias = (string)$alias['type'];
        if(empty($alias))
        {
          continue;
        }
        $aliases[] = $alias;
      }
      $aliases = array_unique($aliases);
    }
  }

  $parents = array();
  foreach($mimeSubclasses as $subclass)
  {
    $parents[] = (string)$subclass['type'];
  }

  // just make sure there are not redundant data
  $parents = array_unique($parents);

  if(count($extensions))
  {
    $types[strtolower($mimeType)] = array(
      'extension' => $extensions,
      'name' => $name,
      'icon' => $icon,
      'parent' => $parents,
      'alias' => $aliases
    );
  }

  $messages[] = $name;
}

// missing definitions
$missing = array();

$missing['application/json'] = array(
  'extension' => array('json'),
  'name' => $types['application/javascript']['name'],
  'icon' => $types['application/javascript']['icon'],
  'parent' => array(
      'application/javascript'
  ),
  'alias' => array()
);

// parse missing
foreach($missing as $m)
{
  $messages[] = $m['name'];
}

$types = array_merge($types, $missing);

asort($types);

print sprintf('Found %s mime types.', count($types)) . "\n";

$i18n = sfI18nMessageSource::factory('gettext', $i18nDir);

foreach($cultures as $culture)
{
  $i18n->setCulture($culture);
  $i18n->load('mime_type');

  $existing = $i18n->read();

  $old = array();
  if(count($existing))
  {
    $existing = current($existing);
    $old = array_diff(array_keys($existing), $messages);
  }

  foreach($messages as $message)
  {
    if(!isset($existing[$message]))
    {
      $i18n->append($message);
    }
  }

  foreach($old as $message)
  {
    $i18n->delete($message, 'mime_type');
  }

  $i18n->save('mime_type');
}

file_put_contents($siftDataDir.'/data/mime_types.dat', serialize($types));
print 'done.' . "\n";
