<?php // no direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php JHTML::_('stylesheet', 'poll_bars.css', 'components/com_poll/assets/'); ?>
<form action="index.php" method="post" name="poll" id="poll">
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

<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<label for="id">
		<?php echo JText::_('Select Poll'); ?>
		<?php echo $this->lists['polls']; ?>
	</label>
</div>
<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
<?php echo $this->loadTemplate('graph'); ?>
</div>

</div>
<div class="cleared"></div>


</div>

    </div>
</div>

</form>
