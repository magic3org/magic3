<?php function article_6($data) {
    ob_start();
    ?>
        
        <article class="data-control-id-1695 bd-article-6">
            <h2 class="data-control-id-1523 bd-postheader-6">
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
	
		<div class="data-control-id-1547 bd-layoutcontainer-22">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class="data-control-id-1543 
 col-md-12">
    <div class="bd-layoutcolumn-49"><div class="bd-vertical-align-wrapper"><?php if (isset($data['date-icons']) && count($data['date-icons'])) : ?>
<div class="data-control-id-1531 bd-posticondate-12">
    <span class="data-control-id-1530 bd-icon-59">
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
	
		<div class="data-control-id-1545 
 col-md-12">
    <div class="bd-layoutcolumn-50"><div class="bd-vertical-align-wrapper"><?php if (isset($data['author-icon']) && strlen($data['author-icon'])) : ?>
<div class="data-control-id-1540 bd-posticonauthor-13">
    <span class="data-control-id-1539 bd-icon-61">
        <span><?php echo $data['author-icon']; ?></span>
    </span>
</div>
<?php endif; ?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class="data-control-id-1580 bd-postcontent-6 bd-tagstyles">
    <div class="bd-container-inner">
        <?php if (isset($data['content']) && strlen($data['content'])) : ?>
            <?php
                $content = funcPostprocessPostContent($data['content']);
                echo funcContentRoutesCorrector($content);
            ?>
        <?php endif; ?>
    </div>
</div>
	
		<div class="data-control-id-1593 bd-layoutcontainer-23">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class="data-control-id-1591 
 col-md-8">
    <div class="bd-layoutcolumn-51"><div class="bd-vertical-align-wrapper"><?php if (isset($data['category-icon']) && strlen($data['category-icon'])) : ?>
<div class="data-control-id-1588 bd-posticoncategory-14">
    <span class="data-control-id-1587 bd-icon-62">
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
<div class="data-control-id-1664 bd-pager-6">
    <ul class="data-control-id-1663 bd-pagination pager">
        <?php if (preg_match('/<li[^>]*previous[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $prevMatches)) : ?>
        <li class="data-control-id-1662 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($prevMatches[1]); ?></li>
        <?php endif; ?>
        <?php if (preg_match('/<li[^>]*next[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $nextMatches)) : ?>
        <li class="data-control-id-1662 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($nextMatches[1]); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?></div>
        
<?php
    return ob_get_clean();
}