<?php
function joomlaposition_2() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('footer1') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('footer1')) : ?>

    <?php if ($isPreview && !$view->containsModules('footer1')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-2033 bd-joomlaposition-2 clearfix" <?php echo buildDataPositionAttr('footer1'); ?>>
        <?php echo $view->position('footer1', 'block%joomlaposition_block_2', '2'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('footer1')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}