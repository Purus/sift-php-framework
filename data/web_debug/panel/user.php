<table class="web-debug-logs small">
  <thead>
    <tr>
      <th>Property</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th>Culture</th>
      <td><?php echo $culture; ?></td>
    </tr>
    <tr>
      <th>Timezone</th>
      <td><?php echo $timezone; ?></td>
    </tr>
    <tr>
      <th>Credentials</th>
      <td>
<?php if(count($credentials)): ?>
        <ul>
<?php foreach($credentials as $credential): ?>
        <li><?php echo $credential; ?></li>
<?php endforeach; ?>
        </ul>
<?php else: ?>
        No credentials
<?php endif; ?>
      </td>
    </tr>
    <tr>
      <th>Attributes</th>
      <td>
        <pre><code><?php echo sfYaml::dump($attributes); ?></code></pre>
      </td>
    </tr>
  </tbody>
</table>