<!--COMPONENT common -->
<?php
$view = new DesignerContent($this, $this->params);

$this->articleTemplate = 'article_2';

ob_start();
?>
<div class=" bd-blog <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">
<?php
$categoryInfo = '';
if ($this->params->get('show_category_title', 1) || strlen($this->params->get('page_subheading'))) {
    $categoryInfo = $this->escape($this->params->get('page_subheading'));
    if ($this->params->get('show_category_title') && strlen($this->category->title))
        $categoryInfo .= '<span class="subheading-category">' . $this->category->title . '</span>';
}
if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) {
    if ($this->params->get('show_description_image') && $this->category->getParams()->get('image'))
        $categoryInfo .= '<img src="' . $this->category->getParams()->get('image') . '" alt="" />';
    if ($this->params->get('show_description') && $this->category->description)
        $categoryInfo .= JHtml::_('content.prepare', $this->category->description, '', 'com_content.category');
}
$pageHeading = '';
if ('' !== $categoryInfo) {
    echo renderTemplateFromIncludes('article_2', array(array('header-text' => $view->pageHeading, 'content' => $categoryInfo)));
} else {
    $pageHeading = $view->pageHeading;
}
$pagination_list = array();
if (($this->params->def('show_pagination', 1) == 1 || $this->params->get('show_pagination') == 2)
    && $this->pagination->get('pages.total') > 1)
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php if ($pageHeading) : ?>
    <h2 class=" bd-container-15 bd-tagstyles"><?php echo $pageHeading; ?></h2>
<?php endif; ?>

 <?php
    $itemClass = 'separated-item-33 col-md-24 ';
    $mergedModes = '' . '0' . '' . '';
    $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
?>
<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class=" bd-grid-5">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        $leadingItemClass = preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . 24, $str);
    ?>
    <?php foreach ($this->lead_items as &$item) : ?>
        <div class="<?php echo $leadingItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-33">
            <?php
                $this->item = &$item;
                echo $this->loadTemplate('item');
            ?>
            </div>
        </div>
        <?php $leadingcount++; ?>
    <?php endforeach; ?>
        </div>
   </div>
</div>
<?php endif; ?>
<?php
    $introcount = (count($this->intro_items));
    $counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
<div class=" bd-grid-5">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        if ($introcount <= $this->columns) {
            $columnWidth = floor(24/$introcount);
            $balanceWidth = 24 % $introcount;
        } else {
            $columnWidth = floor(24/$this->columns);
            $balanceWidth = 24%$this->columns;
        }
        $introItemClass = $itemClass;
        if ('' === $mergedModes)
            $introItemClass = $introItemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
    ?>
    <?php foreach ($this->intro_items as $key => &$item) : ?>
    <?php
        $rowcount = ((int) $key % (int) $this->columns) + 1;
        $row = $counter / $this->columns ;
    ?>
    <?php $counter++; ?>
    <div class="<?php echo $introItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
        <div class="bd-griditem-33">
    <?php
        $this->item = &$item;
        echo $this->loadTemplate('item');
    ?>
        </div>
    </div>
    <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php  if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
    <div class="cat-children">
    <?php
        echo renderTemplateFromIncludes('article_2', array(array('header-text' => JTEXT::_('JGLOBAL_SUBCATEGORIES'),
            'content' => $this->loadTemplate('children'))));
    ?>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class=" bd-blogpagination-1">
        <?php
            renderTemplateFromIncludes('pagination_list_render_1', array($pagination_list));
        ?>
    </div>
<?php endif; ?>
</div>
<?php
echo ob_get_clean();
?>
<!--COMPONENT common /-->
<!--COMPONENT blog_5 -->
<?php
$view = new DesignerContent($this, $this->params);

$this->articleTemplate = 'article_4';

ob_start();
?>
<div class=" bd-blog-5 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">
<?php
$categoryInfo = '';
if ($this->params->get('show_category_title', 1) || strlen($this->params->get('page_subheading'))) {
    $categoryInfo = $this->escape($this->params->get('page_subheading'));
    if ($this->params->get('show_category_title') && strlen($this->category->title))
        $categoryInfo .= '<span class="subheading-category">' . $this->category->title . '</span>';
}
if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) {
    if ($this->params->get('show_description_image') && $this->category->getParams()->get('image'))
        $categoryInfo .= '<img src="' . $this->category->getParams()->get('image') . '" alt="" />';
    if ($this->params->get('show_description') && $this->category->description)
        $categoryInfo .= JHtml::_('content.prepare', $this->category->description, '', 'com_content.category');
}
$pageHeading = '';
if ('' !== $categoryInfo) {
    echo renderTemplateFromIncludes('article_4', array(array('header-text' => $view->pageHeading, 'content' => $categoryInfo)));
} else {
    $pageHeading = $view->pageHeading;
}
$pagination_list = array();
if (($this->params->def('show_pagination', 1) == 1 || $this->params->get('show_pagination') == 2)
    && $this->pagination->get('pages.total') > 1)
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php if ($pageHeading) : ?>
    <h2 class=" bd-container-21 bd-tagstyles"><?php echo $pageHeading; ?></h2>
