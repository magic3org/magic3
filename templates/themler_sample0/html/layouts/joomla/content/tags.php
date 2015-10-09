<?php

defined('JPATH_BASE') or die;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');

$custom = false;
if (isset($displayData['custom'])) {
    $custom = true;
    unset($displayData['custom']);
}

$list = array();
if (!empty($displayData)) {
    foreach ($displayData as $i => $tag) {
        if (in_array($tag->access, JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id')))) {
            $item = array();
            $tagParams = new JRegistry($tag->params);
            $link_class = $tagParams->get('tag_link_class', '');
            if (!$custom) { ?>
                <span class="tag-<?php echo $tag->tag_id; ?> tag-list<?php echo $i ?>" itemprop="keywords">
                    <a href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias)) ?>" class="<?php echo $link_class; ?>">
                        <?php echo $this->escape($tag->title); ?>
                    </a>
                </span>
            <?php }
            else {
                $list[] = $item = array('title' => $this->escape($tag->title),
                    'href' => JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias)),
                    'class' => $link_class);;
            }
        }
    
    }
}

if ($custom) echo json_encode($list);