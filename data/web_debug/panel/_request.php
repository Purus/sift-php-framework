<table class="web-debug-logs small">
  <tbody>
<?php foreach($request as $param => $value): ?>
  <tr>
    <td class="name"><?php echo $param; ?></td>
    <td><pre><code data-language="yaml"><?php echo sfYaml::dump($value); ?></code></pre></td>
  </tr>
<?php endforeach; ?>
  </tbody>
</table>