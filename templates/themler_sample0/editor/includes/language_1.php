<?php
function language_1() {
    $document = JFactory::getDocument();
    $view = $document->view;
    ?>
    
    <div class="data-control-id-452 bd-language-2" data-responsive-menu="false" data-responsive-levels="">
        <?php if ($view->containsModules('language')) : ?>
            <?php echo $view->position('language', '', '1', 'language'); ?>
        <?php else: ?>
            Please add a language module in the 'language' position
        <?php endif; ?>
    </div>
    
    <?php
}