<?php if(count($links)): ?>
<ul>
<?php foreach($links as $link => $href): ?>
  <li><a href="<?php echo htmlspecialchars($href, ENT_QUOTES, sfConfig::get('sf_charset')); ?>"><?php echo htmlspecialchars($link); ?></a></li>
<?php endforeach; ?>
</ul>
<?php else: ?>
  No links are available.
<?php endif; ?>