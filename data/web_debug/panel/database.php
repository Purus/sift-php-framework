<table class="web-debug-logs">
  <thead>
    <tr>
      <th class="web-debug-log-number">
        #
      </th>
      <th class="web-debug-log-message">
        Query
      </th>
    </tr>
  </thead>
  <tbody>
<?php foreach($queries as $i => $query): ?>
  <tr>
    <td><?php echo $i + 1; ?></td>
    <td><?php echo $query; ?></td>
  </tr>
<?php endforeach; ?>
  </tbody>
</table>