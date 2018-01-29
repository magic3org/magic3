<?php
function joomlaposition_10() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('our-team') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('our-team')) : ?>

    <?php if ($isPreview && !$view->containsModules('our-team')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class="data-control-id-835854 bd-joomlaposition-10 clearfix" <?php echo buildDataPositionAttr('our-team'); ?>>
        <?php echo $view->position('our-team', 'block%joomlaposition_block_10', '10'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('our-team')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}