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
<?php require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'functions.php'; ?>
<?php
$categories_per_row = VmConfig::get ( 'categories_per_row', 3);
$itemClass = 'separated-item-3  grid' . preg_replace('/col-(lg|md|sm|xs)-\d+/',
    'col-$1-' . round(24 / min(24, max(1, $categories_per_row))), 'col-lg-1 col-md-1 col-sm-1 col-xs-1');
?>
<div class="data-control-id-2876 bd-productcategories-1">
  <div class="container-fluid">
    <div class="separated-grid row">
    <div class="data-control-id-2857 bd-container-9 bd-tagstyles">
    <?php echo JText::_('COM_VIRTUEMART_CATEGORIES') ?>
</div>
    <?php foreach ($this->categories as $category) : ?>
    <?php
            $categoryNameDecorator = new stdClass();
            $categoryNameDecorator->link = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id, FALSE);
    $categoryNameDecorator->name = $category->category_name;
    $categoryImageDecorator = new stdClass();
    $categoryImageDecorator->image = $category->images[0];
    $categoryImageDecorator->link = $categoryNameDecorator->link;
    $categoryItems = new stdClass();
    $categoryItems->categoryName = $categoryNameDecorator;
    $categoryItems->categoryImage = $categoryImageDecorator;
    ?>
    
    <div class="<?php echo $itemClass; ?>">
        <div class="bd-griditem-3">
            <?php if (isset($categoryItems->categoryName)) : ?>
<div class="data-control-id-281 bd-categoryname-1">
    <?php
            if ('' !== $categoryItems->categoryName->link)
    echo JHTML::link($categoryItems->categoryName->link, $categoryItems->categoryName->name);
    else
    echo $categoryItems->categoryName->name;
    ?>
</div>
<?php endif; ?>
	
		<?php if (isset($categoryItems->categoryImage)) : ?>
<?php
            $height = 'height:' . VmConfig::get ('img_height') . 'px;';
            $width = 'width:' . VmConfig::get ('img_width') . 'px;';
            $size = $height . $width;
        ?>
<?php if ('' !== $categoryItems->categoryImage->link) : ?>
<a class="data-control-id-283 bd-categoryimage-1" href="<?php echo $categoryItems->categoryImage->link; ?>">
    <?php echo $categoryItems->categoryImage->image->displayMediaThumb('class="data-control-id-282 bd-imagestyles-32"', false); ?>
</a>
<?php else: ?>
<div class="data-control-id-283 bd-categoryimage-1">
    <?php echo $categoryItems->categoryImage->image->displayMediaThumb('class="data-control-id-282 bd-imagestyles-32"', false); ?>
</div>
<?php endif; ?>
<?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
  </div>
</div>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>