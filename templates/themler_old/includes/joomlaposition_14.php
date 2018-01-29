<?php
function joomlaposition_14() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('text-2') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('text-2')) : ?>

    <?php if ($isPreview && !$view->containsModules('text-2')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-14 clearfix" <?php echo buildDataPositionAttr('text-2'); ?>>
        <?php echo $view->position('text-2', 'block%joomlaposition_block_14', '14'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('text-2')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}