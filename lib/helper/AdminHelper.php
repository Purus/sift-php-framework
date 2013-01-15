<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * AdminHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

use_helper('Object', 'ObjectAdmin', 'Validation', 'Date', 'I18N');

/**
 * Get controls for move up / move down for object. Used by admin generator
 *
 * @param Object $object
 * @param string $method
 * @param array $params
 * @return string
 */
function admin_object_sortable_list($object, $method, $params = array())
{
  $value = _get_object_value($object, $method);
  $module = $params['module'];

  $html   = array();    
  $html[] = '<div class="sorting">';

  if($object->getPrevious())
  {
    $html[] = link_to('<span>'.__('Up', array(), 'myAdmin/sortable').'</span>', $module.'/moveUp?id='.$object->getId(), array('class' => 'move-up', 'title' => __('Move up', array(), 'myAdmin/sortable')));
  }
  if($object->getNext())
  {
    $html[] = link_to('<span>'.__('Down', array(), 'myAdmin/sortable').'</span>', $module.'/moveDown?id='.$object->getId(), array('class' => 'move-down', 'title' => __('Move down', array(), 'myAdmin/sortable')));
  }

  $html[] = sprintf('<span class="sorting-value">%s</span>', $value);
  
  $html[] = '</div>';
  return join("\n", $html);
}

/**
 * 
 * @param object $object
 * @param string $method
 * @param array $params
 * @return string
 */
function admin_object_icon_preview_list($object, $method, $params = array())
{
  $html   = array();
  $html[] = 'Preview ikonky';
  return join("\n", $html);
}

function object_admin_tags_edit_tag($object, $method, $params = array())
{
  $control_name = $params['control_name'];
  unset($params['control_name']);

  $separator = ', ';
  if(isset($params['separator']))
  {
    $separator = $params['separator'];
    unset($params['separator']);
  }

  // object does not exist yet, so will get the request data!
  if(!$object->exists())
  {
    $tags = sfContext::getInstance()->getRequest()->getParameter($control_name);
  }
  else
  {
    $value = _get_object_value($object, $method);
    $tmp = array();

    foreach($value as $tag)
    {
      $tmp[] = $tag->getTag();
    }
    $tags = join($separator, $tmp);
  }

  // textarea size
  if(!isset($params['size']))
  {
    $params['size'] = '30x5';
  }

  // textarea class
  $params['class'] = 'tag-editor';

  // we need jquery
  /*
  use_jquery();
  use_javascript(sfConfig::get('sf_jquery_web_dir').'/jquery.jtags.js');
  use_javascript(sfConfig::get('sf_jquery_web_dir').'/jquery.tagSuggest.js');
  use_javascript(sfConfig::get('sf_admin_web_dir').'/js/tags.js');
  */
  
  return textarea_tag($control_name, $tags, $params);
}

/**
 *
 * @param <type> $object
 * @param <type> $method
 * @param <type> $params
 * @return <type> string
 * 
 */
function object_admin_title_mode_edit_tag($object, $method, $params = array())
{
  $value = _get_object_value($object, $method);

  $values = array(
    sfWebResponse::TITLE_MODE_APPEND =>  __('append', array(), 'myAdmin/seo'),
    sfWebResponse::TITLE_MODE_PREPEND => __('prepend', array(), 'myAdmin/seo'),
    sfWebResponse::TITLE_MODE_REPLACE => __('replace', array(), 'myAdmin/seo')
  );

  $control_name = $params['control_name'];
  unset($params['control_name']);

  $include_blank = true;
  if(isset($params['include_blank']))
  {
    $include_blank = (bool)$params['include_blank'];
    unset($params['include_blank']);
  }

  return select_tag($control_name, options_for_select($values, $value, array('include_blank' => $include_blank)), $params);  
}

function object_admin_tree_select($object, $method, $params = array())
{

  $id = get_id_from_name($params['control_name']);
  
  $values   = _get_object_value($object, $method);
  $selected = $selected_nodes = array();
  foreach($values as $value)
  {
    $selected_nodes[] = sprintf('node-%s', $value->getId());
    $selected         = $value->getId();
  }

  $html = array();
  $html[] = sprintf('<div id="%s" class="tree-manager">', $id);

  $module = 'myAdmin';
  if(isset($params['module']))
  {
    $module = $params['module'];
  }  
  $component = 'treeManager';
  if(isset($params['component']))
  {
    $component = $params['component'];
  }
  
  $html[] = get_component($module, $component, array_merge(array('selected' => $selected, 'selected_nodes' => $selected_nodes, 'full' => false, 'id' => $id), $params));
  $html[] = '</div>';
  return join("\n", $html);
}

function object_app_cultures_edit_tag($object, $method, $params = array())
{
  $value = _get_object_value($object, $method);
  
  $control_name = $params['control_name'];
  unset($params['control_name']);

  $cultures = array();
  foreach(sfConfig::get('sf_i18n_enabled_cultures', array()) as $culture)
  {
    $cultures[$culture] = format_language(substr($culture, 0, 2));
  }

  return select_tag($control_name, options_for_select($cultures, $value, $params), $params);
}

function object_admin_many_to_many_plain_list($object, $method, $params = array())
{
  
}

/**
 * Returns front application url
 * 
 * @return string
 */
function front_app_url()
{  
  $url    = 'http://' . sfContext::getInstance()->getRequest()->getHost();
  $script = $_SERVER['SCRIPT_NAME'];
  if($script == '/index.php')
  {
    $script = '/';
  }
  elseif(preg_match('|admin(_.*)?.php|i', $script, $matches))
  {
    $env = sfConfig::get('sf_environment');
    if($env != 'prod')
    {
      $script = '/index_' . $env . '.php';
    }
    else
    {
      $script = '/';
    }
  }
  // strip admin part if subdomain is used
  $url = str_replace('admin.', '', $url) . $script;  
  return $url;
}

/**
 * Returns maximum allowed file size to be uploaded
 * 
 * @param string $format Format to human readable string?
 * @return string|integer
 */
function get_max_file_upload_size($format = true)
{
  trigger_error('Deprecated usage of get_max_file_upload_size(). Use file_max_upload_size() is FileHelper.');
  use_helper('File');
  
  return file_max_upload_size($format);    
}

function format_mime_type($mime)
{
  trigger_error('Deprecated usage of format_mime_type(). Use file_mime_name() is FileHelper.');
  
  use_helper('File');
  
  return file_mime_name($mime);
}

function icon_for_mime_type($mime)
{
  // $mime = 'application/msword; charset=binary';
  static $knownTypes = array(
    'application/word' => 'document-word.png',
    'application/msword' => 'document-word.png',
    'application/vnd.ms-office' => 'document-word.png',
    'application/pdf'  => 'document-pdf-text.png',
    'application/x-pdf'  => 'document-pdf-text.png',
    'text/rtf'         => 'document-text.png',
    // ''                 => 'document-powerpoint.png'
    // 'application/zip'  => 'zip'
  );

  $parts = explode(';', $mime);
  $mime  = trim($parts[0]);

  $icon = 'document.png';

  if(isset($knownTypes[$mime]))
  {
    $icon = $knownTypes[$mime];
  }

  return image_tag(sfConfig::get('sf_admin_web_dir') . '/images/' . $icon);
}


function object_admin_culture_select_tag($object, $method, $params = array())
{
  return object_app_cultures_edit_tag($object, $method, $params);
}


