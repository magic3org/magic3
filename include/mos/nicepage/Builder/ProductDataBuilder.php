<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

JLoader::register('DataBuilder', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Builder/DataBuilder.php');
/**
 * Class ProductDataBuilder
 */
class ProductDataBuilder extends DataBuilder
{
    private $_item;
    private $_data;

    /**
     * ProductDataBuilder constructor.
     *
     * @param object $item Article object
     */
    public function __construct($item)
    {
        $this->_item = $item;
        $base = array(
            'product-title' => $this->title(),
            'product-title-link' => $this->titleLink(),
            'product-desc' => $this->content(),
            'product-image' => $this->image(),
        );
        $this->_data = array_merge($base, $this->button(), $this->price());
    }

    /**
     * Get product data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Get product title
     *
     * @return mixed
     */
    public function title()
    {
        return $this->_item->product_name;
    }

    /**
     * Get product content
     *
     * @return false|string|string[]|null
     */
    public function content()
    {
        $desc = $this->_item->product_s_desc ? $this->_item->product_s_desc : $this->_item->product_desc;
        return $this->excerpt($desc, 150, '...', true);
    }

    /**
     * Get product title link
     *
     * @return string
     */
    public function titleLink()
    {
        $productId = $this->_item->virtuemart_product_id;
        $categoryId = $this->_item->virtuemart_category_id;
        $baseUrl = 'index.php?option=com_virtuemart&view=productdetails';
        return JRoute::_($baseUrl . '&virtuemart_product_id=' . $productId . '&virtuemart_category_id=' . $categoryId);
    }

    /**
     * Get product image
     *
     * @return string
     */
    public function image()
    {
        $imageSource = '';
        if (!empty($this->_item->images[0])) {
            $imageSource = JURI::root(true) . '/' . $this->_item->images[0]->file_url;
        }
        return $imageSource;
    }

    /**
     * Get product button
     *
     * @return array
     */
    public function button()
    {
        $button = array('product-button-text' => '', 'product-button-link' => '#', 'product-button-html' => '');

        if (VmConfig::get('use_as_catalog', 0)) {
            return $button;
        }

        $buttonHtml = shopFunctionsF::renderVmSubLayout('addtocart', array('product'=> $this->_item));
        if (strpos($buttonHtml, 'addtocart-button-disabled') !== false) {
            $button['product-button-text'] = vmText::_('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT');
            $button['product-button-link'] = $this->titleLink();
        } else {
            $button['product-button-text'] = vmText::_('COM_VIRTUEMART_CART_ADD_TO');

            $productId = $this->_item->virtuemart_product_id;
            $productName = $this->_item->product_name;
            $button['product-button-html'] = <<<HTML
<form method="post" class="product js-recalculate" action="<?php echo JRoute::_ ('index.php?option=com_virtuemart',false); ?>" autocomplete="off" >
			[[button]]
			<input type="hidden" name="option" value="com_virtuemart"/>
			<input type="hidden" name="view" value="cart"/>
			<input type="hidden" name="virtuemart_product_id[]" value="$productId"/>
			<input type="hidden" name="pname" value="$productName"/>
			<input type="hidden" name="pid" value="$productId"/>
			<input type="hidden" class="quantity-input js-recalculate" name="quantity[]" value="1">
            <noscript><input type="hidden" name="task" value="add"/></noscript>
HTML;
            $itemId = vRequest::getInt('Itemid', false);
            if ($itemId) {
                $button['product-button-html'] .= '<input type="hidden" name="Itemid" value="'.$itemId.'"/>';
            }

            $button['product-button-html'] .= '</form>';
        }
        return $button;
    }

    /**
     * Get product price
     *
     * @return array
     */
    public function price()
    {
        $currency = CurrencyDisplay::getInstance();

        $regularPrice = $currency->createPriceDiv('salesPrice', '', $this->_item->prices, true, false, 1.0, true);
        $oldPrice = $currency->createPriceDiv('basePrice', '', $this->_item->prices, true, false, 1.0, true);

        if (!$regularPrice) {
            $regularPrice = $oldPrice;
        }

        $prices = array('product-price' => '', 'product-old-price' => '');
        if ($regularPrice) {
            $prices['product-price'] = $regularPrice;
        }
        if ($oldPrice) {
            $prices['product-old-price'] = $oldPrice;
        }

        return $prices;
    }
}