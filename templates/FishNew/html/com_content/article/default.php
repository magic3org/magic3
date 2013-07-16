<?php
defined('_JEXEC') or die('Restricted access'); // no direct access
require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../functions.php');

$canEdit = ($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own'));

echo artxPost(artxPageTitle($this, $this->params->get('show_page_title', 1) && $this->params->get('page_title') != $this->article->title), null);
?>
<?php $metadata = array(); ?>
<div class="Post">
    <div class="Post-body">
<div class="Post-inner">
<?php if ($this->params->get('show_title')) : ?>
<h2 class="PostHeaderIcon-wrapper"> 	<?php if ($this->params->get('show_title')) : ?><span class="PostHeader">
		<?php if ($this->params->get('link_titles') && $this->article->readmore_link != '') : ?>
		<a href="<?php echo $this->article->readmore_link; ?>" class="PostHeader">
			<?php echo $this->escape($this->article->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->article->title); ?>
		<?php endif; ?></span>

	<?php endif; ?>
</h2>
<?php endif; ?>
<div class="PostHeaderIcons metadata-icons">
<?php if ($this->params->get('show_create_date')) : ?>
<?php ob_start(); ?><?php echo JHTML::_('image.site', 'PostDateIcon.png', null, null, null, JText::_("PostDateIcon"), array('width' => '17', 'height' => '18')); ?> <?php echo JHTML::_('date', $this->article->created, JText::_('DATE_FORMAT_LC2')); ?>
<?php $metadata[] = ob_get_clean(); ?>
<?php endif; ?><?php if (($this->params->get('show_author')) && ($this->article->author != "")) : ?>
<?php ob_start(); ?><?php echo JHTML::_('image.site', 'PostAuthorIcon.png', null, null, null, JText::_("PostAuthorIcon"), array('width' => '14', 'height' => '14')); ?> <?php JText::printf('Author: %s', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author) ); ?>
<?php $metadata[] = ob_get_clean(); ?>
<?php endif; ?><?php
if ($this->params->get('show_url') && $this->article->urls)
 $metadata[] = '<a href="http://' . $this->item->urls . '" target="_blank">' . $this->item->urls . '</a>';
if (!$this->print) {
 $joomlaIcons = array();
 if ($this->params->get('show_pdf_icon'))
  $joomlaIcons[] = JHTML::_('icon.pdf',  $this->article, $this->params, $this->access);
 if ($this->params->get('show_print_icon' ))
  $joomlaIcons[] = JHTML::_('icon.print_popup',  $this->article, $this->params, $this->access);
 if ($this->params->get('show_email_icon'))
  $joomlaIcons[] = JHTML::_('icon.email',  $this->article, $this->params, $this->access);
 if ($joomlaIcons != '')
  $metadata[] = '<span class="metadata-icons">' . implode('&nbsp;', $joomlaIcons) . '</span>';
 if ($canEdit)
  $metadata[] = JHTML::_('icon.edit', $this->article, $this->params, $this->access);
} else {
 $metadata[] = JHTML::_('icon.print_screen',  $this->article, $this->params, $this->access, array('class' => 'metadata-icon'));
}
echo implode(' | ', $metadata);
?>

</div>
<div class="PostContent">
<?php  if (!$this->params->get('show_intro')) :
	echo $this->article->event->afterDisplayTitle;
endif; ?>
<?php echo $this->article->event->beforeDisplayContent; ?>
<?php if (($this->params->get('show_section') && $this->article->sectionid) || ($this->params->get('show_category') && $this->article->catid)) : ?>
<table class="contentpaneopen<?php echo $this->params->get('pageclass_sfx' ); ?>">
<tr>
	<td>
		<?php if ($this->params->get('show_section') && $this->article->sectionid && isset($this->article->section)) : ?>
		<span>
			<?php if ($this->params->get('link_section')) : ?>
				<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->article->sectionid)).'">'; ?>
			<?php endif; ?>
			<?php echo $this->article->section; ?>
			<?php if ($this->params->get('link_section')) : ?>
				<?php echo '</a>'; ?>
			<?php endif; ?>
				<?php if ($this->params->get('show_category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>
		<?php if ($this->params->get('show_category') && $this->article->catid) : ?>
		<span>
			<?php if ($this->params->get('link_category')) : ?>
				<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->article->catslug, $this->article->sectionid)).'">'; ?>
			<?php endif; ?>
			<?php echo $this->article->category; ?>
			<?php if ($this->params->get('link_category')) : ?>
				<?php echo '</a>'; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
</table>
<?php endif; ?>
<?php if (isset ($this->article->toc)) : ?>
	<?php echo $this->article->toc; ?>
<?php endif; ?>
<div class="article">
<?php echo $this->article->text; ?>
</div>
<?php if (intval($this->article->modified) !=0 && $this->params->get('show_modify_date')) : ?>
<p class="modifydate">
		<?php echo JText::_('Last Updated' ); ?> (<?php echo JHTML::_('date', $this->article->modified, JText::_('DATE_FORMAT_LC2')); ?>)
</p>
<?php endif; ?>
<span class="article_separator">&nbsp;</span>
<?php echo $this->article->event->afterDisplayContent; ?>

</div>
<div class="cleared"></div>

</div>

    </div>
</div>

