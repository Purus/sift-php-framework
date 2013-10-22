<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="robots" content="noindex, noarchive">
<title><?php echo htmlspecialchars($message); ?></title>
<style type="text/css">
<?php echo file_get_contents(dirname(__FILE__) . '/exception.min.css') . "\n"; ?>
</style>
<script type="text/javascript">
<!--
<?php echo file_get_contents(dirname(__FILE__) . '/../../web/sf/js/jquery/jquery-1.10.2.min.js') . "\n"; ?>
<?php echo file_get_contents(dirname(__FILE__) . '/exception.min.js') . "\n"; ?>
//-->
</script>
</head>
<body>
  <div id="exception">
    <h1><?php echo $name ?></h1>
    <h2><?php echo nl2br(htmlspecialchars($message)); ?></h2>
    <h3><a href="#" class="exception-toggler active" data-target="#exception-backtrace">Stack trace <abbr></abbr></a></h3>
    <div id="exception-backtrace">
      <?php echo $debug_backtrace; ?>
    </div>
    <h3><a href="#exception-output" class="exception-toggler" data-target="#exception-output">Output <abbr></abbr></a></h3>
    <div id="exception-output" class="hidden">
      <pre><code><?php echo htmlspecialchars($output); ?></code></pre>
    </div>
  </div>
  <div id="footer"></div>
</body>
</html>