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
$modulePath = dirname(dirname(dirname(__FILE__))) . '/includes/breadcrumbs';
$filePath = isset($attribs['id']) && '' !== $attribs['id'] ?
    $modulePath . '/default_breadcrumbs_' . $attribs['id'] . '.php' : '';
?>
<?php if ('' !== $filePath && file_exists($filePath)) : ?>
    <?php include($filePath); ?>
<?php else : ?>
    <ul class="breadcrumb<?php echo $moduleclass_sfx; ?>">
        <?php
        if ($params->get('showHere', 1))
        {
            echo '<li class="active">' . JText::_('MOD_BREADCRUMBS_HERE') . '&#160;</li>';
        }
        else
        {
            echo '<li class="active"><span class="divider icon-location"></span></li>';
        }

        // Get rid of duplicated entries on trail including home page when using multilanguage
        for ($i = 0; $i < $count; $i++)
        {
            if ($i == 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link == $list[$i - 1]->link)
            {
                unset($list[$i]);
            }
        }

        // Find last and penultimate items in breadcrumbs list
        end($list);
        $last_item_key = key($list);
        prev($list);
        $penult_item_key = key($list);

        // Make a link if not the last item in the breadcrumbs
        $show_last = $params->get('showLast', 1);

        // Generate the trail
        foreach ($list as $key => $item) :
            if ($key != $last_item_key)
            {
                // Render all but last item - along with separator
                echo '<li>';
                if (!empty($item->link))
                {
                    echo '<a href="' . $item->link . '" class="pathway">' . $item->name . '</a>';
                }
                else
                {
                    echo '<span>' . $item->name . '</span>';
                }

                if (($key != $penult_item_key) || $show_last)
                {
                    echo '<span class="divider">' . $separator . '</span>';
                }

                echo '</li>';
            }
            elseif ($show_last)
            {
                // Render last item if reqd.
                echo '<li class="active">';
                echo '<span>' . $item->name . '</span>';
                echo '</li>';
            }
        endforeach; ?>
    </ul>
<?php endif; ?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>