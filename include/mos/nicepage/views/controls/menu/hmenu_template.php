<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

ob_start();
?>
    <?php echo $menuProps['menu_template']; ?>
<?php
$menuTemplate = PositionsProcessor::process(ob_get_clean());


if (!function_exists('buildMenu')) {
    /**
     * Build menu
     *
     * @param array  $list       Array items
     * @param int    $default_id Default id
     * @param int    $active_id  Active id
     * @param string $path       Path
     * @param array  $options    Options
     *
     * @return string
     */
    function buildMenu($list, $default_id, $active_id, $path, $options)
    {
        ob_start();
        ?>
        <ul class="<?php echo $options['menu_class']; ?>">
        <?php
        foreach ($list as $i => &$item) {
            $class = 'item-' . $item->id;
            if ($item->id == $default_id) {
                $class .= ' default';
            }
            $itemIsCurrent = false;
            if (($item->id == $active_id) || ($item->type == 'alias' && $item->params->get('aliasoptions') == $active_id)) {
                $class .= ' current';
                $itemIsCurrent = true;
            }

            if (in_array($item->id, $path)) {
                $class .= ' active';
            } elseif ($item->type == 'alias') {
                $aliasToId = $item->params->get('aliasoptions');
                if (count($path) > 0 && $aliasToId == $path[count($path) - 1]) {
                    $class .= ' active';
                } elseif (in_array($aliasToId, $path)) {
                    $class .= ' alias-parent-active';
                }
            }

            if ($item->type == 'separator') {
                $class .= ' divider';
            }

            if ($item->deeper) {
                $class .= ' deeper';
            }

            if ($item->parent) {
                $class .= ' parent';
            }

            echo '<li class="' . ($item->level == 1 ? $options['item_class'] : $options['submenu_item_class']) . ' ' . $class . '">';
            $linkClassName = $item->level == 1 ? $options['link_class'] : $options['submenu_link_class'];
            $linkInlineStyles = $item->level == 1 ? $options['link_style'] : $options['submenu_link_style'];
            switch ($item->type) :
            case 'separator':
            case 'component':
            case 'heading':
            case 'url':
                include dirname(__FILE__) . '/default_' . $item->type . '.php';
                break;
            default:
                include dirname(__FILE__) . '/default_url.php';
                break;
            endswitch;

            // The next item is deeper.
            if ($item->deeper) {
                echo '<div class="u-nav-popup"><ul class="' . $options['submenu_class'] . '">';
            } elseif ($item->shallower) { // The next item is shallower.
                echo '</li>';
                echo str_repeat('</ul></div></li>', $item->level_diff);
            } else {// The next item is on the same level.
                echo '</li>';
            }
        }
        ?>
        </ul>
        <?php
        return ob_get_clean();
    }
}
$menu_html_options = array(
    'container_class' => $menuProps['container_class'],
    'menu_class' => $menuProps['menu_class'],
    'item_class' => $menuProps['item_class'],
    'link_class' => $menuProps['link_class'],
    'link_style' => $menuProps['link_style'],
    'submenu_class' => $menuProps['submenu_class'],
    'submenu_item_class' => $menuProps['submenu_item_class'],
    'submenu_link_class' => $menuProps['submenu_link_class'],
    'submenu_link_style' => $menuProps['submenu_link_style']
);
$menu_html = buildMenu($list, $default_id, $active_id, $path, $menu_html_options);

$resp_menu_options = array(
    'container_class' => $menuProps['container_class'],
    'menu_class' => $menuProps['responsive_menu_class'],
    'item_class' => $menuProps['responsive_item_class'],
    'link_class' => $menuProps['responsive_link_class'],
    'link_style' => $menuProps['responsive_link_style'],
    'submenu_class' => $menuProps['responsive_submenu_class'],
    'submenu_item_class' => $menuProps['responsive_submenu_item_class'],
    'submenu_link_class' => $menuProps['responsive_submenu_link_class'],
    'submenu_link_style' => $menuProps['responsive_submenu_link_style']
);
$resp_menu = buildMenu($list, $default_id, $active_id, $path, $resp_menu_options);

if (preg_match('#<ul[\s\S]*ul>#', $resp_menu, $m)) {
    $responsive_nav = $m[0];
    if (preg_match('#<ul[\s\S]*ul>#', $menu_html, $m)) {
        $regular_nav = $m[0];
        $menu_html = strtr($menuTemplate, array('[[menu]]' => $regular_nav, '[[responsive_menu]]' => $responsive_nav));
        $menu_html = preg_replace('#<\/li>\s+<li#', '</li><li', $menu_html); // remove spaces
        echo $menu_html;
    }
}
