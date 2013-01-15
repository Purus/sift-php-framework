<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
use_helper('Form', 'Javascript', 'Helper', 'I18N');
 
/**
 * ObjectHelper for admin generator
 *
 * @package    Sift
 * @subpackage helper
 */
 
 
function object_admin_input_file_tag($object, $method, $options = array())
{
  $options = _parse_attributes($options);
  $name = _convert_method_to_name($method, $options);

  $html = '';

  $value = _get_object_value($object, $method);

  if($value)
  {
    if($include_link = _get_option($options, 'include_link'))
    {
      $include_link = sfToolkit::replaceConstants($include_link);      
      if($object->getTable()->hasTemplate('Doctrine_Template_DataStorage'))
      {
        // FIXME: MAKE THIS WORK
        $html .= 'doctrine template data storage - not implemented';
      }
      else
      {
        if($include_link[0] != '/')
        {
          $image_path = image_path('/' . sfConfig::get('sf_upload_dir_name') . '/' . $include_link . '/' . $value);
        }
        else
        {
          $image_path = image_path($include_link . '/' . $value);
        }
        
        $image_text = ($include_text = _get_option($options, 'include_text')) ? __($include_text) : __('show file');
        $html .= sprintf('<a onclick="window.open(this.href);return false;" href="%s">%s</a>', $image_path, $image_text) . "\n";
      }
    }

    if($include_remove = _get_option($options, 'include_remove'))
    {
      $html .= checkbox_tag(strpos($name, ']') !== false ? substr($name, 0, -1) . '_remove]' : $name) . ' ' . ($include_remove !== true ? __($include_remove) : __('remove file')) . "\n";
    }
  }

  // redefine input className
  $options['class'] = 'upload';

  sfContext::getInstance()->getResponse()->addJavascript(sfConfig::get('sf_admin_web_dir') . '/js/upload.js');

  return input_file_tag($name, $options) . "\n<br />" . $html;
}

function object_admin_input_file_tag_old($object, $method, $options = array())
{
  $options = _parse_attributes($options);
  $name = _convert_method_to_name($method, $options);

  $html = '';

  $value = _get_object_value($object, $method);

  if($value)
  {
    if($include_link = _get_option($options, 'include_link'))
    {
      unset($options['include_link']);
      $image_path = image_path('/' . sfConfig::get('sf_upload_dir_name') . '/' . $include_link . '/' . $value);
      $image_text = ($include_text = _get_option($options, 'include_text')) ? __($include_text) : __('[show file]');

      $html .= sprintf('<a onclick="window.open(this.href);return false;" href="%s">%s</a>', $image_path, $image_text) . "\n";
    }

    if($include_remove = _get_option($options, 'include_remove'))
    {
      $html .= checkbox_tag(strpos($name, ']') !== false ? substr($name, 0, -1) . '_remove]' : $name) . ' ' . ($include_remove !== true ? __($include_remove) : __('remove file')) . "\n";
    }
  }

  // unset invalid html options
  if(isset($options['include_link']))
  {
    unset($options['include_link']);
  }
  // unset invalid html options
  if(isset($options['include_remove']))
  {
    unset($options['include_remove']);
  }

  // redefine input className
  $options['class'] = 'upload';

  return input_file_tag($name, $options) . "\n<br />" . $html;
}

function object_admin_double_list($object, $method, $options = array(), $callback = null)
{

  $options = _parse_attributes($options);

  $options['multiple'] = true;
  $options['class'] = 'multiple';
  if(!isset($options['size']))
  {
    $options['size'] = 10;
  }

  if(isset($options['unassociated_label']))
  {
    $label_all = __($options['unassociated_label']);
    unset($options['unassociated_label']);
  }
  else
  {
    $label_all = __('Unassociated', array(), 'myAdmin/messages');
  }

  if(isset($options['associated_label']))
  {
    $label_assoc = __($options['associated_label']);
    unset($options['associated_label']);
  }
  else
  {
    $label_assoc = __('Associated', array(), 'myAdmin/messages');
  }

  // get the lists of objects
  list($all_objects, $objects_associated, $associated_ids) = _get_object_list($object, $method, $options, $callback);

  $objects_unassociated = array();
  foreach($all_objects as $object)
  {
    if(!in_array($object->getPrimaryKey(), $associated_ids))
    {
      $objects_unassociated[] = $object;
    }
  }

  // remove non html option
  unset($options['through_class']);
  // override field name
  unset($options['control_name']);
  $name = _convert_method_to_name($method, $options);
  $name1 = 'unassociated_' . $name;
  $name2 = 'associated_' . $name;
  $select1 = select_tag($name1, options_for_select(_get_options_from_objects($objects_unassociated), '', $options), $options);
  $options['class'] = 'multiple-selected';
  $select2 = select_tag($name2, options_for_select(_get_options_from_objects($objects_associated), '', $options), $options);

  $html =
          '<table class="admin-double-list">
  <tr>
    <td>
    <h4>%s</h4>
      %s
    </td>
    <td class="admin-double-list-buttons">
     <p>%s</p>
     <p>%s</p>
    </td>
    <td>
      <h4>%s</h4>
      %s
    </td>
  </tr>
</table>
';
  
  $response = sfContext::getInstance()->getResponse();
  $response->addJavascript('/js/jquery/multiselects/jquery.multiselects.js');
  $response->addJavascript(sfConfig::get('sf_admin_web_dir') . '/js/double_list');

  return sprintf($html,
          $label_all,
          $select1,
          submit_tag(__('move left', array(), 'myAdmin/messages'), "type=button class=move-left"),
          submit_tag(__('move right', array(), 'myAdmin/messages'), "type=button class=move-right"),
          $label_assoc,
          $select2
  );
}

