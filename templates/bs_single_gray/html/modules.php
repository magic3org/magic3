<?php
defined('_JEXEC') or die;

function modChrome_bootblock($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php if ($module->showtitle != 0) : ?>
			<h2><?php echo $module->title; ?></h2>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}
function modChrome_bootheader($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<?php if ($module->showtitle != 0) : ?>
			<h1 class="brand-heading"><?php echo $module->title; ?></h1>
		<?php endif; ?>
			<div class="intro-text"><?php echo $module->content; ?></div>
	<?php endif;
}
