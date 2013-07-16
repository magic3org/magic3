<?php
defined('_JEXEC') or die;
require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../functions.php');
$component = new ArtxContent15($this, $this->params);

$config =& JFactory::getConfig();
$publish_up =& JFactory::getDate($this->article->publish_up);
$publish_up->setOffset($config->getValue('config.offset'));
$publish_up = $publish_up->toFormat();

if (! isset($this->article->publish_down) || $this->article->publish_down == 'Never') {
    $publish_down = JText::_('Never');
} else {
    $publish_down =& JFactory::getDate($this->article->publish_down);
    $publish_down->setOffset($config->getValue('config.offset'));
    $publish_down = $publish_down->toFormat();
}
?>
<?php ob_start(); ?>
<script language="javascript" type="text/javascript">

function setgood() { return true; }

var sectioncategories = [];
<?php
$i = 0;
foreach ($this->lists['sectioncategories'] as $k => $items) {
	foreach ($items as $v) {
		echo "sectioncategories[" . $i++ . "] = ['$k', '" . addslashes($v->id) . "', '" . addslashes( $v->title ) . "'];\n\t\t";
	}
}
?>

function submitbutton(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}
	try {
		form.onsubmit();
	} catch (e) {
		alert(e);
	}

	// do field validation
	var text = <?php echo $this->editor->getContent( 'text' ); ?>
	if (form.title.value == '') {
		return alert ("<?php echo JText::_( 'Article must have a title', true ); ?>");
	} else if (text == '') {
		return alert ("<?php echo JText::_( 'Article must have some text', true ); ?>");
	} else if (parseInt('<?php echo $this->article->sectionid;?>')) {
		// for articles
		if (form.catid && getSelectedValue('adminForm','catid') < 1) {
			return alert("<?php echo JText::_( 'Please select a category', true ); ?>");
		}
	}
	<?php echo $this->editor->save('text'); ?>
	submitform(pressbutton);
}
</script>
<form action="<?php echo $this->action ?>" method="post" name="adminForm" onsubmit="setgood();">
	<fieldset>
		<legend><?php echo JText::_('Editor'); ?></legend>
		<div class="adminform">
			<div style="float: left;">
				<label for="title"><?php echo JText::_( 'Title' ); ?>:</label>
				<input class="inputbox" type="text" id="title" name="title" size="50" maxlength="100" value="<?php echo $this->escape($this->article->title); ?>" />
				<input class="inputbox" type="hidden" id="alias" name="alias" value="<?php echo $this->escape($this->article->alias); ?>" />
			</div>
			<div style="float: right;">
				<span class="art-button-wrapper">
					<span class="art-button-l"> </span>
					<span class="art-button-r"> </span>
					<input type="button" class="art-button" onclick="submitbutton('save')" value="<?php echo JText::_('Save') ?>"/>
				</span>
				<span class="art-button-wrapper">
					<span class="art-button-l"> </span>
					<span class="art-button-r"> </span>
					<input type="button" class="art-button" onclick="submitbutton('cancel')" value="<?php echo JText::_('Cancel') ?>"/>
				</span>
			</div>
			<div style="clear: both;"></div>
		</div>
		<?php echo $this->editor->display('text', $this->article->text, '100%', '400', '70', '15'); ?>
	</fieldset>
	<fieldset>
		<legend><?php echo JText::_('Publishing'); ?></legend>
		<table class="adminform">
		<tr>
			<td class="key"><label for="sectionid"><?php echo JText::_( 'Section' ); ?>:</label></td>
			<td><?php echo $this->lists['sectionid']; ?></td>
		</tr>
		<tr>
			<td class="key"><label for="catid"><?php echo JText::_( 'Category' ); ?>:</label></td>
			<td><?php echo $this->lists['catid']; ?></td>
		</tr>
		<?php if ($this->user->authorize('com_content', 'publish', 'content', 'all')) : ?>
		<tr>
			<td class="key"><label for="state1"><?php echo JText::_( 'Published' ); ?>:</label></td>
			<td><?php echo $this->lists['state']; ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td width="120" class="key"><label for="frontpage1"><?php echo JText::_( 'Show on Front Page' ); ?>:</label></td>
			<td><?php echo $this->lists['frontpage']; ?></td>
		</tr>
		<tr>
			<td class="key"><label for="created_by_alias"><?php echo JText::_( 'Author Alias' ); ?>:</label></td>
			<td><input type="text" id="created_by_alias" name="created_by_alias" size="50" maxlength="100" value="<?php echo $this->escape($this->article->created_by_alias); ?>" class="inputbox" /></td>
		</tr>
		<tr>
			<td class="key"><label for="publish_up"><?php echo JText::_( 'Start Publishing' ); ?>:</label></td>
			<td><?php echo JHTML::_('calendar', $publish_up, 'publish_up', 'publish_up', '%Y-%m-%d %H:%M:%S', array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); ?></td>
		</tr>
		<tr>
			<td class="key"><label for="publish_down"><?php echo JText::_( 'Finish Publishing' ); ?>:</label></td>
			<td><?php echo JHTML::_('calendar', $publish_down, 'publish_down', 'publish_down', '%Y-%m-%d %H:%M:%S', array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); ?></td>
		</tr>
		<tr>
			<td valign="top" class="key"><label for="access"><?php echo JText::_( 'Access Level' ); ?>:</label></td>
			<td><?php echo $this->lists['access']; ?></td>
		</tr>
		<tr>
			<td class="key"><label for="ordering"><?php echo JText::_( 'Ordering' ); ?>:</label></td>
			<td><?php echo $this->lists['ordering']; ?></td>
		</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('Metadata'); ?></legend>
		<table class="adminform" width="100%">
		<tr>
			<td valign="top" class="key" width="120"><label for="metadesc"><?php echo JText::_( 'Description' ); ?>:</label></td>
			<td>
				<textarea rows="5" cols="50" style="width: 95%; height: 120px" class="inputbox" id="metadesc" name="metadesc"><?php echo str_replace('&','&amp;',$this->article->metadesc); ?></textarea>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key" width="120"><label for="metakey"><?php echo JText::_( 'Keywords' ); ?>:</label></td>
			<td>
				<textarea rows="5" cols="50" style="width: 95%; height: 50px" class="inputbox" id="metakey" name="metakey"><?php echo str_replace('&','&amp;',$this->article->metakey); ?></textarea>
			</td>
		</tr>
		</table>
	</fieldset>

	<input type="hidden" name="option" value="com_content" />
	<input type="hidden" name="id" value="<?php echo $this->article->id; ?>" />
	<input type="hidden" name="version" value="<?php echo $this->article->version; ?>" />
	<input type="hidden" name="created_by" value="<?php echo $this->article->created_by; ?>" />
	<input type="hidden" name="referer" value="<?php echo str_replace(array('"', '<', '>', "'"), '', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
</form>
<?php echo JHTML::_('behavior.keepalive'); ?>
<?php echo artxPost(array('header-text' => $component->pageHeading, 'content' => ob_get_clean())); ?>