<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div class="Post">
    <div class="Post-body">
<div class="Post-inner">

<?php if ($this->params->def('show_page_title', 1)): ?>
<h2 class="PostHeaderIcon-wrapper"><?php echo JHTML::_('image.site', 'PostHeaderIcon.png', null, null, null, JText::_("PostHeaderIcon"), array('width' => '24', 'height' => '31')); ?> <span class="PostHeader">
<span class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></span>
</span>
</h2>

<?php endif; ?>
<div class="PostContent">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td>
		<?php echo JText::_( 'WELCOME_DESC' ); ?>
	</td>
</tr>
</table>

</div>
<div class="cleared"></div>


</div>

    </div>
</div>

