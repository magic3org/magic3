<?php
defined('_JEXEC') or die('Restricted access'); // no direct access
$canEdit = ($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own'));
?>
<?php if ($this->item->state == 0) : ?>
<div class="system-unpublished">
<?php endif; ?>

<div class="art-Post">
    <div class="art-Post-tl"></div>
    <div class="art-Post-tr"></div>
    <div class="art-Post-bl"></div>
    <div class="art-Post-br"></div>
    <div class="art-Post-tc"></div>
    <div class="art-Post-bc"></div>
    <div class="art-Post-cl"></div>
    <div class="art-Post-cr"></div>
    <div class="art-Post-cc"></div>
    <div class="art-Post-body">
<div class="art-Post-inner">
<?php
if ($this->item->params->get('show_title')) {
 ob_start();
?>
 <h2 class="art-PostHeader"><?php echo JHTML::_('image.site', 'PostHeaderIcon.png', null, null, null, JText::_("PostHeaderIcon"), array('width' => '26', 'height' => '26')); ?> 
<?php
 artxFragmentBegin(ob_get_clean());
 if ($this->item->params->get('link_titles') && $this->item->readmore_link != '')
  artxFragmentContent('<a href="' . $this->item->readmore_link . '" class="PostHeader">' . $this->escape($this->item->title) . '</a>');
 else
  artxFragmentContent($this->escape($this->item->title));
 ob_start();
?>

</h2>

<?php
 artxFragmentEnd(ob_get_clean());
}
artxFragmentBegin("<div class=\"art-PostHeaderIcons art-metadata-icons\">\r\n");
  if ($this->params->get('show_url') && $this->article->urls)
    artxFragment('', '<a href="http://' . $this->item->urls . '" target="_blank">' . $this->item->urls . '</a>', '', ' | ');
if ($this->item->params->get('show_create_date')) {
artxFragment('', JHTML::_('image.site', 'PostDateIcon.png', null, null, null, JText::_("PostDateIcon"), array('width' => '13', 'height' => '11')) . JHTML::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2')), '', ' | ');
}
if (($this->item->params->get('show_author')) && ($this->item->author != "")) {
  artxFragment('', JHTML::_('image.site', 'PostAuthorIcon.png', null, null, null, JText::_("PostAuthorIcon"), array('width' => '12', 'height' => '14')) . JText::sprintf('Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author)), '', ' | ');
}
if ($this->item->params->get('show_pdf_icon'))
  artxFragment('', JHTML::_('icon.pdf', $this->item, $this->item->params, $this->access), '', ' | ');
if ($this->item->params->get('show_print_icon'))
  artxFragment('', JHTML::_('icon.print_popup', $this->item, $this->item->params, $this->access), '', ' | ');
if ($this->item->params->get('show_email_icon'))
  artxFragment('', JHTML::_('icon.email', $this->item, $this->item->params, $this->access), '', ' | ');
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
 <span class="art-button-wrapper">
  <span class="l"> </span>
  <span class="r"> </span>
  <a class="readon art-button" href="<?php echo $this->item->readmore_link; ?>">
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
 </span>
</p>
<?php
}
echo "<span class=\"article_separator\">&nbsp;</span>";
echo $this->item->event->afterDisplayContent;
echo "\r\n</div>\r\n<div class=\"cleared\"></div>\r\n";
?>

</div>

		<div class="cleared"></div>
    </div>
</div>


<?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>
