<?php
defined('_JEXEC') or die;
?>
<?php /*BEGIN_EDITOR_OPEN*/
$app = JFactory::getApplication('site');
$templateName = $app->getTemplate();

$ret = false;
$templateDir = JPATH_THEMES . '/' . $templateName;
$editorClass = $templateDir . '/app/' . 'Editor.php';

if (!$app->isAdmin() && file_exists($editorClass)) {
    require_once $templateDir . '/app/' . 'Editor.php';
    $ret = DesignerEditor::override($templateName, __FILE__);
}

if ($ret) {
    $editorDir = $templateName . '/editor';
    require($ret);
    return;
} else {
/*BEGIN_EDITOR_CLOSE*/ ?>
<?php
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'functions.php';
?>
<form role="form" class=" bd-searchwidget-2 form-inline" name="search" action="<?php echo JRoute::_('index.php');?>" method="post">
    <div class="bd-container-inner">
        <input type="hidden" name="task" value="search">
        <input type="hidden" name="option" value="com_search">
        <input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
        <div class="bd-search-wrapper">
            
                
                <div class="bd-input-wrapper">
                    <input name="searchword" id="mod-search-searchword" maxlength="<?php echo $maxlength; ?>" class=" bd-bootstrapinput-2 form-control"
                     placeholder="Search" type="text" size="<?php echo $width; ?>" onblur="if (this.value=='') this.value='<?php echo $text; ?>';"
                     onfocus="if (this.value=='<?php echo $text; ?>') this.value='';" />
                </div>
                
                <div class="bd-button-wrapper">
                    <input type="submit" value="<?php echo $button_text ?>" class=" bd-button <?php echo $moduleclass_sfx ?>" onclick="this.form.searchword.focus();"/>
                </div>
        </div>
        <script>
            jQuery('.bd-searchwidget-2 .bd-icon-6').on('click', function (e) {
                e.preventDefault();
                jQuery('#search-2').submit();
            });
        </script>
    </div>
</form>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>