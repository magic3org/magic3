<?php
defined('_JEXEC') or die;

require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../functions.php');

$view = new ArtxContent15($this, $this->params);

echo $view->beginPageContainer('blog-featured');
if ($view->showPageHeading)
    echo $view->pageHeading();

if ($this->params->def('num_leading_articles', 1)) :
	$leadingcount = 0;
?>
<div class="items-leading">
<?php
	for ($i = $this->pagination->limitstart; $i < ($this->pagination->limitstart + $this->params->get('num_leading_articles')); $i++) :
		if ($i >= $this->total) : break; endif;
?>
<div class="leading-<?php echo $leadingcount; ?>">
<?php
		$this->item =& $this->getItem($i, $this->params);
		echo $this->loadTemplate('item');
?>
</div>
<?php
		$leadingcount++;
	endfor;
?>
</div>
<?php
else :
	$i = $this->pagination->limitstart;
endif;

$startIntroArticles = $this->pagination->limitstart + $this->params->get('num_leading_articles');
$numIntroArticles = $startIntroArticles + $this->params->get('num_intro_articles', 4);
if (($numIntroArticles != $startIntroArticles) && ($i < $this->total)) : ?>
		<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
		<?php
			$divider = '';
			if ($this->params->def('multi_column_order', 1)) : // order across as before
			for ($z = 0; $z < $this->params->def('num_columns', 2); $z ++) :
				if ($z > 0) : $divider = " column_separator"; endif; ?>
				<?php
					$rows = (int) ($this->params->get('num_intro_articles', 4) / $this->params->get('num_columns'));
					$cols = ($this->params->get('num_intro_articles', 4) % $this->params->get('num_columns'));
				?>
				<td valign="top" width="<?php echo intval(100 / $this->params->get('num_columns')) ?>%" class="article_column<?php echo $divider ?>">
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
			else : // otherwise, order down columns, like old category blog
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
		endif;?>
		</tr>
		</table>
<?php endif; ?>
<?php
if ($this->params->def('num_links', 4) && ($i < $this->total)) :
	$this->links = array_splice($this->items, $i - $this->pagination->limitstart);
	if (count($this->links) > 0) :
		ob_start();
?>
<div class="items-more">
<?php echo $this->loadTemplate('links'); ?>
</div>
<?php
		echo artxPost(ob_get_clean());
	endif;
endif;

if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) :
	ob_start();
?>
<div id="navigation">
<?php if ($this->params->def('show_pagination_results', 1)) : ?>
	<p><?php echo $this->pagination->getPagesCounter(); ?></p>
<?php endif; ?>
	<p><?php echo $this->pagination->getPagesLinks(); ?></p>
</div>
<?php
	echo artxPost(ob_get_clean());
endif;
echo $view->endPageContainer();
