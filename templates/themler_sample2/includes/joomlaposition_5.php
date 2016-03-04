<?php
function joomlaposition_5() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('footer4') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('footer4')) : ?>

    <?php if ($isPreview && !$view->containsModules('footer4')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-5 clearfix" <?php echo buildDataPositionAttr('footer4'); ?>>
        <?php echo $view->position('footer4', 'block%joomlaposition_block_5', '5'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('footer4')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}