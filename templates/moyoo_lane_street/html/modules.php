<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

function modChrome_artstyle($module, &$params, &$attribs)
{
	$style = isset($attribs['artstyle']) ? $attribs['artstyle'] : 'art-nostyle';
	$sfx = $params->get('moduleclass_sfx');
	if (in_array($sfx, array('art-nostyle', 'art-block', 'art-article')))
		$style = $sfx;
	switch ($style) {
		case 'art-block':
			modChrome_artblock($module, $params, $attribs);
			break;
		case 'art-article':
			modChrome_artarticle($module, $params, $attribs);
			break;
		case 'art-nostyle':
			modChrome_artnostyle($module, $params, $attribs);
			break;
	}
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
if (!empty ($module->content)) : ?>
<div class="art-Block">
    <div class="art-Block-body">

<?php if ($module->showtitle != 0) : ?>
<div class="art-BlockHeader">
    <div class="l"></div>
    <div class="r"></div>
    <div class="art-header-tag-icon">
        <div class="t">
<?php echo $module->title; ?>
</div>
    </div>
</div>
<?php endif; ?>
<div class="art-BlockContent">
    <div class="art-BlockContent-tl"></div>
    <div class="art-BlockContent-tr"></div>
    <div class="art-BlockContent-bl"></div>
    <div class="art-BlockContent-br"></div>
    <div class="art-BlockContent-tc"></div>
    <div class="art-BlockContent-bc"></div>
    <div class="art-BlockContent-cl"></div>
    <div class="art-BlockContent-cr"></div>
    <div class="art-BlockContent-cc"></div>
    <div class="art-BlockContent-body">

<?php echo $module->content; ?>

    </div>
</div>


    </div>
</div>

<?php endif;
}

function modChrome_artarticle($module, &$params, &$attribs)
{
if (!empty ($module->content)) : ?>
<div class="art-Post">
    <div class="art-Post-body">
<div class="art-Post-inner">

<?php if ($module->showtitle != 0) : ?>
<h2 class="art-PostHeaderIcon-wrapper"> <span class="art-PostHeader">
<?php echo $module->title; ?>
</span>
</h2>

<?php endif; ?>
<div class="art-PostContent">

<?php echo $module->content; ?>

</div>
<div class="cleared"></div>


</div>

    </div>
</div>

<?php endif;
}