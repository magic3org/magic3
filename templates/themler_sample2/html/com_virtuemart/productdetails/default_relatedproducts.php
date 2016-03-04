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
<?php if (isset($this->product->customfieldsSorted) && isset($this->product->customfieldsSorted['related_products'])) : ?>
<?php
$productsCount = count($this->product->customfieldsSorted['related_products']);
$i = 0;

$themeParams = JFactory::getApplication()->getTemplate(true)->params;
$itemsInRow = $themeParams->get('itemsInRow', '');
$desktops =  ''; $laptops = ''; $tablets = ''; $phones = '';

$slidersOptions = $themeParams->get('slidersOptions', '');
if ('' !== $slidersOptions) {
    $slidersOptions = json_decode(base64_decode($slidersOptions), true);
    if (isset($slidersOptions['-1'])) {
        $desktops =  $slidersOptions['-1']['desktops'];
        $laptops = $slidersOptions['-1']['laptops'];
        $tablets = $slidersOptions['-1']['tablets'];
        $phones = $slidersOptions['-1']['phones'];
    }
}

$_itemsInRow = empty($itemsInRow) ? '2' : intval($itemsInRow);

$_itemClass = 'separated-item-2  grid';

$_widthLg = empty($desktops) ? '' : $desktops;
$_widthMd = empty($laptops) ? '' : $laptops;
$_widthSm = empty($tablets) ? '12' : $tablets;
$_widthXs = empty($phones) ? '' : $phones;

if ($_widthLg) {
    $_itemClass .= ' col-lg-' . $_widthLg;
}
if ($_widthMd) {
    $_itemClass .= ' col-md-' . $_widthMd;
}
if ($_widthSm) {
    $_itemClass .= ' col-sm-' . $_widthSm;
}
if ($_widthXs) {
    $_itemClass .= ' col-xs-' . $_widthXs;
}
?>
    <?php if ($productsCount > 0) : ?>
    
	<div data-slider-id="relatedproducts_slider" class=" bd-productsslider-1">
	    <div class="bd-container-inner">
            <div class=" bd-block-5">
                <div class=" bd-blockheader bd-tagstyles">
                    <h4><?php echo JText::_('COM_VIRTUEMART_RELATED_PRODUCTS'); ?></h4>
                </div>
                <div class=" bd-blockcontent bd-tagstyles shape-only">
                <div class=" bd-grid-26">
                  <div class="container-fluid">
                    <div class="separated-grid row">
                        <div class="carousel slide<?php if ($productsCount <= $_itemsInRow): ?> single<?php endif; ?> adjust-slides">
                            <div class="carousel-inner">
                    <?php foreach ($this->product->customfieldsSorted['related_products'] as $field):	?>
                        <?php if ($i % $_itemsInRow == 0): ?>
                            <div class="item<?php if ($i == 0): ?> active<?php endif ?>">
                        <?php endif; ?>
                        <?php
                            $display = $field->display;
                            if (preg_match('/<img[^>]+\/>/', $field->display, $matches)) {
                                $image = $matches[0];
                                $content = str_replace($image, '', $field->display);
                                if (preg_match('/<a href=\"([^"]+)\"[^>]*>([\s\S]*?)<\/a>/', $content, $results)) {
                                    $link = $results[1];
                                    $title = trim($results[2]);
                                }
                            }
                            //create product title decorator object
                            $productTitleDecorator = new stdClass();
                            $productTitleDecorator->link = $link;
                            $productTitleDecorator->name = $title;
                            //create product image decorator object
                            $productImageDecorator = new stdClass();
                            $productImageDecorator->imagesExists = '' !== $image ? true : false;
                            $productImageDecorator->image = $productImageDecorator->imagesExists ? $image : null;
                            $productImageDecorator->link = $link;
                            //cretae products items collection
                            $productItems = new stdClass();
                            $productItems->productTitle = $productTitleDecorator;
                            $productItems->productImage = $productImageDecorator;
                        ?>
                        
                        <?php
                            $itemClass = 'separated-item-2 col-md-24 grid';
                            $itemClass = preg_replace('/col-sm-\d+/', 'col-sm-' . round(24 / min(24, max(1, $_itemsInRow))), $itemClass);
                        ?>
                        <div class="<?php echo $itemClass; ?> vm-product-item">
                            <div class=" bd-griditem-2">
                                <?php if (isset($productItems->productImage)) : ?>
    <?php if ($productItems->productImage->imagesExists) : ?>
    <?php
        $offsetHeight = isset($productItems->productImage->offsetHeight) ? $productItems->productImage->offsetHeight : 0;
        $offsetWidth = isset($productItems->productImage->offsetWidth) ? $productItems->productImage->offsetWidth : 0;
        $height = 'height:' . (VmConfig::get ('img_height') + $offsetHeight) . 'px;';
        $width ='width:' . (VmConfig::get ('img_width') + $offsetWidth) . 'px;';
        if (is_object($productItems->productImage->image))
            $imgHtml = $productItems->productImage->image->displayMediaThumb('class=" bd-imagestyles-14"', false);
        else
            $imgHtml = str_replace('<img', '<img class=" bd-imagestyles-14" ', $productItems->productImage->image);
    ?>
    <a class=" bd-productimage-2" href="<?php echo $productItems->productImage->link; ?>">
        <?php echo $imgHtml; ?>
    </a>
    <?php endif; ?>
