<!--COMPONENT common -->
<?php ob_start(); ?>

<div class=" bd-products">
    <?php if (!empty($this->keyword)) : ?>
        <h3><?php echo $this->keyword; ?></h3>
    <?php endif; ?>
    
    <div class=" bd-container-52 bd-tagstyles">
    <h2><?php echo $this->category->category_name; ?></h2>
</div>
    
    <div class=" bd-categories-24">
    
    <div class=" bd-container-53 bd-tagstyles">
    <?php
    $mainCategoryNameDecorator = new stdClass();
    $mainCategoryNameDecorator->link = '';
    $mainCategoryNameDecorator->name = $this->category->category_name;
    $mainCategoryDescDecorator = new stdClass();
    $mainCategoryDescDecorator->description = $this->category->category_description;
    $mainCategoryCountDecorator = new stdClass();
    $mainCategoryCountDecorator->count = count($this->products);
    $mainCategoryImageDecorator = new stdClass();
    $mainCategoryImageDecorator->image = $this->category->images[0];
    $mainCategoryImageDecorator->link = '';
    $categoryItems = new stdClass();
    $categoryItems->categoryName = $mainCategoryNameDecorator;
    $categoryItems->categoryDesc = $mainCategoryDescDecorator;
    $categoryItems->categoryCount = $mainCategoryCountDecorator;
    $categoryItems->categoryImage = $mainCategoryImageDecorator;
    ?>
    <?php if (isset($categoryItems->categoryName)) : ?>
<div class=" bd-categoryname-2">
    <?php
            if ('' !== $categoryItems->categoryName->link)
    echo JHTML::link($categoryItems->categoryName->link, $categoryItems->categoryName->name);
    else
    echo $categoryItems->categoryName->name;
    ?>
</div>
<?php endif; ?>
</div>
    
    <?php if (VmConfig::get('showCategory', 1) and empty($this->keyword)) : ?>
<?php if ($this->category->haschildren) : ?>
<?php
    $categories_per_row = VmConfig::get ( 'categories_per_row', 3 );
    $value = round(24 / min(24, max(1, $categories_per_row)));
    $str = 'col-lg-' . $value . ' col-md-' . $value . ' col-sm-' . $value . ' col-xs-' . $value;
?>
<div class=" bd-productcategories-23">
    <?php if(!empty($this->category->children)) : ?>
    <div class=" bd-grid-52">
      <div class="container-fluid">
        <div class="separated-grid row">
        <?php foreach ( $this->category->children as $category ) : ?>
        <?php
            $categoryNameDecorator = new stdClass();
            $categoryNameDecorator->link = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id );
            $categoryNameDecorator->name = $category->category_name;
            $categoryDescDecorator = new stdClass();
            $categoryDescDecorator->description = $category->category_description;

            $categoryCountDecorator = new stdClass();
            $productModel = VmModel::getModel('product');
            $ids = $productModel->sortSearchListQuery (TRUE, $category->virtuemart_category_id);
            $categoryCountDecorator->count = count($ids);

            $categoryImageDecorator = new stdClass();
            $categoryImageDecorator->image = $category->images[0];
            $categoryImageDecorator->link = $categoryNameDecorator->link;
            $categoryItems = new stdClass();
            $categoryItems->categoryName = $categoryNameDecorator;
            $categoryItems->categoryDesc = $categoryDescDecorator;
            $categoryItems->categoryCount = $categoryCountDecorator;
            $categoryItems->categoryImage = $categoryImageDecorator;
        ?>
        
        <div class="<?php echo str_replace('col-md-24', $str, 'separated-item-9 col-md-8 grid'); ?>">
            <div class=" bd-griditem-9">
                <?php if (isset($categoryItems->categoryName)) : ?>
<div class=" bd-categoryname-3">
    <?php
            if ('' !== $categoryItems->categoryName->link)
    echo JHTML::link($categoryItems->categoryName->link, $categoryItems->categoryName->name);
    else
    echo $categoryItems->categoryName->name;
    ?>
</div>
<?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div>
    <?php if (!empty($this->products)) : ?>
    <div class=" bd-productsgridbar-28">
    <div class="bd-container-inner">
        <div class=" bd-layoutcontainer-27">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class=" 
 col-md-8">
    <div class="bd-layoutcolumn-57"><div class="bd-vertical-align-wrapper"><div class=" bd-typeselector-1">
    