<?php endif; ?>

 <?php
    $itemClass = 'separated-item-47 col-md-24 ';
    $mergedModes = '' . '0' . '' . '';
    $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
?>
<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class=" bd-grid-7">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        $leadingItemClass = preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . 24, $str);
    ?>
    <?php foreach ($this->lead_items as &$item) : ?>
        <div class="<?php echo $leadingItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-47">
            <?php
                $this->item = &$item;
                echo $this->loadTemplate('item');
            ?>
            </div>
        </div>
        <?php $leadingcount++; ?>
    <?php endforeach; ?>
        </div>
   </div>
</div>
<?php endif; ?>
<?php
    $introcount = (count($this->intro_items));
    $counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
<div class=" bd-grid-7">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        if ($introcount <= $this->columns) {
            $columnWidth = floor(24/$introcount);
            $balanceWidth = 24 % $introcount;
        } else {
            $columnWidth = floor(24/$this->columns);
            $balanceWidth = 24%$this->columns;
        }
        $introItemClass = $itemClass;
        if ('' === $mergedModes)
            $introItemClass = $introItemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
    ?>
    <?php foreach ($this->intro_items as $key => &$item) : ?>
    <?php
        $rowcount = ((int) $key % (int) $this->columns) + 1;
        $row = $counter / $this->columns ;
    ?>
    <?php $counter++; ?>
    <div class="<?php echo $introItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
        <div class="bd-griditem-47">
    <?php
        $this->item = &$item;
        echo $this->loadTemplate('item');
    ?>
        </div>
    </div>
    <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php  if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
    <div class="cat-children">
    <?php
        echo renderTemplateFromIncludes('article_4', array(array('header-text' => JTEXT::_('JGLOBAL_SUBCATEGORIES'),
            'content' => $this->loadTemplate('children'))));
    ?>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class=" bd-blogpagination-3">
        <?php
            renderTemplateFromIncludes('pagination_list_render_3', array($pagination_list));
        ?>
    </div>
<?php endif; ?>
</div>
<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_5 /-->
<!--COMPONENT blog_3 -->
<?php
$view = new DesignerContent($this, $this->params);

$this->articleTemplate = 'article_3';

ob_start();
?>
<div class=" bd-blog-3 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">
<?php
$categoryInfo = '';
if ($this->params->get('show_category_title', 1) || strlen($this->params->get('page_subheading'))) {
    $categoryInfo = $this->escape($this->params->get('page_subheading'));
    if ($this->params->get('show_category_title') && strlen($this->category->title))
        $categoryInfo .= '<span class="subheading-category">' . $this->category->title . '</span>';
}
if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) {
    if ($this->params->get('show_description_image') && $this->category->getParams()->get('image'))
        $categoryInfo .= '<img src="' . $this->category->getParams()->get('image') . '" alt="" />';
    if ($this->params->get('show_description') && $this->category->description)
        $categoryInfo .= JHtml::_('content.prepare', $this->category->description, '', 'com_content.category');
}
$pageHeading = '';
if ('' !== $categoryInfo) {
    echo renderTemplateFromIncludes('article_3', array(array('header-text' => $view->pageHeading, 'content' => $categoryInfo)));
} else {
    $pageHeading = $view->pageHeading;
}
$pagination_list = array();
if (($this->params->def('show_pagination', 1) == 1 || $this->params->get('show_pagination') == 2)
    && $this->pagination->get('pages.total') > 1)
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php if ($pageHeading) : ?>
    <h2 class=" bd-container-18 bd-tagstyles"><?php echo $pageHeading; ?></h2>
<?php endif; ?>

 <?php
    $itemClass = 'separated-item-38 col-md-24 ';
    $mergedModes = '' . '0' . '' . '';
    $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
