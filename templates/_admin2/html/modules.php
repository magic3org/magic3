<?php
defined('_JEXEC') or die('Restricted access'); // no direct access

$GLOBALS['artx_settings'] = array('block' => array('has_header' => true));

function modChrome_artstyle($module, &$params, &$attribs)
{
  $style = isset($attribs['artstyle']) ? $attribs['artstyle'] : 'art-nostyle';
  $styles = array(
    'art-nostyle' => 'modChrome_artnostyle',
    'art-block' => 'modChrome_artblock',
    'art-article' => 'modChrome_artarticle',
    'art-vmenu' => 'modChrome_artvmenu'
  );
  $sfx = $params->get('moduleclass_sfx');
  if (in_array($sfx, array_keys($styles)))
    $style = $sfx;
  call_user_func($styles[$style], $module, $params, $attribs);
}

function modChrome_artnostyle($module, &$params, &$attribs)
{
if (!empty ($module->content)) : ?>
<div class="art-nostyle">
<?php if ($module->showtitle != 0) : ?>
<h3><?php echo $module->title; ?></h3>
<?php endif; ?>
<?php echo $module->content; ?>
</div>
<?php endif;
}

function modChrome_artblock($module, &$params, &$attribs)
{
  if (!empty ($module->content))
    echo _artxBlock(($module->showtitle != 0) ? $module->title : '', $module->content);
}

function modChrome_artarticle($module, &$params, &$attribs)
{
  if (!empty ($module->content))
    echo artxPost(($module->showtitle != 0) ? $module->title : '', $module->content);
}

function _artxBlock($caption, $content)
{
	$hasCaption = ($GLOBALS['artx_settings']['block']['has_header']
		&& null !== $caption && strlen(trim($caption)) > 0);
	$hasContent = (null !== $content && strlen(trim($content)) > 0);

	if (!$hasCaption && !$hasContent)
		return '';

	ob_start();
?>
<div class="m3widget_box ui-widget">
<?php if ($hasCaption): ?>
<div class="m3widget_box_head ui-state-default ui-priority-primary ui-corner-tl ui-corner-tr"><?php echo $caption; ?></div>
<?php endif; ?>
<?php if ($hasContent): ?>
		<div class="m3widget_box_content ui-widget-content ui-corner-bl ui-corner-br">
		<!-- block-content -->
<?php echo $content; ?>
		<!-- /block-content -->
		</div>
<?php endif; ?>
</div>
<?php
	return ob_get_clean();
}
?>