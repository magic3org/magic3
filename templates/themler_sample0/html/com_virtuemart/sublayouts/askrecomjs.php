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

static $ask_recommened_loaded = false;
if($ask_recommened_loaded) return '';

$product = $viewData['product'];
// addon for joomla modal Box
JHtml::_('behavior.modal');

$MailLink = 'index.php?option=com_virtuemart&view=productdetails&task=recommend&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id . '&tmpl=component';
$askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id . '&tmpl=component', FALSE);


$boxFuncReco = '';
$boxFuncAsk = '';
if(VmConfig::get('usefancy',1)){
	vmJsApi::js( 'fancybox/jquery.fancybox-1.3.4.pack');
	vmJsApi::css('jquery.fancybox-1.3.4');
	if(VmConfig::get('show_emailfriend',0)){
		$boxReco = "jQuery.fancybox({
				href: '" . $MailLink . "',
				type: 'iframe',
				height: '550'
			});";
	}
    $boxAsk = "jQuery.fancybox({
            href: '" . $askquestion_url . "',
            type: 'iframe',
            height: '550'
        });";

} else {
	vmJsApi::js( 'facebox' );
	vmJsApi::css( 'facebox' );
	if(VmConfig::get('show_emailfriend',0)){
		$boxReco = "jQuery.facebox({
				iframe: '" . $MailLink . "',
				rev: 'iframe|550|550'
			});";
	}
    $boxAsk = "jQuery.facebox({
            iframe: '" . $askquestion_url . "',
            rev: 'iframe|550|550'
        });";
}
if(VmConfig::get('show_emailfriend',0) ){
	$boxFuncReco = "jQuery('a.recommened-to-friend').click( function(){
					".$boxReco."
			return false ;
		});";
}
$boxFuncAsk = "jQuery('a.ask-question-c').click( function(){
                ".$boxAsk."
        return false ;
    });";

if(!empty($boxFuncAsk) or !empty($boxFuncReco)){
	$document = JFactory::getDocument();
	$document->addScriptDeclaration("
//<![CDATA[
	jQuery(document).ready(function($) {
		".$boxFuncReco."
		".$boxFuncAsk."
	});
//]]>
");
}
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>