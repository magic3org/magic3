<?php
function joomlaposition_9() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('slide2') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('slide2')) : ?>

    <?php if ($isPreview && !$view->containsModules('slide2')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-458465 bd-joomlaposition-9 hidden-xs clearfix" <?php echo buildDataPositionAttr('slide2'); ?>>
        <?php echo $view->position('slide2', 'block%joomlaposition_block_9'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('slide2')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}