<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

JLoader::register('PostDataBuilder', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Builder/PostDataBuilder.php');
JLoader::register('ContentModelArticles', JPATH_ROOT . '/components/com_content/models/articles.php');

/**
 * Class NicepageContentModelBlog
 */
class ContentModelCustomArticles extends ContentModelArticles
{
    private $_options = array();

    /**
     * ContentModelCustomArticles constructor.
     *
     * @param array $options options
     */
    public function __construct($options = array())
    {
        $this->_options = $options;
        parent::__construct();
    }

    /**
     * Set settigns for state
     *
     * @param string $ordering  Order
     * @param string $direction Direction
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        parent::populateState($ordering, $direction);
        if (isset($this->_options['category_id']) && $this->_options['category_id']) {
            $this->setState('filter.category_id', $this->_options['category_id']);
        }
        if (isset($this->_options['tags']) && $this->_options['tags']) {
            $tags = array_map('trim', explode(',', $this->_options['tags']));
            $tagIds = array();
            $tagsHelper = new JHelperTags();
            foreach ($tags as $tag) {
                $items = $tagsHelper->searchTags(array('like' => $tag));
                foreach ($items as $item) {
                    array_push($tagIds, $item->value);
                }
            }
            if (count($tagIds) < 1) {
                $tagIds = array(0, 0);
            }
            $this->setState('filter.tag', $tagIds);
        }
        $this->setState('filter.published', 1);
        $this->setState('list.ordering', 'modified');
        $this->setState('list.direction', 'DESC');
        $this->setState('list.start', 0);
        $this->setState('list.limit', 20);
    }

    /**
     * @return mixed
     */
    public function getItems() {
        return parent::getItems();
    }

    /**
     * Get posts by category id
     *
     * @return array
     */
    public function getPosts() {
        // exclude np pages
        $sectionsPageIds = NicepageHelpersNicepage::getSectionsTable()->getAllPageIds();
        $posts = array();
        $items = $this->getItems();
        foreach ($items as $key => $item) {
            if (in_array($item->id, $sectionsPageIds)) {
                continue;
            }
            $builder = new PostDataBuilder($item);
            $post = $builder->getData();
            array_push($posts, $post);
        }
        return $posts;
    }
}