<?php
defined('_JEXEC') or die;
require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../functions.php');
$version = new JVersion();
$view = ('1.5' == $version->RELEASE) ? new ArtxContent15($this, $this->params) : new ArtxContent16($this, $this->params);
?>
<?php $cparams =& JComponentHelper::getParams('com_media'); ?>
<?php echo artxPost(array('header-text' => $view->pageHeading)); ?>
<?php if (($this->params->get('show_description_image') && $this->section->image)
	|| ($this->params->get('show_description') && $this->section->description)) : ?>
<?php ob_start(); ?>
<?php if ($this->params->get('show_description_image') && $this->section->image) : ?>
	<img src="<?php echo $this->baseurl . '/' . $cparams->get('image_path') . '/'. $this->section->image;?>" align="<?php echo $this->section->image_position;?>" hspace="6" alt="" />
<?php endif; ?>
<?php if ($this->params->get('show_description') && $this->section->description) : ?>
	<?php echo $this->section->description; ?>
<?php endif; ?>
<?php echo artxPost(ob_get_clean()); ?>
<?php endif; ?>
<?php if ($this->params->def('num_leading_articles', 1)) : ?>
<table class="blog<?php echo $view->pageClassSfx; ?>" cellpadding="0" cellspacing="0" width="100%">
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
</table>
<?php else : $i = $this->pagination->limitstart; endif; ?>

<?php
$startIntroArticles = $this->pagination->limitstart + $this->params->get('num_leading_articles');
$numIntroArticles = $startIntroArticles + $this->params->get('num_intro_articles', 4);
if (($numIntroArticles != $startIntroArticles) && ($i < $this->total)) : ?>
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
						$i = $i + $this->params->get('num_intro_articles', 4) ; 
			else : // otherwise, order down, same as before (default behaviour)
				for ($z = 0; $z < $this->params->get('num_columns'); $z ++) :
				if ($z > 0) : $divider = " column_separator"; endif; ?>
				<td valign="top" width="<?php echo intval(100 / $this->params->get('num_columns')) ?>%" class="article_column<?php echo $divider ?>">
				<?php for ($y = 0; $y < ($this->params->get('num_intro_articles', 4) / $this->params->get('num_columns')); $y ++) :
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
<?php endif; ?>
<?php if ($this->params->def('num_links', 4) && ($i < $this->total)) : ?>
<?php $this->links = array_splice($this->items, $i - $this->pagination->limitstart); ?>
<?php if (count($this->links) > 0) : ?>
<?php ob_start(); ?>
<?php echo $this->loadTemplate('links'); ?>
<?php echo artxPost(ob_get_clean()); ?>
<?php endif; ?>
<?php endif; ?>
<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
<?php ob_start(); ?>
<div id="navigation">
	<p><?php echo $this->pagination->getPagesLinks(); ?></p>
<?php if ($this->params->def('show_pagination_results', 1)) : ?>
	<p><?php echo $this->pagination->getPagesCounter(); ?></p>
<?php endif; ?>
</div>
<?php echo artxPost(ob_get_clean()); ?>
<?php endif; ?>