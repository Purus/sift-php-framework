<?php if($css): ?>
<style type="text/css">
<?php echo $css; ?>
</style>
<?php endif; ?>
<div class="debug-dump" title="<?php echo $title ? htmlspecialchars($title) : ''; ?>">
<?php if($label && $location): ?>
<pre class="debug-dump-label">Dump of <span><?php echo htmlspecialchars($label); ?><span> <?php echo $location; ?></pre>
<?php elseif($label): ?>
<pre class="debug-dump-label">Dump of <span><?php echo htmlspecialchars($label); ?></span></pre>
<?php endif; ?>
  <pre><?php echo $dump; ?></pre>
</div>
<?php if($js): ?>
<script type="text/javascript"><?php echo $js; ?></script>
<?php endif; ?>