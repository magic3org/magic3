<?php
defined('_JEXEC') or die;
?>
<div class="widgetwide">
<?php if (!empty($this->item->title)) : ?>
	<h2><?php echo convertToHtmlEntity($this->item->title); ?></h2>
<?php endif; ?>
	<?php echo $this->item->text; ?>
</div>