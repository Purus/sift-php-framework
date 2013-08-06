<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaFormatterUnorderedList class
 *
 * @package    Sift
 * @subpackage form_formatter
 */
class sfWidgetFormSchemaFormatterUnorderedList extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "<li>%label%   %field%%hidden_fields%</li>\n",
    $errorRowFormat  = '',
    $helpFormat      = '<br />%help%',
    $decoratorFormat = '';
}
