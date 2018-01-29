<?php
function joomlaposition_15() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('top-4') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('top-4')) : ?>

    <?php if ($isPreview && !$view->containsModules('top-4')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-784107 bd-joomlaposition-15 clearfix" <?php echo buildDataPositionAttr('top-4'); ?>>
        <?php echo $view->position('top-4', 'block%joomlaposition_block_15', '15'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('top-4')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}