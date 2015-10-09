<?php
function joomlaposition_3() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    $GLOBALS['isModuleContentExists'] = $view->containsModules('footer2') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('footer2')) : ?>

    <?php if ($isPreview && !$view->containsModules('footer2')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-2099 bd-joomlaposition-3 clearfix" <?php echo buildDataPositionAttr('footer2'); ?>>
        <?php echo $view->position('footer2', 'block%joomlaposition_block_3'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('footer2')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}