<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

JLoader::register('Nicepage_Image_Processor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/images.php');
/**
 * Class Nicepage_Site_Posts_Builder
 */
class Nicepage_Site_Posts_Builder
{
    /**
     * @param JInput $data Data parameters
     *
     * @return array
     */
    public function getSitePosts($data)
    {
        $result = array();
        $options = $data->get('options', array(), 'RAW');

        if (isset($options['page'])) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__content');
            $query->where('(state = 1 or state = 0)');
            $query->order('created', 'desc');
            $query->where('id in (' . $options['page'] . ')');
            $db->setQuery($query);
            $list = $db->loadObjectList();
            $posts = $this->_getPosts($list);

            return array(
                'posts' => array(
                    'text' => $posts[0]['text'],
                    'images' => $posts[0]['images'],
                    'url' => $this->_getArticleUrlById($list[0]->id),
                )
            );
        }

        $posts = array();
        if (isset($options['pageNumber'])) {
            $pageSize = 20;
            $pageNumber = isset($options['pageNumber']) ? (int)$options['pageNumber'] : 1;

            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__content');
            $query->where('(state = 1 or state = 0)');
            $query->order('created', 'desc');
            $sectionsPageIds = NicepageHelpersNicepage::getSectionsTable()->getAllPageIds();
            if (count($sectionsPageIds) > 0) {
                $query->where('id not in (' . implode(',', $sectionsPageIds) . ')');
            }
            $db->setQuery($query, ($pageNumber - 1) * $pageSize, $pageSize);
            $list = $db->loadObjectList();


            $posts = $this->_getPosts($list);
            if (count($posts) < $pageSize) {
                $result['nextPage'] = 0;
                $result['isMultiplePages'] = false;
            } else {
                $result['nextPage'] = $pageNumber + 1;
                $result['isMultiplePages'] = true;
            }
        }

        $products = array();
        if (isset($options['productsPageNumber'])) {
            ob_start();
            $productsSize = 20;
            $productsPageNumber = (int)$options['productsPageNumber'];
            $products = $this->_getProducts(null, $productsSize, $productsPageNumber);
            if (count($products) < $productsSize) {
                $result['nextProductsPage'] = 0;
                $result['isMultipleProducts'] = false;
            } else {
                $result['nextProductsPage'] = $productsPageNumber + 1;
                $result['isMultipleProducts'] = true;
            }
            $result['virtuemartMessages'] = ob_get_clean();
        }

        $items = array_merge($posts, $products);

        $result['posts'] = $items;

        $images = array();
        if (isset($options['imagesPageNumber'])) {
            $imagesSize = 20;
            $imagesPageNumber = (int)$options['imagesPageNumber'];
            $term = isset($options['term']) ? $options['term'] : '';
            $imagesInfo = $this->_getImagesFromMedia($imagesSize, $imagesPageNumber, $term);
            $images = $imagesInfo['posts'];
            $count = $imagesInfo['count'];
            if ($count < $imagesSize) {
                $result['nextImagesPage'] = 0;
                $result['isMultipleImages'] = false;
            } else {
                $result['nextImagesPage'] = $imagesPageNumber + 1;
                $result['isMultipleImages'] = true;
            }
        }
        $result['images'] = $images;

