<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

$menuProps = $attribs['styleProps'];
$menuType = isset($attribs['variation']) ? $attribs['variation'] : '';
$tagId = ($params->get('tag_id') != null) ? ' id="' . $params->get('tag_id') . '"' : '';

if ('' !== $menuType && file_exists(dirname(__FILE__) . '/hmenu_template.php')) {
    include dirname(__FILE__) . '/hmenu_template.php';
} else {
    $menutype = 'default';
    ?>
    <ul class="nav menu<?php echo $class_sfx; ?>"<?php echo $tagId; ?>>
    <?php
    foreach ($list as $i => &$item) {
        $class = 'item-' . $item->id;

        if ($item->id == $default_id) {
            $class .= ' default';
        }


        if (($item->id == $active_id) || ($item->type == 'alias' && $item->params->get('aliasoptions') == $active_id)) {
            $class .= ' current';
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

        echo '<li class="' . $class . '">';

        switch ($item->type) {
        case 'separator':
        case 'component':
        case 'heading':
        case 'url':
            include __DIR__ . '/default_' . $item->type . '.php';
            break;

        default:
            include __DIR__ . 'default_url.php';
            break;
        }

        // The next item is deeper.
        if ($item->deeper) {
            echo '<ul class="nav-child unstyled small">';
        } elseif ($item->shallower) { // The next item is shallower.
            echo '</li>';
            echo str_repeat('</ul></li>', $item->level_diff);
        } else {// The next item is on the same level.
            echo '</li>';
        }
    }
    ?>
    </ul>
<?php
}
?>