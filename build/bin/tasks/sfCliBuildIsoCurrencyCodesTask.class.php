<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds ISO 4217 currency codes
 *
 * @package Sift
 * @subpackage build
 */
class sfCliBuildIsoCurrencyCodesTask extends sfCliBaseBuildTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->aliases = array('currencies');
    $this->namespace = '';
    $this->name = 'currency-codes';
    $this->briefDescription = 'Builds currency codes definitions (ISO 4217)';

    $this->detailedDescription = <<<EOF
The [currency-codes|INFO] task builds currency codes (ISO 4217) definitions

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->buildCurrencyCodes();
    $this->logSection($this->getFullName(), 'Done.');
  }

  protected function buildCurrencyCodes()
  {
    $dataSourceDir = $this->environment->get('build_data_dir');
    $isoDatabase = $dataSourceDir. '/iso_4217.xml';
    $isoClass = $this->environment->get('sf_sift_lib_dir') . '/i18n/iso/sfISO4217.class.php';

    $xml = simplexml_load_file($isoDatabase);

    $currencies = array();
    foreach($xml->xpath('/ISO_4217/CcyTbl/CcyNtry') as $line)
    {
      $code = (string)current($line->xpath('Ccy'));
      $name = (string)current($line->xpath('CcyNm'));

      if(strlen($code) !== 3)
      {
        continue;
      }

      $currencies[$code] = sfUtf8::ucwords(sfUtf8::lower($name));
    }

    ksort($currencies);

    $php = '';
    foreach($currencies as $code => $name)
    {
      $php .= sprintf("    '%s', // %s\n", $code, $name);
    }

    $content = preg_replace('/protected static \$currencies = array *\(.*?\);/s',
        sprintf("protected static \$currencies = array(\n%s  );", $php), file_get_contents($isoClass));

    file_put_contents($isoClass, $content);

    $this->logSection($this->getFullName(), sprintf("Found %s currency codes.", count($currencies)));
  }

}
