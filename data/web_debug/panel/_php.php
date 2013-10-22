<table class="web-debug-logs small">
<?php foreach($php as $title => $value): ?>
  <tr>
    <th><?php echo $title; ?></th>
    <td>
<?php if(is_bool($value)): ?>
<?php echo $value ? 'yes' : 'no'; ?>
<?php elseif(is_array($value)): ?>
      <ul>
<?php foreach($value as $k => $v): ?>
        <li><?php echo $k; ?>: <?php echo $v; ?></li>
<?php endforeach; ?>
      </ul>
<?php elseif(empty($value)): ?>
<span class="empty">n/a</span>
<?php else: ?>
<?php echo $value; ?>
<?php endif; ?>
  </tr>
<?php endforeach; ?>
</table>