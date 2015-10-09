<?php
function joomlaposition_4() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    $GLOBALS['isModuleContentExists'] = $view->containsModules('footer3') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('footer3')) : ?>

    <?php if ($isPreview && !$view->containsModules('footer3')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-2165 bd-joomlaposition-4 clearfix" <?php echo buildDataPositionAttr('footer3'); ?>>
        <?php echo $view->position('footer3', 'block%joomlaposition_block_4'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('footer3')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}