<!--COMPONENT common -->
<?php ob_start();?>

<?php
echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));

/* Let's see if we found the product */
if (empty($this->product)) {
    echo JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
    echo '<br /><br />  ' . $this->continue_link_html;
    return;
}

$product = $this->product;

//create product title decorator object
$productTitleDecorator = new stdClass();
$productTitleDecorator->link = '';
$productTitleDecorator->name = $this->product->product_name;
//create product desc decorator object
$productDescDecorator = new stdClass();
$productDescDecorator->desc = $this->product->product_desc;
$productDescDecorator->isFull = true;
//create product manufacturer decorator object
$productManufacturerDecorator = new stdClass();
$productManufacturerDecorator->name = $product->mf_name;
//create product price decorator object
$productPriceDecorator = new stdClass();
$productPriceDecorator->show_prices = $this->show_prices;
$productPriceDecorator->currency = $this->currency;
$productPriceDecorator->prices = $this->product->prices;
$productPriceDecorator->imagesExists = isset($this->product->images) ? true : false;
$productPriceDecorator->image = $productPriceDecorator->imagesExists ? $this->product->images[0] : null;
//create product image decorator object
$productImageDecorator = new stdClass();
$productImageDecorator->imagesExists = isset($this->product->images) ? true : false;
$productImageDecorator->image = $productImageDecorator->imagesExists ? $this->product->images[0] : null;
$productImageDecorator->link = $product->link;
//create product sale decorator object
$productSaleDecorator = new stdClass();
$productSaleDecorator->prices = $product->prices;
//create product out of stock decorator object
$productOutOfStockDecorator = new stdClass();
if (isset($this->product->product_in_stock) && isset($this->product->product_ordered)) {
    $productOutOfStockDecorator->product_in_stock = $this->product->product_in_stock;
    $productOutOfStockDecorator->product_ordered = $this->product->product_ordered;
} else {
    $productOutOfStockDecorator = null;
}
//create product buy decorator object
$productBuyDecorator = new stdClass();
$productBuyDecorator->prices = $this->product->prices;
$productBuyDecorator->min_order_level = isset($this->product->min_order_level) ? $this->product->min_order_level : null;
$productBuyDecorator->step_order_level = isset($this->product->step_order_level) ? $this->product->step_order_level : null;
$productBuyDecorator->product_in_stock = isset($this->product->product_in_stock) ? $this->product->product_in_stock : 0;
$productBuyDecorator->product_ordered = isset($this->product->product_ordered) ? $this->product->product_ordered : 0;
$productBuyDecorator->orderable = $this->product->orderable;
$productBuyDecorator->link = $this->product->link;
$productBuyDecorator->product_name = $this->product->product_name;
$productBuyDecorator->virtuemart_product_id = $this->product->virtuemart_product_id;
$productBuyDecorator->isOne = true;
//cretae products items collection
$productItems = new stdClass();
$productItems->productTitle = $productTitleDecorator;
$productItems->productDesc  = $productDescDecorator;
$productItems->productManufacturer = $productManufacturerDecorator;
$productItems->productPrice = $productPriceDecorator;
$productItems->productImage = $productImageDecorator;
$productItems->productBuy   = $productBuyDecorator;
$productItems->productSale = $productSaleDecorator;
$productItems->productOutOfStock = $productOutOfStockDecorator;
$productItems->askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
?>
<div class=" bd-productoverview vm-product-item">
    <div class=" bd-layoutcontainer-29">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-64 
 col-md-14">
    <div class="bd-layoutcolumn-64"><div class="bd-vertical-align-wrapper"><h2 class=" bd-productoverviewtitle-1">
    <?php echo $productItems->productTitle->name; ?>
</h2>
	
		<?php if (!empty($this->product->images)) : ?>
<?php $image = $this->product->images[0]; ?>
<div class=" bd-productimage-6">
    <div class="zoom-container">
        <a class="zoom" rel="thumbnails" href="<?php echo JURI::root() . $image->file_url; ?>">
            <img width="260" height="260" class=" bd-imagestyles" src="<?php echo JURI::root() . $image->file_url; ?>" />
        </a>
    </div>
