<?php
function joomlaposition_17() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('our-projects') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('our-projects')) : ?>

    <?php if ($isPreview && !$view->containsModules('our-projects')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-17 clearfix" <?php echo buildDataPositionAttr('our-projects'); ?>>
        <?php echo $view->position('our-projects', 'block%joomlaposition_block_17', '17'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('our-projects')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}