<?php

require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');

$t = new lime_test(2);

$t->diag('->render()');

sfConfig::set('sf_i18n_enabled_cultures', array('en', 'cs'));


$w = new sfWidgetFormI18nChoiceEnabledLanguages();

$t->is(array_keys($w->getOption('choices')), array('en', 'cs'), 'returns enabled cultures');
$t->is($w->render('lang'), fix_linebreaks('<select name="lang" id="lang">
<option value="en">English</option>
<option value="cs">Czech</option>
</select>'), '->render() renders the widget correctly');

