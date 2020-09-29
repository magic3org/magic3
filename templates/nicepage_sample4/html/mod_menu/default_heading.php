<?php
defined('_JEXEC') or die;

$title      = $item->anchor_title ? ' title="' . $item->anchor_title . '"' : '';
$anchor_css = $item->anchor_css ?: '';

$linktype   = $item->title;

if ($item->menu_image) {
    $image_attributes = $item->menu_image_css ? array('class' => $item->menu_image_css) : array();
    $linktype = JHtml::_('image', $item->menu_image, $item->title, $image_attributes);
    if ($item->params->get('menu_text', 1)) {
        $linktype .= '<span class="image-title">' . $item->title . '</span>';
    }
}

?>
<span class="nav-header <?php echo $anchor_css; ?>"<?php echo $title; ?>><?php echo $linktype; ?></span>
