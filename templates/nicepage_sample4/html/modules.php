<?php
defined('_JEXEC') or die;

if (!defined('_DESIGNER_FUNCTIONS'))
    require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../functions.php');

function modChrome_upstyle($module, &$params, &$attribs)
{
    if ('' === $attribs['upstyle']) {
        echo $module->content;
        return;
    }

    $module->content = stylingDefaultControls($module->content);
    
    $parts = explode('%', $attribs['upstyle']);
    $style = $parts[0];

    if (!isset($attribs['funcStyling']))
        $attribs['funcStyling'] = count($parts) > 1 ? $parts[1] : '';

    $styles = array(
        'block' => 'modChrome_block',
        'nostyle' => 'modChrome_nostyle'
    );

    $classes = explode(' ', rtrim($params->get('moduleclass_sfx')));
    $keys = array_keys($styles);
    $dr = array();
    foreach ($classes as $key => $class) {
        if (in_array($class, $keys)) {
            $dr[] = $class;
            $classes[$key] = ' ';
        }
    }
    $classes = str_replace('  ', ' ', rtrim(implode(' ', $classes)));
    $style = count($dr) ? array_pop($dr) : $style;
    $params->set('moduleclass_sfx', $classes);
    call_user_func($styles[$style], $module, $params, $attribs);
}

function modChrome_block($module, $params, $attribs)
{
    $result = '';
    if (!empty ($module->content)) {
        $args = array('title' => $module->showtitle != 0 ? $module->title : '',
            'content' => $module->content,
            'classes' => $params->get('moduleclass_sfx'),
            'id' => $module->id);
        if ('' !== $attribs['funcStyling']) {
            $args['extraClass'] = isset($attribs['extraClass']) ? $attribs['extraClass'] : '';
            $result = call_user_func_array($attribs['funcStyling'], $args);
        }
        else {
            $result = $module->content;
        }
    }
    if (isset($attribs['positionNumber'])) {
        $content = JFactory::getDocument()->getBuffer('component');
        $placeholder = '[[position_' . $attribs['positionNumber'] . ']]';
        $newPlaceholder = '';
        if (preg_match('/\[\[position_' . $attribs['positionNumber'] . '_(\d+)\]\]/', $content, $matches)) { // more than one module in position
            $placeholder = $matches[0];
            $count = (int) $matches[1];
            if ($count > 1) {
                $newPlaceholder = '[[position_' . $attribs['positionNumber'] . '_' . --$count . ']]';
            }
        }
        $content = str_replace($placeholder, $result . $newPlaceholder, $content);
        JFactory::getDocument()->setBuffer($content, 'component');
        echo '';
    } else {
        echo $result;
    }
}

function modChrome_nostyle($module, $params, $attribs)
{
    if (!empty($module->content)) {
        $result = $module->content;
        if (isset($attribs['positionNumber'])) {
            $content = JFactory::getDocument()->getBuffer('component');
            $content = str_replace('[[position_' . $attribs['positionNumber'] . ']]', $result, $content);
            JFactory::getDocument()->setBuffer($content, 'component');
            echo '';
        } else {
            echo $result;
        }
    }
}

function modChrome_upstylefromtheme($module, &$params, &$attribs) {
    $number = $attribs['iterator'];
    $result = $GLOBALS['themeBlocks'][$number];
    if (!empty($module->content) && $result) {
        if ($module->showtitle != 0) {
            $result = preg_replace('/<\!--block_header_content-->[\s\S]+?<\!--\/block_header_content-->/', $module->title, $result);
        } else {
            $result = preg_replace('/<\!--block_header-->[\s\S]+?<\!--\/block_header-->/', '', $result);
        }
        $result = preg_replace('/<\!--block_content_content-->[\s\S]+?<\!--\/block_content_content-->/', $module->content, $result);
        $result = preg_replace('/<\!--\/?block\_?(header|content)?-->/', '', $result);
        echo $result;
    }
}