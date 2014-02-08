<?php /* @var form myForm */ ?>
<div class="alert alert-block alert-error">
    <button type="button" class="close" data-dismiss="alert"
            aria-label="<?php echo escape_once(__('close', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash')); ?>">×
    </button>
    <?php $errors = $form->getGlobalErrors(); ?>
    <strong><?php echo __('Error:', array(), '%SF_SIFT_DATA_DIR%/i18n/catalogues/flash'); ?></strong>
    <?php if (count($errors) > 1): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><span class="form-error"><?php echo $error; ?></span></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <span class="form-error"><?php echo current($errors); ?></span>
    <?php endif; ?>
</div>