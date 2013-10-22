# System error notification for: <?php echo $host; ?> / <?php echo $app; ?> (env: <?php echo $env; ?>)


## Request information

 * IP: `<?php echo ($ip ? $ip : 'n/a') . "`\n"; ?>
 * Url: `<?php echo ($url ? $url : 'n/a') . "`\n"; ?>
 * Referer: `<?php echo ($referer ? $referer : 'n/a') . "`\n"; ?>
 * User agent: `<?php echo ($user_agent ? $user_agent : 'n/a') . "`\n"; ?>

## Errors

System logged <?php echo count($logs); ?> <?php echo count($logs) > 1 ? 'errors' : 'error' ?>. Highest level: **<?php echo $highest_level; ?>**.

<?php $i = 0; foreach($logs as $log): ?>
### <?php echo ++$i; ?>. <?php echo $log['message_formatted'] . "\n"; ?>

 * Level: **<?php echo $log['level_name']; ?>**
 * Time: **<?php echo date($time_format, $log['time']); ?>**

#### Backtrace

<?php echo "\t" . str_replace("\t", "\n\t", $log['debug_backtrace']) . "\n"; // make is code block ?>

<?php endforeach; ?>

### Memory usage

 Current memory usage was <?php echo $memory_usage; ?>


------

Report generated at <?php echo $now; ?>
