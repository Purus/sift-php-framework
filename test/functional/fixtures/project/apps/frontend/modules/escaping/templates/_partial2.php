<h5><?php echo $var ?></h5>
<h6><?php echo $sf_data->getRaw('var') ?></h6>

<span class="<?php echo $sf_data->getRaw('arr') ? 'yes' : 'no' ?>"></span>
<?php if(isset($truth)): ?>
  <span class="truth"><?php echo $truth; ?></span>
<?php endif; ?>

  