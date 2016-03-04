<?php
function joomlaposition_18() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('slide-2') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('slide-2')) : ?>

    <?php if ($isPreview && !$view->containsModules('slide-2')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-18 clearfix" <?php echo buildDataPositionAttr('slide-2'); ?>>
        <?php echo $view->position('slide-2', 'block%joomlaposition_block_18', '18'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('slide-2')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}