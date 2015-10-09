<?php
defined('_JEXEC') or die;
?>
<?php /*BEGIN_EDITOR_OPEN*/
$app = JFactory::getApplication('site');
$templateName = $app->getTemplate();

$ret = false;
$templateDir = JPATH_THEMES . '/' . $templateName;
$editorClass = $templateDir . '/app/' . 'Editor.php';

if (!$app->isAdmin() && file_exists($editorClass)) {
	require_once $templateDir . '/app/' . 'Editor.php';
	$ret = DesignerEditor::override($templateName, __FILE__);
}

if ($ret) {
	$editorDir = $templateName . '/editor';
    require($ret);
    return;
} else {
/*BEGIN_EDITOR_CLOSE*/ ?>

<?php
$menutype = 'vertical';
$attribs['drstyle'] = '';

$tag_id = ($params->get('tag_id') != NULL) ? ' id="' . $params->get('tag_id') . '"' : '';
$start = $params->get('startLevel');
$showAll = 'one level' != '';
$itemLI = '<li class="">';
// true - skip the current node, false - render the current node.
$skip = false;
// limit of rendering - skip items when a level is exceeding the limit.
$limit = $start;
$caption = $module->showtitle != 0 ? $module->title : '';
$hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
?>

<div class=" bd-vmenu-1" data-responsive-menu="true" data-responsive-levels="">
    <div class=" bd-block-2 <?php echo $params->get('moduleclass_sfx'); ?>">
        <?php if ($hasCaption) : ?>
        <div class=" bd-container-14 bd-tagstyles">
            <h4><?php echo $caption; ?></h4>
        </div>
        <?php endif; ?>
        <div class=" bd-container-34 bd-tagstyles shape-only">
            <div class=" bd-verticalmenu-3">
                <div class="bd-container-inner">
                    <?php
$mIconClassName = '';
ob_start();
?>
<li class=" bd-menuitem-23">
<?php 
$menuStartItem = ob_get_clean();
ob_start();
?>

<ul class=" bd-menu-23 nav nav-pills<?php echo $class_sfx;?>" <?php echo $tag_id; ?>>
<?php
$bVMenu = ob_get_clean();
$eVMenu = '</ul>';
?>
                    <?php echo $bVMenu; ?>
                    <?php foreach ($list as $i => & $item) : ?>
                        <?php $activeClass = $showAll && in_array($item->id, $path) ? ' active' : ''; ?>
                        <?php
    if ($skip) {
        if ($item->shallower) {
            if (($item->level - $item->level_diff) <= $limit) {
                echo '</li>' . str_repeat('</ul></div></li>', $limit - $item->level + $item->level_diff);
                $skip = false;
            }
        }
        continue;
    }
    ?>
    <?php
$subIconClassName = '';
ob_start();
?>
<li class=" bd-menuitem-24">
<?php $subMenuStartItem = ob_get_clean(); ?>
    <?php
    
    $class = 'item-' . $item->id;
    $class .= $item->id == $active_id ? ' current' : '';
    $class .= ('alias' == $item->type
        && in_array($item->params->get('aliasoptions'), $path)
        || in_array($item->id, $path)) ? '' : '';
    $class .= $item->deeper ? ' deeper' : '';
    $class .= $item->parent ? ' parent' : '';

    if ($item->level == $start) {
        $itemLI = $menuStartItem;
    }

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
        <div class="bd-menu-24-popup">
            
                <ul class=" bd-menu-24 nav ">
    <?php
    }
    elseif ($item->shallower){
        echo '</li>' . str_repeat('</ul></div></li>', $item->level_diff);
    } else
        echo '</li>';
 ?>
                    <?php endforeach; ?>
                    <?php echo $eVMenu; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>