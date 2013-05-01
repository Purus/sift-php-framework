<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if(!isset($since))
{
  $since = strtotime('-10 year');
}

$since = date('d/m/Y', $since);
$cmd = sprintf('git log --since=%s --no-merges --format="{%%at} [%%h]%%w(900,0,21) %%B"',
                $since);

// where to save the changelog
$changelogDir = realpath(dirname(__FILE__) . '/../..');

$return = null;
$output = array();

echo "Generating changelog\n";
exec($cmd, $output, $return);

if($return)
{
  echo "Error executing command\n";
  exit($return);
}

if(count($output))
{
  $content = array();
  foreach($output as $line)
  {
    if(empty($line))
    {
      continue;
    }

    $line = preg_replace_callback('/{(\d+)}/', 'format_date', $line);
    $content[] = str_replace("\n", ' ', $line);
  }

  file_put_contents($changelogDir.'/CHANGELOG', join("\n", $content));
  echo "Done.\n";
}

function format_date($matches)
{
  return date('d.m.Y', $matches[1]);
}

exit($return);
