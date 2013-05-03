<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ValidationHelper.
 *
 * @package    Sift
 * @subpackage helper
 * @deprecated
 */

function form_has_error($param)
{
  return sfContext::getInstance()->getRequest()->hasError($param);
}

function form_error($param, $options = array(), $catalogue = 'messages')
{
  $param_for_sf = str_replace(array('[', ']'), array('{', '}'), $param);
  $param = str_replace(array('{', '}'), array('[', ']'), $param);

  $options = _parse_attributes($options);

  $request = sfContext::getInstance()->getRequest();

  $style = $request->hasError($param_for_sf) ? '' : 'display:none;';
  $options['style'] = $style.(isset($options['style']) ? $options['style']:'');

  if (!isset($options['class']))
  {
    $options['class'] = sfConfig::get('sf_validation_error_class', 'form_error');
  }
  if (!isset($options['id']))
  {
    $options['id'] = sfConfig::get('sf_validation_error_id_prefix', 'error_for_').get_id_from_name($param);
  }

  $prefix = sfConfig::get('sf_validation_error_prefix', '');
  if (isset($options['prefix']))
  {
    $prefix = $options['prefix'];
    unset($options['prefix']);
  }

  $suffix = sfConfig::get('sf_validation_error_suffix', '');
  if (isset($options['suffix']))
  {
    $suffix = $options['suffix'];
    unset($options['suffix']);
  }

  $error = $request->getError($param_for_sf, $catalogue);

  return content_tag('div', $prefix.$error.$suffix, $options)."\n";
}
