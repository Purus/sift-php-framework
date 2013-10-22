<div id="web-debug-cache-<?php echo $id; ?>" class="web-debug-cached-fragment hidden <?php echo $class; ?>">
  <a href="#" class="web-debug-cache-toggler">Cache info<abbr></abbr></a>
  <div class="web-debug-cache-info hidden">
    <ul>
      <li><strong>Uri:</strong> <?php echo htmlspecialchars($uri); ?></li>
      <li><strong>Lifetime:</strong> <?php echo $lifetime; ?></li>
      <li><strong>Last modified:</strong> <?php echo $last_modified; ?></li>
      <li><strong>New:</strong> <?php echo $new ? 'yes' : 'no'; ?></li>
    </ul>
  </div>
  <div class="web-debug-cached-content"><?php echo $content; ?></div>
</div>