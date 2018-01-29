<?php

Designer::load('Designer_Data_Mappers');

class Designer_Data_Loader
{
    /**
     * Loaded content.
     */
    private $_data;

    /**
     * The data item that is currently being loaded: article, module, etc.
     */
    private $_dataItem;

    /**
     * Path to the data item that is currently being loaded.
     */
    private $_path;

    /**
     * Numeric identificator of the currently selected template style in Joomla
     * administrator.
     */
    private $_style;

    /**
     * Absolute path to the directory with content images.
     */
    private $_images;

    /**
     * Name of the template.
     */
    private $_template;

    function getData() {
        return $this->_data;
    }

    public function load($file)
    {
        $path = realpath($file);
        if (false === $path)
            return;
        $images = dirname($path) . '/images';
        if (file_exists($images) && is_dir($images))
            $this->_images = $images;
        $this->_template = basename(dirname(dirname($path)));
        return $this->_parse($path);
    }

    public function execute($params)
    {
        $callback = array();
        $callback[] = $this;
        $callback[] = '_error';
        Designer_Data_Mappers::errorCallback($callback);

        $action = isset($params['action']) && is_string($params['action']) ? $params['action'] : '';
        if (0 == strlen($action) || !in_array($action, array('check', 'run', 'params')))
            return 'Invalid action.';
        $this->_style = isset($params['id']) && is_string($params['id'])
            && ctype_digit($params['id']) ? intval($params['id'], 10) : -1;
        if (-1 === $this->_style)
            return 'Invalid style id.';
        $result = '';
        switch ($action) {
            case 'check':
                $result = 'result:' . ($this->_isInstalled() ? '1' : '0');
                break;
            case 'run':
                $this->_load();
                $this->_setParameters();
                $result = 'result:ok';
                break;
            case 'params':
                $this->_setParameters();
                $parameters = array();
                foreach ($this->_data['parameter'] as $key => $parameterData){
                    $parameters['jform_params_' . $parameterData['name']] = $parameterData['value'];
                }
                $result = 'params:' . json_encode($parameters);
                break;
        }
        return $result;
    }

    public function _error($msg, $code)
    {
        exit('error:' . $code . ':' . $msg);
    }

    private function _isInstalled()
    {
        $categories = Designer_Data_Mappers::get('category');
        $menus = Designer_Data_Mappers::get('menu');
        $modules = Designer_Data_Mappers::get('module');
        foreach ($this->_data['category'] as $value) {
            $categoriesList = $categories->find(array('title' => $value['title']));
            if (0 != count($categoriesList))
                return true;
        }

        foreach ($this->_data['menu'] as $value) {
            $menusList = $menus->find(array('title' => $value['title']));
            if (0 != count($menusList))
                return true;
        }

        foreach ($this->_data['module'] as $value) {
            $modulesList = $modules->find(array('title' => $value['title']));
            if (0 != count($modulesList))
                return true;
        }

        return false;
    }

    private function _load()
    {
        $this->_loadContent();
        $this->_loadMenus();
        $this->_createModules();
        $this->_updateContent();
        $this->_configureModulesVisibility();
        $this->_configureEditor();
        $this->_copyImages();
        $this->_updateIndexPage();
    }

    private function _updateIndexPage()
    {
        $index = dirname(dirname(dirname(dirname(__FILE__)))) .  DS . 'index.php';
        $content = file_get_contents($index);
        file_put_contents($index, $this->_processingContent($content));
    }

