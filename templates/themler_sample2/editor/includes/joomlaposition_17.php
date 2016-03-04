<?php
function joomlaposition_17() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('small-text') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('small-text')) : ?>

    <?php if ($isPreview && !$view->containsModules('small-text')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-838191 bd-joomlaposition-17 clearfix" <?php echo buildDataPositionAttr('small-text'); ?>>
        <?php echo $view->position('small-text', 'block%joomlaposition_block_17', '17'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('small-text')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}