</div>
<?php endif; ?>
	
		<?php if (!empty($this->product->images) &&
        ($imagesCount = count($this->product->images)) &&
        ($slidesCount = intval('5'))) : ?>
    <?php
        $j = 0;
        $innerStyle = '';
        $itemStyle = '';
        // 
            if ($imagesCount < $slidesCount && $slidesCount !== 0) {
                $innerStyle = 'style="width: ' . floor(100 / $slidesCount) * $imagesCount . '%;margin: 0 auto;"';
                $itemStyle = 'style="width:' . floor(100 / $imagesCount) . '%"';
            }
        //
    ?>
    <div class=" bd-imagethumbnails-1 carousel slide adjust-slides">
        
        <div class="carousel-inner" <?php echo $innerStyle ?>>
            <?php for ($i = 0; $i < $imagesCount; $i++) : ?>
                <?php if ($j % $slidesCount === 0): ?>
                    <div class="item<?php if ($j == 0): ?> active<?php endif; ?>">
                <?php endif ?>
                    <?php
                        $image = $this->product->images[$i];
                        $thumbUrl = $image->file_url;
                        if (preg_match('#src=["\'](.*?)["\']#', $image->displayMediaThumb(), $matches)) {
                            $thumbUrl = $matches[1];
                        }
                    ?>
                    <a class=" bd-productimage-7"
                       <?php echo $itemStyle ?>
                       rel="smallImage: '<?php echo JURI::root() . $image->file_url; ?>'"
                       href="<?php echo JURI::root() . $image->file_url; ?>">
                        <img class=" bd-imagestyles-20" src="<?php echo $thumbUrl; ?>" />
                    </a>
                <?php if (($j + 1) % $slidesCount === 0 || ($j + 1) === $imagesCount): ?>
                    </div>
                <?php endif ?>
                <?php $j++ ?>
            <?php endfor ?>
        </div>
        <?php if ($imagesCount > $slidesCount): ?>
            
                <div class="bd-left-button">
    <a class=" bd-carousel-3" href="#">
        <span></span>
    </a>
</div>

<div class="bd-right-button">
    <a class=" bd-carousel-3" href="#">
        <span></span>
    </a>
</div>
        <?php endif ?>
    </div>
<?php endif ?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-65 
 col-md-10">
    <div class="bd-layoutcolumn-65"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productPrice)) : ?>
<div class=" bd-productprice-5 product-prices">
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
    
    
    <div class=" bd-pricetext-15">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $oldPriceProps);
    ?>
    
        <span class=" bd-label-18">
            <?php echo JText::_($oldPriceProps['description']); ?>
        </span>
    <span class=" bd-container-37 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table basePrice">
        <?php echo $html; ?>
    </span>
</div>
    <div class=" bd-pricetext-14">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $regularPriceProps);
    ?>
    
        <span class=" bd-label-17">
            <?php echo JText::_($regularPriceProps['description']); ?>
        </span>
    <span class=" bd-container-36 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table salesPrice">
        <?php echo $html; ?>
    </span>

</div>
    <?php } ?>
</div>
<?php endif; ?>
	
		<?php if ($this->showRating) : ?>
<div class=" bd-productrating-1">
    <?php
            $maxRating = VmConfig::get('vm_maximum_rating_scale', 5);
            if (empty($this->rating)) {
    echo JText::_('COM_VIRTUEMART_RATING') . ' ' . JText::_('COM_VIRTUEMART_UNRATED');
    } else {
    $rating = $this->rating->rating;
    ?>
    <div class=' bd-rating'>
        <?php for ($i = 1; $i <= $maxRating; $i++) : ?>
        <?php $active = ($i <= $rating ? ' active' : ''); ?>
        <span class=' bd-icon-3 <?php echo $active ?>'></span>
        <?php endfor; ?>
    </div>
    <?php
            }
        ?>
