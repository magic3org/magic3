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

$_prefix = $viewData['prefix'];
$field = $viewData['field'];
//$userData = $viewData['userData'];
$app = JFactory::getApplication();
if($app->isSite()){
	vmJsApi::popup('#full-tos','#terms-of-service');
	if (!class_exists('VirtueMartCart')) require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	$cart = VirtuemartCart::getCart();
	$cart->prepareVendor();
	if(is_array($cart->BT) and isset($cart->BT['tos'])){
		$tos = $cart->BT['tos'];
	} else {
		$tos = 0;
	}
} else {
	$tos = $field['value'];
}

if(!class_exists('VmHtml')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');
echo VmHtml::checkbox ($_prefix.$field['name'], $tos, 1, 0, 'class="terms-of-service"');

?>
<?php if (VmConfig::get ('oncheckout_show_legal_info', 1) and $app->isSite()) : ?>
<div class="terms-of-service">
    <a href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=1', FALSE) ?>" class="terms-of-service" id="terms-of-service" rel="facebox"
       target="_blank">
        <span class="vmicon vm2-termsofservice-icon"></span>
        <?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED') ?>
    </a>
	<div id="full-tos">
		<h2><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS') ?></h2>
		<?php echo $cart->vendor->vendor_terms_of_service ?>
		</div>
</div>
<?php endif; ?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>