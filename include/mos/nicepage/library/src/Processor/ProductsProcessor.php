<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

//JLoader::register('ContentModelCustomProducts', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Models/ContentModelCustomProducts.php');

class ProductsProcessor
{
    private $_products = array();
    private $_product = array();

    /**
     * Process products
     *
     * @param string $content Content
     *
     * @return string|string[]|null
     */
    public function process($content) {
        return preg_replace_callback('/<\!--products-->([\s\S]+?)<\!--\/products-->/', array(&$this, '_processProducts'), $content);
    }

    /**
     * Process products
     *
     * @param array $productsMatch Matches
     *
     * @return string|string[]|null
     */
    private function _processProducts($productsMatch) {
        $productsHtml = $productsMatch[1];
        $productsOptions = [];
        if (preg_match('/<\!--products_options_json--><\!--([\s\S]+?)--><\!--\/products_options_json-->/', $productsHtml, $matches)) {
            $productsOptions = json_decode($matches[1], true);
            $productsHtml = str_replace($matches[0], '', $productsHtml);
        }
        $productsSourceType = isset($productsOptions['type']) ? $productsOptions['type'] : '';
        if ($productsSourceType === 'products-featured') {
            $productsSource = 'Featured products';
        } else if ($productsSourceType === 'products-recent') {
            $productsSource = 'Recent products';
        } else {
            $productsSource = isset($productsOptions['source']) && $productsOptions['source'] ? $productsOptions['source'] : '';
        }
        $this->_products = $this->_getProducts($productsSource);
        return preg_replace_callback('/<\!--product_item-->([\s\S]+?)<\!--\/product_item-->/', array(&$this, '_processProductItem'), $productsHtml);
    }

    /**
     * Process product item
     *
     * @param array $productItemMatch Matches
     *
     * @return mixed|string|string[]|null
     */
    private function _processProductItem($productItemMatch) {
        $productItemHtml = $productItemMatch[1];

        if (count($this->_products) < 1) {
            return ''; // remove cell, if post is missing
        }

        $this->_product = array_shift($this->_products);
        $productItemHtml = preg_replace_callback('/<\!--product_title-->([\s\S]+?)<\!--\/product_title-->/', array(&$this, '_setTitleData'), $productItemHtml);
        $productItemHtml = preg_replace_callback('/<\!--product_content-->([\s\S]+?)<\!--\/product_content-->/', array(&$this, '_setTextData'), $productItemHtml);
        $productItemHtml = preg_replace_callback('/<\!--product_image-->([\s\S]+?)<\!--\/product_image-->/', array(&$this, '_setImageData'), $productItemHtml);
        $productItemHtml = preg_replace_callback('/<\!--product_button-->([\s\S]+?)<\!--\/product_button-->/', array(&$this, '_setButtonData'), $productItemHtml);
        $productItemHtml = preg_replace_callback('/<\!--product_price-->([\s\S]+?)<\!--\/product_price-->/', array(&$this, '_setPriceData'), $productItemHtml);
        return $productItemHtml;
    }

    /**
     * Get products by source
     *
     * @param string $source Source
     *
     * @return array
     */
    private function _getProducts($source)
    {
        $products = new ContentModelCustomProducts(array('categoryName' => $source));
        return $products->getProducts();
    }

    /**
     * Set title
     *
     * @param string $titleMatch Title match
     *
     * @return mixed|string|string[]|null
     */
    private function _setTitleData($titleMatch) {
        $titleHtml = $titleMatch[1];
        $titleHtml = preg_replace_callback(
            '/<\!--product_title_content-->([\s\S]+?)<\!--\/product_title_content-->/',
            function ($titleContentMatch) {
                return isset($this->_product['product-title']) ? $this->_product['product-title'] : $titleContentMatch[1];
            },
            $titleHtml
        );
        $titleLink = isset($this->_product['product-title-link']) ? $this->_product['product-title-link'] : '#';
        $titleHtml = preg_replace('/(href=[\'"])([\s\S]+?)([\'"])/', '$1' . $titleLink . '$3', $titleHtml);
        return $titleHtml;
    }

    /**
     * Set text
     *
     * @param string $textMatch Text match
     *
     * @return mixed|string|string[]|null
     */
    private function _setTextData($textMatch) {
        $textHtml = $textMatch[1];
        $textHtml = preg_replace_callback(
            '/<\!--product_content_content-->([\s\S]+?)<\!--\/product_content_content-->/',
            function ($contentMatch) {
                return isset($this->_product['product-desc']) ? $this->_product['product-desc'] : $contentMatch[1];
            },
            $textHtml
        );
        return $textHtml;
    }

