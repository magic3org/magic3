<?php function article_3($data) {
    ob_start();
    ?>
        
        <article class=" bd-article-3">
            <h2 class=" bd-postheader-3">
    <div class="bd-container-inner">
    <?php if (isset($data['header-text']) && strlen($data['header-text'])) : ?>
        <?php if (isset($data['header-link']) && strlen($data['header-link'])) : ?>
            <a <?php echo funcBuildRoute($data['header-link'], 'href'); ?>>
                <?php echo $data['header-text'];?>
            </a>
        <?php else: ?>
            <?php echo $data['header-text']; ?>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</h2>
	
		<div class=" bd-layoutcontainer-16">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class=" 
 col-md-10">
    <div class="bd-layoutcolumn-34"><div class="bd-vertical-align-wrapper"><?php if (isset($data['date-icons']) && count($data['date-icons'])) : ?>
<div class=" bd-posticondate-4">
    <span class=" bd-icon-41">
        <span>
        <?php
        $count = count($data['date-icons']);
        foreach ($data['date-icons'] as $key => $icon) {
            echo $icon;
            if ($key !== $count - 1) echo ' | ';
        }
        ?>
        </span>
    </span>
</div>
<?php endif; ?></div></div>
</div>
	
		<div class=" 
 col-md-8">
    <div class="bd-layoutcolumn-35"><div class="bd-vertical-align-wrapper"><?php if (isset($data['author-icon']) && strlen($data['author-icon'])) : ?>
<div class=" bd-posticonauthor-5">
    <span class=" bd-icon-43">
        <span><?php echo $data['author-icon']; ?></span>
    </span>
</div>
<?php endif; ?></div></div>
</div>
	
		<div class=" 
 col-md-2">
    <div class="bd-layoutcolumn-36"><div class="bd-vertical-align-wrapper"><?php if (isset($data['print-icon'])) : ?>
<div class=" bd-posticonprint-6 print-action">
    <?php preg_match('/<a([^>]+)>[\s\S]+<\/a>/', $data['print-icon']['content'], $matches); ?>
    <a<?php echo $matches[1];?>>
    <?php if ($data['print-icon']['showIcon']) : ?>
    <span class=" bd-icon-45"><span></span></span>
    <?php else: ?>
    <span><?php echo JText::_('JGLOBAL_PRINT'); ?></span>
    <?php endif; ?>
    </a>
</div>
<?php endif; ?></div></div>
</div>
	
		<div class=" 
 col-md-2">
    <div class="bd-layoutcolumn-37"><div class="bd-vertical-align-wrapper"><?php if (isset($data['email-icon'])) : ?>
<div class=" bd-posticonemail-7">
    <?php preg_match('/<a([^>]+)>[\s\S]+<\/a>/', $data['email-icon']['content'], $matches); ?>
    <a<?php echo $matches[1];?>>
    <?php if ($data['email-icon']['showIcon']) : ?>
    <span class=" bd-icon-47"><span></span></span>
    <?php else: ?>
    <span><?php echo JText::_('JGLOBAL_EMAIL'); ?></span>
    <?php endif; ?>
    </a>
</div>
<?php endif; ?></div></div>
</div>
	
		<div class=" 
 col-md-2">
    <div class="bd-layoutcolumn-38"><div class="bd-vertical-align-wrapper"><?php if (isset($data['edit-icon'])) : ?>
<div class=" bd-posticonedit-8">
    <?php preg_match('/<a([^>]+)>[\s\S]+<\/a>/', $data['edit-icon']['content'], $matches); ?>
    <a<?php echo $matches[1];?>>
    <?php if ($data['edit-icon']['showIcon']) : ?>
    <span class=" bd-icon-49"><span></span></span>
    <?php else: ?>
    <span><?php echo JText::_('JGLOBAL_EDIT'); ?></span>
    <?php endif; ?>
    </a>
</div>
<?php endif; ?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-postcontent-3 bd-tagstyles">
    <div class="bd-container-inner">
        <?php if (isset($data['content']) && strlen($data['content'])) : ?>
            <?php
                $content = funcPostprocessPostContent($data['content']);
                echo funcContentRoutesCorrector($content);
            ?>
        <?php endif; ?>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-17">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class=" 
 col-md-8">
    <div class="bd-layoutcolumn-40"><div class="bd-vertical-align-wrapper"><?php if (isset($data['category-icon']) && strlen($data['category-icon'])) : ?>
<div class=" bd-posticoncategory-9">
    <span class=" bd-icon-50">
        <span><?php echo $data['category-icon']; ?></span>
    </span>
</div>
<?php endif; ?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
        </article>
        <div class="bd-container-inner"><?php if (isset($data['pager'])) : ?>
<div class=" bd-pager-3">
    <ul class=" bd-pagination-2 pager">
        <?php if (preg_match('/<li[^>]*previous[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $prevMatches)) : ?>
        <li class=" bd-paginationitem-2"><?php echo funcContentRoutesCorrector($prevMatches[1]); ?></li>
        <?php endif; ?>
        <?php if (preg_match('/<li[^>]*next[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $nextMatches)) : ?>
        <li class=" bd-paginationitem-2"><?php echo funcContentRoutesCorrector($nextMatches[1]); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?></div>
        
<?php
    return ob_get_clean();
}