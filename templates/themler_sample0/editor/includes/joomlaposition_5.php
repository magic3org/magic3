<?php
function joomlaposition_5() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    $GLOBALS['isModuleContentExists'] = $view->containsModules('footer4') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('footer4')) : ?>

    <?php if ($isPreview && !$view->containsModules('footer4')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-2231 bd-joomlaposition-5 clearfix" <?php echo buildDataPositionAttr('footer4'); ?>>
        <?php echo $view->position('footer4', 'block%joomlaposition_block_5'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('footer4')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}