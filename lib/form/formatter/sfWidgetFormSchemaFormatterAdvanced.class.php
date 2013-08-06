<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaFormatter allows to format a form schema with HTML formats.
 *
 * @package    Sift
 * @subpackage form_formatter
 */
class sfWidgetFormSchemaFormatterAdvanced extends sfWidgetFormSchemaFormatterDiv
{
  protected
    $rowFormat                 = '',
    $helpFormat                = '<div class="form-help"><i class="icon-lightbulb"></i> %help%</div>',
    $errorRowFormat            = '%errors%',
    $errorListFormatInARow     = '<label class="form-error" for="%field_id%" generated="true" role="alert">%errors%</label>',
    $errorRowFormatInARow      = '%error%',
    $namedErrorRowFormatInARow = '%name%: %error%';

}
