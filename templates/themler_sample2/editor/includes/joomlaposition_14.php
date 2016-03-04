<?php
function joomlaposition_14() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('icon-3') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('icon-3')) : ?>

    <?php if ($isPreview && !$view->containsModules('icon-3')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-808624 bd-joomlaposition-14 clearfix" <?php echo buildDataPositionAttr('icon-3'); ?>>
        <?php echo $view->position('icon-3', 'block%joomlaposition_block_14', '14'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('icon-3')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}