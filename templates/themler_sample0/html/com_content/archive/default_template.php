<!--COMPONENT common -->

<?php
$component = new DesignerContent($this, $this->params);

$pageHeading = $component->pageHeading;
ob_start();

?>
<div class=" bd-blog <?php echo $this->pageclass_sfx; ?>">
    
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-15 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <?php
        $GLOBALS['theme_settings']['active_paginator'] = 'specific';
        $pagination_list = $this->pagination->getPagesLinks();
    ?>
    
    <div class="bd-container-inner">
        <form id="adminForm" action="<?php echo JRoute::_('index.php')?>" method="post">
            <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <?php if ($this->params->get('filter_field') != 'hide') : ?>
                <label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
                <?php endif; ?>

                <?php echo $this->form->monthField; ?>
                <?php echo $this->form->yearField; ?>
                <?php echo $this->form->limitField; ?>
                <button type="submit" class="button"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
            </div>
            <input type="hidden" name="view" value="archive" />
            <input type="hidden" name="option" value="com_content" />
            <input type="hidden" name="limitstart" value="0" />
            </fieldset>
            <div class=" bd-grid-5">
              <div class="container-fluid">
                <div class="separated-grid row">
            
            <?php foreach ($this->items as $i => $item) : ?>
                    <div class="separated-item-30 col-md-24 ">
                        <div class="bd-griditem-30">
                        <?php
                            $this->item = $item;
                            $this->articleTemplate = 'article_2';
                            echo $this->loadTemplate('item');
                        ?>
                        </div>
                    </div>
            <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
$component = new DesignerContent($this, $this->params);

$pageHeading = $component->pageHeading;
ob_start();

?>
<div class=" bd-blog-5 <?php echo $this->pageclass_sfx; ?>">
    
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-21 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <?php
        $GLOBALS['theme_settings']['active_paginator'] = 'specific';
        $pagination_list = $this->pagination->getPagesLinks();
    ?>
    
    <div class="bd-container-inner">
        <form id="adminForm" action="<?php echo JRoute::_('index.php')?>" method="post">
            <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <?php if ($this->params->get('filter_field') != 'hide') : ?>
                <label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
                <?php endif; ?>

                <?php echo $this->form->monthField; ?>
                <?php echo $this->form->yearField; ?>
                <?php echo $this->form->limitField; ?>
                <button type="submit" class="button"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
            </div>
            <input type="hidden" name="view" value="archive" />
            <input type="hidden" name="option" value="com_content" />
            <input type="hidden" name="limitstart" value="0" />
            </fieldset>
            <div class=" bd-grid-7">
              <div class="container-fluid">
                <div class="separated-grid row">
            
            <?php foreach ($this->items as $i => $item) : ?>
                    <div class="separated-item-46 col-md-24 ">
                        <div class="bd-griditem-46">
                        <?php
                            $this->item = $item;
                            $this->articleTemplate = 'article_4';
                            echo $this->loadTemplate('item');
                        ?>
                        </div>
                    </div>
            <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
$component = new DesignerContent($this, $this->params);

$pageHeading = $component->pageHeading;
ob_start();

?>
<div class=" bd-blog-3 <?php echo $this->pageclass_sfx; ?>">
    
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-18 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <?php
        $GLOBALS['theme_settings']['active_paginator'] = 'specific';
        $pagination_list = $this->pagination->getPagesLinks();
    ?>
    
    <div class="bd-container-inner">
        <form id="adminForm" action="<?php echo JRoute::_('index.php')?>" method="post">
            <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <?php if ($this->params->get('filter_field') != 'hide') : ?>
                <label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
                <?php endif; ?>

                <?php echo $this->form->monthField; ?>
                <?php echo $this->form->yearField; ?>
                <?php echo $this->form->limitField; ?>
                <button type="submit" class="button"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
            </div>
            <input type="hidden" name="view" value="archive" />
            <input type="hidden" name="option" value="com_content" />
            <input type="hidden" name="limitstart" value="0" />
            </fieldset>
            <div class=" bd-grid-6">
              <div class="container-fluid">
                <div class="separated-grid row">
            
            <?php foreach ($this->items as $i => $item) : ?>
                    <div class="separated-item-38 col-md-24 ">
                        <div class="bd-griditem-38">
                        <?php
                            $this->item = $item;
                            $this->articleTemplate = 'article_3';
                            echo $this->loadTemplate('item');
                        ?>
                        </div>
                    </div>
            <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
$component = new DesignerContent($this, $this->params);

$pageHeading = $component->pageHeading;
ob_start();

?>
<div class=" bd-blog-7 <?php echo $this->pageclass_sfx; ?>">
    
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-24 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <?php
        $GLOBALS['theme_settings']['active_paginator'] = 'specific';
        $pagination_list = $this->pagination->getPagesLinks();
    ?>
    
    <div class="bd-container-inner">
        <form id="adminForm" action="<?php echo JRoute::_('index.php')?>" method="post">
            <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <?php if ($this->params->get('filter_field') != 'hide') : ?>
                <label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
                <?php endif; ?>

                <?php echo $this->form->monthField; ?>
                <?php echo $this->form->yearField; ?>
                <?php echo $this->form->limitField; ?>
                <button type="submit" class="button"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
            </div>
            <input type="hidden" name="view" value="archive" />
            <input type="hidden" name="option" value="com_content" />
            <input type="hidden" name="limitstart" value="0" />
            </fieldset>
            <div class=" bd-grid-8">
              <div class="container-fluid">
                <div class="separated-grid row">
            
            <?php foreach ($this->items as $i => $item) : ?>
                    <div class="separated-item-12 col-md-24 ">
                        <div class="bd-griditem-12">
                        <?php
                            $this->item = $item;
                            $this->articleTemplate = 'article_5';
                            echo $this->loadTemplate('item');
                        ?>
                        </div>
                    </div>
            <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
$component = new DesignerContent($this, $this->params);

$pageHeading = $component->pageHeading;
ob_start();

?>
<div class=" bd-blog-8 <?php echo $this->pageclass_sfx; ?>">
    
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-27 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <?php
        $GLOBALS['theme_settings']['active_paginator'] = 'specific';
        $pagination_list = $this->pagination->getPagesLinks();
    ?>
    
    <div class="bd-container-inner">
        <form id="adminForm" action="<?php echo JRoute::_('index.php')?>" method="post">
            <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <?php if ($this->params->get('filter_field') != 'hide') : ?>
                <label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
                <?php endif; ?>

                <?php echo $this->form->monthField; ?>
                <?php echo $this->form->yearField; ?>
                <?php echo $this->form->limitField; ?>
                <button type="submit" class="button"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
            </div>
            <input type="hidden" name="view" value="archive" />
            <input type="hidden" name="option" value="com_content" />
            <input type="hidden" name="limitstart" value="0" />
            </fieldset>
            <div class=" bd-grid-9">
              <div class="container-fluid">
                <div class="separated-grid row">
            
            <?php foreach ($this->items as $i => $item) : ?>
                    <div class="separated-item-23 col-md-24 ">
                        <div class="bd-griditem-23">
                        <?php
                            $this->item = $item;
                            $this->articleTemplate = 'article_6';
                            echo $this->loadTemplate('item');
                        ?>
                        </div>
                    </div>
            <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
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