<?php endif; ?>
	
		<?php if (isset($productItems->productSale)) : ?>
<?php if ($productItems->productSale->prices['discountedPriceWithoutTax'] != $productItems->productSale->prices['priceWithoutTax']) : ?>
<div class=" bd-productsaleicon bd-productsale-1">
    <span>Sale!</span>
</div>
<?php endif; ?>
<?php endif; ?>
	
		<?php if (isset($productItems->productOutOfStock)) : ?>
<?php if (($productItems->productOutOfStock->product_in_stock - $productItems->productOutOfStock->product_ordered) < 1) : ?>
<div class="bd-productoutofstockicon-2  bd-productoutofstock-1">
    Out of stock
</div>
<?php endif; ?>
<?php endif; ?>
	
		<?php if (isset($productItems->productTitle)) : ?>
<div class=" bd-producttitle-4">
    <?php
    if ('' !== $productItems->productTitle->link)
        echo JHTML::link($productItems->productTitle->link, $productItems->productTitle->name);
    else 
        echo $productItems->productTitle->name;
    ?>
</div>
<?php endif; ?>
	
		<?php if (isset($productItems->productPrice)) : ?>
<div class=" bd-productprice-2 product-prices">
    <?php
        if ($productItems->productPrice->show_prices == '1') {
    if ($productItems->productPrice->prices['salesPrice']<=0 and VmConfig::get ('askprice', 1)
    and $productItems->productPrice->imagesExists && !$productItems->productPrice->image->file_is_downloadable) {
    echo JText::_ ('COM_VIRTUEMART_PRODUCT_ASKPRICE');
    }
    $oldPrice = false;
    $oldPriceProps = array('name' => 'basePrice', 'description' => 'COM_VIRTUEMART_PRODUCT_BASEPRICE', $productItems->productPrice->prices, true);
    $regularPriceProps = array('name' => 'salesPrice', 'description' => 'COM_VIRTUEMART_PRODUCT_SALESPRICE', $productItems->productPrice->prices, true);
    ?>
    
    
    <div class=" bd-pricetext-6">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $oldPriceProps);
    ?>
    
        <span class=" bd-label-6">
            <?php echo JText::_($oldPriceProps['description']); ?>
        </span>
    <span class=" bd-container-8 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table basePrice">
        <?php echo $html; ?>
    </span>
</div>
    <div class=" bd-pricetext-5">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $regularPriceProps);
    ?>
    
        <span class=" bd-label-5">
            <?php echo JText::_($regularPriceProps['description']); ?>
        </span>
    <span class=" bd-container-7 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table salesPrice">
        <?php echo $html; ?>
    </span>

