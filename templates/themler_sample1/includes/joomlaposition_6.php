<?php
function joomlaposition_6() {
    $document = JFactory::getDocument();
    $view = $document->view;
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    $GLOBALS['isModuleContentExists'] = $view->containsModules('team-home') ? true : false;
?>
    <?php if ($isPreview || $view->containsModules('team-home')) : ?>

    <?php if ($isPreview && !$view->containsModules('team-home')) : ?>
    <!-- empty::begin -->
    <?php endif; ?>
    <div class=" bd-joomlaposition-6 clearfix" <?php echo buildDataPositionAttr('team-home'); ?>>
        <?php echo $view->position('team-home', 'block%joomlaposition_block_6'); ?>
    </div>
    <?php if ($isPreview && !$view->containsModules('team-home')) : ?>
    <!-- empty::end -->
    <?php endif; ?>
    <?php endif; ?>
<?php
}