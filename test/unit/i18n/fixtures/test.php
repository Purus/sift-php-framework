<?php echo __('This is a translated text'); ?>

<?php echo __('Jesus is my Lord', array(), 'important'); ?>

<?php echo __('If you have an account here, please %%login%%', array(
    '%%login%%' => __('login')
)); ?>

<?php
// not translatable
echo date('d.m.Y'); ?>