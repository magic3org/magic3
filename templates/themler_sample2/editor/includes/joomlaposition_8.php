<?php
function joomlaposition_8() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('slide-1') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('slide-1')) : ?>

    <?php if ($isPreview && !$view->containsModules('slide-1')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-778077 bd-joomlaposition-8 clearfix" <?php echo buildDataPositionAttr('slide-1'); ?>>
        <?php echo $view->position('slide-1', 'block%joomlaposition_block_8', '8'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('slide-1')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}