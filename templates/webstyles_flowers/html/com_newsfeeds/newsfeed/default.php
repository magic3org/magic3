<?php // no direct acces
defined('_JEXEC') or die('Restricted access'); ?>
<div style="direction: <?php echo $this->newsfeed->rtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $this->newsfeed->rtl ? 'right' :'left'; ?>">
<div class="Post">
    <div class="Post-tl"></div>
    <div class="Post-tr"><div></div></div>
    <div class="Post-bl"><div></div></div>
    <div class="Post-br"><div></div></div>
    <div class="Post-tc"><div></div></div>
    <div class="Post-bc"><div></div></div>
    <div class="Post-cl"><div></div></div>
    <div class="Post-cr"><div></div></div>
    <div class="Post-cc"></div>
    <div class="Post-body">
<div class="Post-inner">

<?php if ($this->params->def('show_page_title', 1)): ?>
<h2 class="PostHeaderIcon-wrapper"> <span class="PostHeader">
<span class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></span>
</span>
</h2>

<?php endif; ?>
<div class="PostContent">

<table width="100%" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td class="contentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<a href="<?php echo $this->newsfeed->channel['link']; ?>" target="_blank">
			<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['title']); ?></a>
	</td>
</tr>
<?php if ( $this->params->get( 'show_feed_description' ) ) : ?>
<tr>
	<td>
		<?php echo str_replace('&apos;', "'", $this->newsfeed->channel['description']); ?>
		<br />
		<br />
	</td>
</tr>
<?php endif; ?>
<?php if ( isset($this->newsfeed->image['url']) && isset($this->newsfeed->image['title']) && $this->params->get( 'show_feed_image' ) ) : ?>
<tr>
	<td>
		<img src="<?php echo $this->newsfeed->image['url']; ?>" alt="<?php echo $this->newsfeed->image['title']; ?>" />
	</td>
</tr>
<?php endif; ?>
<tr>
	<td>
		<ul>
		<?php foreach ( $this->newsfeed->items as $item ) :  ?>
			<li>
			<?php if ( !is_null( $item->get_link() ) ) : ?>
				<a href="<?php echo $item->get_link(); ?>" target="_blank">
					<?php echo $item->get_title(); ?></a>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_item_description' ) && $item->get_description()) : ?>
				<br />
				<?php $text = $this->limitText($item->get_description(), $this->params->get( 'feed_word_count' ));
					echo str_replace('&apos;', "'", $text);
				?>
				<br />
				<br />
			<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</td>
</tr>
</table>

</div>
<div class="cleared"></div>


</div>

    </div>
</div>

</div>
