<?php

defined('_JEXEC') or die;

if (!defined('_DESIGNER_FUNCTIONS')) {

    define('_DESIGNER_FUNCTIONS', 1);

    require_once dirname(__FILE__) . '/library/Core.php';

    Core::load("Core_Statements");

    $config = JFactory::getConfig();
    $document = JFactory::getDocument();
    $pageDesc = $document->getDescription() ? $document->getDescription() : $config->get('sitename');

    $document->seoTags = array();
    $document->seoTags['canonical'] = array('tag' => 'link', 'rel' => 'canonical', 'href' => JUri::getInstance()->toString());
    $document->seoTags['og:site_name'] = array('tag' => 'meta', 'property' => 'og:site_name', 'content' => $config->get('sitename'));
    $document->seoTags['og:url'] = array('tag' => 'meta', 'property' => 'og:url', 'content' => JUri::getInstance()->toString());
    $document->seoTags['og:title'] = array('tag' => 'meta', 'property' => 'og:title', 'content' => $document->getTitle());
    $document->seoTags['og:type'] = array('tag' => 'meta', 'property' => 'og:type', 'content' => 'website');
    $document->seoTags['og:description'] = array('tag' => 'meta', 'property' => 'og:description', 'content' =>  $pageDesc);

    $twitterAccount = '';

    if ($twitterAccount) {
        $document->seoTags['twitter:site'] = array('tag' => 'meta', 'name' => 'twitter:site', 'content' => $twitterAccount);
        $document->seoTags['twitter:card'] = array('tag' => 'meta', 'name' => 'twitter:card', 'content' => 'summary_large_image');
        $document->seoTags['twitter:title'] = array('tag' => 'meta', 'name' => 'twitter:title', 'content' => $document->getTitle());
        $document->seoTags['twitter:description'] = array('tag' => 'meta', 'name' => 'twitter:description', 'content' => $pageDesc);
    }

    function renderSeoTags($tags) {
        foreach ($tags as $values) {
            $tag = '<';
            foreach ($values as $property => $value) {
                if ($property == 'tag') {
                    $tag .= $value;
                    continue;
                }
                $tag .= ' ' . $property . '="' . $value . '"';
            }
            $tag .= '>';
            echo $tag;
        }
    }

    

    function block_sidebar_blog($caption, $content) {
            $hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
            $hasContent = (null !== $content && strlen(trim($content)) > 0);
            if (!$hasCaption && !$hasContent)
                return '';
            ob_start();
            ?>
            <div class="u-block u-indent-30">
        <div class="u-block-container u-clearfix">
            <?php if ($hasCaption) : ?><h5 class="u-block-header u-text" style="font-size: 1.125rem; line-height: 2;"><?php echo $caption; ?></h5><?php endif; ?>
            <?php if ($hasContent) : ?><div class="u-block-content u-text" style="font-size: 0.875rem; line-height: 2;"><?php echo $content; ?></div><?php endif; ?>
        </div>
    </div>
            <?php
            return ob_get_clean();
        }
function block_sidebar_post($caption, $content) {
            $hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
            $hasContent = (null !== $content && strlen(trim($content)) > 0);
            if (!$hasCaption && !$hasContent)
                return '';
            ob_start();
            ?>
            <div class="u-block u-indent-30">
        <div class="u-block-container u-clearfix">
            <?php if ($hasCaption) : ?><h5 class="u-block-header u-text" style="font-size: 1.125rem; line-height: 2;"><?php echo $caption; ?></h5><?php endif; ?>
            <?php if ($hasContent) : ?><div class="u-block-content u-text" style="font-size: 0.875rem; line-height: 2;"><?php echo $content; ?></div><?php endif; ?>
        </div>
    </div>
            <?php
            return ob_get_clean();
        }


    function funcTagBuilder($tag, $attributes = array(), $content = '') {
        $result = '<' . $tag;
        foreach ($attributes as $name => $value) {
            if (is_string($value)) {
                if (!empty($value))
                    $result .= ' ' . $name . '="' . $value . '"';
            } else if (is_array($value)) {
                $values = array_filter($value);
                if (count($values))
                    $result .= ' ' . $name . '="' . implode(' ', $value) . '"';
            }
        }
        $result .= '>' . $content . '</' . $tag . '>';
        return $result;
    }

    function getThemeParams($name) {
        $template = JFactory::getApplication()->getTemplate();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, params')
            ->from('#__template_styles')
            ->where('template = ' . $db->quote($template))
            ->where('client_id = 0');
        $db->setQuery($query);
        $templates = $db->loadObjectList('id');

        if (count($templates) < 1)
            return '';

        $site = JFactory::getApplication('site');
        $menu = $site->getMenu('site');
        $item = $menu->getActive();

        $id         = is_object($item) ? $item->template_style_id : 0;
        $template   = isset($templates[$id]) ? $templates[$id] : array_shift($templates);

        $registry = new JRegistry();
        $registry->loadString($template->params);
        return $registry->get($name, '');
    }

    function getLogoInfo($defaults = array(), $setThemeSizes = false)
    {
        $app = JFactory::getApplication();
        $rootPath = str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT);

        $info = array();
        $info['default_width'] = isset($defaults['default_width']) ? $defaults['default_width'] : 0;
        $info['src'] = isset($defaults['src']) ? $defaults['src'] : '';
        $info['src_path'] = '';
        if ($info['src']) {
            $info['src'] = preg_match('#^(http:|https:|//)#', $info['src']) ? $info['src'] :
                JURI::root() . 'templates/' . $app->getTemplate() . $info['src'];
            $info['src_path'] = $rootPath . '/templates/' . $app->getTemplate() . $defaults['src'];
        }
        $info['href'] = isset($defaults['href']) ? $defaults['href'] : JUri::base(true);

        $themeParams = $app->getTemplate(true)->params;
        if ($themeParams->get('logoFile')) {
            $info['src'] = JURI::root() . $themeParams->get('logoFile');
            $info['src_path'] = $rootPath . '/' . $themeParams->get('logoFile');
        }
        if ($themeParams->get('logoLink')) {
            $info['href'] = $themeParams->get('logoLink');
        }

        $parts = explode(".", $info['src_path']);
        $extension = end($parts);
        $isSvgFile = strtolower($extension) == 'svg' ? true : false;

        if ($setThemeSizes) {
            $style = '';
            $themeLogoWidth = $themeParams->get('logoWidth');
            $themeLogoHeight = $themeParams->get('logoHeight');
            if ($themeLogoWidth) {
                $style .= "max-width: " . $themeLogoWidth . "px !important;\n";
            }
            if ($themeLogoHeight) {
                $style .= "max-width: " . $themeLogoHeight . "px !important;\n";
            }
            $style .= "width: " . ($isSvgFile ? ($themeLogoWidth ? $themeLogoWidth : $info['default_width']) . "px" : "auto") . " !important\n";
            if ($style) {
                $document = JFactory::getDocument();
                $document->addStyleDeclaration('.u-logo img {' . $style . '}');
            }
        }
        return $info;
    }

    $balanceStorage = array();
    $balanceIndex = 0;

    function balanceReplacer($match) {
        global $balanceStorage;
        global $balanceIndex;
        $balanceIndex++;
        $key = '[[BDSCRIPT' . $balanceIndex . ']]';
        $balanceStorage[$key] = $match[0];
        return $key;
    }

    function balanceReplacer2($match) {
        global $balanceStorage;
        return $balanceStorage[$match[0]];
    }

    function funcBalanceTags($text) {

        $text = preg_replace_callback('/<script[^>]*>([\s\S]*?)<\/script>/', 'balanceReplacer' , $text);

        $singleTags = array('area', 'base', 'basefont', 'br', 'col', 'command', 'embed', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param', 'source');
        $nestedTags = array('blockquote', 'div', 'object', 'q', 'span');

        $stack = array();
        $size = 0;
        $queue = '';
        $output = '';

        while (preg_match("/<(\/?[\w:]*)\s*([^>]*)>/", $text, $match)) {
            $output .= $queue;

            $i = strpos($text, $match[0]);
            $l = strlen($match[0]);

            $queue = '';

            if (isset($match[1][0]) && '/' == $match[1][0]) {
                // processing of the end tag
                $tag = strtolower(substr($match[1],1));

                if($size <= 0) {
                    $tag = '';
                } else if ($stack[$size - 1] == $tag) {
                    $tag = '</' . $tag . '>';
                    array_pop($stack);
                    $size--;
                } else {
                    for ($j = $size-1; $j >= 0; $j--) {
                        if ($stack[$j] == $tag) {
                            for ($k = $size-1; $k >= $j; $k--) {
                                $queue .= '</' . array_pop($stack) . '>';
                                $size--;
                            }
                            break;
                        }
                    }
                    $tag = '';
                }
            } else {
                // processing of the begin tag
                $tag = strtolower($match[1]);

                if (substr($match[2], -1) == '/') {
                    if (!in_array($tag, $singleTags))
                        $match[2] = trim(substr($match[2], 0, -1)) . "></$tag";
                } elseif (in_array($tag, $singleTags)) {
                    $match[2] .= '/';
                } else {
                    if ($size > 0 && !in_array($tag, $nestedTags) && $stack[$size - 1] == $tag) {
                        $queue = '</' . array_pop($stack) . '>';
                        $size--;
                    }
                    $size = array_push($stack, $tag);
                }

                // attributes
                $attributes = $match[2];
                if(!empty($attributes) && $attributes[0] != '>')
                    $attributes = ($tag ? ' ' : '') . $attributes;

                $tag = '<' . $tag . $attributes . '>';

                if (!empty($queue)) {
                    $queue .= $tag;
                    $tag = '';
                }
            }
            $output .= substr($text, 0, $i) . $tag;
            $text = substr($text, $i + $l);
        }

        $output .= ($queue . $text);

        while($t = array_pop($stack))
            $output .= '</' . $t . '>';

        $output = preg_replace_callback('/\[\[BDSCRIPT[0-9]+\]\]/', 'balanceReplacer2', $output);
        return $output;
    }

    function stylingDefaultControls($content) {
        $content = preg_replace('/<form([\s\S]+?)class="form/', '<form$1class="u-form form', $content);
        $content = preg_replace('/<input([\s\S]+?)class="input/', '<input$1class="u-input input', $content);
        $content = preg_replace('/<button([\s\S]+?)class="btn/', '<button$1class="u-btn u-button-style btn', $content);
        return $content;
    }

    function getProportionImage(&$images, &$etalons) {
        if (count($images) < 1) {
            return '';
        }
        for ($i = 0; $i < count($images); $i++) {
            $image = $images[$i];
            for ($j = 0; $j < count($etalons); $j++) {
                $etalon = $etalons[$j];
                if ($image['width'] >= $etalon['width']) {
                    $image = $image['name'];
                    array_splice($images, $i, 1);
                    array_splice($etalons, $j, 1);
                    return $image;
                }
            }
        }
        return $images[0]['name'];
    }

    $GLOBALS['themeBlocks'] = array();

    function processPositions($content) {
        $content = preg_replace_callback(
            '/<\!--position-->([\s\S]+?)<\!--\/position-->/',
            function ($match) {
                $block = $match[1];
                preg_match('/data-position="([^"]*)"/', $block, $match2);
                $position = $match2[1];
                $i = count($GLOBALS['themeBlocks']) + 1;
                $GLOBALS['themeBlocks'][$i] = $block;
                $document = JFactory::getDocument();
                if ($position && $document->countModules($position) !== 0) {
                    $attr = array(
                        'style' => 'upstylefromtheme',
                        'iterator' => $i,
                        'id' => $i,
                        'name' => $position
                    );
                    return $document->getBuffer('modules', $position, $attr);
                } else {
                    return '';
                }
            },
            $content
        );
        return $content;
    }
}