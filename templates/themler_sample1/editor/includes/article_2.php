<?php function article_2($data) {
    ob_start();
    ?>
        
        <article class="data-control-id-2957 bd-article-2">
            <h2 class="data-control-id-1021 bd-postheader-2">
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
	
		<?php if (isset($data['author-icon']) && strlen($data['author-icon'])) : ?>
<div class="data-control-id-1038 bd-posticonauthor-3">
    <span class="data-control-id-1037 bd-icon-38">
        <span><?php echo $data['author-icon']; ?></span>
    </span>
</div>
<?php endif; ?>
	
		<div class="data-control-id-1078 bd-postcontent-2 bd-tagstyles bd-custom-blockquotes bd-custom-bulletlist bd-custom-orderedlist bd-custom-table">
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
<div class="data-control-id-2926 bd-pager-2">
    <ul class="data-control-id-2925 bd-pagination-6 pager">
        <?php if (preg_match('/<li[^>]*previous[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $prevMatches)) : ?>
        <li class="data-control-id-2924 bd-paginationitem-6"><?php echo funcContentRoutesCorrector($prevMatches[1]); ?></li>
        <?php endif; ?>
        <?php if (preg_match('/<li[^>]*next[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $nextMatches)) : ?>
        <li class="data-control-id-2924 bd-paginationitem-6"><?php echo funcContentRoutesCorrector($nextMatches[1]); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?></div>
        
<?php
    return ob_get_clean();
}