<?php
function funcInfoMessage($msg) {
    ob_start();
    $document = JFactory::getDocument();
    ?>
    <div class=" bd-informationmessage-1 alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span><?php echo $msg; ?></span>
    </div>
    <?php
    return ob_get_clean();
}