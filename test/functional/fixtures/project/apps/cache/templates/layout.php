<!DOCTYPE html>
<head>
<?php echo include_http_metas() ?>
<?php echo include_metas() ?>
<?php echo include_title() ?>
<?php echo include_stylesheets() ?>
<?php echo include_javascripts() ?>
<link rel="shortcut icon" href="/favicon.ico" />
</head>
<body>
<?php echo $sf_content; ?>

<div id="component_slot_content"><?php echo get_slot('component') ?></div>

<div id="partial_slot_content"><?php echo get_slot('partial') ?></div>

<div id="another_partial_slot_content"><?php echo get_slot('another_partial') ?></div>

</body>
</html>