<h3>Total time: <?php printf('%.0f ms', $total_time); ?></h3>
<table class="web-debug-logs small">
  <thead>
    <tr>
      <th>Type</th>
      <th>Calls</th>
      <th>Time (ms)</th>
      <th>Time (%)</th>
    </tr>
  </thead>
  <tbody>
<?php foreach($timers as $name => $timer): ?>
  <tr>
    <td class=""><?php echo $name; ?></td>
    <td class=""><?php echo sprintf('%d', $timer->getCalls()); ?></td>
    <td class=""><?php echo sprintf('%.2f', $timer->getElapsedTime() * 1000); ?></td>
    <td class="">
      <?php echo $total_time ? round($timer->getElapsedTime() * 1000 / $total_time * 100, 1) : 'n/a' ; ?>
    </td>
  </tr>
<?php endforeach; ?>
  </tbody>
</table>