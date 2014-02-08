<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
<head>
    <?php include_http_metas(); ?>
    <?php include_canonical_url(); ?>
    <?php include_metas(); ?>
    <?php include_title(); ?>
</head>
<?php echo body_tag(); ?>
<div id="container">
    <?php include '_flash.php'; ?>
    <?php echo $sf_content; ?>
</div>
</body>
</html>