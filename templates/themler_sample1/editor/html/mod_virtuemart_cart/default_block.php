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
<div class="data-control-id-2796 bd-cartcontainer-1">
    <?php $currentRawProduct = reset($data->rawProducts); ?>
<div class="data-control-id-546095 bd-grid-15">
    <div class="container-fluid">
        <div class="separated-grid row">
        <?php foreach ($data->products as $product): ?>
            <?php
                preg_match('/href="(.*?)"/i', $product['product_name'], $hrefParts);
                $product['href'] = $hrefParts[1];
                //create product title decorator object
                $productTitleDecorator = new stdClass();
                $productTitleDecorator->link = $product['href'];
                $productTitleDecorator->name = $product['product_name'];
                //cretae products items collection
                $productItems = new stdClass();
                $productItems->productTitle = $productTitleDecorator;
                $productImageDecorator = new stdClass();
                $productImageDecorator->imagesExists = true;
                if ($currentRawProduct && $currentRawProduct->virtuemart_media_id && $currentRawProduct->virtuemart_media_id[0]) {
                    if (!class_exists ('TableMedias'))
                        require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'medias.php');
                    $db = JFactory::getDBO ();
                    $result = new TableMedias($db);
                    $result->load((int)$currentRawProduct->virtuemart_media_id[0]);
                    if (!class_exists ('VmMediaHandler'))
                        require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'mediahandler.php');
                    $media = VmMediaHandler::createMedia ($result, 'product');
                    $productImageDecorator->image = $media;
                } else {
                    $themeUrl = VmConfig::get('vm_themeurl',0);
                    if(empty($themeUrl)) {
                        $themeUrl = JURI::root().'components/com_virtuemart/';
                    }
                    $src = $themeUrl.'assets/images/vmgeneral/' . VmConfig::get('no_image_set');
                    $alt = JText::_('COM_VIRTUEMART_NO_IMAGE_SET');
                    $productImageDecorator->image = '<img src="' . $src . '" alt="' . $alt . '" />';
                }
                $productImageDecorator->link = $product['href'];
                $productImageDecorator->offsetHeight = 0;
                $productImageDecorator->offsetWidth = 0;
                $productItems->productImage = $productImageDecorator;
            ?>
            <div class="separated-item-1 col-md-24 list">
    <div class="data-control-id-139 bd-griditem-1"><div class="data-control-id-128 bd-layoutcontainer-1">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class="data-control-id-122 
 col-md-6">
    <div class="bd-layoutcolumn-1"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productImage)) : ?>
    <?php if ($productItems->productImage->imagesExists) : ?>
    <?php
        $offsetHeight = isset($productItems->productImage->offsetHeight) ? $productItems->productImage->offsetHeight : 0;
        $offsetWidth = isset($productItems->productImage->offsetWidth) ? $productItems->productImage->offsetWidth : 0;
        $height = 'height:' . (VmConfig::get ('img_height') + $offsetHeight) . 'px;';
        $width ='width:' . (VmConfig::get ('img_width') + $offsetWidth) . 'px;';
        if (is_object($productItems->productImage->image))
            $imgHtml = $productItems->productImage->image->displayMediaThumb('class="data-control-id-66 bd-imagestyles"', false);
        else
            $imgHtml = str_replace('<img', '<img class="data-control-id-66 bd-imagestyles" ', $productItems->productImage->image);
    ?>
    <a class="data-control-id-67 bd-productimage-1" href="<?php echo $productItems->productImage->link; ?>">
        <?php echo $imgHtml; ?>
    </a>
    <?php endif; ?>
<?php endif; ?></div></div>
</div>
	
		<div class="data-control-id-124 
 col-md-15">
    <div class="bd-layoutcolumn-2"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productTitle)) : ?>
<div class="data-control-id-68 bd-producttitle-2">
    <?php
    if ('' !== $productItems->productTitle->link)
        echo JHTML::link($productItems->productTitle->link, $productItems->productTitle->name);
    else 
        echo $productItems->productTitle->name;
    ?>
</div>
<?php endif; ?>
	
		<div class="data-control-id-104 bd-cartprice-1">
    <?php echo $product['quantity'] ?> x <div class="data-control-id-103 bd-pricetext-3">
<?php
    $currency = '';
    $parts = explode(' ', str_replace(',', '.', $product['subtotal_with_tax']));
    $value = trim($parts[0]);
    if (count($parts) > 1)
        $currency = ' ' . trim($parts[1]);
    $price = $value / $product['quantity'];
    if(!class_exists('calculationHelper'))
        require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
    $calculator = calculationHelper::getInstance ();
    $calculator->_roundindig = 0;
    echo  $calculator->roundInternal($price, 'salesPrice') . $currency;
?>
</div>
</div></div></div>
</div>
	
		<div class="data-control-id-126 
 col-md-3">
    <div class="bd-layoutcolumn-3"><div class="bd-vertical-align-wrapper"><a class="data-control-id-112 bd-itemeditlink-1" href="<?php echo $product['href']; ?>">
    <span class="data-control-id-111 bd-icon-9"></span>
</a>
	
		</div></div>
</div>
            </div>
        </div>
    </div>
</div></div>
</div>
        <?php $currentRawProduct = next($data->rawProducts); ?>
        <?php endforeach; ?>
        </div>
    </div>
</div>
	
		<div class="data-control-id-181 bd-pricetext-4">
    <span class="data-control-id-148 bd-label-4">
	<?php echo $totalLabel; ?>
</span>
    <span class="data-control-id-180 bd-container-6 bd-tagstyles">
        <?php echo $totalPrice; ?>
    </span>
</div>
	
		<div class="data-control-id-198 bd-layoutcontainer-2">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class="data-control-id-194 
 col-md-9">
    <div class="bd-layoutcolumn-4"><div class="bd-vertical-align-wrapper"><a href="<?php echo $cartHref; ?>" class="data-control-id-186 bd-button">
    <?php echo $cartText; ?>
</a></div></div>
</div>
	
		<div class="data-control-id-196 
 col-md-15">
    <div class="bd-layoutcolumn-5"><div class="bd-vertical-align-wrapper"><a href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=cart&task=checkout_task'); ?>" class="data-control-id-191 bd-button">
    Checkout now
</a></div></div>
</div>
            </div>
        </div>
    </div>
</div>
</div>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>