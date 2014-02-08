<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base task for i18n tasks
 *
 * @package    Sift
 * @subpackage cli_task
 */
abstract class sfCliI18nBaseTask extends sfCliBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('app', sfCliCommandArgument::REQUIRED, 'The application or plugin name'),
      new sfCliCommandArgument('culture', sfCliCommandArgument::REQUIRED, 'The target culture'),
    ));
  }

}
