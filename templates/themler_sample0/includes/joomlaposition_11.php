<?php
function joomlaposition_11() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    $GLOBALS['isModuleContentExists'] = $view->containsModules('left') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('left')) : ?>

    <?php if ($isPreview && !$view->containsModules('left')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-11 clearfix" <?php echo buildDataPositionAttr('left'); ?>>
        <?php echo $view->position('left', 'block%joomlaposition_block_11'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('left')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}