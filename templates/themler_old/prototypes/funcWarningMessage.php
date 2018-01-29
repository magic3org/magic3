<?php
function funcWarningMessage($msg) {
    ob_start();
    $document = JFactory::getDocument();
    ?>
    <div class=" bd-warningmessage-1 alert alert-warning">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span><?php echo $msg; ?></span>
    </div>
    <?php
    return ob_get_clean();
}