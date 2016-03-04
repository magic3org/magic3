<?php
function joomlaposition_10() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('icon-2') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('icon-2')) : ?>

    <?php if ($isPreview && !$view->containsModules('icon-2')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-10 clearfix" <?php echo buildDataPositionAttr('icon-2'); ?>>
        <?php echo $view->position('icon-2', 'block%joomlaposition_block_10', '10'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('icon-2')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}