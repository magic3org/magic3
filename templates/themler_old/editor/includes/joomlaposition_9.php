<?php
function joomlaposition_9() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('top-1') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('top-1')) : ?>

    <?php if ($isPreview && !$view->containsModules('top-1')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-783579 bd-joomlaposition-9 clearfix" <?php echo buildDataPositionAttr('top-1'); ?>>
        <?php echo $view->position('top-1', 'block%joomlaposition_block_9', '9'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('top-1')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}