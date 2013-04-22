<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Table formatter
 *
 * @package    Sift
 * @subpackage form
 */
class sfWidgetFormSchemaFormatterTable extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "<tr>\n  <th>%label%</th>\n  <td>%error%%field%%help%%hidden_fields%</td>\n</tr>\n",
    $errorRowFormat  = "<tr><td colspan=\"2\">\n%errors%</td></tr>\n",
    $helpFormat      = '<br />%help%',
    $decoratorFormat = "<table>\n  %content%</table>";
}
