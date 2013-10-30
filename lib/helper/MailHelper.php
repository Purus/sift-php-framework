<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mail helper functions
 *
 * @package    Sift
 * @subpackage helper_mail
 */
 
/**
 * Embeds given image to message and returns its source (cid:xyz@server)
 *
 * @param string $path
 * @param sfMailerMessage $message
 * @return sfMailer
 */
function mail_embeded_image_src($path, sfMailerMessage $message)
{
  if(!sfToolkit::isPathAbsolute($path))
  {
    $path = sfConfig::get('sf_data_dir') . '/email/images/' . $path;
  }

  return $message->embedImage($path);
}

/**
 * Attaches file to message
 *
 * @param string $path
 * @param sfMailerMessage $message
 * @param string $filename
 * @param string $contentType
 * @param string $disposition
 * @return sfMailer
 */
function mail_attach_file($path, sfMailerMessage $message, $filename = null, $contentType = null, $disposition = null)
{
  return $message->attachFromPath($path, $filename, $contentType, $disposition);
}

/**
 * Returns image tag for mail usage (embeds image to message)
 * 
 * @param string $path
 * @param array $options
 * @return string
 */
function mail_image_tag($path, sfMailerMessage $message, $options = array())
{
  $options = _parse_attributes($options);

  if(!isset($options['alt']))
  {
    $alt = basename($path);
    $ext = strrchr($alt, '.');
    if($ext !== false)
    {
      $alt = substr($alt, 0, -strlen($ext));
    }
    $options['alt'] = $alt;
  }

  return image_tag(mail_embeded_image_src($path, $message), $options);
}

/**
 * Returns mail signature if present, site title otherwise.
 * Filters the value using event named "mailer.message.site_signature".
 *
 * @return string
 */
function mail_get_site_signature()
{
  $signature = sfConfig::get('app_mail_site_signature');
  
  if(!$signature)
  {
    $signature = sfConfig::get('app_title_name');
  }
  
  return sfCore::getEventDispatcher()
          ->filter(new sfEvent('mailer.message.site_signature'), $signature)
          ->getReturnValue();
}