</div></div></div>
</div>
	
		<div class=" 
 col-md-8">
    <div class="bd-layoutcolumn-58"><div class="bd-vertical-align-wrapper"><div class=" bd-productssorter-1">
    <?php echo JText::_ ('COM_VIRTUEMART_ORDERBY'); ?>
    <?php
        $content = $this->orderByList['orderby'];
        $result = '';
    ?>
    <?php
            if (preg_match_all('/<a title="([^"]*)" href="([^"]*)">(.*?)<\/a>/', $content, $matches, PREG_SET_ORDER)) {
    $result = '<select onchange="location.href=this.options[this.selectedIndex].value">';
    foreach($matches as $value) {
    $selected = '';
    $name = $value[3];
    if ($value[1] !== $value[3]) {
    $name = str_replace($value[1],'', $name);
    $selected = ' selected="selected"';
    }
    $result .= '<option value="' . $value[2] . '"' . $selected .'>' . $name . '</option>';
    }
    $result .= '</select>';
    } else {
    $result = $content;
    }
    echo $result;
    ?>
</div></div></div>
</div>
	
		<div class=" 
 col-md-8">
    <div class="bd-layoutcolumn-59"><div class="bd-vertical-align-wrapper"><div class=" bd-productsperpage-1">
    <?php echo $this->vmPagination->getResultsCounter();?>
    <?php echo str_replace( 'window.top.location', 'location',  $this->vmPagination->getLimitBox()); ?>

</div></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<?php renderTemplateFromIncludes('products_grid_pagination_1', array($this)); ?>
    </div>
</div>
    <div class=" bd-grid-55">
      <div class="container-fluid">
        <div class="separated-grid row">
          <?php foreach ( $this->products as $product ) : ?>
    <?php
        $customfieldsModel = VmModel::getModel('customfields');
        $product->customfields = $customfieldsModel->getCustomEmbeddedProductCustomFields($product->allIds, 0, 1);
        if ($product->customfields){
            $customfieldsModel->displayProductCustomfieldFE($product, $product->customfields);
        }
        //create product title decorator object
        $productTitleDecorator = new stdClass();
        $productTitleDecorator->link = $product->link;
        $productTitleDecorator->name = $product->product_name;
        //create product desc decorator object
        $productDescDecorator = new stdClass();
        $productDescDecorator->desc = $product->product_s_desc;
        //create product manufacturer decorator object
        $productManufacturerDecorator = new stdClass();
        $productManufacturerDecorator->name = $product->mf_name;
        //create product price decorator object
        $productPriceDecorator = new stdClass();
        $productPriceDecorator->show_prices = $this->show_prices;
        $productPriceDecorator->currency = $this->currency;
        $productPriceDecorator->prices = $product->prices;
        $productPriceDecorator->imagesExists = isset($product->images) ? true : false;
        $productPriceDecorator->image = $productPriceDecorator->imagesExists ? $product->images[0] : null;
        //create product image decorator object
        $productImageDecorator = new stdClass();
        $productImageDecorator->imagesExists = isset($product->images) ? true : false;
        $productImageDecorator->image = $productImageDecorator->imagesExists ? $product->images[0] : null;
        $productImageDecorator->link = $product->link;
        //create product sale decorator object
        $productSaleDecorator = new stdClass();
        $productSaleDecorator->prices = $product->prices;
        $productSaleDecorator->currency = $this->currency;
        //create product out of stock decorator object
        $productOutOfStockDecorator = new stdClass();
        if (isset($product->product_in_stock) && isset($product->product_ordered)) {
            $productOutOfStockDecorator->product_in_stock = $product->product_in_stock;
            $productOutOfStockDecorator->product_ordered = $product->product_ordered;
        } else {
            $productOutOfStockDecorator = null;
        }
        $productOutOfStockDecorator->product_in_stock = isset($product->product_in_stock) ? $product->product_in_stock : null;
        $productOutOfStockDecorator->product_ordered = isset($product->product_ordered) ? $product->product_ordered : null;
        //create products items collection
        $productItems = new stdClass();
        $productItems->productTitle = $productTitleDecorator;
        $productItems->productDesc  = $productDescDecorator;
        $productItems->productManufacturer = $productManufacturerDecorator;
        $productItems->productPrice = $productPriceDecorator;
        $productItems->productImage = $productImageDecorator;
        $productItems->productSale = $productSaleDecorator;
        $productItems->productOutOfStock = $productOutOfStockDecorator;

        $defaultLayoutName = "grid";
        $activeLayoutName = empty($_COOKIE['layoutType']) ? $defaultLayoutName : $_COOKIE['layoutType'];
    ?>
    
    <div class="separated-item-5 col-md-8 grid"<?php if ('grid' !== $activeLayoutName): ?> style="display: none;"<?php endif ?>>
        <div class=" bd-griditem-5">
            <?php if (isset($productItems->productImage)) : ?>
    <?php if ($productItems->productImage->imagesExists) : ?>
    <?php
        $offsetHeight = isset($productItems->productImage->offsetHeight) ? $productItems->productImage->offsetHeight : 0;
        $offsetWidth = isset($productItems->productImage->offsetWidth) ? $productItems->productImage->offsetWidth : 0;
        $height = 'height:' . (VmConfig::get ('img_height') + $offsetHeight) . 'px;';
        $width ='width:' . (VmConfig::get ('img_width') + $offsetWidth) . 'px;';
        if (is_object($productItems->productImage->image))
            $imgHtml = $productItems->productImage->image->displayMediaThumb('class=" bd-imagestyles"', false);
        else
            $imgHtml = str_replace('<img', '<img class=" bd-imagestyles" ', $productItems->productImage->image);
    ?>
    <a class=" bd-productimage-4" href="<?php echo $productItems->productImage->link; ?>">
        <?php echo $imgHtml; ?>
    </a>
    <?php endif; ?>
