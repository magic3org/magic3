<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

class ShoppingCartProcessor
{
    private $_cart = array();

    /**
     * Process shopping cart
     *
     * @param string $content Content
     *
     * @return string|string[]|null
     */
    public function process($content) {
        return preg_replace_callback('/<\!--shopping_cart-->([\s\S]+?)<\!--\/shopping_cart-->/', array(&$this, '_processShoppingCart'), $content);
    }

    /**
     * Process one shopping cart
     *
     * @param array $shoppingCartMatch Matches
     *
     * @return string|string[]|null
     */
    private function _processShoppingCart($shoppingCartMatch) {
        $shoppingCartHtml = $shoppingCartMatch[1];
        $this->_cart = $this->_getCart();

        if (!$this->_cart) {
            return $shoppingCartHtml;
        }

        $shoppingCartHtml = preg_replace('/(\s+href=[\'"])([\s\S]+?)([\'"])/', '$1' . $this->_cart['link'] . '$3', $shoppingCartHtml);
        $shoppingCartHtml = preg_replace_callback('/<\!--shopping_cart_count-->([\s\S]+?)<\!--\/shopping_cart_count-->/', array(&$this, '_processShoppingCartCount'), $shoppingCartHtml);
        return $shoppingCartHtml;
    }

    /**
     * Process shopping cart products count
     * 
     * @return int|mixed
     */
    private function _processShoppingCartCount() {
        return isset($this->_cart['count']) ? $this->_cart['count'] : 0;
    }

    /**
     * Get sshopping cart
     *
     * @return array
     */
    private function _getCart()
    {
        $model = new ContentModelCustomProducts();
        return $model->getCart();
    }
}
