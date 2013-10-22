<table class="web-debug-config small">
  <thead>
    <tr>
      <th class="name">Section</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
<?php foreach($settings as $key => $value): ?>
  <tr>
    <td class="name"><?php echo $key; ?></td>
    <td><pre><code data-language="yaml"><?php echo sfYaml::dump($value); ?></code></pre></td>
  </tr>
<?php endforeach; ?>
  </tbody>
</table>