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
			<h1 id="homeHeading"><?php echo $module->title; ?></h1>
		<?php endif; ?>
			<hr /><?php echo $module->content; ?>
	<?php endif;
} 
function modChrome_bootother($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
    <section class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
		<?php if ($module->showtitle != 0) : ?>
			<h2><?php echo $module->title; ?></h2>
		<?php endif; ?>
			<?php echo $module->content; ?>
            </div>
        </div>
    </section>
	<?php endif;
}
function modChrome_boottitle($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"><span class="sr-only">Toggle navigation</span><?php echo $module->content; ?><i class="fa fa-bars"></i></button>
	<?php endif;
}
function modChrome_bootbrand($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
			<a class="navbar-brand page-scroll" href="#page-top"><?php echo $module->content; ?></a>
	<?php endif;
}
