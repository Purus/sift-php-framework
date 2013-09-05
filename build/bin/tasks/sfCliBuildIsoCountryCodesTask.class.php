<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds ISO 3166 country codes
 *
 * @package Sift
 * @subpackage build
 */
class sfCliBuildIsoCountryCodesTask extends sfCliBaseBuildTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->aliases = array('countries');
    $this->namespace = '';
    $this->name = 'country-codes';
    $this->briefDescription = 'Builds country codes definitions (ISO 3166)';

    $this->detailedDescription = <<<EOF
The [country-codes|INFO] task builds country codes (ISO 3166) definitions

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->buildCountryCodes();
    $this->logSection($this->getFullName(), 'Done.');
  }

  protected function buildCountryCodes()
  {
    // update!
    $euCountriesAlpha2 = array('AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR',
                               'GB', 'GR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL',
                               'PT', 'RO', 'SE', 'SI', 'SK');

    $dataSourceDir = $this->environment->get('build_data_dir');
    $isoDatabase = $dataSourceDir. '/wikipedia-iso-country-codes.csv';
    $isoClass = $this->environment->get('sf_sift_lib_dir') . '/i18n/iso/sfISO3166.class.php';

    // we do not check for errors!
    $handle = fopen($isoDatabase, 'r');
    while(($data = fgetcsv($handle, 1000, ',')) !== false)
    {
      $iso[] = $data;
    }
    fclose($handle);

    $countriesAlpha2Code = $countriesAlpha3Code = $alpha2Alpha3Map = $alpha3Alpha2Map = $euCountriesAlpha3 = array();

    foreach($iso as $line)
    {
      // English short name lower case,Alpha-2 code,Alpha-3 code,Numeric code,ISO 3166-2
      list($name, $alpha2code, $alpha3code) = $line;

      if(empty($name) || strlen($alpha2code) !== 2 || strlen($alpha3code) !== 3)
      {
        continue;
      }

      $name = sfUtf8::ucwords(sfUtf8::lower($name));

      $alpha2Alpha3Map[$alpha2code] = $alpha3code;
      $alpha3Alpha2Map[$alpha3code] = $alpha2code;

      $countriesAlpha2Code[$alpha2code] = $name;
      $countriesAlpha3Code[$alpha3code] = $name;

      if(in_array($alpha2code, $euCountriesAlpha2))
      {
        $euCountriesAlpha3[] = $alpha3code;
      }
    }

    ksort($countriesAlpha2Code);
    ksort($countriesAlpha3Code);

    $alpha2 = '';
    foreach($countriesAlpha2Code as $code => $name)
    {
      $alpha2 .= sprintf("    '%s', // %s\n", $code, $name);
    }

    $alpha3 = '';
    foreach($countriesAlpha3Code as $code => $name)
    {
      $alpha3 .= sprintf("    '%s', // %s\n", $code, $name);
    }

    $alpha2ToAlpha3 = '';
    foreach($alpha2Alpha3Map as $code2 => $code3)
    {
      $alpha2ToAlpha3 .= sprintf("    '%s' => '%s',\n", $code2, $code3);
    }

    $alpha3ToAlpha2 = '';
    foreach($alpha3Alpha2Map as $code3 => $code2)
    {
      $alpha3ToAlpha2 .= sprintf("    '%s' => '%s',\n", $code3, $code2);
    }

    $euAlpha2 = '';
    foreach($euCountriesAlpha2 as $code)
    {
      $euAlpha2 .= sprintf("    '%s',\n", $code);
    }

    $euAlpha3 = '';
    foreach($euCountriesAlpha3 as $code)
    {
      $euAlpha3 .= sprintf("    '%s',\n", $code);
    }

    $fileContents = file_get_contents($isoClass);

    $content = preg_replace('/protected static \$countriesAlpha2 = array *\(.*?\);/s',
        sprintf("protected static \$countriesAlpha2 = array(\n%s  );", $alpha2), $fileContents);

    $content = preg_replace('/protected static \$countriesAlpha3 = array *\(.*?\);/s',
        sprintf("protected static \$countriesAlpha3 = array(\n%s  );", $alpha3), $content);

    $content = preg_replace('/protected static \$alpha2ToAlpha3Map = array *\(.*?\);/s',
        sprintf("protected static \$alpha2ToAlpha3Map = array(\n%s  );", $alpha2ToAlpha3), $content);

    $content = preg_replace('/protected static \$alpha3ToAlpha2Map = array *\(.*?\);/s',
        sprintf("protected static \$alpha3ToAlpha2Map = array(\n%s  );", $alpha3ToAlpha2), $content);

    $content = preg_replace('/protected static \$euCountriesAlpha2 = array *\(.*?\);/s',
        sprintf("protected static \$euCountriesAlpha2 = array(\n%s  );", $euAlpha2), $content);

    $content = preg_replace('/protected static \$euCountriesAlpha3 = array *\(.*?\);/s',
        sprintf("protected static \$euCountriesAlpha3 = array(\n%s  );", $euAlpha3), $content);

    file_put_contents($isoClass, $content);

    $this->logSection($this->getFullName(), sprintf("Found %s country codes.", count($countriesAlpha2Code)));
  }

}
