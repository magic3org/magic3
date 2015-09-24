<?php
function joomlaposition_7() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('slide1') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('slide1')) : ?>

    <?php if ($isPreview && !$view->containsModules('slide1')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-7 hidden-xs clearfix" <?php echo buildDataPositionAttr('slide1'); ?>>
        <?php echo $view->position('slide1', 'block%joomlaposition_block_7'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('slide1')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}