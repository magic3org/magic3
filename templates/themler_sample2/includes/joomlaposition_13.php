<?php
function joomlaposition_13() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('icon-1') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('icon-1')) : ?>

    <?php if ($isPreview && !$view->containsModules('icon-1')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-13 clearfix" <?php echo buildDataPositionAttr('icon-1'); ?>>
        <?php echo $view->position('icon-1', 'block%joomlaposition_block_13', '13'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('icon-1')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}