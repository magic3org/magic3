<?php
function joomlaposition_14() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('latest-home') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('latest-home')) : ?>

    <?php if ($isPreview && !$view->containsModules('latest-home')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-464051 bd-joomlaposition-14 clearfix" <?php echo buildDataPositionAttr('latest-home'); ?>>
        <?php echo $view->position('latest-home', 'block%joomlaposition_block_14'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('latest-home')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}