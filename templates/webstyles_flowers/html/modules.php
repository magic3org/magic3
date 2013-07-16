<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

function modChrome_artblock($module, &$params, &$attribs)
{
if (!empty ($module->content)) : ?>
<div class="Block">
    <div class="Block-tl"></div>
    <div class="Block-tr"><div></div></div>
    <div class="Block-bl"><div></div></div>
    <div class="Block-br"><div></div></div>
    <div class="Block-tc"><div></div></div>
    <div class="Block-bc"><div></div></div>
    <div class="Block-cl"><div></div></div>
    <div class="Block-cr"><div></div></div>
    <div class="Block-cc"></div>
    <div class="Block-body">

<?php if ($module->showtitle != 0) : ?>
<div class="BlockHeader">
    <div class="header-tag-icon">
        <div class="BlockHeader-text">

<?php echo $module->title; ?>

        </div>
    </div>
    <div class="l"></div>
    <div class="r"><div></div></div>
</div>

<?php endif; ?>
<div class="BlockContent">
    <div class="BlockContent-body">

<?php echo $module->content; ?>

    </div>
</div>


    </div>
</div>

<?php endif;
}
