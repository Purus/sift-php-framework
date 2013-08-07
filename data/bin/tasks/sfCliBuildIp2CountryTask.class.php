<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds Ip2country database
 *
 * @package Sift
 * @subpackage build
 */
class sfCliBuildIp2CountryTask extends sfCliBaseBuildTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = '';
    $this->name = 'ip2country';
    $this->briefDescription = 'Builds ip2country database';

    $this->detailedDescription = <<<EOF
The [ip2country|INFO] task builds ip2country database

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $result = $this->askConfirmation('This task is a wholeday task. Continue?');

    if($result)
    {
      $this->logSection($this->getFullName(), 'Building IP to country IP database');
      $this->logSection($this->getFullName(), 'Please wait. This takes a long time...');
      $this->logSection($this->getFullName(), 'Go out for couple of hours... Seriously');
      $this->build();
      $this->logSection($this->getFullName(), 'Done.');
    }
    else
    {
      $this->logSection($this->getFullName(), 'Aborted.');
    }
  }

  protected function build()
  {
    $csvDatabase = realpath($this->environment->get('build_data_dir') . '/IpToCountry.csv');
    $database = $this->environment->get('sf_sift_data_dir').'/data/ip2country.db';

    if(is_readable($database))
    {
      unlink($database);
    }

    $db = new sfPDO(sprintf('sqlite:%s', $database));

    $statements = array(
      'CREATE TABLE [ip2country] (
        [ip_from]  INTEGER UNSIGNED,
        [ip_to]    INTEGER UNSIGNED,
        [code]  CHAR(3)
      )',
      'CREATE INDEX [from_idx] ON [ip2country] ([ip_from])',
      'CREATE INDEX [to_idx] ON [ip2country] ([ip_to])'
    );

    foreach($statements as $statement)
    {
      if(!$db->query($statement))
      {
        throw new Exception($db->lastError());
      }
    }

    $i = $invalid = 0;
    $f = fopen($csvDatabase, 'r');

    while(!feof($f))
    {
      $s = fgets($f);
      if(substr($s, 0, 1) == '#')
      {
        continue;
      }

      $temp = explode(',', $s);
      if(count($temp) < 7)
      {
        continue;
      }

      list($from, $to,,,$code) = $temp;

      $from = trim($from, '"');
      $to = trim($to, '"');
      $code = trim($code, '"');

      if(!sfISO3166::isValidCode($code))
      {
        $invalid++;
        continue;
      }

      $stm = $db->prepare('INSERT INTO ip2country VALUES(?, ?, ?)');
      $stm->bindParam(1, $from);
      $stm->bindParam(2, $to);
      $stm->bindParam(3, $code);
      $stm->execute();

      $i++;

      if($i % 100 === 0)
      {
        $this->logSection($this->getFullName(), sprintf('Inserted %s records.', $i));
      }
    }

    $this->logSection($this->getFullName(), sprintf('Inserted %s records.', $i));
    $this->logSection($this->getFullName(), sprintf('Skipped %s invalid records.', $invalid));
  }

}
