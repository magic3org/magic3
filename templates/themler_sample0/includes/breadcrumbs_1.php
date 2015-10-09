<?php
function breadcrumbs_1() {
    $document = JFactory::getDocument();
    $view = $document->view;
    ?>
    <?php echo $view->position('breadcrumb', '', '1', 'breadcrumbs'); ?>
    <?php
}