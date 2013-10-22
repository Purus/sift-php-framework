<table class="web-debug-logs small">
  <thead>
    <tr>
      <th>Name</th>
      <th>Version</th>
      <th>Root directory</th>
    </tr>
  </thead>
<?php foreach($plugins as $plugin): ?>
  <tr>
    <td><?php echo $plugin['name']; ?></td>
    <td><?php echo $plugin['version']; ?></td>
    <td><?php echo $plugin['root_dir']; ?></td>
  </tr>
<?php endforeach; ?>
</table>