?>
<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class=" bd-grid-6">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        $leadingItemClass = preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . 24, $str);
    ?>
    <?php foreach ($this->lead_items as &$item) : ?>
        <div class="<?php echo $leadingItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-38">
            <?php
                $this->item = &$item;
                echo $this->loadTemplate('item');
            ?>
            </div>
        </div>
        <?php $leadingcount++; ?>
    <?php endforeach; ?>
        </div>
   </div>
</div>
<?php endif; ?>
<?php
    $introcount = (count($this->intro_items));
    $counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
<div class=" bd-grid-6">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        if ($introcount <= $this->columns) {
            $columnWidth = floor(24/$introcount);
            $balanceWidth = 24 % $introcount;
        } else {
            $columnWidth = floor(24/$this->columns);
            $balanceWidth = 24%$this->columns;
        }
        $introItemClass = $itemClass;
        if ('' === $mergedModes)
            $introItemClass = $introItemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
    ?>
    <?php foreach ($this->intro_items as $key => &$item) : ?>
    <?php
        $rowcount = ((int) $key % (int) $this->columns) + 1;
        $row = $counter / $this->columns ;
    ?>
    <?php $counter++; ?>
    <div class="<?php echo $introItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
        <div class="bd-griditem-38">
    <?php
        $this->item = &$item;
        echo $this->loadTemplate('item');
    ?>
        </div>
    </div>
    <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php  if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
    <div class="cat-children">
    <?php
        echo renderTemplateFromIncludes('article_3', array(array('header-text' => JTEXT::_('JGLOBAL_SUBCATEGORIES'),
            'content' => $this->loadTemplate('children'))));
    ?>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class=" bd-blogpagination-2">
        <?php
            renderTemplateFromIncludes('pagination_list_render_2', array($pagination_list));
        ?>
    </div>
<?php endif; ?>
</div>
<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_3 /-->
<!--COMPONENT blog_7 -->
<?php
$view = new DesignerContent($this, $this->params);

$this->articleTemplate = 'article_5';

ob_start();
?>
<div class=" bd-blog-7 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">
<?php
$categoryInfo = '';
if ($this->params->get('show_category_title', 1) || strlen($this->params->get('page_subheading'))) {
    $categoryInfo = $this->escape($this->params->get('page_subheading'));
    if ($this->params->get('show_category_title') && strlen($this->category->title))
        $categoryInfo .= '<span class="subheading-category">' . $this->category->title . '</span>';
}
if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) {
    if ($this->params->get('show_description_image') && $this->category->getParams()->get('image'))
        $categoryInfo .= '<img src="' . $this->category->getParams()->get('image') . '" alt="" />';
    if ($this->params->get('show_description') && $this->category->description)
        $categoryInfo .= JHtml::_('content.prepare', $this->category->description, '', 'com_content.category');
}
$pageHeading = '';
if ('' !== $categoryInfo) {
    echo renderTemplateFromIncludes('article_5', array(array('header-text' => $view->pageHeading, 'content' => $categoryInfo)));
} else {
    $pageHeading = $view->pageHeading;
}
$pagination_list = array();
if (($this->params->def('show_pagination', 1) == 1 || $this->params->get('show_pagination') == 2)
    && $this->pagination->get('pages.total') > 1)
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php if ($pageHeading) : ?>
    <h2 class=" bd-container-24 bd-tagstyles"><?php echo $pageHeading; ?></h2>
<?php endif; ?>

 <?php
    $itemClass = 'separated-item-12 col-md-24 ';
    $mergedModes = '' . '0' . '' . '';
    $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
?>
<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class=" bd-grid-8">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        $leadingItemClass = preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . 24, $str);
    ?>
    <?php foreach ($this->lead_items as &$item) : ?>
        <div class="<?php echo $leadingItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-12">
            <?php
                $this->item = &$item;
                echo $this->loadTemplate('item');
            ?>
            </div>
        </div>
        <?php $leadingcount++; ?>
    <?php endforeach; ?>
        </div>
   </div>
</div>
<?php endif; ?>
<?php
    $introcount = (count($this->intro_items));
    $counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