    private function _loadContent()
    {
        $categories = Designer_Data_Mappers::get('category');
        $content = Designer_Data_Mappers::get('content');

        foreach ($this->_data['category'] as & $categoryData) {
            $categoryList = $categories->find(array('title' => $categoryData['title']));
            foreach ($categoryList as & $categoryListItem)
                $categories->delete($categoryListItem->id);
        }

        foreach ($this->_data['category'] as & $categoryData) {
            $category = $categories->create();
            $category->title = $categoryData['title'];
            $category->extension = 'com_content';
            if (isset($categoryData['parent']))
                $category->setLocation($this->_data['category'][$categoryData['parent']]['joomla_id'], 'last-child');
            $category->metadata = $this->_paramsToString(array('robots' => '', 'author' => '', 'tags' => ''));
            $status = $categories->save($category);
            if (is_string($status))
                return $this->_error($status, 1);
            $categoryData['joomla_id'] = $category->id;
        }

        foreach ($this->_data['article'] as & $articleData) {
            $article = $content->create();
            $article->catid = $this->_data['category'][$articleData['category']]['joomla_id'];
            $article->title = $articleData['title'];
            $article->alias = $articleData['alias'];
            $article->introtext = $articleData['text'];
            $article->attribs = $this->_paramsToString(array
                (
                    'show_title' => '',
                    'link_titles' => '',
                    'show_intro' => '',
                    'show_category' => '',
                    'link_category' => '',
                    'show_parent_category' => '',
                    'link_parent_category' => '',
                    'show_author' => '',
                    'link_author' => '',
                    'show_create_date' => '',
                    'show_modify_date' => '',
                    'show_publish_date' => '',
                    'show_item_navigation' => '',
                    'show_icons' => '',
                    'show_print_icon' => '',
                    'show_email_icon' => '',
                    'show_vote' => '',
                    'show_hits' => '',
                    'show_noauth' => '',
                    'alternative_readmore' => '',
                    'article_layout' => ''
                ));
            if (isset($articleData['description']))
                $article->metadesc = $articleData['description'];
            if (isset($articleData['keywords']))
                $article->metakey = $articleData['keywords'];
            $article->metadata = $this->_paramsToString(array('robots' => '', 'author' => '', 'rights' => '', 'xreference' => '', 'tags' => ''));
            $status = $content->save($article);
            if (is_string($status))
                return $this->_error($status, 1);
            $articleData['joomla_id'] = $article->id;
        }
    }

    function _loadMenus()
    {
        $menus = Designer_Data_Mappers::get('menu');
        $menuItems = Designer_Data_Mappers::get('menuItem');

        $home = $menuItems->find(array('home' => 1));

        // Create a temporary menu with one item to clean up the Home flag:
        $rndMenu = $menus->create();
        $rndMenu->title = $rndMenu->menutype = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10);
        $status = $menus->save($rndMenu);
        if (is_string($status))
            return $this->_error($status, 1);
        $rndItem = $menuItems->create();
        $rndItem->home = '1';
        $rndItem->checked_out = $home[0]->checked_out;
        $rndItem->menutype = $rndMenu->menutype;
        $rndItem->alias = $rndItem->title = $rndMenu->menutype;
        $rndItem->link = 'index.php?option=com_content&view=article&id=';
        $rndItem->type = 'component';
        $rndItem->component_id = '22';
        $rndItem->params = $this->_paramsToString(array());
        $status = $menuItems->save($rndItem);
        if (is_string($status))
            return $this->_error($status, 1);

        $db = JFactory::getDBO();

        foreach ($this->_data['menu'] as & $menuData) {
            $menuList = $menus->find(array('title' => $menuData['title']));
            $mid = array();
            foreach ($menuList as $menuListItem) {
                $status = $menus->delete($menuListItem->id);
                if (is_string($status))
                    return $this->_error($status, 1);
            }
        }

        foreach ($this->_data['menu'] as & $menuData) {
            $menu = $menus->create();
            $menu->menutype = $menuData['name'];
            $menu->title = $menuData['title'];
            $status = $menus->save($menu);
            if (is_string($status))
                return $this->_error($status, 1);
        }

