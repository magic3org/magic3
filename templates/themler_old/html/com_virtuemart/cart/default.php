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
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'functions.php';
?>
<!--TEMPLATE <?php echo getCurrentTemplateByType('shoppingcart'); ?> /-->
<?php

JHtml::_ ('behavior.formvalidation');
$document = JFactory::getDocument ();
$document->addScriptDeclaration ("
//<![CDATA[
	jQuery(document).ready(function($) {
	if ($('#STsameAsBTjs').is(':checked')) {
        $('#output-shipto-display').hide();
	} else {
		$('#output-shipto-display').show();
	}
    $('#STsameAsBTjs').click(function(event) {
        if($(this).is(':checked')){
            $('#STsameAsBT').val('1') ;
            $('#output-shipto-display').hide();
        } else {
            $('#STsameAsBT').val('0') ;
            $('#output-shipto-display').show();
        }
    });
});
//]]>
");
$document->addScriptDeclaration ("
//<![CDATA[
	jQuery(document).ready(function($) {
	    $('#checkoutFormSubmit').click(function(e){
            $(this).attr('disabled', 'true');
            var name = $(this).attr('name');
            $('#checkoutForm').append('<input name=\"' + name + '\" value=\"1\" type=\"hidden\">');
            $(this).fadeIn(400);
            $('#checkoutForm').submit();
        });
	});
//]]>
");

$this->blockFuncName = 'shoppingcart_block_1';

?>

<div class=" bd-shoppingcart">
    <div class=" bd-carttitle-1">
    <h2><?php echo JText::_ ('COM_VIRTUEMART_CART_TITLE'); ?></h2>
</div>
            <?php if (VmConfig::get ('oncheckout_show_steps', 1) && $this->checkout_task === 'confirm') : ?>
                <div class="checkoutStep" id="checkoutStep4">
                    <?php echo JText::_ ('COM_VIRTUEMART_USER_FORM_CART_STEP4'); ?>
                </div>
            <?php endif; ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-24">
                <?php
                    echo renderTemplateFromIncludes($this->blockFuncName,
                        array('', shopFunctionsF::getLoginForm ($this->cart, FALSE)));
                ?>
            </div>
        </div>
    </div>
    <?php $taskRoute = ''; ?>
    <form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=cart' . $taskRoute, $this->useXHTML, $this->useSSL); ?>">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <?php echo $this->loadTemplate('billto'); ?>
                </div>
                <div class="col-md-12">
                    <?php echo $this->loadTemplate('shipto'); ?>
                </div>
            </div>
        </div>

        <?php echo $this->loadTemplate ('pricelist'); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <?php echo $this->loadTemplate ('info'); ?>
                </div>
                <div class="col-md-18">
                    <div id="checkout-advertise-box">
                        <?php if (!empty($this->checkoutAdvertise)) : ?>
                            <?php foreach ($this->checkoutAdvertise as $checkoutAdvertise) : ?>
                                <div class="checkout-advertise">
                                    <?php echo $checkoutAdvertise; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php  echo $this->loadTemplate ('cartfields'); ?>

                    <div class="checkout-button-top">
                        <?php echo str_replace('vm-button-correct', ' bd-button', $this->checkout_link_html); ?>
                    </div>
                </div>
            </div>
        </div>
        <input type='hidden' name='order_language' value='<?php echo $this->order_language; ?>'/>
        <input type='hidden' name='task' value='updatecart'/>
        <input type='hidden' name='option' value='com_virtuemart'/>
        <input type='hidden' name='view' value='cart'/>
    </form>
</div>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>