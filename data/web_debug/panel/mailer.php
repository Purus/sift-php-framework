<h3>Configuration</h3>
<table class="web-debug-logs small">
  <thead>
    <tr>
      <th>Option</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th>Realtime transport</th>
      <td><?php echo $realtime_transport; ?></td>
    </tr>
    <tr>
      <th>Spool</th>
      <td><?php echo $spool ? $spool : 'No'; ?></td>
    </tr>
    <tr>
      <th>Deliver <small>(in current environment)</small></th>
      <td><?php echo $deliver ? 'Yes' : 'No'; ?></td>
    </tr>
  </tbody>
</table>

<h3>Emails sent (<?php echo count($messages); ?>)</h3>

<?php foreach($messages as $message): ?>
<div class="web-debug-mailer-message">
  <h4>
    <?php echo $message['subject'] ? $message['subject'] : '[No subject]'; ?>, To: <?php echo $message['to']; ?>
  </h4>
  <ul>
    <li><strong>To:</strong> <?php echo $message['to']; ?></li>
    <li><strong>Bcc:</strong> <?php echo $message['bcc'] ? $message['bcc'] : '[n/a]'; ?></li>
    <li><strong>Charset:</strong> <?php echo $message['charset']; ?></li>
  </ul>
  <pre><?php echo htmlspecialchars($message['content']); ?></pre>
</div>
<?php endforeach; ?>