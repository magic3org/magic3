<?php
function joomlaposition_11() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('top-2') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('top-2')) : ?>

    <?php if ($isPreview && !$view->containsModules('top-2')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-11 clearfix" <?php echo buildDataPositionAttr('top-2'); ?>>
        <?php echo $view->position('top-2', 'block%joomlaposition_block_11', '11'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('top-2')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}