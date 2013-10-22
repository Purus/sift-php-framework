<?php if($cache_ignored): ?>
  <h3>Cache is ignored.</h3>
<?php endif; ?>

<ul class="web-debug-pills">
  <li>
<?php if($cache_ignored): ?>
    <a href="<?php echo htmlspecialchars($enable_url); ?>">Reload with cache enabled</a>
<?php else: ?>
    <a href="<?php echo htmlspecialchars($ignore_url); ?>">Reload and ignore cache</a>
<?php endif; ?>
  </li>
</ul>

<table class="web-debug-logs">
  <thead>
    <tr>
      <th>Id</th>
      <th>Uri</th>
      <th>Lifetime</th>
      <th>
        Last modified
<?php if($cache_ignored): ?>
        <small>(Cache is ignored)</small>
<?php endif; ?>
      </th>
    </tr>
  </thead>
  <tbody>
<?php if(count($cached)): ?>
<?php foreach($cached as $cache): ?>
    <tr>
      <td><a href="#web-debug-cache-<?php echo $cache['id']; ?>" title="View cache content" class="web-debug-cache-toggler" data-cache-id="<?php echo $cache['id']; ?>"><?php echo $cache['id']; ?></a></td>
      <td><?php echo htmlspecialchars($cache['uri']); ?></td>
      <td><?php echo $cache['lifetime']; ?></td>
      <td><?php echo $cache['last_modified']; ?></td>
    </tr>
<?php endforeach; ?>
<?php else: ?>
    <tr>
      <td colspan="4">
        No cached fragments.
      </td>
    </tr>
<?php endif; ?>
  </tbody>
</table>