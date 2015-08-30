<!--COMPONENT common -->

<?php
$view = new DesignerContent($this, $this->params);

$pageHeading = $view->pageHeading;
$this->articleTemplate = 'article_2';

ob_start();
?>
<div class="data-control-id-3035 bd-blog <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">

    <?php if ($pageHeading) : ?>
        <h2 class="data-control-id-2913 bd-container-15 bd-tagstyles"><?php echo $pageHeading; ?></h2>
    <?php endif; ?>
<?php
$pagination_list = array();
if ($this->params->def('show_pagination', 2) == 1
    || ($this->params->get('show_pagination') == 2
        && $this->pagination->get('pages.total') > 1))
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php
$leadingcount = 0;
?>

<?php if (!empty($this->lead_items)) : ?>
    <div class="data-control-id-2881 bd-grid-5">
      <div class="container-fluid">
        <div class="separated-grid row">
    
    <?php foreach ($this->lead_items as $item) : ?>
        <div class="separated-item-33 col-md-24 " itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-33">
        <?php
            $this->item = $item;
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
$introcount = count($this->intro_items);
$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
    <div class="data-control-id-2881 bd-grid-5">
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
    ?>
    <?php foreach ($this->intro_items as $key => $item) : ?>
        <?php
            $key = ($key - $leadingcount) + 1;
            $rowcount = (((int)$key - 1) % (int)$this->columns) + 1;
            $row = $counter / $this->columns;
            $counter++;
        ?>
        <?php
            
            $itemClass = 'separated-item-33 col-md-24 ';
            $mergedModes = '' . '0' . '' . '';
            if ('' === $mergedModes) {
                $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
                $itemClass = $itemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
            }
        ?>
        <div class="<?php echo $itemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="data-control-id-548675 bd-griditem-33">
        <?php
            $this->item = $item;
            echo $this->loadTemplate('item');
        ?>
            </div>
        </div>
    <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class="data-control-id-2919 bd-blogpagination-1">
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

$pageHeading = $view->pageHeading;
$this->articleTemplate = 'article_4';

ob_start();
?>
<div class="data-control-id-3353 bd-blog-5 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">

    <?php if ($pageHeading) : ?>
        <h2 class="data-control-id-3231 bd-container-21 bd-tagstyles"><?php echo $pageHeading; ?></h2>
    <?php endif; ?>
<?php
$pagination_list = array();
if ($this->params->def('show_pagination', 2) == 1
    || ($this->params->get('show_pagination') == 2
        && $this->pagination->get('pages.total') > 1))
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php
$leadingcount = 0;
?>

<?php if (!empty($this->lead_items)) : ?>
    <div class="data-control-id-3199 bd-grid-7">
      <div class="container-fluid">
        <div class="separated-grid row">
    
    <?php foreach ($this->lead_items as $item) : ?>
        <div class="separated-item-47 col-md-24 " itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-47">
        <?php
            $this->item = $item;
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
$introcount = count($this->intro_items);
$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
    <div class="data-control-id-3199 bd-grid-7">
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
    ?>
    <?php foreach ($this->intro_items as $key => $item) : ?>
        <?php
            $key = ($key - $leadingcount) + 1;
            $rowcount = (((int)$key - 1) % (int)$this->columns) + 1;
            $row = $counter / $this->columns;
            $counter++;
        ?>
        <?php
            
            $itemClass = 'separated-item-47 col-md-24 ';
            $mergedModes = '' . '0' . '' . '';
            if ('' === $mergedModes) {
                $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
                $itemClass = $itemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
            }
        ?>
        <div class="<?php echo $itemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="data-control-id-548906 bd-griditem-47">
        <?php
            $this->item = $item;
            echo $this->loadTemplate('item');
        ?>
            </div>
        </div>
    <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class="data-control-id-3237 bd-blogpagination-3">
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

$pageHeading = $view->pageHeading;
$this->articleTemplate = 'article_3';

ob_start();
?>
<div class="data-control-id-3194 bd-blog-3 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">

    <?php if ($pageHeading) : ?>
        <h2 class="data-control-id-3072 bd-container-18 bd-tagstyles"><?php echo $pageHeading; ?></h2>
    <?php endif; ?>
<?php
$pagination_list = array();
if ($this->params->def('show_pagination', 2) == 1
    || ($this->params->get('show_pagination') == 2
        && $this->pagination->get('pages.total') > 1))
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php
$leadingcount = 0;
?>

