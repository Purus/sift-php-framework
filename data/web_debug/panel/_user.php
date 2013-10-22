<table class="web-debug-logs small">
<?php foreach($user as $param => $value): ?>
  <tr>
    <td class="name"><?php echo $param; ?></td>
    <td><pre><?php echo sfYaml::dump($value); ?></pre></td>
  </tr>
<?php endforeach; ?>
</table>