<?php endif; ?>
	
		<div class=" bd-layoutcontainer-41">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class=" 
 col-md-15">
    <div class="bd-layoutcolumn-119"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productTitle)) : ?>
<div class=" bd-producttitle-8">
    <?php
    if ('' !== $productItems->productTitle->link)
        echo JHTML::link($productItems->productTitle->link, $productItems->productTitle->name);
    else 
        echo $productItems->productTitle->name;
    ?>
</div>
<?php endif; ?>
	
		<?php $descLength = intval('40'); ?>
<?php if (isset($productItems->productDesc)) : ?>
<div class=" bd-productdesc-9">
    <?php if (property_exists($productItems->productDesc, 'isFull') || $descLength <= 0) :
        echo $productItems->productDesc->desc;
    else :
        echo shopFunctionsF::limitStringByWord($productItems->productDesc->desc, $descLength, '...');
    ?>
    <?php endif; ?>
</div>
<?php endif; ?></div></div>
</div>
	
		<div class=" 
 col-md-9">
    <div class="bd-layoutcolumn-8"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productPrice)) : ?>
<div class=" bd-productprice-3 product-prices">
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
    
    
    <div class=" bd-pricetext-11">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $oldPriceProps);
    ?>
    
        <span class=" bd-label-11">
            <?php echo JText::_($oldPriceProps['description']); ?>
        </span>
    <span class=" bd-container-31 bd-tagstyles basePrice">
        <?php echo $html; ?>
    </span>
</div>
    <div class=" bd-pricetext-10">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $regularPriceProps);
    ?>
    
        <span class=" bd-label-10">
            <?php echo JText::_($regularPriceProps['description']); ?>
        </span>
    <span class=" bd-container-30 bd-tagstyles salesPrice">
        <?php echo $html; ?>
    </span>

</div>
    <?php } ?>
</div>
<?php endif; ?></div></div>
</div>
            </div>
        </div>
    </div>
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
                echo JHTML::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $product->virtuemart_product_id), vmText::_ ('COM_VIRTUEMART_CART_NOTIFY'), array('class' => ' bd-productbuy-2 bd-button notify'));
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
                            'class' => ' bd-productbuy-2 bd-button add_to_cart_button'
                        );
                        echo JHTML::link ('#', JText::_ ('COM_VIRTUEMART_CART_ADD_TO'), $attributes);
                    } else {
                        $button = JHTML::link ($product->link, JText::_ ('COM_VIRTUEMART_CART_ADD_TO'),
                            array('title' => $product->product_name, 'class' => ' bd-productbuy-2 bd-button'));
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
<!-- end productbuy layout -->
	
		<?php if (isset($productItems->productSale)) : ?>
<?php if ($productItems->productSale->prices['discountedPriceWithoutTax'] != $productItems->productSale->prices['priceWithoutTax']) : ?>
<div class=" bd-productsaleicon bd-productsale-2">
    <span>Sale!</span>
</div>
<?php endif; ?>
<?php endif; ?>
        </div>
    </div>
    <div class="separated-item-6 col-md-24 list"<?php if ('list' !== $activeLayoutName): ?> style="display: none;"<?php endif ?>>
        <div class=" bd-griditem-6">
            <div class=" bd-layoutcontainer-26">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class=" 
 col-md-5">
    <div class="bd-layoutcolumn-54"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productImage)) : ?>
    <?php if ($productItems->productImage->imagesExists) : ?>
    <?php
        $offsetHeight = isset($productItems->productImage->offsetHeight) ? $productItems->productImage->offsetHeight : 0;
        $offsetWidth = isset($productItems->productImage->offsetWidth) ? $productItems->productImage->offsetWidth : 0;
        $height = 'height:' . (VmConfig::get ('img_height') + $offsetHeight) . 'px;';
        $width ='width:' . (VmConfig::get ('img_width') + $offsetWidth) . 'px;';
        if (is_object($productItems->productImage->image))
            $imgHtml = $productItems->productImage->image->displayMediaThumb('class=" bd-imagestyles"', false);
        else
            $imgHtml = str_replace('<img', '<img class=" bd-imagestyles" ', $productItems->productImage->image);
    ?>
    <a class=" bd-productimage-5" href="<?php echo $productItems->productImage->link; ?>">
        <?php echo $imgHtml; ?>
    </a>
    <?php endif; ?>
