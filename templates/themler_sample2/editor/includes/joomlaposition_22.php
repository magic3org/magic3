<?php
function joomlaposition_22() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('hexagon-text') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('hexagon-text')) : ?>

    <?php if ($isPreview && !$view->containsModules('hexagon-text')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-816250 bd-joomlaposition-22 clearfix" <?php echo buildDataPositionAttr('hexagon-text'); ?>>
        <?php echo $view->position('hexagon-text', 'block%joomlaposition_block_22', '22'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('hexagon-text')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}