        foreach ($this->_data['menuitem'] as & $itemData) {
            $item = $menuItems->create();

            if (isset($itemData['default']) && $itemData['default'])
                $item->home = '1';

            $item->menutype = $itemData['menu'];
            $item->title = $itemData['title'];
            $item->alias = $itemData['alias'];

            $params = array();
            switch ($itemData['type']) {
                case 'single-article':
                    $id = '';
                    if (isset($itemData['article']))
                        $id = $this->_data['article'][$itemData['article']]['joomla_id'];
                    $item->link = 'index.php?option=com_content&view=article&id=' . $id;
                    $item->type = 'component';
                    $item->component_id = '22';
                    $params = array
                        (
                            'show_title' => 'yes' === $itemData['showTitle'] ? '1' : '0',
                            'link_titles' => '',
                            'show_intro' => '',
                            'show_category' => '0',
                            'link_category' => '',
                            'show_parent_category' => '0',
                            'link_parent_category' => '',
                            'show_author' => '0',
                            'link_author' => '',
                            'show_create_date' => '0',
                            'show_modify_date' => '0',
                            'show_publish_date' => '0',
                            'show_item_navigation' => '0',
                            'show_vote' => '0',
                            'show_icons' => '0',
                            'show_print_icon' => '0',
                            'show_email_icon' => '0',
                            'show_hits' => '0',
                            'show_noauth' => '',
                            'menu-anchor_title' => '',
                            'menu-anchor_css' => '',
                            'menu_image' => '',
                            'menu_text' => '1',
                            'page_title' => '',
                            'show_page_heading' => '0',
                            'page_heading' => '',
                            'pageclass_sfx' => '',
                            'menu-meta_description' => '',
                            'menu-meta_keywords' => '',
                            'robots' => '',
                            'secure' => '0',
                            'page_title' => $itemData['titleInBrowser']
                        );
                    break;
                case 'category-blog-layout':
                    $item->link = 'index.php?option=com_content&view=category&layout=blog&id='
                        . $this->_data['category'][$itemData['category']]['joomla_id'];
                    $item->type = 'component';
                    $item->component_id = '22';
                    $params = array
                        (
                            'layout_type' => 'blog',
                            'show_category_title' => '',
                            'show_description' => '',
                            'show_description_image' => '',
                            'maxLevel' => '',
                            'show_empty_categories' => '',
                            'show_no_articles' => '',
                            'show_subcat_desc' => '',
                            'show_cat_num_articles' => '',
                            'page_subheading' => '',
                            'num_leading_articles' => '0',
                            'num_intro_articles' => '4',
                            'num_columns' => '1',
                            'num_links' => '',
                            'multi_column_order' => '',
                            'show_subcategory_content' => '',
                            'orderby_pri' => '',
                            'orderby_sec' => 'order',
                            'order_date' => '',
                            'show_pagination' => '',
                            'show_pagination_results' => '',
                            'show_title' => '',
                            'link_titles' => '',
                            'show_intro' => '',
                            'show_category' => '',
                            'link_category' => '',
                            'show_parent_category' => '',
                            'link_parent_category' => '',
                            'show_author' => '',
                            'link_author' => '',
                            'show_create_date' => '',
                            'show_modify_date' => '',
                            'show_publish_date' => '',
                            'show_item_navigation' => '',
                            'show_vote' => '',
                            'show_readmore' => '',
                            'show_readmore_title' => '',
                            'show_icons' => '',
                            'show_print_icon' => '',
                            'show_email_icon' => '',
                            'show_hits' => '',
                            'show_noauth' => '',
                            'show_feed_link' => '',
                            'feed_summary' => '',
                            'menu-anchor_title' => '',
                            'menu-anchor_css' => '',
                            'menu_image' => '',
                            'menu_text' => 1,
                            'page_title' => '',
                            'show_page_heading' => 0,
                            'page_heading' => '',
                            'pageclass_sfx' => '',
                            'menu-meta_description' => '',
                            'menu-meta_keywords' => '',
                            'robots' => '',
                            'secure' => 0,
                            'page_title' => $itemData['titleInBrowser']
                        );
                    break;
            }

            // parameters:
            $item->params = $this->_paramsToString($params);

            // parent:
            if (isset($itemData['parent']))
                 $item->setLocation($this->_data['menuitem'][$itemData['parent']]['joomla_id'], 'last-child');

            $item->template_style_id = $this->_style;

            $status = $menuItems->save($item);
            if (is_string($status))
                return $this->_error($status, 1);

            $itemData['joomla_id'] = $item->id;
        }

