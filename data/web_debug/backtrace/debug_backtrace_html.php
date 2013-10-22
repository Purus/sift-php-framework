<ol>
<?php foreach($traces as $i => $trace): ?>
  <li class="debug-backtrace-item">
    <span class="keyword"><?php echo $trace['function']; ?></span>
<?php if(count($trace['arguments'])): ?>
    <span class="keyword">(</span><a href="#" class="<?php echo $class; ?>-toggler" data-target="<?php echo $class; ?>-arguments">arguments <abbr></abbr></a><span class="keyword">)</span>
<?php else: ?>
    <span class="keyword">()</span>
<?php endif; ?>
<?php if($trace['file']): ?>
    in
<?php if($trace['file_edit_url']): ?>
    <a href="<?php echo htmlspecialchars($trace['file_edit_url']); ?>" class="file"><?php echo $trace['file_short']; ?></a>
<?php else: ?>
    <span class="file"><?php echo $trace['file_short']; ?></span>
<?php endif; ?>
    on line <span class="file-line"><?php echo $trace['line']; ?></span>
<?php endif; ?>
<?php if(isset($trace['file_excerpt']) && $trace['file_excerpt']): ?>
<a href="#" class="<?php echo $class; ?>-toggler" data-target="<?php echo $class; ?>-file-excerpt">File source <abbr></abbr></a>
<?php endif; ?>
<?php if(count($trace['arguments'])): ?>
    <div class="<?php echo $class; ?>-arguments hidden">
      <table>
        <thead>
          <tr>
            <th>Type</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
<?php foreach($trace['arguments'] as $value): ?>
          <tr>
            <td><?php echo $value['type']; ?></td>
            <td><?php echo htmlspecialchars($value['value']); ?></td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>
<?php if(isset($trace['file_excerpt']) && $trace['file_excerpt']): ?>
    <div class="file-excerpt">
      <pre class="<?php echo $class; ?>-file-excerpt<?php echo $i > 0 ? ' hidden' : ''; ?>"><code><?php echo $trace['file_excerpt']; ?></code></pre>
    </div>
<?php endif; ?>
  </li>
<?php endforeach; ?>
</ol>
