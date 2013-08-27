<div id="secured"><?php echo $secured ? 'yes' : 'no'; ?></div>
<div id="index"><?php echo $index ? 'yes' : 'no'; ?></div>

<div id="secured-credentials"><?php echo join(',', $securedCredentials); ?></div>

<div id="secured-allowed"><?php echo $securedIsAllowed ? 'yes' : 'no'; ?></div>

<div id="user-credentials"><?php echo join(',', $userCredentials); ?></div>