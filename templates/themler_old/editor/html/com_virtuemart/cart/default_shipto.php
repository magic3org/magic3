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
<?php ob_start(); ?>
<div class="output-shipto">
    <?php if (empty($this->cart->STaddress['fields'])) : ?>
        <?php echo JText::sprintf ('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_EXPLAIN', JText::_ ('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL')); ?>
    <?php else : ?>
        <?php if (!class_exists ('VmHtml')) {
            require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'html.php');
        }
        echo JText::_ ('COM_VIRTUEMART_USER_FORM_ST_SAME_AS_BT');
        echo VmHtml::checkbox ('STsameAsBTjs', $this->cart->STsameAsBT) . '<br />';
        ?>
        <div id="output-shipto-display">
            <?php foreach ($this->cart->STaddress['fields'] as $item) : ?>
                <?php if (!empty($item['value'])) : ?>
                    <?php if ($item['name'] == 'first_name' || $item['name'] == 'middle_name' || $item['name'] == 'zip') : ?>
                        <span class="values<?php echo '-' . $item['name'] ?>"><?php echo $this->escape ($item['value']) ?></span>
                    <?php else : ?>
                        <span class="values"><?php echo $this->escape ($item['value']) ?></span>
                        <br class="clear"/>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <div class="clear"></div>
</div>
<?php if (!isset($this->cart->lists['current_id'])) {
    $this->cart->lists['current_id'] = 0;
} ?>
<div class="data-control-id-191179 bd-container-42 bd-tagstyles">
<a class="data-control-id-191173 bd-button" href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&virtuemart_user_id[]=' . $this->cart->lists['current_id'], $this->useXHTML, $this->useSSL) ?>" >
    <?php echo JText::_ ('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL'); ?>
</a></div>
<?php 
    echo renderTemplateFromIncludes($this->blockFuncName, array(JText::_ ('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL'), ob_get_clean())); 
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>