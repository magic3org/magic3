<?php
function joomlaposition_8() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('text-1') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('text-1')) : ?>

    <?php if ($isPreview && !$view->containsModules('text-1')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-8 clearfix" <?php echo buildDataPositionAttr('text-1'); ?>>
        <?php echo $view->position('text-1', 'block%joomlaposition_block_8', '8'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('text-1')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}