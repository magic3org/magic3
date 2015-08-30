<?php
defined('_JEXEC') or die;
?>
<?php /*BEGIN_EDITOR_OPEN*/
$app = JFactory::getApplication('site');
$templateName = $app->getTemplate();

$ret = false;
$templateDir = JPATH_THEMES . '/' . $templateName;
$editorClass = $templateDir . '/app/' . 'Editor.php';

if (!$app->isAdmin() && file_exists($editorClass)) {
    require_once $templateDir . '/app/' . 'Editor.php';
    $ret = DesignerEditor::override($templateName, __FILE__);
}

if ($ret) {
    $editorDir = $templateName . '/editor';
    require($ret);
    return;
} else {
/*BEGIN_EDITOR_CLOSE*/ ?>
<?php if ($this->params->get('presentation_style') == 'sliders') : ?>
    <div class=" bd-menuitem-13" id="slide-links">
        <a data-toggle="collapse" data-target="#slide-links + div">
            <?php echo JText::_('COM_CONTACT_LINKS'); ?>
        </a>
    </div>
    <div class="collapse">
        <div class=" bd-container-49 bd-tagstyles clearfix">
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'tabs') : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'display-links', JText::_('COM_CONTACT_LINKS', true)); ?>
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'plain'):?>
	<?php echo '<h3>'. JText::_('COM_CONTACT_LINKS').'</h3>';  ?>
<?php endif; ?>

<div class="contact-links">
	<ul class="nav nav-tabs nav-stacked">
		<?php
		foreach (range('a', 'e') as $char) :// letters 'a' to 'e'
			$link = $this->contact->params->get('link'.$char);
			$label = $this->contact->params->get('link'.$char.'_name');

			if (!$link) :
				continue;
			endif;

			// Add 'http://' if not present
			$link = (0 === strpos($link, 'http')) ? $link : 'http://'.$link;

			// If no label is present, take the link
			$label = ($label) ? $label : $link;
			?>
			<li>
				<a href="<?php echo $link; ?>" itemprop="url">
					<?php echo $label; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<?php if ($this->params->get('presentation_style') == 'sliders') : ?>
        </div>
    </div>
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'tabs') : ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
<?php endif; ?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>