<?php if (!empty($this->lead_items)) : ?>
    <div class="data-control-id-3040 bd-grid-6">
      <div class="container-fluid">
        <div class="separated-grid row">
    
    <?php foreach ($this->lead_items as $item) : ?>
        <div class="separated-item-38 col-md-24 " itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-38">
        <?php
            $this->item = $item;
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
$introcount = count($this->intro_items);
$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
    <div class="data-control-id-3040 bd-grid-6">
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
    ?>
    <?php foreach ($this->intro_items as $key => $item) : ?>
        <?php
            $key = ($key - $leadingcount) + 1;
            $rowcount = (((int)$key - 1) % (int)$this->columns) + 1;
            $row = $counter / $this->columns;
            $counter++;
        ?>
        <?php
            
            $itemClass = 'separated-item-38 col-md-24 ';
            $mergedModes = '' . '0' . '' . '';
            if ('' === $mergedModes) {
                $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
                $itemClass = $itemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
            }
        ?>
        <div class="<?php echo $itemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="data-control-id-548795 bd-griditem-38">
        <?php
            $this->item = $item;
            echo $this->loadTemplate('item');
        ?>
            </div>
        </div>
    <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class="data-control-id-3078 bd-blogpagination-2">
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

$pageHeading = $view->pageHeading;
$this->articleTemplate = 'article_5';

ob_start();
?>
<div class="data-control-id-1522 bd-blog-7 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">

    <?php if ($pageHeading) : ?>
        <h2 class="data-control-id-1400 bd-container-24 bd-tagstyles"><?php echo $pageHeading; ?></h2>
    <?php endif; ?>
<?php
$pagination_list = array();
if ($this->params->def('show_pagination', 2) == 1
    || ($this->params->get('show_pagination') == 2
        && $this->pagination->get('pages.total') > 1))
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php
$leadingcount = 0;
?>

<?php if (!empty($this->lead_items)) : ?>
    <div class="data-control-id-1368 bd-grid-8">
      <div class="container-fluid">
        <div class="separated-grid row">
    
    <?php foreach ($this->lead_items as $item) : ?>
        <div class="separated-item-12 col-md-24 " itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-12">
        <?php
            $this->item = $item;
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
$introcount = count($this->intro_items);
$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
    <div class="data-control-id-1368 bd-grid-8">
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
    ?>
    <?php foreach ($this->intro_items as $key => $item) : ?>
        <?php
            $key = ($key - $leadingcount) + 1;
            $rowcount = (((int)$key - 1) % (int)$this->columns) + 1;
            $row = $counter / $this->columns;
            $counter++;
        ?>
        <?php
            
            $itemClass = 'separated-item-12 col-md-24 ';
            $mergedModes = '' . '0' . '' . '';
            if ('' === $mergedModes) {
                $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
                $itemClass = $itemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
            }
        ?>
        <div class="<?php echo $itemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="data-control-id-547188 bd-griditem-12">
        <?php
            $this->item = $item;
            echo $this->loadTemplate('item');
        ?>
            </div>
        </div>
    <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class="data-control-id-1406 bd-blogpagination-4">
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

$pageHeading = $view->pageHeading;
$this->articleTemplate = 'article_6';

ob_start();
?>
<div class="data-control-id-1773 bd-blog-8 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">

    <?php if ($pageHeading) : ?>
        <h2 class="data-control-id-1651 bd-container-27 bd-tagstyles"><?php echo $pageHeading; ?></h2>
    <?php endif; ?>
<?php
$pagination_list = array();
if ($this->params->def('show_pagination', 2) == 1
    || ($this->params->get('show_pagination') == 2
        && $this->pagination->get('pages.total') > 1))
{
    $GLOBALS['theme_settings']['active_paginator'] = 'specific';
    $pagination_list = $this->pagination->getPagesLinks();
}
?>

<?php
$leadingcount = 0;
?>

<?php if (!empty($this->lead_items)) : ?>
    <div class="data-control-id-1619 bd-grid-9">
      <div class="container-fluid">
        <div class="separated-grid row">
    
    <?php foreach ($this->lead_items as $item) : ?>
        <div class="separated-item-23 col-md-24 " itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="bd-griditem-23">
        <?php
            $this->item = $item;
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
$introcount = count($this->intro_items);
$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
    <div class="data-control-id-1619 bd-grid-9">
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
    ?>
    <?php foreach ($this->intro_items as $key => $item) : ?>
        <?php
            $key = ($key - $leadingcount) + 1;
            $rowcount = (((int)$key - 1) % (int)$this->columns) + 1;
            $row = $counter / $this->columns;
            $counter++;
        ?>
        <?php
            
            $itemClass = 'separated-item-23 col-md-24 ';
            $mergedModes = '' . '0' . '' . '';
            if ('' === $mergedModes) {
                $str = 'col-lg-1 col-md-1 col-sm-1 col-xs-1';
                $itemClass = $itemClass . preg_replace('/col-(lg|md|sm|xs)-\d+/', 'col-$1-' . round(24 / min(24, max(1, $this->columns))), $str);
            }
        ?>
        <div class="<?php echo $itemClass; ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
            <div class="data-control-id-547366 bd-griditem-23">
        <?php
            $this->item = $item;
            echo $this->loadTemplate('item');
        ?>
            </div>
        </div>
    <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (count($pagination_list) > 0) : ?>
    <div class="data-control-id-1657 bd-blogpagination-5">
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