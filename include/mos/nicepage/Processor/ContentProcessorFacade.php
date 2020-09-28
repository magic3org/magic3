<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

/*
JLoader::register('CommonProcessor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Processor/CommonProcessor.php');
JLoader::register('ControlsProcessor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Processor/ControlsProcessor.php');
JLoader::register('PositionsProcessor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Processor/PositionsProcessor.php');
JLoader::register('BlogProcessor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Processor/BlogProcessor.php');
JLoader::register('ProductsProcessor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Processor/ProductsProcessor.php');
JLoader::register('ShoppingCartProcessor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Processor/ShoppingCartProcessor.php');
*/
require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/Processor/CommonProcessor.php');
require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/Processor/ControlsProcessor.php');
require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/Processor/PositionsProcessor.php');
require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/Processor/BlogProcessor.php');
require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/Processor/ProductsProcessor.php');
require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/Processor/ShoppingCartProcessor.php');

class ContentProcessorFacade
{
    private $_isPulic;
    private $_pageId;

    /**
     * ContentProcessorFacade constructor.
     *
     * @param bool   $isPublic Is public content
     * @param string $pageId   Page id
     */
    public function __construct($isPublic = true, $pageId = '')
    {
        $this->_isPulic = $isPublic;
        $this->_pageId = $pageId;
    }

    /**
     * Process content
     *
     * @param string $content Page content
     *
     * @return mixed|string|string[]|null
     */
    public function process($content)
    {
        $common = new CommonProcessor();
        $content = $common->processDefaultImage($content);
        if ($this->_isPulic) {
            $content = $common->processForm($content, $this->_pageId);
            $content = $common->processCustomPhp($content);
            $content = ControlsProcessor::process($content);

            $blog = new BlogProcessor();
            $content = $blog->process($content);

            $products = new ProductsProcessor();
            $content = $products->process($content);

            $shoppingCart = new ShoppingCartProcessor();
            $content = $shoppingCart->process($content);
        }
        $content = PositionsProcessor::process($content);
        return $content;
    }
}