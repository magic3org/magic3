<?php
    function sidebar_area_22() {
        $isPreview = $GLOBALS['theme_settings']['is_preview'];
        $GLOBALS['isModuleContentExists'] = false;
        ob_start();
?>
        <img class="bd-imagestyles bd-imagelink-6   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/ecfa33a307d6607cf52bed31baec898e7efa00b4533e40759b23cde91ac9d0c0.png">
        <?php
            $content = trim(ob_get_clean());
            $modContentExists = $GLOBALS['isModuleContentExists'];
            $showContent = strlen(trim(preg_replace('/<!-- empty::begin -->[\s\S]*?<!-- empty::end -->/', '', $content)));
        ?>
        <?php if ($isPreview || ($content && true === $modContentExists)): ?>
            <aside class="bd-sidebararea-22-column  bd-flex-vertical bd-flex-fixed<?php echo ($isPreview && !$modContentExists) ? ' hidden bd-hidden-sidebar' : ''; ?>">
                <div class="bd-sidebararea-22 bd-flex-wide">
                    
                    <?php echo $content; ?>
                    
                </div>
            </aside>
        <?php endif; ?>
<?php
    }
?>