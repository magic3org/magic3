<?php
defined('_JEXEC') or die('Restricted access'); // no direct access
require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../functions.php');

$canEdit = ($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own'));

echo artxPost(artxPageTitle($this, $this->params->get('show_page_title', 1) && $this->params->get('page_title') != $this->article->title), null);
?>
<div class="art-post">
    <div class="art-post-body">
<div class="art-post-inner">
<?php
if ($this->params->get('show_title')) {
 ob_start();
?>
 <h2 class="art-postheader"> 
<?php
 artxFragmentBegin(ob_get_clean());
 if ($this->params->get('link_titles') && $this->article->readmore_link != '')
  artxFragmentContent('<a href="' . $this->article->readmore_link . '" class="PostHeader">' . $this->escape($this->article->title) . '</a>');
 else
  artxFragmentContent($this->escape($this->article->title));
 ob_start();
?>

</h2>

<?php
 artxFragmentEnd(ob_get_clean());
}
artxFragmentBegin("<div class=\"art-postheadericons art-metadata-icons\">\r\n");
  if ($this->params->get('show_url') && $this->article->urls)
   artxFragment('', '<a href="http://' . $this->item->urls . '" target="_blank">' . $this->item->urls . '</a>', '', ' | ');
if ($this->params->get('show_create_date')) {
  artxFragment('', JHTML::_('image.site', 'postdateicon.png', null, null, null, JText::_("postdateicon"), array('width' => '17', 'height' => '18')) . JHTML::_('date', $this->article->created, JText::_('DATE_FORMAT_LC2')), '', ' | ');
}
if (($this->params->get('show_author')) && ($this->article->author != "")) {
  artxFragment('', JHTML::_('image.site', 'postauthoricon.png', null, null, null, JText::_("postauthoricon"), array('width' => '18', 'height' => '18')) . JText::sprintf('Written by', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author)), '', ' | ');
}
if (!$this->print && $this->params->get('show_pdf_icon'))
 artxFragment('', JHTML::_('icon.pdf',  $this->article, $this->params, $this->access), '', ' | ');
if (!$this->print && $this->params->get('show_print_icon' ))
 artxFragment('', JHTML::_('icon.print_popup', $this->article, $this->params, $this->access), '', ' | ');
if (!$this->print && $this->params->get('show_email_icon'))
 artxFragment('', JHTML::_('icon.email', $this->article, $this->params, $this->access), '', ' | ');
if (!$this->print && $canEdit)
 artxFragment('', JHTML::_('icon.edit', $this->article, $this->params, $this->access), '', ' | ');

if ($this->print)
 artxFragment('', JHTML::_('icon.print_screen',  $this->article, $this->params, $this->access, array('class' => 'art-metadata-icon')), '', ' | ');
artxFragmentEnd("\r\n</div>\r\n");
echo "<div class=\"art-postcontent\">\r\n    <!-- article-content -->\r\n";
if (!$this->params->get('show_intro'))
 echo $this->article->event->afterDisplayTitle;
echo $this->article->event->beforeDisplayContent;
if (($this->params->get('show_section') && $this->article->sectionid) || ($this->params->get('show_category') && $this->article->catid)) {
?>
<table class="contentpaneopen<?php echo $this->params->get('pageclass_sfx' ); ?>">
<tr>
	<td>
<?php
if ($this->params->get('show_section') && $this->article->sectionid && isset($this->article->section)) {
 echo "<span>";
 if ($this->params->get('link_section'))
  echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->article->sectionid)).'">';
 echo $this->article->section;
 if ($this->params->get('link_section'))
  echo '</a>';
 if ($this->params->get('show_category'))
  echo ' - ';
 echo "</span>";
}
if ($this->params->get('show_category') && $this->article->catid) {
 echo "<span>";
 if ($this->params->get('link_category'))
  echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->article->catslug, $this->article->sectionid)).'">';
 echo $this->article->category;
 if ($this->params->get('link_category'))
  echo '</a>';
 echo "</span>";
}
?>
	</td>
</tr>
</table>
<?php
}
if (isset ($this->article->toc))
 echo $this->article->toc;
echo "<div class=\"art-article\">";
echo $this->article->text;
echo "</div>";
if (intval($this->article->modified) !=0 && $this->params->get('show_modify_date')) {
 echo "<p class=\"modifydate\">";
 echo JText::_('Last Updated' ) . ' (' . JHTML::_('date', $this->article->modified, JText::_('DATE_FORMAT_LC2')) . ')';
 echo "</p>";
}
echo "<span class=\"article_separator\">&nbsp;</span>";
echo $this->article->event->afterDisplayContent;
echo "\r\n    <!-- /article-content -->\r\n</div>\r\n<div class=\"cleared\"></div>\r\n";
?>

</div>

		<div class="cleared"></div>
    </div>
</div>

