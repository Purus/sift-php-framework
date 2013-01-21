<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// stop here if ajax!
if(sfError::isAjaxRequest())
{
  exit;
}

// init
$path = sfError::getPath();

class sfError {

  public static function getWebmasterEmail()
  {
    $email = sfConfig::get('app_webmaster_email', sfConfig::get('sf_webmaster_email', ''));
    if(is_array($email))
    {
      return $email[0];
    }
    return $email;
  }

  public static function getPath()
  {
    if(!class_exists('sfConfig'))
    {
      return '';
    }
    return sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : '')));
  }

  public static function loadTranslation($error = 'error500')
  {
    // default language file
    $i18n = 'en';
    // try to detect user culture
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
      $languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
      $values = array();
      foreach(array_filter(explode(',', $languages)) as $value)
      {
        // Cut off any q-value that might come after a semi-colon
        if($pos = strpos($value, ';'))
        {
          $q = (float) trim(substr($value, $pos + 3));
          $value = trim(substr($value, 0, $pos));
        }
        else
        {
          $q = 1;
        }
        $values[$value] = $q;
      }
      arsort($values);

      $languages = array_keys($values);
      $cultures = array();
      foreach($languages as $lang)
      {
        if(strstr($lang, '-'))
        {
          $codes = explode('-', $lang);
          if($codes[0] == 'i')
          {
            // Language not listed in ISO 639 that are not variants
            // of any listed language, which can be registerd with the
            // i-prefix, such as i-cherokee
            if(count($codes) > 1)
            {
              $lang = $codes[1];
            }
          }
          else
          {
            for($i = 0, $max = count($codes); $i < $max; $i++)
            {
              if($i == 0)
              {
                $lang = strtolower($codes[0]);
              }
              else
              {
                $lang .= '_' . strtoupper($codes[$i]);
              }
            }
          }
        }
        if(is_readable(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . $error . DIRECTORY_SEPARATOR . $lang . '.php'))
        {
          $i18n = $lang;
          break;
        }
      }
    }

    // load culture i18n
    return include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . $error . DIRECTORY_SEPARATOR . $i18n . '.php';
  }

  public static function isAjaxRequest()
  {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;
  }

}