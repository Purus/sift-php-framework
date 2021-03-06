<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function is_cli()
{
  return !isset($_SERVER['HTTP_HOST']);
}

/**
 * Checks a configuration.
 */
function check($boolean, $message, $help = '', $fatal = false)
{
  echo $boolean ? "  OK        " : sprintf("[[%s]] ", $fatal ? ' ERROR ' : 'WARNING');
  echo sprintf("$message%s\n", $boolean ? '' : ': FAILED');

  if (!$boolean)
  {
    echo "            *** $help ***\n";
    if ($fatal)
    {
      die("You must fix this problem before resuming the check.\n");
    }
  }
}

/**
 * Gets the php.ini path used by the current PHP interpretor.
 *
 * @return string the php.ini path
 */
function get_ini_path()
{
  if ($path = get_cfg_var('cfg_file_path'))
  {
    return $path;
  }

  return 'WARNING: not using a php.ini file';
}

if (!is_cli())
{
  echo '<html><body><pre>';
}

echo "********************************\n";
echo "*  Requirements check  *\n";
echo "********************************\n\n";

echo sprintf("php.ini used by PHP: %s\n\n", get_ini_path());

if (is_cli())
{
  echo "** WARNING **\n";
  echo "*  The PHP CLI can use a different php.ini file\n";
  echo "*  than the one used with your web server.\n";
  if ('\\' == DIRECTORY_SEPARATOR)
  {
    echo "*  (especially on the Windows platform)\n";
  }
  echo "*  If this is the case, please launch this\n";
  echo "*  utility from your web server.\n";
  echo "** WARNING **\n";
}

// mandatory
echo "\n** Mandatory requirements **\n\n";

check(version_compare(phpversion(), '5.2.3', '>='), sprintf('PHP version is at least 5.2.3 (Current: %s)', phpversion()), 'Current version is '.phpversion(), true);
check(class_exists('PDO'), 'PDO is installed', 'Install PDO (mandatory for Doctrine)', true);
if(class_exists('PDO'))
{
  $drivers = PDO::getAvailableDrivers();
  check(count($drivers), 'PDO has some drivers installed: '.implode(', ', $drivers), 'Install PDO drivers (mandatory for Doctrine)');
}

check(class_exists('DomDocument'), 'PHP-XML module is installed', 'Install the php-xml module (required by Propel)', false);
check(class_exists('XSLTProcessor'), 'XSL module is installed', 'Install the XSL module (recommended for Propel)', false);
check(function_exists('token_get_all'), 'The token_get_all() function is available', 'Install token_get_all() function (highly recommended)', false);
check(function_exists('mb_strlen'), 'The mb_strlen() function is available', 'Install mb_strlen() function', false);
check(function_exists('iconv'), 'The iconv() function is available', 'Install iconv() function', false);
check(function_exists('utf8_decode'), 'The utf8_decode() is available', 'Install utf8_decode() function', false);
check(extension_loaded('gd') && function_exists('gd_info'), 'GD graphics library is installed', 'Install GD graphics library', true);
check(extension_loaded('mcrypt') && function_exists('mcrypt_module_open'), 'MCrypt module is installed', 'Install Mcrypt module', true);
check(extension_loaded('intl') && class_exists('Collator'), 'Intl module is installed', 'Install intl module (Locale comparison methods like sorting may not work ok.)', false);
check(extension_loaded('bcmath'), 'Bcmath module is installed', 'Install bcmtac module.', false);

$accelerator =
  (function_exists('apc_store') && ini_get('apc.enabled'))
  ||
  function_exists('eaccelerator_put') && ini_get('eaccelerator.enable')
  ||
  function_exists('xcache_set')
;
check($accelerator, 'A PHP accelerator is installed', 'Install a PHP accelerator like APC (highly recommended)', false);

check(!ini_get('short_open_tag'), 'php.ini has short_open_tag set to off', 'Set it to off in php.ini', false);
check(!ini_get('magic_quotes_gpc'), 'php.ini has magic_quotes_gpc set to off', 'Set it to off in php.ini', false);
check(!ini_get('register_globals'), 'php.ini has register_globals set to off', 'Set it to off in php.ini', false);
check(!ini_get('session.auto_start'), 'php.ini has session.auto_start set to off', 'Set it to off in php.ini', false);
check(!ini_get('safe_mode'), 'safe mode is turned off', 'Set it to off in php.ini', false);

check(version_compare(phpversion(), '5.2.9', '!='), 'PHP version is not 5.2.9', 'PHP 5.2.9 broke array_unique() and sfToolkit::arrayDeepMerge(). Use 5.2.10 instead [Ticket #6211]', false);

echo "\n\n** Other settings: **\n\n";

echo sprintf('  Open base dir directive is: %s', ini_get('open_basedir')) . "\n";
echo sprintf('  Disabled functions: %s', ini_get('disable_functions')) . "\n";
echo sprintf('  Disabled classes: %s', ini_get('disable_classes')) . "\n";
echo sprintf('  Memory limit: %s', ini_get('memory_limit')) . "\n";
echo sprintf('  Max execution time: %s', ini_get('max_execution_time')) . "\n";
echo sprintf('  Post max size: %s', ini_get('post_max_size')) . "\n";

echo "\n** Loaded extensions: **\n\n";

$extensions = get_loaded_extensions();
asort($extensions);

$i = 1;
foreach($extensions as $extension)
{
  echo sprintf('  %s', $extension);
  if($i % 10 == 0)
  {
    echo "\n";
  }
  $i++;
}

if(function_exists('gd_info'))
{
  echo "\n\n** GD library: **\n\n";
  foreach(gd_info() as $k => $v)
  {
    echo sprintf('  %s: %s', $k,
            is_bool($v) ? ($v ? 'true' : 'false') : $v)
                     . "\n";
  }
}

if(!is_cli())
{
  echo '</pre></body></html>';
}
