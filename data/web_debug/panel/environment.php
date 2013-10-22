<table class="web-debug-logs small">
  <tbody>
    <tr>
      <th>Application name</th>
      <td><?php echo $app_name; ?></td>
    </tr>
    <tr>
      <th>Environment</th>
      <td><?php echo $environment; ?></td>
    </tr>
    <tr>
      <th>Sift</th>
      <td>
        Version: <?php echo $sift['version']; ?><br />
        Lib dir: <?php echo $sift['lib_dir']; ?><br />
        Data dir: <?php echo $sift['data_dir']; ?>
      </td>
    </tr>
    <tr>
      <th>Php version</th>
      <td><?php echo $sift['php']; ?></td>
    </tr>
  </tbody>
</table>

<ul class="web-debug-pills">
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-config">Configuration</a></li>
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-plugins">Plugins</a></li>
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-globals">Globals</a></li>
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-request">Request</a></li>
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-response">Response</a></li>
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-user">User</a></li>
  <li><a href="#" class="web-debug-toggler" data-target="#web-debug-environment-php">Php information</a></li>
</ul>

<div id="web-debug-environment-plugins" class="hidden">
  <h3>Plugins</h3>
  <?php include '_plugins.php'; ?>
</div>
<div id="web-debug-environment-request" class="hidden">
  <h3>Request</h3>
  <?php include '_request.php'; ?>
</div>
<div id="web-debug-environment-response" class="hidden">
  <h3>Response</h3>
  <?php include '_response.php'; ?>
</div>
<div id="web-debug-environment-user" class="hidden">
  <h3>User</h3>
  <?php include '_user.php'; ?>
</div>
<div id="web-debug-environment-config" class="hidden">
  <h3>Configuration</h3>
  <?php include '_configuration.php'; ?>
</div>
<div id="web-debug-environment-globals" class="hidden">
  <h3>Globals</h3>
  <?php include '_globals.php'; ?>
</div>
<div id="web-debug-environment-php" class="hidden">
  <h3>Php information</h3>
  <?php include '_php.php'; ?>
</div>