<ul class="web-debug-pills">
  <li><a href="#" class="data-filter active" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => '__all__'))); ?>">All levels</a></li>
  <li><a href="#" class="data-filter debug" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'debug'))); ?>">Debug <small>(<?php echo isset($counts['debug']) ? $counts['debug'] : 0; ?>)</small></a></li>
  <li><a href="#" class="data-filter info" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'info'))); ?>">Info <small>(<?php echo isset($counts['info']) ? $counts['info'] : 0; ?>)</small></a></li>
  <li><a href="#" class="data-filter notice" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'notice'))); ?>">Notice <small>(<?php echo isset($counts['notice']) ? $counts['notice'] : 0; ?>)</small></a></li>
  <li><a href="#" class="data-filter warning" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'warning'))); ?>">Warning <small>(<?php echo isset($counts['warning']) ? $counts['warning'] : 0; ?>)</small></a></li>
  <li><a href="#" class="data-filter error" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'error'))); ?>">Error <small>(<?php echo isset($counts['error']) ? $counts['error'] : 0; ?>)</small></a></li>
  <li><a href="#" class="data-filter critical" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'critical'))); ?>">Critical <small>(<?php echo isset($counts['critical']) ? $counts['critical'] : 0; ?>)</small></a></li>
  <li><a href="#" class="data-filter alert" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'alert'))); ?>">Alert <small>(<?php echo isset($counts['alert']) ? $counts['alert'] : 0; ?>)</small></a></li>
  <li><a href="#" class="data-filter emergency" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'level', 'value' => 'emergency'))); ?>">Emergency <small>(<?php echo isset($counts['emergency']) ? $counts['emergency'] : 0; ?>)</small></a></li>
</ul>
<div class="clearfix"></div>
<ul class="web-debug-pills">
  <li><a href="#" class="data-filter active" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'type', 'value' => '__all__'))); ?>">All</a></li>
<?php foreach($types as $type): ?>
  <li><a href="#" class="data-filter" data-filter="<?php echo htmlspecialchars(json_encode(array('target' => 'web-debug-logs', 'attribute' => 'type', 'value' => $type))); ?>"><?php echo $type; ?></a></li>
<?php endforeach; ?>
</ul>
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
<?php $line_nb = 0; foreach($logs as $log): ++$line_nb; ?>
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
