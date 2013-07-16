<?php

defined('_JEXEC') or die;

require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../functions.php');

// Note. It is important to remove spaces between elements.

$tag = ($params->get('tag_id') != NULL) ? ' id="' . $params->get('tag_id') . '"' : '';
if (isset($attribs['name']) && $attribs['name'] == 'user3') {
    $menutype = 'horizontal';

    $start = $params->get('startLevel');

    // render subitems or not.
    $subitems = $GLOBALS['artx_settings']['menu']['show_submenus'] && 1 == $params->get('showAllChildren');
    // true - skip current node, false - render current node.
    $skip = false;
    
    ob_start();
    echo '<ul class="art-hmenu"' . $tag . '>';
    foreach ($list as $i => & $item) {
        if ($skip) {
            if ($item->shallower) {
                if (($item->level - $item->level_diff) <= $limit) {
                    echo '</li>' . str_repeat('</ul></li>', $limit - $item->level + $item->level_diff);
                    $skip = false;
                }
            }
            continue;
        }
        $id = ($item->id == $active_id) ? ' id="current"' : '';
        $class = ' class="' . (in_array($item->id, $path) ? 'active ' : '') . 'item' . $item->id . '"';
        echo '<li' . $id . $class . '>';
        // Render the menu item.
        switch ($item->type) {
            case 'separator':
            case 'url':
            case 'component':
                require JModuleHelper::getLayoutPath('mod_menu', 'default_' . $item->type);
                break;
            default:
                require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
                break;
        }
        if ($item->deeper) {
            if (!$subitems) {
                $limit = $item->level;
                $skip = true;
                continue;
            }
            echo '<ul>';
        }
        elseif ($item->shallower)
            echo '</li>' . str_repeat('</ul></li>', $item->level_diff);
        else
            echo '</li>';
    }
    echo '</ul>';
    echo ob_get_clean();
} else if (0 === strpos($params->get('moduleclass_sfx'), 'art-vmenu') || false !== strpos($params->get('moduleclass_sfx'), ' art-vmenu')) {
    $menutype = 'vertical';

    $start = $params->get('startLevel');

    // render subitems or not.
    $subitems = $GLOBALS['artx_settings']['vmenu']['show_submenus'] && 1 == $params->get('showAllChildren');
    // true - all items expanded, false - only active items expanded.
    $simple = $GLOBALS['artx_settings']['vmenu']['simple'];
    // true - skip current node, false - render current node.
    $skip = false;
    // limit of rendering - skip items with level more then limit.
    $limit = $start;
    
    ob_start();
    echo '<ul class="art-vmenu"' . $tag . '>';
    foreach ($list as $i => & $item) {
        if ($skip) {
            if ($item->shallower) {
                if (($item->level - $item->level_diff) <= $limit) {
                    echo '</li>' . str_repeat('</ul></li>', $limit - $item->level + $item->level_diff);
                    $skip = false;
                }
            }
            continue;
        }
        $id = ($item->id == $active_id) ? ' id="current"' : '';
        $class = ' class="' . (in_array($item->id, $path) ? 'active ' : '') . 'item' . $item->id . '"';
        echo '<li' . $id . $class . '>';
        // Render the menu item.
        switch ($item->type) {
            case 'separator':
            case 'url':
            case 'component':
                require JModuleHelper::getLayoutPath('mod_menu', 'default_' . $item->type);
                break;
            default:
                require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
                break;
        }
        if ($item->deeper) {
            if (!$subitems || (!$simple && !in_array($item->id, $path) && 'separator' != $item->type)) {
                $limit = $item->level;
                $skip = true;
                continue;
            }
            echo '<ul ' . (($subitems && !$simple && 'separator' == $item->type && !in_array($item->id, $path))
                ? 'class="collapsed"' : '') . '>';
        }
        elseif ($item->shallower)
            echo '</li>' . str_repeat('</ul></li>', $item->level_diff);
        else
            echo '</li>';
    }
    echo '</ul>';
    echo ob_get_clean();
} else {
    $menutype = 'default';
    echo '<ul class="menu' . $params->get('class_sfx') . '"' . $tag . '>';
    foreach ($list as $i => &$item) {
        $id = '';
        if ($item->id == $active_id)
            $id = ' id="current"';
        $class = '';
        if (in_array($item->id, $path))
            $class .= 'active ';
        if ($item->deeper)
            $class .= 'parent ';

        $class = ' class="' . $class . 'item' . $item->id . '"';

        echo '<li' . $id . $class . '>';

        // Render the menu item.
        switch ($item->type) {
            case 'separator':
            case 'url':
            case 'component':
                require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
                break;
            default:
                require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
                break;
        }

        if ($item->deeper)
            echo '<ul>';
        elseif ($item->shallower)
            echo '</li>' . str_repeat('</ul></li>', $item->level_diff);
        else
            echo '</li>';
    }
    echo '</ul>';
}
