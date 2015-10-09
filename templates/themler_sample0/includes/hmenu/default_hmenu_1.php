<?php
$menutype = 'horizontal';
$tag_id = ($params->get('tag_id') != NULL) ? ' id="' . $params->get('tag_id') . '"' : '';
$itemLI = '<li class="">';
$optLevels = '';
$optResponsiveLevels = '';

$showAll = true;
if ('one level' === $optLevels && 'one level' === $optResponsiveLevels) {
	$showAll = false;
}

$start = $params->get('startLevel');

// true - skip the current node, false - render the current node.
$skip = false;
?>
<div class=" bd-horizontalmenu-2 clearfix">
    <div class="bd-container-inner">
        <?php
$mIconClassName = '';
ob_start();
?>
<li class=" bd-menuitem-3">
<?php 
$menuStartItem = ob_get_clean();
ob_start();
?>

<ul class=" bd-menu-3 nav navbar-left nav-pills<?php echo $class_sfx;?>" <?php echo $tag_id; ?>>
<?php
$bHMenu = ob_get_clean();
$eHMenu = "</ul>";
?>
        <?php echo $bHMenu; ?>
        <?php foreach ($list as $i => & $item) : ?>
            <?php
    if ($item->level == $start) {
        $itemLI = $menuStartItem;
    }
    if ($skip) {
        if ($item->shallower) {
            if (($item->level - $item->level_diff) <= $limit) {
                echo '</li>' . str_repeat('</ul></div></li>', $limit - $item->level + $item->level_diff);
                $skip = false;
            }
        }
        continue;
    }

    $class = 'item-' . $item->id;
    $class .= $item->id == $active_id ? ' current' : '';
    $class .= ('alias' == $item->type
        && in_array($item->params->get('aliasoptions'), $path)
        || in_array($item->id, $path)) ? '' : '';
    $class .= $item->deeper ? ' deeper' : '';
    $class .= $item->parent ? ' parent' : '';
    ?>    
    <?php
$subIconClassName = '';
ob_start();
?>
<li class=" bd-menuitem-4">
<?php $subMenuStartItem = ob_get_clean(); ?>
    <?php
    $iconClassName = 1 == $item->level ? $mIconClassName : $subIconClassName;
    echo preg_replace('/class\s*=\s*[\'"]{1}([^\'^"]*)[\'"]{1}/', 'class="$1 ' . $class . '"', $itemLI);
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
        if (!$showAll) {
            $limit = $item->level;
            $skip = true;
            continue;
        }
        $itemLI = $subMenuStartItem;
        ?>
        <div class="bd-menu-4-popup"><ul class=" bd-menu-4">
        <?php
    }
    elseif ($item->shallower){
        echo '</li>' . str_repeat('</ul></div></li>', $item->level_diff);
    } else
        echo '</li>';
 ?>
        <?php endforeach; ?>
        <?php echo $eHMenu; ?>
    </div>
</div>