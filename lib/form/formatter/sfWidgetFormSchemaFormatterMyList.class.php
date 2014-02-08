<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaFormatterDiv forms the form using div layout.
 *
 * @package    Sift
 * @subpackage form_formatter
 */
class sfWidgetFormSchemaFormatterMyList extends sfWidgetFormSchemaFormatter
{
    protected $rowFormat = "<li class=\"form-element clear\">\n  %error%%label%\n  %field%%help%\n%hidden_fields%</li>\n",
        $errorRowFormat = "<li>\n%errors%</li>\n",
        $helpFormat = '<div class="help-wrap">%help%</div>',
        $decoratorFormat = "<ul class=\"form-elements\">\n  %content%</ul>";
}