        $status = $menus->delete($rndMenu->id);
        if (is_string($status))
            return $this->_error($status, 1);
    }

    private function _updateContent()
    {
        $content = Designer_Data_Mappers::get('content');
        foreach ($this->_data['article'] as & $articleData) {
            $article = $content->fetch($articleData['joomla_id']);
            if (!is_null($article)) {
                $text = $this->_processingContent($articleData['text']);
                $parts = explode('<!--CUT-->', $text);
                $article->introtext = $parts[0];
                if (count($parts) > 1)
                    $article->fulltext = $parts[1];
                $status = $content->save($article);
                if (is_string($status))
                    return $this->_error($status, 1);
            }
        }
    }

    private function _createModules()
    {
        $modules = Designer_Data_Mappers::get('module');

        foreach ($this->_data['module'] as & $moduleData) {
            $moduleList = $modules->find(array('title' => $moduleData['title']));
            foreach ($moduleList as & $moduleListItem)
                $modules->delete($moduleListItem->id);
        }

        $order = array();

        foreach ($this->_data['module'] as & $moduleData) {
            $module = $modules->create();
            $module->title = $moduleData['title'];
            $module->position = $moduleData['position'];
            $style = isset($moduleData['style']) ? $moduleData['style'] : '';
            $params = array();
            switch ($moduleData['type']) {
                case 'menu':
                    $module->module = 'mod_menu';
                    $params = array
                        (
                            'menutype' => $moduleData['menu'],
                            'startLevel' => '1',
                            'endLevel' => '0',
                            'showAllChildren' => '1',
                            'tag_id' => '',
                            'class_sfx' => '',
                            'window_open' => '',
                            'layout' => '_:default',
                            'moduleclass_sfx' => $style,
                            'cache' => '1',
                            'cache_time' => '900',
                            'cachemode' => 'itemid'
                        );
                    break;
                case 'login':
                    $module->module = 'mod_login';
                    $params = array
                        (
                            'pretext' => '',
                            'posttext' => '',
                            'login' => '',
                            'logout' => '',
                            'greeting' => '1',
                            'name' => '0',
                            'usesecure' => '0',
                            'layout' => '_:default',
                            'moduleclass_sfx' => '',
                            'cache' => '0'
                        );
                    break;
                case 'search':
                    $module->module = 'mod_search';
                    $params = array
                        (
                            'layout' => '_:default',
                            'moduleclass_sfx' => '',
                            'cache' => '0'
                        );
                    break;
                case 'custom':
                    $module->module = 'mod_custom';
                    $module->content = $this->_processingContent($moduleData['content']);
                    $params = array
                        (
                            'prepare_content' => '1',
                            'layout' => '_:default',
                            'moduleclass_sfx' => '',
                            'cache' => '1',
                            'cache_time' => '900',
                            'cachemode' => 'static'
                        );
                    break;
            }

            // show title:
            $module->showtitle = 'true' == $moduleData['showTitle'] ? '1' : '0';

            // style:
            if (isset($moduleData['style']) && isset($params['moduleclass_sfx']))
                $params['moduleclass_sfx'] = $moduleData['style'];

            // parameters:
            $module->params = $this->_paramsToString($params);

            // ordering:
            if (!isset($order[$moduleData['position']]))
                $order[$moduleData['position']] = 1;
            $module->ordering = $order[$moduleData['position']];
            $order[$moduleData['position']]++;

            $status = $modules->save($module);
            if (is_string($status))
                return $this->_error($status, 1);
            $moduleData['joomla_id'] = $module->id;
        }
    }

    private function _parseHref($matches)
    {
        $path = urldecode($matches[1]);
        $menuItems = Designer_Data_Mappers::get('menuItem');
        foreach ($this->_data['menuitem'] as & $itemData) {
            if (isset($itemData['path']) && $path === $itemData['path']) {
                $menuItem = $menuItems->fetch($itemData['joomla_id']);
                if (!is_null($menuItem))
                    return 'href="' . $menuItem->link . '&Itemid=' . $menuItem->id . '"';
            }
        }

        $content = Designer_Data_Mappers::get('content');
        $specialMenuItems = array_slice($this->_data['menuitem'], -2);
        foreach ($this->_data['article'] as & $articleData) {
            if (isset($articleData['path']) && $path === $articleData['path']) {
                $article = $content->fetch($articleData['joomla_id']);
                $itemId = strstr($path, '/Blog Posts/') ? $specialMenuItems[0] : $specialMenuItems[1];
                if (!is_null($article))
                    return 'href="index.php?option=com_content&amp;view=article'.
                    '&amp;id=' . $article->id . '&amp;catid=' . $article->catid .
                    '&amp;Itemid=' . $itemId['joomla_id'] . '"';
            }
        }
        if ('' === $matches[1])
            return 'href="#"';
        else
            return $matches[0];
    }

    private function _processingContent($content) {

        $config = JFactory::getConfig();
        $live_site = $config->get('live_site');
        $root = trim($live_site) != '' ? JURI::root(true) : dirname(dirname(dirname(JURI::root(true))));
        if ('/' === substr($root, -1))
            $root  = substr($root, 0, -1);

        $content = str_replace('url(\\\'images/', 'url(\\\'' . $root . '/images/', $content);
        $content = preg_replace('/src="images\/template\//', 'src="' . $root .'/templates/' . $this->_template . '/images/', $content);
        $content = preg_replace_callback('/href="?([^"]*)"/', array( &$this, '_parseHref'), $content);
        return $content;
    }

    private function _configureModulesVisibility()
    {
        $contentMenuItems = array();
        foreach ($this->_data['menuitem'] as $item)
            $contentMenuItems[] = $item['joomla_id'];

        $contentModules = array();
        foreach ($this->_data['module'] as $module)
            $contentModules[] = $module['joomla_id'];

        $modules = Designer_Data_Mappers::get('module');
        $menuItems = Designer_Data_Mappers::get('menuItem');

        $userMenuItems = array();
        $menuItemList = $menuItems->find(array('scope' => 'site'));
        foreach ($menuItemList as $menuItem) {
            if (in_array($menuItem->id, $contentMenuItems))
                continue;
            $userMenuItems[] = $menuItem->id;
        }

        $moduleList = $modules->find(array('scope' => 'site'));
        foreach ($moduleList as $moduleListItem) {
            if (in_array($moduleListItem->id, $contentModules)) {
                $modules->enableOn($moduleListItem->id, $contentMenuItems);
            } else {
                $pages = $modules->getAssignment($moduleListItem->id);
                if (1 == count($pages) && '0' == $pages[0])
                    $modules->disableOn($moduleListItem->id, $contentMenuItems);
                if (0 < count($pages) && 0 > $pages[0]) {
                    $disableOnPages = array_unique(array_merge(array_map('abs', $pages), $contentMenuItems));
                    $modules->disableOn($moduleListItem->id, $disableOnPages);
                }
            }
        }
    }

    private function _setParameters()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('params')->from('#__template_styles')->where('id=' . $query->escape($this->_style));
        $db->setQuery($query);
        $parameters = $this->_stringToParams($db->loadResult());

        foreach ($this->_data['parameter'] as & $parameterData)
            $parameters[$parameterData['name']] = $parameterData['value'];

        $query = $db->getQuery(true);
        $query->update('#__template_styles')->set(
            $db->quoteName('params') . '=' .
                $db->quote($this->_paramsToString($parameters))
        )->where('id=' . $query->escape($this->_style));

        $db->setQuery($query);
        $db->query();
    }

    private function _configureEditor()
    {
        $extensions = Designer_Data_Mappers::get('extension');
        $tinyMce = $extensions->findOne(array('element' => 'tinymce'));
        if (is_string($tinyMce))
            return $this->_error($tinyMce, 1);
        if (!is_null($tinyMce)) {
            $params = $this->_stringToParams($tinyMce->params);
            $elements = strlen($params['extended_elements']) ? explode(',', $params['extended_elements']) : array();
            $invalidElements = strlen($params['invalid_elements']) ? explode(',', $params['invalid_elements']) : array();
            if (in_array('script', $invalidElements))
                array_splice($invalidElements, array_search('script', $invalidElements), 1);
            if (!in_array('style', $elements))
                $elements[] = 'style';
            if (!in_array('script', $elements))
                $elements[] = 'script';
            if (!in_array('div[*]', $elements))
                $elements[] = 'div[*]';
            $params['extended_elements'] = implode(',', $elements);
            $params['invalid_elements'] = implode(',', $invalidElements);
            $tinyMce->params = $this->_paramsToString($params);
            $status = $extensions->save($tinyMce);
            if (is_string($status))
                return $this->_error($status, 1);
        }
        return null;
    }

    private function _copyImages()
    {
        if (is_null($this->_images) || 0 == strlen($this->_images))
            return;
        $imgDir = dirname(JPATH_BASE) . DIRECTORY_SEPARATOR . 'images';
        $contentDir = $imgDir . DIRECTORY_SEPARATOR . 'template-content';
        if (!file_exists($contentDir))
            mkdir($contentDir);
        if ($handle = opendir($this->_images)) {
            while (false !== ($file = readdir($handle))) {
                if ('.' == $file || '..' == $file || is_dir($file))
                    continue;
                if (!preg_match('~\.(?:bmp|jpg|jpeg|png|ico|gif)$~i', $file))
                    continue;
                copy($this->_images . DIRECTORY_SEPARATOR . $file, $contentDir . DIRECTORY_SEPARATOR . $file);
            }
            closedir($handle);
        }
    }

    private function _paramsToString($params)
    {
        $registry = new JRegistry();
        $registry->loadArray($params);
        return $registry->toString();
    }

    private function _stringToParams($string)
    {
        $registry = new JRegistry();
        $registry->loadString($string);
        return $registry->toArray();
    }

    /**
     * Loads the content of the XML file specified by the $file parameter to the $_data class field.
     */
    function _parse($file)
    {
        $this->_data = array
            (
                'category' => array(),
                'article' => array(),
                'menu' => array(),
                'menuitem' => array(),
                'module' => array(),
                'parameter' => array()
            );
        $this->_dataItem = null;

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($parser, array($this, '_parserStartElementHandler'), array($this, '_parserEndElementHandler'));
        xml_set_character_data_handler($parser, array($this, '_parserCharacterDataHandler'));

        $error = null;
        if (!($fp = fopen($file, 'r')))
            $error = 'Could not open XML input';
        if (is_null($error)) {
            while ($data = fread($fp, 4096)) {
                if (xml_parse($parser, $data, feof($fp)))
                    continue;
                $error = 'XML error: ' . xml_error_string(xml_get_error_code($parser))
                    . ' at line ' . xml_get_current_line_number($parser);
                break;
            }
        }
        xml_parser_free($parser);

        // Clean up the _dataItem reference:
        $null = null;
        $this->_dataItem = & $null;

        // Initialize _path
        $this->_path = array();

        return $error;
    }

    function _parserStartElementHandler($parser, $name, $attrs)
    {
        $this->_path[] = $name;
        $path = implode('/', $this->_path);
        switch ($path) {
            case 'data/categories/category':
            case 'data/articles/article':
            case 'data/menus/menu':
            case 'data/menuitems/menuitem':
            case 'data/modules/module':
            case 'data/parameters/parameter':
                $this->_data[$name][$attrs['id']] = $attrs;
                $this->_dataItem = & $this->_data[$name][$attrs['id']];
                $this->_dataItem['entity'] = $name;
                break;
            case 'data/categories/category/parameters/parameter':
                $this->_dataItem['parameters'][$attrs['name']] = $attrs['value'];
                break;
        }
    }

    function _parserEndElementHandler($parser, $name)
    {
        array_pop($this->_path);
    }

    function _parserCharacterDataHandler($parser, $data)
    {
        switch ($this->_dataItem['entity']) {
            case 'article':
                if (!isset($this->_dataItem['text']))
                    $this->_dataItem['text'] = '';
                $this->_dataItem['text'] .= $data;
                break;
            case 'module':
                if (!isset($this->_dataItem['content']))
                    $this->_dataItem['content'] = '';
                $this->_dataItem['content'] .= $data;
                break;
        }
    }
}