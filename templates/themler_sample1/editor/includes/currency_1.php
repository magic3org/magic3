<?php
function currency_1() {
    $document = JFactory::getDocument();
    $view = $document->view;
    ?>
    <?php echo $view->position('currency', '', '1', 'currency'); ?>
    <?php
}