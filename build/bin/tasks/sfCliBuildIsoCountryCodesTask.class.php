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
    $dataSourceDir = $this->environment->get('build_data_dir');
    $isoDatabase = $dataSourceDir. '/country_names_and_code_elements.txt';
    $isoClass = $this->environment->get('sf_sift_lib_dir') . '/i18n/iso/sfISO3166.class.php';

    $iso = explode("\n", file_get_contents($isoDatabase));

    $countries = array();
    foreach($iso as $line)
    {
      $line = trim($line);

      if(empty($line))
      {
        continue;
      }

      list($name, $code) = explode(';', $line, 2);

      $name = str_replace('\\\'', '\'', trim($name, '\''));

      if(empty($name) || strlen($code) !== 2)
      {
        continue;
      }

      $countries[$code] = sfUtf8::ucwords(sfUtf8::lower($name));
    }

    ksort($countries);

    $php = '';
    foreach($countries as $code => $name)
    {
      $php .= sprintf("    '%s', // %s\n", $code, $name);
    }

    $content = preg_replace('/protected static \$countries = array *\(.*?\);/s',
        sprintf("protected static \$countries = array(\n%s  );", $php), file_get_contents($isoClass));

    file_put_contents($isoClass, $content);

    $this->logSection($this->getFullName(), sprintf("Found %s country codes.", count($countries)));

  }

}
