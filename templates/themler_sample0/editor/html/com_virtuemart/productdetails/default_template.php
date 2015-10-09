<!--COMPONENT common -->
<?php ob_start();

echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));

/* Let's see if we found the product */
if (empty($this->product)) {
    echo JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
    echo '<br /><br />  ' . $this->continue_link_html;
    return;
}

$product = $this->product;
$product->isDetailsLayout = true;

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
//cretae products items collection
$productItems = new stdClass();
$productItems->productTitle = $productTitleDecorator;
$productItems->productDesc  = $productDescDecorator;
$productItems->productManufacturer = $productManufacturerDecorator;
$productItems->productPrice = $productPriceDecorator;
$productItems->askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
?>
<div class="data-control-id-3571 bd-productoverview">
    <div class="data-control-id-2366 bd-layoutcontainer-29">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class="data-control-id-2362 
 col-md-16">
    <div class="bd-layoutcolumn-64"><div class="bd-vertical-align-wrapper"><h2 class="data-control-id-2273 bd-productoverviewtitle-1">
    <?php echo $productItems->productTitle->name; ?>
</h2>
	
		<?php if (!empty($this->product->images)) : ?>
<?php $image = $this->product->images[0]; ?>
<div class="data-control-id-2258 bd-productimage-6">
    <div class="zoom-container">
        <a class="zoom" rel="thumbnails" href="<?php echo JURI::root() . $image->file_url; ?>">
            <img width="260" height="260" class="data-control-id-2257 bd-imagestyles" src="<?php echo JURI::root() . $image->file_url; ?>" />
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
    <div class="data-control-id-2272 bd-imagethumbnails-1 carousel slide adjust-slides">
        
        <div class="carousel-inner" <?php echo $innerStyle ?>>
            <?php for ($i = 0; $i < $imagesCount; $i++) : ?>
                <?php if ($j % $slidesCount === 0): ?>
                    <div class="item<?php if ($j == 0): ?> active<?php endif; ?>">
                <?php endif ?>
                    <?php
                        $image = $this->product->images[$i];
                    ?>
                    <a class="data-control-id-2263 bd-productimage-7"
                       <?php echo $itemStyle ?>
                       rel="smallImage: '<?php echo JURI::root() . $image->file_url; ?>'"
                       href="<?php echo JURI::root() . $image->file_url; ?>">
                        <img class="data-control-id-2262 bd-imagestyles-20" src="<?php echo JURI::root() . $image->file_url_thumb; ?>" />
                    </a>
                <?php if (($j + 1) % $slidesCount === 0 || ($j + 1) === $imagesCount): ?>
                    </div>
                <?php endif ?>
                <?php $j++ ?>
            <?php endfor ?>
        </div>
        <?php if ($imagesCount > $slidesCount): ?>
            
                <div class="left-button">
    <a class="data-control-id-2271 bd-carousel-3" href="#">
        <span class="data-control-id-2270 bd-icon-69"></span>
    </a>
</div>

<div class="right-button">
    <a class="data-control-id-2271 bd-carousel-3" href="#">
        <span class="data-control-id-2270 bd-icon-69"></span>
    </a>
</div>
        <?php endif ?>
    </div>
<?php endif ?></div></div>
</div>
	
		<div class="data-control-id-2364 
 col-md-8">
    <div class="bd-layoutcolumn-65"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productPrice)) : ?>
<div class="data-control-id-2342 bd-productprice-5 product-prices">
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
    
    <div class="data-control-id-2307 bd-pricetext-14">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $regularPriceProps);
    ?>
    
    <span class="data-control-id-2306 bd-container-36 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-imagestyles bd-custom-table salesPrice">
        <?php echo $html; ?>
    </span>

</div>
    
    <div class="data-control-id-2341 bd-pricetext-15">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $oldPriceProps);
    ?>
    
    <span class="data-control-id-2340 bd-container-37 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-imagestyles bd-custom-table basePrice">
        <?php echo $html; ?>
    </span>
</div>
    <?php } ?>
</div>
<?php endif; ?>
	
		<?php if ($this->showRating) : ?>
<div class="data-control-id-2351 bd-productrating-1">
    <?php
            $maxRating = VmConfig::get('vm_maximum_rating_scale', 5);
            if (empty($this->rating)) {
    echo JText::_('COM_VIRTUEMART_RATING') . ' ' . JText::_('COM_VIRTUEMART_UNRATED');
    } else {
    $rating = $this->rating->rating;
    ?>
    <div class='data-control-id-2350 bd-rating'>
        <?php for ($i = 1; $i <= $maxRating; $i++) : ?>
        <?php $active = ($i <= $rating ? ' active' : ''); ?>
        <span class='data-control-id-2349 bd-icon-3 <?php echo $active ?>'></span>
        <?php endfor; ?>
    </div>
    <?php
            }
        ?>
