<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php $canEdit = ($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own')); ?>
<?php if ($this->item->state == 0) : ?>
<div class="system-unpublished">
<?php endif; ?>

<?php $metadata = array(); ?>
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
<?php if ($this->item->params->get('show_title')) : ?>
<h2 class="PostHeaderIcon-wrapper"><?php echo JHTML::_('image.site', 'PostHeaderIcon.png', null, null, null, JText::_("PostHeaderIcon"), array('width' => '29', 'height' => '29')); ?> <?php if ($this->item->params->get('link_titles') && $this->item->readmore_link != '') : ?>
		<a href="<?php echo $this->item->readmore_link; ?>" class="PostHeader">
			<?php echo $this->escape($this->item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->item->title); ?>
		<?php endif; ?>
</h2>
<?php endif; ?>
<div class="PostHeaderIcons metadata-icons">
<?php if ($this->item->params->get('show_create_date')) : ?>
<?php ob_start(); ?><?php echo JHTML::_('image.site', 'PostDateIcon.png', null, null, null, JText::_("PostDateIcon"), array('width' => '17', 'height' => '18')); ?> <?php echo JHTML::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2')); ?>
<?php $metadata[] = ob_get_clean(); ?>
<?php endif; ?>
<?php if (($this->item->params->get('show_author')) && ($this->item->author != "")) : ?>
<?php ob_start(); ?><?php echo JHTML::_('image.site', 'PostAuthorIcon.png', null, null, null, JText::_("PostAuthorIcon"), array('width' => '14', 'height' => '14')); ?> <?php JText::printf('Author: %s', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author) ); ?>
<?php $metadata[] = ob_get_clean(); ?>
<?php endif; ?>

<?php
if ($this->item->params->get('show_url') && $this->item->urls)
 $metadata[] = '<a href="http://' . $this->item->urls . '" target="_blank">' . $this->item->urls . '</a>';
$joomlaIcons = array();
if ($this->item->params->get('show_pdf_icon'))
 $joomlaIcons[] = JHTML::_('icon.pdf', $this->item, $this->item->params, $this->access);
if ( $this->item->params->get( 'show_print_icon' ))
 $joomlaIcons[] = JHTML::_('icon.print_popup', $this->item, $this->item->params, $this->access);
if ($this->item->params->get('show_email_icon'))
 $joomlaIcons[] = JHTML::_('icon.email', $this->item, $this->item->params, $this->access);
if (0 != count($joomlaIcons))
 $metadata[] = '<span class="metadata-icons">' . implode('&nbsp;', $joomlaIcons) . '</span>';
if ($canEdit)
  $metadata[] = JHTML::_('icon.edit', $this->item, $this->item->params, $this->access);
echo implode(' | ', $metadata);
?>

</div>
<div class="PostContent">
<?php  if (!$this->item->params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<?php if (($this->item->params->get('show_section') && $this->item->sectionid) || ($this->item->params->get('show_category') && $this->item->catid)) : ?>
<table class="contentpaneopen<?php echo $this->item->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td>
		<?php if ($this->item->params->get('show_section') && $this->item->sectionid && isset($this->item->section)) : ?>
		<span>
			<?php if ($this->item->params->get('link_section')) : ?>
				<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->item->sectionid)).'">'; ?>
			<?php endif; ?>
			<?php echo $this->item->section; ?>
			<?php if ($this->item->params->get('link_section')) : ?>
				<?php echo '</a>'; ?>
			<?php endif; ?>
				<?php if ($this->item->params->get('show_category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>
		<?php if ($this->item->params->get('show_category') && $this->item->catid) : ?>
		<span>
			<?php if ($this->item->params->get('link_category')) : ?>
				<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug, $this->item->sectionid)).'">'; ?>
			<?php endif; ?>
			<?php echo $this->item->category; ?>
			<?php if ($this->item->params->get('link_category')) : ?>
				<?php echo '</a>'; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
</table>
<?php endif; ?>
<?php if (isset ($this->item->toc)) : ?>
	<?php echo $this->item->toc; ?>
<?php endif; ?>
<div class="article">
<?php echo $this->item->text; ?>
</div>
<?php if ( intval($this->item->modified) != 0 && $this->item->params->get('show_modify_date')) : ?>
<p class="modifydate">
<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo JHTML::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2')); ?> )
</p>
<?php endif; ?>
<?php if ($this->item->params->get('show_readmore') && $this->item->readmore) : ?>
<p>
 <a class="Button" href="<?php echo $this->item->readmore_link; ?>">
  <span class="btn">
   <span class="t"><?php if ($this->item->readmore_register) :
				echo str_replace(' ', '&nbsp;', JText::_('Register to read more...'));
			elseif ($readmore = $this->item->params->get('readmore')) :
				echo str_replace(' ', '&nbsp;', $readmore);
			else :
				echo str_replace(' ', '&nbsp;', JText::sprintf('Read more...'));
			endif; ?></span>
   <span class="r"><span></span></span>
   <span class="l"></span>
  </span>
 </a>
</p>
<?php endif; ?>

</div>
<div class="cleared"></div>

</div>

    </div>
</div>


<?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>
<span class="article_separator">&nbsp;</span>
<?php echo $this->item->event->afterDisplayContent; ?>
