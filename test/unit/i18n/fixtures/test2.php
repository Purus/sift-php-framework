<?php echo __(('If you have an account here, please %%login%%'), array(
    '%%login%%' => __('login')
)); ?>

<?php /* not translatable*/ echo date('d.m.Y'); ?>

<?php echo __(('If you do not have an account here, please %%register%%'), array(
    '%%register%%' => '<a href="">' . __('register') . '</a>'
)); ?>

<?php echo __(('Please %%call%%'), array(
    '%%call%%' => '<a href="call:my-number">' . __('call') . '</a>'
), 'catalogue'); ?>