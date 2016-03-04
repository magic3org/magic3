<?php
function joomlaposition_16() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('icon-4') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('icon-4')) : ?>

    <?php if ($isPreview && !$view->containsModules('icon-4')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-16 clearfix" <?php echo buildDataPositionAttr('icon-4'); ?>>
        <?php echo $view->position('icon-4', 'block%joomlaposition_block_16', '16'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('icon-4')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}