<?php
function joomlaposition_20() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('big-text') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('big-text')) : ?>

    <?php if ($isPreview && !$view->containsModules('big-text')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-20 clearfix" <?php echo buildDataPositionAttr('big-text'); ?>>
        <?php echo $view->position('big-text', 'block%joomlaposition_block_20', '20'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('big-text')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}