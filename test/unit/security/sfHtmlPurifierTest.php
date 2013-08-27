<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

sfConfig::set('sf_cache_dir', sys_get_temp_dir());
sfConfig::set('sf_charset', 'utf-8');

class myHtmlPurifier extends sfHtmlPurifier {

  public function loadSettings()
  {
    $y = new sfYaml();
    $settings = $y->load(dirname(__FILE__).'/fixtures/sanitize.yml');

    $all = array();
    if(isset($settings['all']))
    {
      $all = $this->replaceConstants($settings['all']);
      unset($settings['all']);
    }

    foreach($settings as $section => $value)
    {
      $settings[$section] = sfToolkit::arrayDeepMerge($all, $this->replaceConstants($value));
    }
    return $settings;
  }

  protected function replaceConstants($value)
  {
    if(is_array($value))
    {
      array_walk_recursive($value, create_function('&$value', '$value = myHtmlPurifier::_replaceConstants($value);'));
    }
    else
    {
      $value = myHtmlPurifier::_replaceConstants($value);
    }
    return $value;
  }

  /**
   * Replaces constant identifiers in a scalar value.
   *
   * @param string the value to perform the replacement on
   * @return string the value with substitutions made
   */
  public static function _replaceConstants($value)
  {
    return is_string($value) ? preg_replace_callback('/%(.+?)%/', create_function('$v', 'return sfConfig::has(strtolower($v[1])) ? sfConfig::get(strtolower($v[1])) : "%{$v[1]}%";'), $value) : $value;
  }

}

$value = 'Testing a value with <iframe src="http://hack.com />';

$p = new myHtmlPurifier();
$t->isa_ok($p->purify($value), 'string', 'sanitize() returns string');
$t->is($p->purify(array($value)), array('Testing a value with '), 'sanitize() returns string');

