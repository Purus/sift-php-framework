<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function tree_include_javascripts($options = array())
{
  $options = _parse_attributes($options);
  
  use_jquery();
  use_jquery_ui();
  // use_javascript('jquery/jquery.cookie.js');
  use_javascript('jquery/tree/jquery.jstree.js');

  //
  // use_javascript('jquery/tree/jquery.tree.min.js');
  // use_javascript('jquery/tree/lib/jquery.cookie.js');
  // use_javascript('jquery/tree/plugins/jquery.tree.cookie.js');
  // use_javascript(sfConfig::get('sf_admin_web_dir') . '/js/tree.js');
  
  if($context_menu = _get_option($options, 'context_menu'))
  {
   // use_javascript('jquery/tree/plugins/jquery.tree.contextmenu.js');
  }

  if($checkbox = _get_option($options, 'checkbox'))
  {
    use_javascript('jquery/tree/jquery.jstree.mycheckbox.js');
  }

}

/**
 * Outputs tree toolbar for tree object
 *
 * <div class="tree-toolbar">
 *   <button onclick='$.tree.focused().open_all();'><span>Open all</span></button>
 *   <button onclick='$.tree.focused().close_all();'><span>Close all</span></button>
 * </div>
 * 
 * @param <type> $options
 * @return <type> string
 */
function tree_toolbar($options = array())
{
  $options = _parse_attributes($options);
  $html    = array();

  $html[]  = submit_tag(__('Open all'), array('onclick' => '$.tree.focused().open_all();'));
  $html[]  = submit_tag(__('Close all'), array('onclick' => '$.tree.focused().close_all();'));

  // $html[]  = submit_tag(__('Add new root category'), array('onclick' => '$.tree.focused()'));
  // $html[]  = submit_tag(__('Add new subcategory'),   array('onclick' => '$.tree.focused()'));

  if(!isset($options['class']))
  {
    $options['class'] = 'tree-toolbar';
  }
  
  $html = content_tag('div', join("\n", $html), $options);
  return $html;
}

function tree_setup($refresh_url, $options = array())
{
  return javascript_tag(sprintf("AdminTreeControl.setRefreshUrl('%s');", $refresh_url));
}

/**
 * Returns a list html tag.
 *
 * @param object An object or the selected value
 * @param string An object column.
 * @param array Input options (related_class option is mandatory).
 * @param bool Input default value.
 *
 * @return string A list string which represents an input tag.
 *
 */
function tree_select_tag($name, $object, $method = 'getId', $options = array(), $default_value = null)
{
  $options['related_class'] = get_class($object);
  $options['include_blank'] = true;
  $options['with_i18n']     = true;
  // control name
  $options['control_name'] = $name;

  // css class
  if(!isset($options['class']))
  {
    $options['class'] = 'tree';
  }

  return object_select_tag($object, $method, $options, $default_value);
}