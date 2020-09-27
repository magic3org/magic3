<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

JLoader::register('Nicepage_Data_Mappers', JPATH_ADMINISTRATOR . '/components/com_nicepage/tables/mappers.php');
JLoader::register('ContentModelCustomArticles', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/Models/ContentModelCustomArticles.php');

class BlogProcessor
{
    private $_posts = array();
    private $_post = array();

    private $_metaDataType = '';

    /**
     * Process blog
     *
     * @param string $content Content
     *
     * @return string|string[]|null
     */
    public function process($content) {
        return preg_replace_callback('/<\!--blog-->([\s\S]+?)<\!--\/blog-->/', array(&$this, '_processBlog'), $content);
    }

    /**
     * Process blog
     *
     * @param array $blogMatch Matches
     *
     * @return string|string[]|null
     */
    private function _processBlog($blogMatch) {
        $blogHtml = $blogMatch[1];
        $blogOptions = [];
        if (preg_match('/<\!--blog_options_json--><\!--([\s\S]+?)--><\!--\/blog_options_json-->/', $blogHtml, $matches)) {
            $blogOptions = json_decode($matches[1], true);
            $blogHtml = str_replace($matches[0], '', $blogHtml);
        }
        $blogSourceType = isset($blogOptions['type']) ? $blogOptions['type'] : '';
        if ($blogSourceType === 'Tags') {
            $blogSource = 'tags:' . (isset($blogOptions['tags']) && $blogOptions['tags'] ? $blogOptions['tags'] : '');
        } else {
            $blogSource = isset($blogOptions['source']) && $blogOptions['source'] ? $blogOptions['source'] : '';
        }
        $this->_posts = $this->_getBlogPosts($blogSource);
        return preg_replace_callback('/<\!--blog_post-->([\s\S]+?)<\!--\/blog_post-->/', array(&$this, '_processBlogPost'), $blogHtml);
    }

    /**
     * Process post
     *
     * @param array $postMatch Matches
     *
     * @return mixed|string|string[]|null
     */
    private function _processBlogPost($postMatch) {
        $postHtml = $postMatch[1];

        if (count($this->_posts) < 1) {
            return ''; // remove cell, if post is missing
        }

        $this->_post = array_shift($this->_posts);
        $postHtml = preg_replace_callback('/<\!--blog_post_header-->([\s\S]+?)<\!--\/blog_post_header-->/', array(&$this, '_setHeaderData'), $postHtml);
        $postHtml = preg_replace_callback('/<\!--blog_post_content-->([\s\S]+?)<\!--\/blog_post_content-->/', array(&$this, '_setContentData'), $postHtml);
        $postHtml = preg_replace_callback('/<\!--blog_post_image-->([\s\S]+?)<\!--\/blog_post_image-->/', array(&$this, '_setImageData'), $postHtml);
        $postHtml = preg_replace_callback('/<\!--blog_post_readmore-->([\s\S]+?)<\!--\/blog_post_readmore-->/', array(&$this, '_setReadmoreData'), $postHtml);
        $postHtml = preg_replace_callback('/<\!--blog_post_metadata-->([\s\S]+?)<\!--\/blog_post_metadata-->/', array(&$this, '_setMetadataData'), $postHtml);
        $postHtml = preg_replace_callback('/<\!--blog_post_tags-->([\s\S]+?)<\!--\/blog_post_tags-->/', array(&$this, '_setTagsData'), $postHtml);
        return $postHtml;
    }

    /**
     * Get blog post by source
     *
     * @param string $source Source
     *
     * @return array
     */
    private function _getBlogPosts($source) {
        $posts = array();
        $categoryId = '';
        $tags = '';
        if ($source) {
            if (preg_match('/^tags:/', $source)) {
                $tags = str_replace('tags:', '', $source);
            } else {
                $categoryObject = Nicepage_Data_Mappers::get('category');
                $categoryList = $categoryObject->find(array('title' => $source));
                if (count($categoryList) < 1) {
                    return $posts;
                }
                $categoryId = $categoryList[0]->id;
            }
        }
        // Get recent articles, if $categoryId is empty
        $blog = new ContentModelCustomArticles(array('category_id' => $categoryId, 'tags' => $tags));
        return $blog->getPosts();
    }

    /**
     * Set header
     *
     * @param string $headerMatch Header match
     *
     * @return mixed|string|string[]|null
     */
    private function _setHeaderData($headerMatch) {
        $headerHtml = $headerMatch[1];
        $headerHtml = preg_replace_callback(
            '/<\!--blog_post_header_content-->([\s\S]+?)<\!--\/blog_post_header_content-->/',
            function ($headerContentMatch) {
                return isset($this->_post['post-header']) ? $this->_post['post-header'] : $headerContentMatch[1];
            },
            $headerHtml
        );
        $headerLink = isset($this->_post['post-header-link']) ? $this->_post['post-header-link'] : '#';
        $headerHtml = preg_replace('/(href=[\'"])([\s\S]+?)([\'"])/', '$1' . $headerLink . '$3', $headerHtml);
        return $headerHtml;
    }

    /**
     * Set content
     *
     * @param string $contentMatch Content match
     *
     * @return mixed|string|string[]|null
     */
    private function _setContentData($contentMatch) {
        $contentHtml = $contentMatch[1];
        $contentHtml = preg_replace_callback(
            '/<\!--blog_post_content_content-->([\s\S]+?)<\!--\/blog_post_content_content-->/',
            function ($contentMatch) {
                return isset($this->_post['post-content']) ? $this->_post['post-content'] : $contentMatch[1];
            },
            $contentHtml
        );
        return $contentHtml;
    }

    /**
     * Set image
     *
     * @param string $imageMatch Image match
     *
     * @return mixed
     */
    private function _setImageData($imageMatch) {
        $imageHtml = $imageMatch[1];
        $isBackgroundImage = strpos($imageHtml, '<div') !== false ? true : false;

        $src = isset($this->_post['post-image'])? $this->_post['post-image'] : '';
        if (!$src) {
            return '';
        }

        if ($isBackgroundImage) {
            if (strpos($imageHtml, 'data-bg') !== false) {
                $imageHtml = preg_replace('/(data-bg=[\'"])([\s\S]+?)([\'"])/', '$1url(' . $this->_post['post-image'] . ')$3', $imageHtml);
            } else {
                $imageHtml = str_replace('<div', '<div' . ' style="background-image:url(' . $this->_post['post-image'] . ')"', $imageHtml);
            }
        } else {
            $imageHtml = preg_replace('/(src=[\'"])([\s\S]+?)([\'"])/', '$1' . $this->_post['post-image'] . '$3', $imageHtml);
        }
        return $imageHtml;
    }

    /**
     * Set readmore
     *
     * @param string $readmoreMatch Readmre match
     *
     * @return mixed|string|string[]|null
     */
    private function _setReadmoreData($readmoreMatch) {
        $readmoreHtml = $readmoreMatch[1];
        $readmoreHtml = preg_replace_callback(
            '/<\!--blog_post_readmore_content-->([\s\S]+?)<\!--\/blog_post_readmore_content-->/',
            function ($readmoreContentMatch) {
                return isset($this->_post['post-readmore-text']) ? $this->_post['post-readmore-text'] : $readmoreContentMatch[1];
            },
            $readmoreHtml
        );
        $readmoreLink = isset($this->_post['post-readmore-link']) ? $this->_post['post-readmore-link'] : '#';
        $readmoreHtml = preg_replace('/(href=[\'"])([\s\S]+?)([\'"])/', '$1' . $readmoreLink . '$3', $readmoreHtml);
        return $readmoreHtml;
    }

    /**
     * Set metadata
     *
     * @param string $metadataMatch Metadata match
     *
     * @return mixed|string|string[]|null
     */
    private function _setMetadataData($metadataMatch) {
        $metadataHtml = $metadataMatch[1];
        $metaDataTypes = array('date', 'author', 'category', 'comments', 'edit');
        foreach ($metaDataTypes as $type) {
            $this->_metaDataType = $type;
            $metadataHtml = preg_replace_callback(
                '/<\!--blog_post_metadata_' . $this->_metaDataType . '-->([\s\S]+?)<\!--\/blog_post_metadata_' . $this->_metaDataType . '-->/',
                function ($metadataTypeMatch) {
                    return $metadataTypeMatch[1];
                },
                $metadataHtml
            );
            $metadataHtml = preg_replace_callback(
                '/<\!--blog_post_metadata_' . $this->_metaDataType . '_content-->([\s\S]+?)<\!--\/blog_post_metadata_' . $this->_metaDataType . '_content-->/',
                function ($metadataTypeContentMatch) {
                    return isset($this->_post['post-metadata-' . $this->_metaDataType]) ? $this->_post['post-metadata-' . $this->_metaDataType] : $metadataTypeContentMatch[1];
                },
                $metadataHtml
            );
        }
        return $metadataHtml;
    }

    /**
     * Set tags
     *
     * @param string $tagsMatch tags match
     *
     * @return mixed|string|string[]|null
     */
    private function _setTagsData($tagsMatch) {
        $tagsHtml = $tagsMatch[1];
        $tagsHtml = preg_replace_callback(
            '/<\!--blog_post_tags_content-->([\s\S]+?)<\!--\/blog_post_tags_content-->/',
            function ($contentTagsMatch) {
                return isset($this->_post['post-tags']) ? $this->_post['post-tags'] : $contentTagsMatch[1];
            },
            $tagsHtml
        );
        return $tagsHtml;
    }
}