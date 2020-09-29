<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

JLoader::register('ProductDataBuilder', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Builder/ProductDataBuilder.php');

class ContentModelCustomProducts
{
    private $_options = array();

    /**
     * ContentModelCustomProducts constructor.
     *
     * @param array $options options
     */
    public function __construct($options = array())
    {
        $this->_options = $options;
    }

    /**
     * Get products
     *
     * @return array
     */
    public function getProducts() {
        $products = array();

        if (!$this->_vmInit()) {
            return $products;
        }

        $category_id = "0"; // top level category
        $filter_category = false;
        $categoryName = isset($this->_options['categoryName']) && $this->_options['categoryName'] ? $this->_options['categoryName'] : '';
        $isFeatured = $categoryName === 'Featured products' ? true : false;
        $isFecent = $categoryName === 'Recent products' ? true : false;
        if ($categoryName && !$isFeatured && !$isFecent) {
            $categoryModel = VmModel::getModel('category');
            $records = $categoryModel->getCategoryTree(0, 0, false);
            if ($records) {
                foreach ($records as $record) {
                    if (strtolower($this->_options['categoryName']) == strtolower($record->category_name)) {
                        $category_id = $record->virtuemart_category_id;
                        $filter_category = true;
                        break;
                    }
                }
                if (!$filter_category) {
                    return $products;
                }
            }
        }

        $product_group = $isFeatured ? 'featured' : 'latest';
        $max_items = 20;
        $show_price = true;
        $filter_manufacturer = false;
        $manufacturer_id = null;

        $productModel = VmModel::getModel('Product');
        $productModel::$omitLoaded = false;
        $items = $productModel->getProductListing(
            $product_group,
            $max_items,
            $show_price,
            true,
            false,
            $filter_category,
            $category_id,
            $filter_manufacturer,
            $manufacturer_id
        );

        $products = array();

        if (empty($items)) {
            return $products;
        }

        $productModel->addImages($items);

        foreach ($items as $item) {
            $builder = new ProductDataBuilder($item);
            $product = $builder->getData();
            array_push($products, $product);
        }

        return $products;
    }

    /**
     * Get cart object
     *
     * @return array|null
     */
    public function getCart() {
        if (!$this->_vmInit()) {
            return null;
        }
        $result = array();
        $cart = VirtueMartCart::getCart(false);
        $data = $cart->prepareAjaxData();

        if (isset($data->totalProduct)) {
            $result['count'] = $data->totalProduct;
        }

        $cartLink = $data->cart_show;
        if (preg_match('/(href=[\'"])([\s\S]+?)([\'"])/', $cartLink, $matches)) {
            $result['link'] = $matches[2];
        }
        return $result;
    }

    /**
     * Check vm
     *
     * @return bool
     */
    private function _vmInit()
    {
        if (!file_exists(dirname(JPATH_ADMINISTRATOR) . '/components/com_virtuemart/')) {
            return false;
        }

        if (!JComponentHelper::getComponent('com_virtuemart', true)->enabled) {
            return false;
        }

        $vmdefinesPath = JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/vmdefines.php';
        if (!class_exists('vmDefines') && !file_exists($vmdefinesPath)) {
            return false;
        }

        $configPath = JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
        if (!class_exists('VmConfig') && !file_exists($configPath)) {
            return false;
        }

        include_once $vmdefinesPath;
        include_once $configPath;

        if (!method_exists('VmConfig', 'loadConfig')) {
            return false;
        }

        if (!method_exists('vmDefines', 'core')) {
            return false;
        }

        VmConfig::loadConfig();
        vmDefines::core(JPATH_ROOT);
        vmLanguage::loadJLang('com_virtuemart', true);

        $document = JFactory::getDocument();

        $scripts = <<<SCRIPT
            <script type="text/javascript">
                if (typeof Virtuemart === "undefined") {
                    var Virtuemart = {};
                }
                jQuery(function ($) {
                    Virtuemart.customUpdateVirtueMartNpCart = function(el, options) {
                        var base 	= this;
                        base.npEl 	= $(".u-shopping-cart");
                        base.options 	= $.extend({}, Virtuemart.customUpdateVirtueMartNpCart.defaults, options);
                        
                        base.init = function() {
                            $.ajaxSetup({cache: false});
                            $.getJSON(Virtuemart.vmSiteurl + "index.php?option=com_virtuemart&nosef=1&view=cart&task=viewJS&format=json" + Virtuemart.vmLang,
                                function (datas, textStatus) {
                                    base.npEl.each(function(index, control) {
                                        $(control).find(".u-shopping-cart-count").html(datas.totalProduct);
                                    });
                                }
                            );
                        };
                        base.init();
                    };
                });
                
                jQuery(document).ready(function( $ ) {
                    $(document).off("updateVirtueMartCartModule", "body", Virtuemart.customUpdateVirtueMartNpCart);
                    $(document).on("updateVirtueMartCartModule", "body", Virtuemart.customUpdateVirtueMartNpCart);
                });
            </script>
SCRIPT;
        $document->addCustomTag($scripts);

        return true;
    }
}