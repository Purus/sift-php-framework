<ul class="web-debug-pills">
<?php foreach($globals as $section => $variables): ?>
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-globals-<?php echo $section; ?>">$_<?php echo strtoupper($section); ?></a></li>
<?php endforeach; ?>
</ul>

<?php foreach($globals as $section => $variables): ?>
<div id="web-debug-environment-globals-<?php echo $section; ?>" class="hidden">
<h4>$_<?php echo strtoupper($section); ?></h4>
<table class="web-debug-logs small">
  <thead>
    <th>Name</th>
    <th>Value</th>
  </thead>
  <tbody>
<?php if(count($variables)): ?>
<?php foreach($variables as $name => $variable): ?>
  <tr>
    <td class="name"><?php echo $name; ?></td>
    <td><pre><?php echo (is_array($variable) || !is_string($variable)) ? sfYaml::dump($variable) : $variable; ?></pre></td>
  </tr>
<?php endforeach; ?>
<?php else: ?>
  <tr><td colspan="2">Empty</td></tr>
<?php endif; ?>
  </tbody>
</table>
</div>
<?php endforeach; ?>