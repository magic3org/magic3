<?php function article_5($data) {
    ob_start();
    ?>
        
        <article class="data-control-id-1444 bd-article-5">
            <h2 class="data-control-id-1285 bd-postheader-5">
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
	
		<div class="data-control-id-1309 bd-layoutcontainer-20">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class="data-control-id-1305 
 col-md-12">
    <div class="bd-layoutcolumn-45"><div class="bd-vertical-align-wrapper"><?php if (isset($data['date-icons']) && count($data['date-icons'])) : ?>
<div class="data-control-id-1293 bd-posticondate-10">
    <span class="data-control-id-1292 bd-icon-54">
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
	
		<div class="data-control-id-1307 
 col-md-12">
    <div class="bd-layoutcolumn-46"><div class="bd-vertical-align-wrapper"><?php if (isset($data['author-icon']) && strlen($data['author-icon'])) : ?>
<div class="data-control-id-1302 bd-posticonauthor-11">
    <span class="data-control-id-1301 bd-icon-56">
        <span><?php echo $data['author-icon']; ?></span>
    </span>
</div>
<?php endif; ?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class="data-control-id-1342 bd-postcontent-5 bd-tagstyles">
    <div class="bd-container-inner">
        <?php if (isset($data['content']) && strlen($data['content'])) : ?>
            <?php
                $content = funcPostprocessPostContent($data['content']);
                echo funcContentRoutesCorrector($content);
            ?>
        <?php endif; ?>
    </div>
</div>
        </article>
        <div class="bd-container-inner"><?php if (isset($data['pager'])) : ?>
<div class="data-control-id-1413 bd-pager-5">
    <ul class="data-control-id-1412 bd-pagination pager">
        <?php if (preg_match('/<li[^>]*previous[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $prevMatches)) : ?>
        <li class="data-control-id-1411 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($prevMatches[1]); ?></li>
        <?php endif; ?>
        <?php if (preg_match('/<li[^>]*next[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $nextMatches)) : ?>
        <li class="data-control-id-1411 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($nextMatches[1]); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?></div>
        
<?php
    return ob_get_clean();
}