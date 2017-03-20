<?php
defined('_JEXEC') or die;

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'functions.php';

$tag = ($params->get('tag_id') != NULL) ? ' id="' . $params->get('tag_id') . '"' : '';
//if (isset($attribs['name']) && $attribs['name'] == 'user3') {
if (isset($attribs['type']) && $attribs['type'] == 'navmenu') {
    $menutype = 'horizontal';

    $start = $params->get('startLevel');

    // check if it is necessary to render subitems:
    $subitems = $GLOBALS['artx_settings']['menu']['show_submenus'] && 1 == $params->get('showAllChildren');
    // true - skip the current node, false - render the current node.
    $skip = false;
    
    echo '<ul class="nav navbar-nav"' . $tag . '>';
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

        $class = 'item-' . $item->id;
        $class .= $item->id == $active_id ? ' current' : '';
        $class .= ('alias' == $item->type
            && in_array($item->params->get('aliasoptions'), $path)
            || in_array($item->id, $path)) ? ' active' : '';
        $class .= $item->deeper ? ' deeper' : '';
        $class .= $item->parent ? ' parent' : '';

        echo '<li class="' . $class . '">';

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
}