function object_admin_select_list($object, $method, $options = array(), $callback = null)
{
  $options = _parse_attributes($options);
  $options['multiple'] = true;
  if(!isset($options['size']))
  {
    $options['size'] = 10;
  }

  // get the lists of objects
  list($objects, $objects_associated, $ids) = _get_object_list($object, $method, $options, $callback);
  // remove non html option
  unset($options['through_class']);
  // override field name
  unset($options['control_name']);
  $name = 'associated_' . _convert_method_to_name($method, $options);

  if(isset($options['associated_label']))
  {
    unset($options['associated_label']);
  }

  if(isset($options['unassociated_label']))
  {
    unset($options['unassociated_label']);
  }


  return content_tag('div', select_tag($name, options_for_select(_get_options_from_objects($objects), $ids, $options), $options), array('class' => 'admin-select-list'));
}

function object_admin_check_list($object, $method, $options = array(), $callback = null)
{
  $options = _parse_attributes($options);

  // get the lists of objects
  list($objects, $objects_associated, $assoc_ids) = _get_object_list($object, $method, $options, $callback);

  // override field name
  unset($options['control_name']);
  $name = 'associated_' . _convert_method_to_name($method, $options) . '[]';
  $html = '';

  if(!empty($objects))
  {
    // which method to call?
    $methodToCall = '__toString';
    foreach(array('__toString', 'toString', 'getName', 'getTitle', 'getId') as $method)
    {
      if(method_exists($objects[0], $method))
      {
        $methodToCall = $method;
        break;
      }
    }

    $html .= "<ul>\n";
    foreach($objects as $related_object)
    {
      $relatedPrimaryKey = $related_object->getPrimaryKey();

      // multi primary key handling
      if(is_array($relatedPrimaryKey))
      {
        $relatedPrimaryKeyHtmlId = implode('/', $relatedPrimaryKey);
      }
      else
      {
        $relatedPrimaryKeyHtmlId = $relatedPrimaryKey;
      }

      $html .= '<li>' . checkbox_tag($name, $relatedPrimaryKeyHtmlId, in_array($relatedPrimaryKey, $assoc_ids)) . ' <label for="' . get_id_from_name($name, $relatedPrimaryKeyHtmlId) . '">' . $related_object->$methodToCall() . "</label></li>\n";
    }
    $html .= "</ul>\n";

    $html = content_tag('div', $html, array('class' => 'admin-check-list'));
  }

  return $html;
}

/**
 * _get_doctrine_object_list
 *
 * @param string $object
 * @param string $method
 * @param string $options
 * @return void
 */
function _get_doctrine_object_list($object, $method, $options)
{
  if(!isset($options['relation_name']))
  {
    $name = substr($method[0], 3);
    if($object->getTable()->hasRelation($name))
    {
      $options['relation_name'] = $name;
    }
    else
    {
      throw new sfConfigurationException(sprintf('Cannot detect relation name from method "%s". Please specify option "relation_name" in your generator.yml', $method[0]));
    }
  }

  $foreignTable = $object->getTable()->getRelation($options['relation_name'])->getTable();
  $foreignClass = $foreignTable->getComponentName();

  if(isset($options['dql']))
  {
    $dql = $options['dql'];
    unset($options['dql']); // Otherwise it will show up in the html

    $allObjects = $foreignTable->findByDQL($dql);
  }
  elseif(isset($options['all_objects_class']))
  {
    $class = $options['all_objects_class'];
    unset($options['all_objects_class']); // Otherwise it will show up in the html
    $allObjects = Doctrine::getTable($class)->findAll();
  }
  elseif(isset($options['find_method']))
  {
    $find_method = $options['find_method'];
    unset($options['find_method']); // Otherwise it will show up in the html
    $allObjects = $foreignTable->$find_method();
  }
  else
  {
    $allObjects = $foreignTable->findAll();
  }

  $associatedObjects = _get_object_value($object, $method);

  $ids = array();
  foreach($associatedObjects as $associatedObject)
  {
    $ids[] = $associatedObject->identifier();
  }

  if($associatedObjects instanceof Doctrine_Collection && $associatedObjects->count() === 0)
  {
    $associatedObjects = null;
  }

  return array($allObjects, $associatedObjects, $ids);
}

function _get_object_list($object, $method, $options, $callback)
{
  $object = $object instanceof sfOutputEscaper ? $object->getRawValue() : $object;

  // the default callback is the propel one
  if(!$callback)
  {
    $callback = '_get_doctrine_object_list';
  }

  return call_user_func($callback, $object, $method, $options);
}
