<?php
function funcSuccessMessage($msg) {
    ob_start();
    $document = JFactory::getDocument();
    ?>
    <div class="data-control-id-2676 bd-successmessage-1 alert alert-success">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span><?php echo $msg; ?></span>
    </div>
    <?php
    return ob_get_clean();
}