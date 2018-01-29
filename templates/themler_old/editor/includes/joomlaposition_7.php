<?php
function joomlaposition_7() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('slider-text-2') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('slider-text-2')) : ?>

    <?php if ($isPreview && !$view->containsModules('slider-text-2')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-858723 bd-joomlaposition-7 clearfix" <?php echo buildDataPositionAttr('slider-text-2'); ?>>
        <?php echo $view->position('slider-text-2', 'block%joomlaposition_block_7', '7'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('slider-text-2')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}