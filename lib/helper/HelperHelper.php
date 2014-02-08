<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * HelperHelper.
 *
 * @package    Sift
 * @subpackage helper
 */
function use_helper()
{
  sfLoader::loadHelpers(func_get_args(), sfContext::getInstance()->getModuleName());
}
