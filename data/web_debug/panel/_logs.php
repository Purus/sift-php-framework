<table class="web-debug-logs" id="web-debug-logs">
  <thead>
    <tr>
      <th class="web-debug-log-number">#</th>
      <th class="web-debug-log-type">Type</th>
      <th>
      Message <br /><input type="text" class="data-filter" placeholder="Type to filter the message..." />
      </th>
    </tr>
  </thead>
  <tbody>
<?php exit; $line_nb = 0; foreach($logs as $log): ++$line_nb; ?>
  <tr data-log-type="<?php echo $log['type']; ?>" data-log-level="<?php echo $log['level_name']; ?>" class="web-debug-log-line <?php echo $log['level_name']; ?>">
    <td class="web-debug-log-number"><?php echo $line_nb; ?></td>
    <td class="web-debug-log-type"><strong><?php echo $log['type']; ?></strong></td>
    <td class="web-debug-log-message"><span class="web-debug-log-message-holder"><?php echo $log['message']; ?></span>
<?php if($log['debug_backtrace']): ?>
      <a href="#" class="web-debug-toggler" data-target="web-debug-backtrace">Call stack <abbr></abbr></a>
      <div class="web-debug-backtrace hidden">
        <?php echo $log['debug_backtrace']; ?>
      </div>
<?php endif; ?>
      </td>
  </tr>
<?php endforeach; ?>
  </tbody>
</table>
