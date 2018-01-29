<?php
function joomlaposition_12() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('slider-text') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('slider-text')) : ?>

    <?php if ($isPreview && !$view->containsModules('slider-text')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-799169 bd-joomlaposition-12 clearfix" <?php echo buildDataPositionAttr('slider-text'); ?>>
        <?php echo $view->position('slider-text', 'block%joomlaposition_block_12', '12'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('slider-text')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}