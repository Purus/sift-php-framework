<?php
// auto-generated by sfValidatorConfigHandler
// date: 2013/01/15 14:51:20

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
  $validators = array();
  $context->getRequest()->setAttribute('fillin', array (
  'enabled' => true,
), 'sift/filter');
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  $validators = array();
  $validatorManager->registerName('fake', 1, 'Required', null, null, false);
  $validatorManager->registerName('id', 1, 'Required', null, null, false);
  $validatorManager->registerName('article[title]', 1, 'Required', null, null, false);
  $validatorManager->registerName('article[body]', 1, 'Required', null, null, false);
  $context->getRequest()->setAttribute('fillin', array (
  'enabled' => true,
), 'sift/filter');
}
