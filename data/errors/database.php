<?php
include dirname(__FILE__) . DIRECTORY_SEPARATOR . '_init.php';

$_i18n = sfError::loadTranslation('database');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="title" content="<?php echo $_i18n['error']; ?>" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?php echo $_i18n['error']; ?></title>
    <style type="text/css">
    <?php echo file_get_contents(dirname(__FILE__) . '/css/screen.css'); ?>
    </style>
  </head>
  <body>
    <div class="app_error">
      <h1><span><?php echo $_i18n['error']; ?></span></h1>
    </div>
    <div class="box">
      <p>
      <?php echo $_i18n['error_msg_1']; ?>
      </p>
    </div>
  </body>
</html>