<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfWidgetFormSchemaFormatterPlain forms the form using plain layout.
 *
 * @package    Sift
 * @subpackage form
 * @author     Mishal.cz <mishal at mishal dot cz>
 */
class sfWidgetFormSchemaFormatterPlain extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "%label%<br /> %help%\n  %error%\n  %field%<br />\n  \n%hidden_fields%\n",
    $errorRowFormat  = "%errors%\n",
    $errorListFormatInARow = "%errors%\n",       
    $errorRowFormatInARow =  "%error%",
    $helpFormat      = '%help% <br />',
    $decoratorFormat = "\n    %content%",
    $namedErrorRowFormatInARow = "%name%: %error%<br />\n";
}