<?php endif; ?>
	
		<?php if (isset($productItems->productSale)) : ?>
<?php if ($productItems->productSale->prices['discountedPriceWithoutTax'] != $productItems->productSale->prices['priceWithoutTax']) : ?>
<div class=" bd-productsaleicon bd-productsale-3">
    <span>Sale!</span>
</div>
<?php endif; ?>
<?php endif; ?>
	
		<?php if (isset($productItems->productOutOfStock)) : ?>
<?php if (($productItems->productOutOfStock->product_in_stock - $productItems->productOutOfStock->product_ordered) < 1) : ?>
<div class="bd-productoutofstockicon  bd-productoutofstock-3">
    Out of stock
</div>
<?php endif; ?>
<?php endif; ?></div></div>
</div>
	
		<div class=" 
 col-md-13">
    <div class="bd-layoutcolumn-55"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productTitle)) : ?>
<div class=" bd-producttitle-10">
    <?php
    if ('' !== $productItems->productTitle->link)
        echo JHTML::link($productItems->productTitle->link, $productItems->productTitle->name);
    else 
        echo $productItems->productTitle->name;
    ?>
</div>
<?php endif; ?>
	
		<?php $descLength = intval('40'); ?>
<?php if (isset($productItems->productDesc)) : ?>
<div class=" bd-productdesc-11">
    <?php if (property_exists($productItems->productDesc, 'isFull') || $descLength <= 0) :
        echo $productItems->productDesc->desc;
    else :
        echo shopFunctionsF::limitStringByWord($productItems->productDesc->desc, $descLength, '...');
    ?>
    <?php endif; ?>
</div>
<?php endif; ?></div></div>
</div>
	
		<div class=" 
 col-md-6">
    <div class="bd-layoutcolumn-56"><div class="bd-vertical-align-wrapper"><?php if (isset($productItems->productPrice)) : ?>
<div class=" bd-productprice-4 product-prices">
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
    
    
    <div class=" bd-pricetext-13">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $oldPriceProps);
    ?>
    
        <span class=" bd-label-13">
            <?php echo JText::_($oldPriceProps['description']); ?>
        </span>
    <span class=" bd-container-33 bd-tagstyles basePrice">
        <?php echo $html; ?>
    </span>
</div>
    <div class=" bd-pricetext-12">
    <?php
		$html = call_user_func_array(array(&$productItems->productPrice->currency, 'createPriceDiv'), $regularPriceProps);
    ?>
    
        <span class=" bd-label-12">
            <?php echo JText::_($regularPriceProps['description']); ?>
        </span>
    <span class=" bd-container-32 bd-tagstyles salesPrice">
        <?php echo $html; ?>
    </span>

</div>
    <?php } ?>
</div>
<?php endif; ?>
	
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
                echo JHTML::link (JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $product->virtuemart_product_id), vmText::_ ('COM_VIRTUEMART_CART_NOTIFY'), array('class' => ' bd-productbuy-3 bd-button notify'));
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
                            'class' => ' bd-productbuy-3 bd-button add_to_cart_button'
                        );
                        echo JHTML::link ('#', JText::_ ('COM_VIRTUEMART_CART_ADD_TO'), $attributes);
                    } else {
                        $button = JHTML::link ($product->link, JText::_ ('COM_VIRTUEMART_CART_ADD_TO'),
                            array('title' => $product->product_name, 'class' => ' bd-productbuy-3 bd-button'));
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
        </div>
    </div>
    <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class=" bd-productsgridbar-30">
    <div class="bd-container-inner">
        <?php renderTemplateFromIncludes('products_grid_pagination_2', array($this)); ?>
    </div>
</div>
    <?php elseif ($this->search !==null ) : ?>
        <?php echo JText::_('COM_VIRTUEMART_NO_RESULT').($this->keyword? ' : ('. $this->keyword. ')' : ''); ?>
    <?php endif; ?>
</div>

<?php
echo ob_get_clean();
?>
<!--COMPONENT common /-->