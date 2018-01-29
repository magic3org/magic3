<?php
$params = array();
if ($GLOBALS['theme_settings']['is_preview'] && ($this->item->params->get('show_intro', 1) == '1' || $this->item->fulltext == '')) {
    $params['postcontent_editor_id'] = 'article-' . $this->item->id;
    $params['article_id'] = 'article-' . $this->item->id;
}
?>

<!--COMPONENT common -->
<?php ob_start(); ?>

<div class=" bd-blog <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Article">
    
    <?php $pageHeading = strlen($article->pageHeading) ? $component->pageHeading($article->pageHeading) : ''; ?>
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-15 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <div class=" bd-grid-5">
      <div class="container-fluid">
        <div class="separated-grid row">
            <div class="separated-item-30 col-md-24 ">
                
                <div class="bd-griditem-30">
    <?php
    if (strlen($article->title)) {
        $params['header-text'] = $this->escape($article->title);
        if (strlen($article->titleLink))
            $params['header-link'] = $article->titleLink;
    }

    // Change the order of ""if"" statements to change the order of article metadata header items.
    if (strlen($article->created)) {
        $params['date-icons'][] = $article->createdDateInfo($article->created);
    }
    if (strlen($article->modified)) {
        $params['date-icons'][] = $article->modifiedDateInfo($article->modified);
    }
    if (strlen($article->published)) {
        $params['date-icons'][] = $article->publishedDateInfo($article->published);
    }

    if (strlen($article->author)) {
        $params['author-icon'] = $article->authorInfo($article->author, $article->authorLink);
    }

    if ($article->printIconVisible) {
        $params['print-icon'] = $article->printIconInfo();
    }
    if ($article->emailIconVisible) {
        $params['email-icon']= $article->emailIconInfo();
    }
    if ($article->editIconVisible) {
        $params['edit-icon'] = $article->editIconInfo();
    }
    if (strlen($article->hits)) {
        $params['hits-icons'] = $article->hitsInfo($article->hits);
    }

    // Build article content
    $content = '';
    if ('above full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    if (!$article->introVisible)
        $content .= $article->event('afterDisplayTitle');
    $content .= $article->event('beforeDisplayContent');
    if (strlen($article->toc))
        $content .= $article->toc($article->toc);
    if (strlen($article->text)) {
        if (strlen($article->images['fulltext']['image']))
            $params['data-image'] = $article->images['fulltext'];
        if ('above text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        $content .= $article->text($article->text);
        if ('below text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        if ($article->showLinks)
            $content .= $this->loadTemplate('links');
    }
    if ($article->introVisible)
        $content .= $article->intro($article->intro);
    if (strlen($article->readmore)) {
        $params['readmore-text'] = $article->readmore;
        $params['readmore-link'] = $article->readmoreLink;
    }
    if ('below full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    $content .= $article->event('afterDisplayContent');
    $params['content'] = DesignerShortcodes::process($content);

    // Change the order of ""if"" statements to change the order of article metadata footer items.
    // Build tags
    if (count(($article->tags)) > 0)
        $params['tags-icon'] = $article->tags;
    if (strlen($article->category)) {
        $params['category-icon'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
    }
    echo renderTemplateFromIncludes('article_2', array($params));
    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo ob_get_clean();
?>
<!--COMPONENT common /-->
<?php
$params = array();
if ($GLOBALS['theme_settings']['is_preview'] && ($this->item->params->get('show_intro', 1) == '1' || $this->item->fulltext == '')) {
    $params['postcontent_editor_id'] = 'article-' . $this->item->id;
    $params['article_id'] = 'article-' . $this->item->id;
}
?>

<!--COMPONENT blog_5 -->
<?php ob_start(); ?>

<div class=" bd-blog-5 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Article">
    
    <?php $pageHeading = strlen($article->pageHeading) ? $component->pageHeading($article->pageHeading) : ''; ?>
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-21 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <div class=" bd-grid-7">
      <div class="container-fluid">
        <div class="separated-grid row">
            <div class="separated-item-46 col-md-24 ">
                
                <div class="bd-griditem-46">
    <?php
    if (strlen($article->title)) {
        $params['header-text'] = $this->escape($article->title);
        if (strlen($article->titleLink))
            $params['header-link'] = $article->titleLink;
    }

    // Change the order of ""if"" statements to change the order of article metadata header items.
    if (strlen($article->created)) {
        $params['date-icons'][] = $article->createdDateInfo($article->created);
    }
    if (strlen($article->modified)) {
        $params['date-icons'][] = $article->modifiedDateInfo($article->modified);
    }
    if (strlen($article->published)) {
        $params['date-icons'][] = $article->publishedDateInfo($article->published);
    }

    if (strlen($article->author)) {
        $params['author-icon'] = $article->authorInfo($article->author, $article->authorLink);
    }

    if ($article->printIconVisible) {
        $params['print-icon'] = $article->printIconInfo();
    }
    if ($article->emailIconVisible) {
        $params['email-icon']= $article->emailIconInfo();
    }
    if ($article->editIconVisible) {
        $params['edit-icon'] = $article->editIconInfo();
    }
    if (strlen($article->hits)) {
        $params['hits-icons'] = $article->hitsInfo($article->hits);
    }

    // Build article content
    $content = '';
    if ('above full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    if (!$article->introVisible)
        $content .= $article->event('afterDisplayTitle');
    $content .= $article->event('beforeDisplayContent');
    if (strlen($article->toc))
        $content .= $article->toc($article->toc);
    if (strlen($article->text)) {
        if (strlen($article->images['fulltext']['image']))
            $params['data-image'] = $article->images['fulltext'];
        if ('above text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        $content .= $article->text($article->text);
        if ('below text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        if ($article->showLinks)
            $content .= $this->loadTemplate('links');
    }
    if ($article->introVisible)
        $content .= $article->intro($article->intro);
    if (strlen($article->readmore)) {
        $params['readmore-text'] = $article->readmore;
        $params['readmore-link'] = $article->readmoreLink;
    }
    if ('below full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    $content .= $article->event('afterDisplayContent');
    $params['content'] = DesignerShortcodes::process($content);

    // Change the order of ""if"" statements to change the order of article metadata footer items.
    // Build tags
    if (count(($article->tags)) > 0)
        $params['tags-icon'] = $article->tags;
    if (strlen($article->category)) {
        $params['category-icon'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
    }
    echo renderTemplateFromIncludes('article_4', array($params));
    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_5 /-->
<?php
$params = array();
if ($GLOBALS['theme_settings']['is_preview'] && ($this->item->params->get('show_intro', 1) == '1' || $this->item->fulltext == '')) {
    $params['postcontent_editor_id'] = 'article-' . $this->item->id;
    $params['article_id'] = 'article-' . $this->item->id;
}
?>

<!--COMPONENT blog_3 -->
<?php ob_start(); ?>

<div class=" bd-blog-3 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Article">
    
    <div class=" bd-grid-6">
      <div class="container-fluid">
        <div class="separated-grid row">
            <div class="separated-item-38 col-md-24 ">
                
                <div class="bd-griditem-38">
    <?php
    if (strlen($article->title)) {
        $params['header-text'] = $this->escape($article->title);
        if (strlen($article->titleLink))
            $params['header-link'] = $article->titleLink;
    }

    // Change the order of ""if"" statements to change the order of article metadata header items.
    if (strlen($article->created)) {
        $params['date-icons'][] = $article->createdDateInfo($article->created);
    }
    if (strlen($article->modified)) {
        $params['date-icons'][] = $article->modifiedDateInfo($article->modified);
    }
    if (strlen($article->published)) {
        $params['date-icons'][] = $article->publishedDateInfo($article->published);
    }

    if (strlen($article->author)) {
        $params['author-icon'] = $article->authorInfo($article->author, $article->authorLink);
    }

    if ($article->printIconVisible) {
        $params['print-icon'] = $article->printIconInfo();
    }
    if ($article->emailIconVisible) {
        $params['email-icon']= $article->emailIconInfo();
    }
    if ($article->editIconVisible) {
        $params['edit-icon'] = $article->editIconInfo();
    }
    if (strlen($article->hits)) {
        $params['hits-icons'] = $article->hitsInfo($article->hits);
    }

    // Build article content
    $content = '';
    if ('above full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    if (!$article->introVisible)
        $content .= $article->event('afterDisplayTitle');
    $content .= $article->event('beforeDisplayContent');
    if (strlen($article->toc))
        $content .= $article->toc($article->toc);
    if (strlen($article->text)) {
        if (strlen($article->images['fulltext']['image']))
            $params['data-image'] = $article->images['fulltext'];
        if ('above text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        $content .= $article->text($article->text);
        if ('below text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        if ($article->showLinks)
            $content .= $this->loadTemplate('links');
    }
    if ($article->introVisible)
        $content .= $article->intro($article->intro);
    if (strlen($article->readmore)) {
        $params['readmore-text'] = $article->readmore;
        $params['readmore-link'] = $article->readmoreLink;
    }
    if ('below full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    $content .= $article->event('afterDisplayContent');
    $params['content'] = DesignerShortcodes::process($content);

    // Change the order of ""if"" statements to change the order of article metadata footer items.
    // Build tags
    if (count(($article->tags)) > 0)
        $params['tags-icon'] = $article->tags;
    if (strlen($article->category)) {
        $params['category-icon'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
    }
    echo renderTemplateFromIncludes('article_3', array($params));
    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_3 /-->
<?php
$params = array();
if ($GLOBALS['theme_settings']['is_preview'] && ($this->item->params->get('show_intro', 1) == '1' || $this->item->fulltext == '')) {
    $params['postcontent_editor_id'] = 'article-' . $this->item->id;
    $params['article_id'] = 'article-' . $this->item->id;
}
?>

<!--COMPONENT blog_7 -->
<?php ob_start(); ?>

<div class=" bd-blog-7 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Article">
    
    <?php $pageHeading = strlen($article->pageHeading) ? $component->pageHeading($article->pageHeading) : ''; ?>
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-24 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <div class=" bd-grid-8">
      <div class="container-fluid">
        <div class="separated-grid row">
            <div class="separated-item-12 col-md-24 ">
                
                <div class="bd-griditem-12">
    <?php
    if (strlen($article->title)) {
        $params['header-text'] = $this->escape($article->title);
        if (strlen($article->titleLink))
            $params['header-link'] = $article->titleLink;
    }

    // Change the order of ""if"" statements to change the order of article metadata header items.
    if (strlen($article->created)) {
        $params['date-icons'][] = $article->createdDateInfo($article->created);
    }
    if (strlen($article->modified)) {
        $params['date-icons'][] = $article->modifiedDateInfo($article->modified);
    }
    if (strlen($article->published)) {
        $params['date-icons'][] = $article->publishedDateInfo($article->published);
    }

    if (strlen($article->author)) {
        $params['author-icon'] = $article->authorInfo($article->author, $article->authorLink);
    }

    if ($article->printIconVisible) {
        $params['print-icon'] = $article->printIconInfo();
    }
    if ($article->emailIconVisible) {
        $params['email-icon']= $article->emailIconInfo();
    }
    if ($article->editIconVisible) {
        $params['edit-icon'] = $article->editIconInfo();
    }
    if (strlen($article->hits)) {
        $params['hits-icons'] = $article->hitsInfo($article->hits);
    }

    // Build article content
    $content = '';
    if ('above full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    if (!$article->introVisible)
        $content .= $article->event('afterDisplayTitle');
    $content .= $article->event('beforeDisplayContent');
    if (strlen($article->toc))
        $content .= $article->toc($article->toc);
    if (strlen($article->text)) {
        if (strlen($article->images['fulltext']['image']))
            $params['data-image'] = $article->images['fulltext'];
        if ('above text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        $content .= $article->text($article->text);
        if ('below text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        if ($article->showLinks)
            $content .= $this->loadTemplate('links');
    }
    if ($article->introVisible)
        $content .= $article->intro($article->intro);
    if (strlen($article->readmore)) {
        $params['readmore-text'] = $article->readmore;
        $params['readmore-link'] = $article->readmoreLink;
    }
    if ('below full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    $content .= $article->event('afterDisplayContent');
    $params['content'] = DesignerShortcodes::process($content);

    // Change the order of ""if"" statements to change the order of article metadata footer items.
    // Build tags
    if (count(($article->tags)) > 0)
        $params['tags-icon'] = $article->tags;
    if (strlen($article->category)) {
        $params['category-icon'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
    }
    echo renderTemplateFromIncludes('article_5', array($params));
    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_7 /-->
<?php
$params = array();
if ($GLOBALS['theme_settings']['is_preview'] && ($this->item->params->get('show_intro', 1) == '1' || $this->item->fulltext == '')) {
    $params['postcontent_editor_id'] = 'article-' . $this->item->id;
    $params['article_id'] = 'article-' . $this->item->id;
}
?>

<!--COMPONENT blog_8 -->
<?php ob_start(); ?>

<div class=" bd-blog-8 <?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Article">
    
    <?php $pageHeading = strlen($article->pageHeading) ? $component->pageHeading($article->pageHeading) : ''; ?>
        <?php if ($pageHeading) : ?>
            <h2 class=" bd-container-27 bd-tagstyles"><?php echo $pageHeading; ?></h2>
        <?php endif; ?>
    <div class=" bd-grid-9">
      <div class="container-fluid">
        <div class="separated-grid row">
            <div class="separated-item-23 col-md-24 ">
                
                <div class="bd-griditem-23">
    <?php
    if (strlen($article->title)) {
        $params['header-text'] = $this->escape($article->title);
        if (strlen($article->titleLink))
            $params['header-link'] = $article->titleLink;
    }

    // Change the order of ""if"" statements to change the order of article metadata header items.
    if (strlen($article->created)) {
        $params['date-icons'][] = $article->createdDateInfo($article->created);
    }
    if (strlen($article->modified)) {
        $params['date-icons'][] = $article->modifiedDateInfo($article->modified);
    }
    if (strlen($article->published)) {
        $params['date-icons'][] = $article->publishedDateInfo($article->published);
    }

    if (strlen($article->author)) {
        $params['author-icon'] = $article->authorInfo($article->author, $article->authorLink);
    }

    if ($article->printIconVisible) {
        $params['print-icon'] = $article->printIconInfo();
    }
    if ($article->emailIconVisible) {
        $params['email-icon']= $article->emailIconInfo();
    }
    if ($article->editIconVisible) {
        $params['edit-icon'] = $article->editIconInfo();
    }
    if (strlen($article->hits)) {
        $params['hits-icons'] = $article->hitsInfo($article->hits);
    }

    // Build article content
    $content = '';
    if ('above full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    if (!$article->introVisible)
        $content .= $article->event('afterDisplayTitle');
    $content .= $article->event('beforeDisplayContent');
    if (strlen($article->toc))
        $content .= $article->toc($article->toc);
    if (strlen($article->text)) {
        if (strlen($article->images['fulltext']['image']))
            $params['data-image'] = $article->images['fulltext'];
        if ('above text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        $content .= $article->text($article->text);
        if ('below text' === $article->paginationPosition) {
            $params['pager'] = $article->pagination();
        }
        if ($article->showLinks)
            $content .= $this->loadTemplate('links');
    }
    if ($article->introVisible)
        $content .= $article->intro($article->intro);
    if (strlen($article->readmore)) {
        $params['readmore-text'] = $article->readmore;
        $params['readmore-link'] = $article->readmoreLink;
    }
    if ('below full article' === $article->paginationPosition) {
        $params['pager'] = $article->pagination();
    }

    $content .= $article->event('afterDisplayContent');
    $params['content'] = DesignerShortcodes::process($content);

    // Change the order of ""if"" statements to change the order of article metadata footer items.
    // Build tags
    if (count(($article->tags)) > 0)
        $params['tags-icon'] = $article->tags;
    if (strlen($article->category)) {
        $params['category-icon'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
    }
    echo renderTemplateFromIncludes('article_6', array($params));
    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo ob_get_clean();
?>
<!--COMPONENT blog_8 /-->