</div>
    <?php } ?>
</div>
<?php endif; ?>
	
		<?php if (isset($productItems->productBuy)) : ?>
<form method="post" class="product" action="<?php echo JRoute::_ ('index.php'); ?>">
    <?php // todo output customfields ?>
    <?php if (!VmConfig::get('use_as_catalog', 0)) : ?>
        <?php
            $quantity = 1;
            if (isset($productItems->productBuy->step_order_level) && (int)$productItems->productBuy->step_order_level > 0) {
                $quantity = $productItems->productBuy->step_order_level;
            } else if (!empty($productItems->productBuy->min_order_level)){
                $quantity = $productItems->productBuy->min_order_level;
            }
        ?>
        <?php $stockhandle = VmConfig::get ('stockhandle', 'none'); ?>
        <?php if (($stockhandle == 'disableit' or $stockhandle == 'disableadd') and ($productItems->productBuy->product_in_stock - $productItems->productBuy->product_ordered) < 1) : ?>
            <?php
                echo JHTML::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $productItems->productBuy->virtuemart_product_id), vmText::_ ('COM_VIRTUEMART_CART_NOTIFY'), array('class' => ' bd-productbuy-1 bd-button notify'));
            ?>
        <?php else : ?>
            <?php
                $tmpPrice = (float)$productItems->productBuy->prices['costPrice'];
                if (!(VmConfig::get('askprice', true) and empty($tmpPrice))) {
                    if (isset($productItems->productBuy->orderable) && $productItems->productBuy->orderable) {
                        $vmLang = VmConfig::get ('vmlang_js', 1) ? '&lang=' . substr (VmConfig::$vmlang, 0, 2) : '';
                        $attributes = array(
                            'data-vmsiteurl' => JURI::root( ),
                            'data-vmlang' => $vmLang,
                            'data-vmsuccessmsg' => JText::_('COM_VIRTUEMART_CART_ADDED'),
                            'title' => $productItems->productBuy->product_name,
                            'class' => ' bd-productbuy-1 bd-button add_to_cart_button'
                        );
                        echo JHTML::link ('#', JText::_ ('COM_VIRTUEMART_CART_ADD_TO'), $attributes);
                    } else {
                        $button = JHTML::link ($productItems->productBuy->link, JText::_ ('COM_VIRTUEMART_CART_ADD_TO'),
                            array('title' => $productItems->productBuy->product_name, 'class' => ' bd-productbuy-1 bd-button'));
                        if (isset($productItems->productBuy->isOne))
                            $button = JText::_('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT');
                        echo $button;
                    }
                }
            ?>
        <?php endif; ?>
    <?php endif; ?>
    <input type="hidden" name="quantity[]" value="<?php echo $quantity; ?>"/>
    <noscript><input type="hidden" name="task" value="add"/></noscript>
    <input type="hidden" name="option" value="com_virtuemart"/>
    <input type="hidden" name="view" value="cart"/>
    <input type="hidden" name="virtuemart_product_id[]" value="<?php echo $productItems->productBuy->virtuemart_product_id ?>"/>
    <input type="hidden" class="pname" value="<?php echo htmlentities($productItems->productBuy->product_name) ?>"/>
</form>
<?php endif; ?>
                            </div>
                        </div>
                        <?php if (($i + 1) % $_itemsInRow == 0 || $i == $productsCount - 1): ?>
                            </div>
                        <?php endif ?>
                        <?php $i++ ?>
                    <?php endforeach; ?>
                    </div>
                    <?php if ($productsCount > $_itemsInRow): ?>
                            
                                <div class="bd-left-button">
    <a class=" bd-carousel-1" href="#">
        <span></span>
    </a>
</div>

<div class="bd-right-button">
    <a class=" bd-carousel-1" href="#">
        <span></span>
    </a>
</div>
                    <?php endif ?>
                        </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
	</div>
	</div>
	
	<?php endif; ?>
<?php endif; ?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>