</div>
<?php endif; ?>
	
		<?php if (isset($productItems->productDesc)) : ?>
<div class="data-control-id-2352 bd-productdesc-13">
    <?php if (property_exists($productItems->productDesc, 'isFull')) :
        echo $productItems->productDesc->desc;
    else :
        echo shopFunctionsF::limitStringByWord($productItems->productDesc->desc, 40, '...');
    ?>
    <?php endif; ?>
</div>
<?php endif; ?>
	
		<?php
    $position = 'position_1';
    $customFields = isset($this->product->customfieldsSorted[$position]) ? $this->product->customfieldsSorted[$position] : '';
?>
<div class="data-control-id-2355 bd-productvariations-1">
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
	
		<!-- start productbuy layout -->
<form method="post" class="product" action="<?php echo JRoute::_ ('index.php'); ?>">
    <?php // todo output customfields ?>
    <?php if (!VmConfig::get('use_as_catalog', 0)) : ?>
        <?php
            $quantity = 1;
            if (isset($product->step_order_level) && (int)$product->step_order_level > 0) {
                $quantity = $product->step_order_level;
            } else if (!empty($product->min_order_level)){
                $quantity = $product->min_order_level;
            }
        ?>
        <?php $stockhandle = VmConfig::get ('stockhandle', 'none'); ?>
        <?php if (($stockhandle == 'disableit' or $stockhandle == 'disableadd') and ($product->product_in_stock - $product->product_ordered) < 1) : ?>
            <?php
                echo JHTML::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $product->virtuemart_product_id), vmText::_ ('COM_VIRTUEMART_CART_NOTIFY'), array('class' => 'data-control-id-2360 bd-productbuy-4 bd-button notify'));
            ?>
        <?php else : ?>
            <?php
                $tmpPrice = (float) $product->prices['costPrice'];
                if (!(VmConfig::get('askprice', true) and empty($tmpPrice))) {
                    if (isset($product->orderable) && $product->orderable) {
                        $vmLang = VmConfig::get ('vmlang_js', 1) ? '&lang=' . substr (VmConfig::$vmlang, 0, 2) : '';
                        $attributes = array(
                            'data-vmsiteurl' => JURI::root( ),
                            'data-vmlang' => $vmLang,
                            'data-vmsuccessmsg' => JText::_('COM_VIRTUEMART_CART_ADDED'),
                            'title' => $product->product_name,
                            'class' => 'data-control-id-2360 bd-productbuy-4 bd-button add_to_cart_button'
                        );
                        echo JHTML::link ('#', JText::_ ('COM_VIRTUEMART_CART_ADD_TO'), $attributes);
                    } else {
                        $button = JHTML::link ($product->link, JText::_ ('COM_VIRTUEMART_CART_ADD_TO'),
                            array('title' => $product->product_name, 'class' => 'data-control-id-2360 bd-productbuy-4 bd-button'));
                        if (isset($product->isDetailsLayout))
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
    <input type="hidden" name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>"/>
    <input type="hidden" class="pname" value="<?php echo htmlentities($product->product_name) ?>"/>
</form>
<!-- end productbuy layout --></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class="data-control-id-2429 bd-tabinformationcontrol-2 tabbable" data-responsive="true">
    <ul class="data-control-id-2396 bd-menu-12 clearfix nav nav-tabs navbar-left">
    <li class="data-control-id-2388 bd-menuitem-12 active">
        <a href="#tab1" data-toggle="tab"><span><?php echo JText::_ ('COM_VIRTUEMART_REVIEWS') ?></span></a>
    </li>
</ul>
    <div class="data-control-id-2428 bd-container-38 bd-tagstyles tab-content">
    <div id="tab1" class="tab-pane active">
        <div class="std">
            <?php if ($this->allowRating || $this->showReview): ?>
                <?php echo $this->loadTemplate('reviews'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
    <div class="data-control-id-859155 bd-accordion accordion">
    <div class="data-control-id-861510 bd-menuitem-14 accordion-item"></div>
    <div class="data-control-id-861520 bd-container-43 bd-tagstyles accordion-content"></div>
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
<script type="text/javascript">
    jQuery(function ($) {
        'use strict';
        window.onEventSetProductType($('.bd-productoverview'));
    });
</script>
<?php
echo ob_get_clean();
?>
<!--COMPONENT common /-->