<?php
defined('_JEXEC') or die;

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'functions.php';
$modPath = dirname(__FILE__) . '/';
if (file_exists($modPath . '/breadcrumbs/default_breadcrumbs_' . $attribs['id'] . '.php')) {
    include($modPath . '/breadcrumbs/default_breadcrumbs_' . $attribs['id'] . '.php');
} else {
JHtml::_('bootstrap.tooltip');
?>

<ul itemscope itemtype="https://schema.org/BreadcrumbList" class="breadcrumb<?php echo $moduleclass_sfx; ?>">
    <?php if ($params->get('showHere', 1)) : ?>
        <li>
            <?php echo JText::_('MOD_BREADCRUMBS_HERE'); ?>&#160;
        </li>
    <?php else : ?>
        <li class="active">
            <span class="divider icon-location"></span>
        </li>
    <?php endif; ?>

    <?php
    // Get rid of duplicated entries on trail including home page when using multilanguage
    for ($i = 0; $i < $count; $i++)
    {
        if ($i === 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link === $list[$i - 1]->link)
        {
            unset($list[$i]);
        }
    }

    // Find last and penultimate items in breadcrumbs list
    end($list);
    $last_item_key   = key($list);
    prev($list);
    $penult_item_key = key($list);

    // Make a link if not the last item in the breadcrumbs
    $show_last = $params->get('showLast', 1);

    // Generate the trail
    foreach ($list as $key => $item) :
        if ($key !== $last_item_key) :
            // Render all but last item - along with separator ?>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <?php if (!empty($item->link)) : ?>
                    <a itemprop="item" href="<?php echo $item->link; ?>" class="pathway"><span itemprop="name"><?php echo $item->name; ?></span></a>
                <?php else : ?>
                    <span itemprop="name">
						<?php echo $item->name; ?>
					</span>
                <?php endif; ?>

                <?php if (($key !== $penult_item_key) || $show_last) : ?>
                    <span class="divider">
						<?php echo $separator; ?>
					</span>
                <?php endif; ?>
                <meta itemprop="position" content="<?php echo $key + 1; ?>">
            </li>
        <?php elseif ($show_last) :
            // Render last item if reqd. ?>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="active">
				<span itemprop="name">
					<?php echo $item->name; ?>
				</span>
                <meta itemprop="position" content="<?php echo $key + 1; ?>">
            </li>
        <?php endif;
    endforeach; ?>
</ul>
<?php
}