    /**
     * Set product image
     *
     * @param string $imageMatch Image match
     *
     * @return mixed
     */
    private function _setImageData($imageMatch) {
        $imageHtml = $imageMatch[1];
        $isBackgroundImage = strpos($imageHtml, '<div') !== false ? true : false;

        $link = isset($this->_product['product-title-link']) ? $this->_product['product-title-link'] : '';
        $src = isset($this->_product['product-image']) ? $this->_product['product-image'] : '';

        if (!$src) {
            return $isBackgroundImage ? $imageHtml : '';
        }

        if ($isBackgroundImage) {
            $imageHtml = str_replace('<div', '<div data-product-control="' . $link . '"', $imageHtml);
            if (strpos($imageHtml, 'data-bg') !== false) {
                $imageHtml = preg_replace('/(data-bg=[\'"])([\s\S]+?)([\'"])/', '$1url(' . $this->_product['product-image'] . ')$3', $imageHtml);
            } else {
                $imageHtml = str_replace('<div', '<div' . ' style="background-image:url(' . $this->_product['product-image'] . ')"', $imageHtml);
            }
        } else {
            $imageHtml = preg_replace('/(src=[\'"])([\s\S]+?)([\'"])/', '$1' . $this->_product['product-image'] . '$3 style="cursor:pointer;" data-product-control="' . $link . '"', $imageHtml);
        }

        return $imageHtml;
    }

    /**
     * Set product button
     *
     * @param array $buttonMatch Image match
     *
     * @return mixed
     */
    private function _setButtonData($buttonMatch) {
        $buttonHtml = $buttonMatch[1];
        $isOnlyCatalog = !$this->_product['product-button-text'] ? true : false;
        if ($isOnlyCatalog) {
            return '';
        }
        $buttonHtml = preg_replace_callback(
            '/<\!--product_button_content-->([\s\S]+?)<\!--\/product_button_content-->/',
            function ($buttonContentMatch) {
                return isset($this->_product['product-button-text']) ? $this->_product['product-button-text'] : $buttonContentMatch[1];
            },
            $buttonHtml
        );
        $buttonLink = isset($this->_product['product-button-link']) ? $this->_product['product-button-link'] : '#';
        $buttonHtml = preg_replace('/(href=[\'"])([\s\S]+?)([\'"])/', '$1' . $buttonLink . '$3', $buttonHtml);
        if (isset($this->_product['product-button-html']) && $this->_product['product-button-html']) {
            $buttonHtml = str_replace('[[button]]', $buttonHtml, $this->_product['product-button-html']);
            $buttonHtml = str_replace('<a', '<a name="addtocart"', $buttonHtml);
        }
        $buttonHtml .= vmJsApi::writeJS();

        vmJsApi::jPrice();
        vmJsApi::cssSite();

        return $buttonHtml;
    }

    /**
     * Set product price
     *
     * @param array $priceMatch Price match
     *
     * @return mixed|string|string[]|null
     */
    private function _setPriceData($priceMatch) {
        $priceHtml = $priceMatch[1];

        $priceHtml = preg_replace_callback(
            '/<\!--product_regular_price-->([\s\S]+?)<\!--\/product_regular_price-->/',
            function ($regularPriceMatch) {
                if ($this->_product['product-price']) {
                    return preg_replace('/<\!--product_regular_price_content-->([\s\S]+?)<\!--\/product_regular_price_content-->/', $this->_product['product-price'], $regularPriceMatch[1]);
                } else {
                    return '';
                }
            },
            $priceHtml
        );

        $priceHtml = preg_replace_callback(
            '/<\!--product_old_price-->([\s\S]+?)<\!--\/product_old_price-->/',
            function ($oldPriceMatch) {
                if ($this->_product['product-old-price'] && $this->_product['product-old-price'] !== $this->_product['product-price']) {
                    return preg_replace('/<\!--product_old_price_content-->([\s\S]+?)<\!--\/product_old_price_content-->/', $this->_product['product-old-price'], $oldPriceMatch[1]);
                } else {
                    return '';
                }
            },
            $priceHtml
        );

        return $priceHtml;
    }
}