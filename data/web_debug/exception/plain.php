[Exception] <?php echo $name . "\n"; ?>
[Message]   <?php echo $message . "\n"; ?>
<?php if($debug_backtrace): ?>
[Backtrace]
  <?php echo str_replace("\t", "\n  ", $debug_backtrace); ?>
<?php endif; ?>
