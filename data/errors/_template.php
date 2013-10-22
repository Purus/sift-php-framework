<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<meta name="title" content="<?php echo htmlspecialchars($header); ?>" />
<title><?php echo htmlspecialchars($header); ?></title>
<style type="text/css">
<?php echo file_get_contents(dirname(__FILE__) . '/css/error.min.css'); ?>
</style>
</head>
<body class="<?php echo $class; ?>">
  <h1><span><?php echo htmlspecialchars($header); ?></span></h1>
  <div id="message">
  <?php foreach($i18n as $msg): ?>
  <p>
    <?php echo $msg; ?>
  </p>
  <?php endforeach; ?>
  </div>
  </body>
</html>