</div>
<?php endif; ?>
	
		<?php $descLength = intval('40'); ?>
<?php if (isset($productItems->productDesc)) : ?>
<div class=" bd-productdesc-13">
    <?php if (property_exists($productItems->productDesc, 'isFull') || $descLength <= 0) :
        echo $productItems->productDesc->desc;
    else :
        echo shopFunctionsF::limitStringByWord($productItems->productDesc->desc, $descLength, '...');
    ?>
    <?php endif; ?>
</div>
<?php endif; ?>
	
		<?php
    $position = 'position_1';
    $customFields = isset($this->product->customfieldsSorted[$position]) ? $this->product->customfieldsSorted[$position] : '';
?>
<div class=" bd-productvariations-1">
    <?php if (!empty($customFields)) : ?>
    <?php $custom_title = null; ?>
    <?php foreach ($customFields as $field) : ?>
    <?php
                    if ( $field->is_hidden ) //OSP http://forum.virtuemart.net/index.php?topic=99320.0
    continue;
    ?>
    <div class="product-field" data-cart-attribute="<?php echo $field->is_cart_attribute ?>">
        <?php if ($field->custom_title != $custom_title and $field->show_title) : ?>
                        <span class="product-fields-title-wrapper">
                            <span class="product-fields-title"><strong><?php echo vmText::_ ($field->custom_title) ?></strong></span>
                            <?php if ($field->custom_tip) : ?>
                            <?php echo JHtml::tooltip ($field->custom_tip, vmText::_ ($field->custom_title), 'tooltip.png'); ?>
                            <?php endif; ?>
                        </span>
        <?php endif; ?>
        <?php if (!empty($field->display)) : ?>
        <span class="product-field-display"><?php echo $field->display ?></span>
        <?php endif; ?>
        <?php if (!empty($field->display)) : ?>
        <span class="product-field-desc"><?php echo vmText::_($field->custom_desc) ?></span>
        <?php endif; ?>
    </div>
    <?php $custom_title = $field->custom_title; ?>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
	
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
                echo JHTML::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $productItems->productBuy->virtuemart_product_id), vmText::_ ('COM_VIRTUEMART_CART_NOTIFY'), array('class' => ' bd-productbuy-4 bd-button notify'));
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
                            'class' => ' bd-productbuy-4 bd-button add_to_cart_button'
                        );
                        echo JHTML::link ('#', JText::_ ('COM_VIRTUEMART_CART_ADD_TO'), $attributes);
                    } else {
                        $button = JHTML::link ($productItems->productBuy->link, JText::_ ('COM_VIRTUEMART_CART_ADD_TO'),
                            array('title' => $productItems->productBuy->product_name, 'class' => ' bd-productbuy-4 bd-button'));
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
<?php endif; ?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-tabinformationcontrol-2 tabbable" data-responsive="true">
    <ul class=" bd-menu-12 clearfix nav nav-tabs navbar-left">
    <li class=" bd-menuitem-12 active">
        <a href="#tab1" data-toggle="tab"><span><?php echo JText::_ ('COM_VIRTUEMART_REVIEWS') ?></span></a>
    </li>
</ul>
    <div class=" bd-container-38 bd-tagstyles tab-content">
    <div id="tab1" class="tab-pane active">
        <div class="std">
            <?php if ($this->allowRating || $this->showReview): ?>
                <?php echo $this->loadTemplate('reviews'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
    <div class=" bd-accordion accordion">
    <div class=" bd-menuitem-25 accordion-item"></div>
    <div class=" bd-container-72 bd-tagstyles accordion-content"></div>
</div>
</div>
<?php if (method_exists('vmJsApi', 'writeJS')) : ?>
<?php
$j = "Virtuemart.container = jQuery('.bd-productoverview');
Virtuemart.containerSelector = '.bd-productoverview';";
vmJsApi::addJScript('ajaxContent', $j);
echo vmJsApi::writeJS();
?>
<?php endif; ?>
</div>

<?php
echo ob_get_clean();
?>
<!--COMPONENT common /-->