<div class=" bd-grid-8">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        if ($introcount <= $this->columns) {
            $columnWidth = floor(24/$introcount);
            $balanceWidth = 24 % $introcount;
        } else {
            $columnWidth = floor(24/$this->columns);
            $balanceWidth = 24%$this->columns;
        }
        $introItemClass = $itemClass;
        if ('' === $mergedModes)
            $introItemClass = $introItemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
    ?>
    <?php foreach ($this->intro_items as $key => &$item) : ?>
    <?php
        $rowcount = ((int) $key % (int) $this->columns) + 1;
        $row = $counter / $this->columns ;
    ?>
    <?php $counter++; ?>
    <div class="<?php echo $introItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
        <div class="bd-griditem-12">
    <?php
        $this->item = &$item;
        echo $this->loadTemplate('item');
    ?>
        </div>
    </div>
    <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php  if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
    <div class="cat-children">
    <?php
        echo renderTemplateFromIncludes('article_5', array(array('header-text' => JTEXT::_('JGLOBAL_SUBCATEGORIES'),
            'content' => $this->loadTemplate('children'))));
    ?>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class=" bd-blogpagination-4">
        <?php
            renderTemplateFromIncludes('pagination_list_render_4', array($pagination_list));
        ?>
    </div>
<?php endif; ?>
</div>
<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_7 /-->
<!--COMPONENT blog_8 -->
<?php
$view = new DesignerContent($this, $this->params);

$this->articleTemplate = 'article_6';

ob_start();
?>
<div class=" bd-blog-8 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">
<?php
$categoryInfo = '';
if ($this->params->get('show_category_title', 1) || strlen($this->params->get('page_subheading'))) {
    $categoryInfo = $this->escape($this->params->get('page_subheading'));
    if ($this->params->get('show_category_title') && strlen($this->category->title))
        $categoryInfo .= '<span class="subheading-category">' . $this->category->title . '</span>';
}
if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) {
    if ($this->params->get('show_description_image') && $this->category->getParams()->get('image'))
        $categoryInfo .= '<img src="' . $this->category->getParams()->get('image') . '" alt="" />';
    if ($this->params->get('show_description') && $this->category->description)
        $categoryInfo .= JHtml::_('content.prepare', $this->category->description, '', 'com_content.category');
}
$pageHeading = '';
if ('' !== $categoryInfo) {
    echo renderTemplateFromIncludes('article_6', array(array('header-text' => $view->pageHeading, 'content' => $categoryInfo)));
} else {
    $pageHeading = $view->pageHeading;
}
$pagination_list = array();
if (($this->params->def('show_pagination', 1) == 1 || $this->params->get('show_pagination') == 2)
    && $this->pagination->get('pages.total') > 1)
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php if ($pageHeading) : ?>
    <h2 class=" bd-container-27 bd-tagstyles"><?php echo $pageHeading; ?></h2>
<?php endif; ?>

 <?php
    $itemClass = 'separated-item-23 col-md-24 ';
    $mergedModes = '' . '0' . '' . '';
    $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
?>
<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class=" bd-grid-9">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        $leadingItemClass = preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . 24, $str);
    ?>
    <?php foreach ($this->lead_items as &$item) : ?>
        <div class="<?php echo $leadingItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-23">
            <?php
                $this->item = &$item;
                echo $this->loadTemplate('item');
            ?>
            </div>
        </div>
        <?php $leadingcount++; ?>
    <?php endforeach; ?>
        </div>
   </div>
</div>
<?php endif; ?>
<?php
    $introcount = (count($this->intro_items));
    $counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
<div class=" bd-grid-9">
  <div class="container-fluid">
    <div class="separated-grid row">
    <?php
        if ($introcount <= $this->columns) {
            $columnWidth = floor(24/$introcount);
            $balanceWidth = 24 % $introcount;
        } else {
            $columnWidth = floor(24/$this->columns);
            $balanceWidth = 24%$this->columns;
        }
        $introItemClass = $itemClass;
        if ('' === $mergedModes)
            $introItemClass = $introItemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
    ?>
    <?php foreach ($this->intro_items as $key => &$item) : ?>
    <?php
        $rowcount = ((int) $key % (int) $this->columns) + 1;
        $row = $counter / $this->columns ;
    ?>
    <?php $counter++; ?>
    <div class="<?php echo $introItemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
        <div class="bd-griditem-23">
    <?php
        $this->item = &$item;
        echo $this->loadTemplate('item');
    ?>
        </div>
    </div>
    <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php  if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
    <div class="cat-children">
    <?php
        echo renderTemplateFromIncludes('article_6', array(array('header-text' => JTEXT::_('JGLOBAL_SUBCATEGORIES'),
            'content' => $this->loadTemplate('children'))));
    ?>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class=" bd-blogpagination-5">
        <?php
            renderTemplateFromIncludes('pagination_list_render_5', array($pagination_list));
        ?>
    </div>
<?php endif; ?>
</div>
<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_8 /-->