<?php
function joomlaposition_16() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('pro') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('pro')) : ?>

    <?php if ($isPreview && !$view->containsModules('pro')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-837281 bd-joomlaposition-16 clearfix" <?php echo buildDataPositionAttr('pro'); ?>>
        <?php echo $view->position('pro', 'block%joomlaposition_block_16', '16'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('pro')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}