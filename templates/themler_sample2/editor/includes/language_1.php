<?php
function language_1() {
    $view = JFactory::getDocument()->view;
    $modulesContains = $view->containsModules('language');
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (isset($GLOBALS['isModuleContentExists']) && false == $GLOBALS['isModuleContentExists'])
        $GLOBALS['isModuleContentExists'] = $view->containsModules('language') ? true : false;
    ?>
    <?php if ($isPreview || $modulesContains) : ?>
        
        <div class="data-control-id-452 bd-language-2" data-responsive-menu="true" data-responsive-levels="">
            <?php if ($view->containsModules('language')) : ?>
                <?php echo $view->position('language', '', '1', 'language'); ?>
            <?php else: ?>
                Please add a language module in the 'language' position
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
<?php
}