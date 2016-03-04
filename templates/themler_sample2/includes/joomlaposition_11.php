<?php
function joomlaposition_11() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('text-bottom') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('text-bottom')) : ?>

    <?php if ($isPreview && !$view->containsModules('text-bottom')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-11 clearfix" <?php echo buildDataPositionAttr('text-bottom'); ?>>
        <?php echo $view->position('text-bottom', 'block%joomlaposition_block_11', '11'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('text-bottom')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}