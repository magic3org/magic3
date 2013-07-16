<?php
defined('_JEXEC') or die('Restricted access'); // no direct access
$canEdit = ($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own'));
?>
<?php if ($this->item->state == 0) : ?>
<div class="system-unpublished">
<?php endif; ?>

<div class="art-Post">
    <div class="art-Post-body">
<div class="art-Post-inner">
<?php
if ($this->item->params->get('show_title')) {
 ob_start();
?>
 <h2 class="art-PostHeaderIcon-wrapper"> <span class="art-PostHeader">
<?php
 artxFragmentBegin(ob_get_clean());
 if ($this->item->params->get('link_titles') && $this->item->readmore_link != '')
  artxFragmentContent('<a href="' . $this->item->readmore_link . '" class="PostHeader">' . $this->escape($this->item->title) . '</a>');
 else
  artxFragmentContent($this->escape($this->item->title));
 ob_start();
?>
</span>
</h2>

<?php
 artxFragmentEnd(ob_get_clean());
}
artxFragmentBegin("<div class=\"art-PostHeaderIcons art-metadata-icons\">\r\n");
if ($this->item->params->get('show_create_date')) {
echo artxFragment('', JHTML::_('image.site', 'PostDateIcon.png', null, null, null, JText::_("PostDateIcon"), array('width' => '17', 'height' => '18')) . JHTML::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2')), '', ' | ');
}
if (($this->item->params->get('show_author')) && ($this->item->author != "")) {
  echo artxFragment('', JHTML::_('image.site', 'PostAuthorIcon.png', null, null, null, JText::_("PostAuthorIcon"), array('width' => '14', 'height' => '14')) . JText::sprintf('Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author)), '', ' | ');
}

if ($this->params->get('show_url') && $this->article->urls)
 artxFragment('', '<a href="http://' . $this->item->urls . '" target="_blank">' . $this->item->urls . '</a>', '', ' | ');
artxFragmentBegin('<span class="art-metadata-icons">');
if ($this->item->params->get('show_pdf_icon'))
 artxFragment('', JHTML::_('icon.pdf',  $this->item, $this->item->params, $this->access), '', '&nbsp;');
if ($this->item->params->get('show_print_icon' ))
 artxFragment('', JHTML::_('icon.print_popup', $this->item, $this->item->params, $this->access), '', '&nbsp;');
if ($this->item->params->get('show_email_icon'))
 artxFragment('', JHTML::_('icon.email', $this->item, $this->item->params, $this->access), '', '&nbsp;');
artxFragmentEnd('</span>', ' | ');
if ($canEdit)
 artxFragment('', JHTML::_('icon.edit', $this->item, $this->item->params, $this->access), '', ' | ');
artxFragmentEnd("\r\n</div>\r\n");
echo "<div class=\"art-PostContent\">\r\n";
if (!$this->item->params->get('show_intro'))
	echo $this->item->event->afterDisplayTitle;
echo $this->item->event->beforeDisplayContent;
if (($this->item->params->get('show_section') && $this->item->sectionid) || ($this->item->params->get('show_category') && $this->item->catid)) {
?>
<table class="contentpaneopen<?php echo $this->item->params->get('pageclass_sfx' ); ?>">
<tr>
	<td>
<?php
if ($this->item->params->get('show_section') && $this->item->sectionid && isset($this->item->section)) {
 echo "<span>";
 if ($this->item->params->get('link_section'))
  echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->item->sectionid)).'">';
 echo $this->item->section;
 if ($this->item->params->get('link_section'))
  echo '</a>';
 if ($this->item->params->get('show_category'))
  echo ' - ';
 echo "</span>";
}
if ($this->item->params->get('show_category') && $this->item->catid) {
 echo "<span>";
 if ($this->item->params->get('link_category'))
  echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug, $this->item->sectionid)).'">';
 echo $this->item->category;
 if ($this->item->params->get('link_category'))
  echo '</a>';
 echo "</span>";
}
?>
	</td>
</tr>
</table>
<?php
}
if (isset ($this->item->toc))
 echo $this->item->toc;
echo "<div class=\"art-article\">", $this->item->text, "</div>";
if (intval($this->item->modified) != 0 && $this->item->params->get('show_modify_date')) {
 echo "<p class=\"modifydate\">";
 echo JText::_('Last Updated') . ' (' . JHTML::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2')) . ')';
 echo "</p>";
}
if ($this->item->params->get('show_readmore') && $this->item->readmore) {
?>
<p>
 <a class="readon" href="<?php echo $this->item->readmore_link; ?>">
  <?php
   if ($this->item->readmore_register) {
    echo str_replace(' ', '&nbsp;', JText::_('Register to read more...'));
   } elseif ($readmore = $this->item->params->get('readmore')){ 
    echo str_replace(' ', '&nbsp;', $readmore);
   } else {
    echo str_replace(' ', '&nbsp;', JText::sprintf('Read more...'));
   }
  ?>
 </a>
</p>
<?php
}
echo "<span class=\"article_separator\">&nbsp;</span>";
echo $this->item->event->afterDisplayContent;
echo "\r\n</div>\r\n<div class=\"cleared\"></div>\r\n";
?>

</div>

    </div>
</div>


<?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>
