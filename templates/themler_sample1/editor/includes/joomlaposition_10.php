<?php
function joomlaposition_10() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('features-home') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('features-home')) : ?>

    <?php if ($isPreview && !$view->containsModules('features-home')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-459773 bd-joomlaposition-10 clearfix" <?php echo buildDataPositionAttr('features-home'); ?>>
        <?php echo $view->position('features-home', 'block%joomlaposition_block_10'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('features-home')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}