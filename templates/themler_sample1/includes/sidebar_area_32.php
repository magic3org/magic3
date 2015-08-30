<?php
    function sidebar_area_32() {
        $isPreview = $GLOBALS['theme_settings']['is_preview'];
        $GLOBALS['isModuleContentExists'] = false;
        ob_start();
?>
        <img class="bd-imagestyles bd-imagelink-5   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/ca397848c3aa6af321a182d3205368ed1bd4c86823e3442188ae38150b3c42a2.png">
        <?php
            $content = trim(ob_get_clean());
            $modContentExists = $GLOBALS['isModuleContentExists'];
            $showContent = strlen(trim(preg_replace('/<!-- empty::begin -->[\s\S]*?<!-- empty::end -->/', '', $content)));
        ?>
        <?php if ($isPreview || ($content && true === $modContentExists)): ?>
            <aside class="bd-sidebararea-32-column  bd-flex-vertical bd-flex-fixed<?php echo ($isPreview && !$modContentExists) ? ' hidden bd-hidden-sidebar' : ''; ?>">
                <div class="bd-sidebararea-32 bd-flex-wide">
                    
                    <?php echo $content; ?>
                    
                </div>
            </aside>
        <?php endif; ?>
<?php
    }
?>