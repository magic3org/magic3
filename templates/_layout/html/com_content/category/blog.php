<?php
defined('_JEXEC') or die;
require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../functions.php');
$version = new JVersion();
$view = ('1.5' == $version->RELEASE) ? new ArtxContent15($this, $this->params) : new ArtxContent16($this, $this->params);
?>
<div class="blog<?php echo $view->pageClassSfx; ?>">
<?php ob_start(); ?>
<?php if (strlen($this->params->get('page_subheading'))) : ?>
    <span class="page-subheading"><?php echo $this->escape($this->params->get('page_subheading')); ?></span>
<?php endif; ?>
<?php if ($this->params->get('show_category_title') && strlen($this->category->title)) : ?>
    <span class="subheading-category"><?php echo $this->category->title;?></span>
<?php endif; ?>
<?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
<div class="category-desc">
    <?php if ('1.5' == $version->RELEASE) : ?>
        <?php if ($this->params->get('show_description_image') && $this->category->image) : ?>
            <img src="<?php echo $this->baseurl . '/' . JComponentHelper::getParams('com_media')->get('image_path') . '/'. $this->category->image; ?>" align="<?php echo $this->category->image_position;?>" hspace="6" alt="" />
        <?php endif; ?>
        <?php if ($this->params->get('show_description') && $this->category->description) : ?>
            <?php echo $this->category->description; ?>
        <?php endif; ?>
    <?php else : ?>
        <?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
            <img src="<?php echo $this->category->getParams()->get('image'); ?>" alt="" />
        <?php endif; ?>
        <?php if ($this->params->get('show_description') && $this->category->description) : ?>
            <?php echo JHtml::_('content.prepare', $this->category->description); ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php echo artxPost(array('header-text' => $view->pageHeading, 'content' => ob_get_clean())); ?>
<?php if ('1.5' != $version->RELEASE) : ?>
    <?php $leadingcount=0 ; ?>
    <?php if (!empty($this->lead_items)) : ?>
    <div class="items-leading">
        <?php foreach ($this->lead_items as &$item) : ?>
            <div class="leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
                <?php
                    $this->item = &$item;
                    echo $this->loadTemplate('item');
                ?>
            </div>
            <?php $leadingcount++; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
        $introcount = (count($this->intro_items));
        $counter = 0;
    ?>
    <?php if (!empty($this->intro_items)) : ?>
        <?php foreach ($this->intro_items as $key => &$item) : ?>
        <?php
            $key= ($key-$leadingcount)+1;
            $rowcount=( ((int)$key-1) % (int) $this->columns) +1;
            $row = $counter / $this->columns ;
            if ($rowcount==1) : ?>
                <div class="items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row ; ?>">
           <?php endif; ?>
        <div class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
        <?php
            $this->item = &$item;
            echo $this->loadTemplate('item');
        ?>
        </div>
            <?php $counter++; ?>
            <?php if (($rowcount == $this->columns) or ($counter ==$introcount)): ?>
        <span class="row-separator"></span>
    </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($this->link_items)) : ?>
        <?php ob_start(); ?>
        <div class="items-more">
            <?php echo $this->loadTemplate('links'); ?>
        </div>
        <?php echo artxPost(ob_get_clean()); ?>
    <?php endif; ?>
    <?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
        <?php ob_start(); ?>
        <div class="cat-children">
            <h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
            <?php echo $this->loadTemplate('children'); ?>
        </div>
        <?php echo artxPost(ob_get_clean()); ?>
    <?php endif; ?>
    <?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
        <?php ob_start(); ?>
        <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
            <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
        <?php endif; ?>
        <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
        <?php echo artxPost(ob_get_clean()); ?>
    <?php endif; ?>