        return $result;
    }

    /**
     * @param null $cids               Ids array
     * @param int  $productsSize       Count products
     * @param int  $productsPageNumber Limit parameter
     *
     * @return array
     */
    private function _getProducts($cids = null, $productsSize = 0, $productsPageNumber = 0)
    {
        $result = array();

        if (!file_exists(dirname(JPATH_ADMINISTRATOR) . '/components/com_virtuemart/')) {
            return false;
        }

        if (!JComponentHelper::getComponent('com_virtuemart', true)->enabled) {
            return $result;
        }

        $categoryId = 0;
        $imgAmount = 5;

        if (!class_exists('VmConfig')) {
            include_once JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php';
        }
        VmConfig::loadConfig();

        if (!class_exists('vmLanguage')) {
            include_once VMPATH_ADMIN . '/helpers/vmlanguage.php';
        }
        vmLanguage::loadJLang('com_virtuemart');

        if (!class_exists('VmModel')) {
            include_once VMPATH_ADMIN . '/helpers/vmmodel.php';
        }

        $productModel = VmModel::getModel('product');
        $ids = $productModel->sortSearchListQuery(true, $categoryId);
        if ($productsSize) {
            $ids = array_slice($ids, ($productsPageNumber - 1) * $productsSize, $productsSize);
        }
        $products = $productModel->getProducts($ids);
        $productModel->addImages($products, $imgAmount);
        $currency = CurrencyDisplay::getInstance();

        foreach ($products as $product) {
            if ($cids && !in_array($product->id, $cids)) {
                continue;
            }
            $item = array(
                'postType' => 'product',
                'id' => 'cms_p_' . $product->id,
                'h1' => array(array('content' => $product->product_name, 'type' => 'h1')),
                'images' => array(),
                'text' => array(array('content' => $product->product_desc))
            );

            foreach ($product->images as $image) {
                $filePath = JPATH_ROOT . '/' . $image->file_url;
                $info = @getimagesize($filePath);
                $item['images'][] = array('sizes' => array(array(
                    'height' => @$info[1],
                    'url' => str_replace(JPATH_SITE, $this->_getHomeUrl(), $filePath),
                    'width' => @$info[0],
                )), 'type' => 'image');
            }
            $priceText = $currency->createPriceDiv('salesPrice', 'COM_VIRTUEMART_PRODUCT_SALESPRICE', $product->prices, true);
            $item['h2'] = array(array('content' => $priceText, 'type' => 'h2'));

            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param array $posts Cms posts
     *
     * @return array
     */
    private function _getPosts($posts)
    {
        $result = array();

        if (count($posts) < 1) {
            return $result;
        }

        foreach ($posts as $key => $item) {
            $post = array(
                'url' => $this->_getArticleUrlById($item->id),
                'date' => $item->created,
                'h1' => array(array('content' => $item->title, 'type' => 'h1')),
                'images' => array(),
            );
            $post['id'] = 'cms_' . $item->id;
            $content = $item->introtext . $item->fulltext;
            // third-party plugins
            $content = JHtml::_('content.prepare', $content);
            // themler shortcodes plugin
            $scpath = JPATH_PLUGINS . '/content/themlercontent/lib/Shortcodes.php';
            if (file_exists($scpath) && $content) {
                include_once $scpath;
                $content = DesignerShortcodes::process($content);
            }

            $images = json_decode($item->images, true);
            $imgsContent = isset($images['image_intro']) ? ('<img src="' . $images['image_intro'] . '" />') : '';
            $imgsContent .= isset($images['image_fulltext']) ? ('<img src="' . $images['image_fulltext'] . '" />') : '';

            $imageProcessor = new Nicepage_Image_Processor();
            $imageProcessor->prepareImages($imgsContent);
            $images = $imageProcessor->getImages();

            $contentImageProcessor = new Nicepage_Image_Processor();
            $content = $contentImageProcessor->prepareImages($content);
            $contentImages = $contentImageProcessor->getImages();

            $post['text'] = array(array('content' => $content));
            $allImages = array_merge($images, $contentImages);
            foreach ($allImages as $image) {
                $info = @getimagesize($image);
                $post['images'][] = array('sizes' => array(array(
                    'height' => @$info[1],
                    'url' => str_replace(JPATH_SITE, $this->_getHomeUrl(), $image),
                    'width' => @$info[0],
                )), 'type' => 'image');
            }
            $result[] = $post;
        }
        return $result;
    }

    /**
     * @param int    $imagesSize       Count images
     * @param int    $imagesPageNumber Limit parameter
     * @param string $term             Search term
     *
     * @return array
     */
    private function _getImagesFromMedia($imagesSize, $imagesPageNumber, $term)
    {
        $mediaPosts = array();
        $count = 0;
        $params = JComponentHelper::getParams('com_media');
        $root = str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT);
        $imagesPath = $root . '/' . $params->get('image_path', 'images');
        if (file_exists($imagesPath)) {
            $regex = '\.jpg|\.png|\.gif|\.bmp|\.jpeg|\.ico';
            if ($term) {
                $regex = '[\s\S]*?' . $term . '[\s\S]*?' . '(' . $regex . ')';
            }
            $fileList = JFolder::files($imagesPath, $regex, true, true);
            $fileList = array_slice($fileList, ($imagesPageNumber - 1) * $imagesSize, $imagesSize);
            $count = count($fileList);
            foreach ($fileList as $key => $file) {
                if (!json_encode($file)) {
                    continue;
                }
                $fileName = basename($file);
                $mediaPost = array(
                    'h1' => array(array('content' => 'Image' . ++$key)),
                    'images' => array(),
                    'id' => $fileName,
                    'fileName' => $fileName,
                );
                $path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($file));
                $info = @getimagesize($path);
                $mediaPost['images'][] = array('sizes' => array(array(
                    'height' => @$info[1],
                    'url' => str_replace($root, $this->_getHomeUrl(), $path),
                    'width' => @$info[0],
                )));
                $mediaPost['postType'] = 'image';
                $mediaPosts[] = $mediaPost;
            }
        }
        return array('posts' => $mediaPosts, 'count' => $count);
    }

    /**
     * @return string
     */
    private function _getHomeUrl()
    {
        return dirname(dirname(JURI::current()));
    }

    /**
     * @param int $id Article id
     *
     * @return string
     */
    private function _getArticleUrlById($id)
    {
        return $this->_getHomeUrl() . '/index.php?option=com_content&view=article&id=' . $id;
    }
}