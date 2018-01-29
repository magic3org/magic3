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
<?php
if (!defined('_DESIGNER_FUNCTIONS'))
  require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../functions.php');

function modChrome_drstyle($module, &$params, &$attribs)
{
    // shortcodes processing
    Designer::load("Designer_Shortcodes");
    $module->content = DesignerShortcodes::process($module->content);

    $module->content = funcContentRoutesCorrector($module->content);

    if ('' === $attribs['drstyle']) {
        echo $module->content;
        return;
    }

    $parts = explode('%', $attribs['drstyle']);
    $style = $parts[0];

    if (!isset($attribs['funcStyling']))
        $attribs['funcStyling'] = count($parts) > 1 ? $parts[1] : '';

    $styles = array(
      'block' => 'modChrome_block',
      'nostyle' => 'modChrome_nostyle'
    );

    $classes = explode(' ', rtrim($params->get('moduleclass_sfx')));
    $keys = array_keys($styles);
    $dr = array();
    foreach ($classes as $key => $class) {
      if (in_array($class, $keys)) {
        $dr[] = $class;
        $classes[$key] = ' ';
      }
    }
    $classes = str_replace('  ', ' ', rtrim(implode(' ', $classes)));
    $style = count($dr) ? array_pop($dr) : $style;
    $params->set('moduleclass_sfx', $classes);
    call_user_func($styles[$style], $module, $params, $attribs);
}

function modChrome_block($module, &$params, &$attribs)
{
    $result = '';
    if (!empty ($module->content)) {
        $args = array('title' => $module->showtitle != 0 ? $module->title : '',
            'content' => $module->content,
            'classes' => $params->get('moduleclass_sfx'),
            'id' => $module->id);
        if ('' !== $attribs['funcStyling']) {
            $args['extraClass'] = isset($attribs['extraClass']) ? $attribs['extraClass'] : '';
            $result = renderTemplateFromIncludes($attribs['funcStyling'], $args);
        }
        else {
            $result = $module->content;
        }
    }
    echo $result;
}

function modChrome_nostyle($module, &$params, &$attribs)
{
if (!empty ($module->content)) : ?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>">
<?php if ($module->showtitle != 0) : ?>
    <h3><?php echo $module->title; ?></h3>
<?php endif; ?>
<?php echo $module->content; ?>
</div>
<?php endif;
}
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>