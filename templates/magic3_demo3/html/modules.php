<?php
defined('_JEXEC') or die('Restricted access'); // no direct access

if (!defined('_ARTX_FUNCTIONS'))
  require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../functions.php');

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
    echo artxBlock(($module->showtitle != 0) ? $module->title : '', $module->content);
}

function modChrome_artvmenu($module, &$params, &$attribs)
{
  if (!empty ($module->content)) {
    if (function_exists('artxVMenuBlock'))
      echo artxVMenuBlock(($module->showtitle != 0) ? $module->title : '', $module->content);
    else
      echo artxBlock(($module->showtitle != 0) ? $module->title : '', $module->content);
  }
}

function modChrome_artarticle($module, &$params, &$attribs)
{
  if (!empty ($module->content))
    echo artxPost(($module->showtitle != 0) ? $module->title : '', $module->content);
}
