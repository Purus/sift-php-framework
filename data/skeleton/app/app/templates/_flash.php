<?php if($sf_flash->has('notice')): ?>
  <div class="alert alert-info">
    <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo __('close', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash'); ?>">×</button>
    <strong><?php echo __('Information:', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash'); ?></strong>
    <?php echo (string)$sf_flash->get('notice'); ?>
  </div>
<?php endif; ?>

<?php if($sf_flash->has('success')): ?>
  <div class="alert alert-success">
    <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo __('close', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash'); ?>">×</button>
    <?php $success = (string)$sf_flash->get('success'); ?>
    <strong><?php echo __('Done!', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash'); ?></strong>
    <?php echo $success; ?>
  </div>
<?php endif; ?>

<?php if($sf_flash->has('error')): ?>
  <div class="alert alert-error">
    <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo __('close', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash'); ?>">×</button>
    <strong><?php echo __('Error!', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash'); ?></strong>
    <?php echo (string)$sf_flash->get('error'); ?>
  </div>
<?php endif; ?>