<?php else : ?>
<table class="blog<?php echo $view->pageClassSfx;?>" cellpadding="0" cellspacing="0" width="100%">
<?php if ($this->params->get('num_leading_articles')) : ?>
<tr>
	<td valign="top">
	<?php for ($i = $this->pagination->limitstart; $i < ($this->pagination->limitstart + $this->params->get('num_leading_articles')); $i++) : ?>
		<?php if ($i >= $this->total) : break; endif; ?>
		<div>
		<?php
			$this->item =& $this->getItem($i, $this->params);
			echo $this->loadTemplate('item');
		?>
		</div>
	<?php endfor; ?>
	</td>
</tr>
<?php else : $i = $this->pagination->limitstart; endif; ?>

<?php
$startIntroArticles = $this->pagination->limitstart + $this->params->get('num_leading_articles');
$numIntroArticles = $startIntroArticles + $this->params->get('num_intro_articles');
if (($numIntroArticles != $startIntroArticles) && ($i < $this->total)) : ?>
<tr>
	<td valign="top">
		<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
		<?php
			$divider = '';
			if ($this->params->get('multi_column_order')) : // order across, like front page
				for ($z = 0; $z < $this->params->def('num_columns', 2); $z ++) :
					if ($z > 0) : $divider = " column_separator"; endif; ?>
					<?php
					$rows = (int) ($this->params->get('num_intro_articles', 4) / $this->params->get('num_columns'));
					$cols = ($this->params->get('num_intro_articles', 4) % $this->params->get('num_columns'));
					?>
					<td valign="top"
						width="<?php echo intval(100 / $this->params->get('num_columns')) ?>%"
						class="article_column<?php echo $divider ?>">
						<?php
						$loop = (($z < $cols)?1:0) + $rows;

						for ($y = 0; $y < $loop; $y ++) :
							$target = $i + ($y * $this->params->get('num_columns')) + $z;
							if ($target < $this->total && $target < ($numIntroArticles)) :
								$this->item =& $this->getItem($target, $this->params);
								echo $this->loadTemplate('item');
							endif;
						endfor;
						?></td>
				<?php endfor; 
						$i = $i + $this->params->get('num_intro_articles') ; 
			else : // otherwise, order down, same as before (default behaviour)
				for ($z = 0; $z < $this->params->get('num_columns'); $z ++) :
					if ($z > 0) : $divider = " column_separator"; endif; ?>
					<td valign="top" width="<?php echo intval(100 / $this->params->get('num_columns')) ?>%" class="article_column<?php echo $divider ?>">
					<?php for ($y = 0; $y < ($this->params->get('num_intro_articles') / $this->params->get('num_columns')); $y ++) :
					if ($i < $this->total && $i < ($numIntroArticles)) :
						$this->item =& $this->getItem($i, $this->params);
						echo $this->loadTemplate('item');
						$i ++;
					endif;
				endfor; ?>
				</td>
		<?php endfor; 
		endif; ?> 
		</tr>
		</table>
	</td>
</tr>
<?php endif; ?>
</table>
<?php if ($this->params->get('num_links') && ($i < $this->total)) : ?>
<?php ob_start(); ?>
<?php
	$this->links = array_splice($this->items, $i - $this->pagination->limitstart);
	echo $this->loadTemplate('links');
?>
<?php echo artxPost(ob_get_clean()); ?>
<?php endif; ?>
<?php
$paginationPagesLinks = $this->params->get('show_pagination')
    ? $this->pagination->getPagesLinks() : '';
$paginationPagesCounter = $this->params->get('show_pagination_results')
    ? $this->pagination->getPagesCounter() : '';
?>
<?php if (strlen($paginationPagesLinks) > 0 && strlen($paginationPagesCounter) > 0) : ?>
<?php ob_start(); ?>
<div id="navigation">
<?php if (strlen($paginationPagesLinks) > 0) : ?>
<p><?php echo $paginationPagesLinks; ?></p>
<?php endif; ?>
<?php if (strlen($paginationPagesCounter) > 0) : ?>
<p><?php echo $paginationPagesCounter; ?></p>
<?php endif; ?>
</div>
<?php echo artxPost(ob_get_clean()); ?>
<?php endif; ?>
<?php endif; ?>
</div>