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
	
		<div class="data-control-id-846380 bd-layoutbox-2 clearfix">
    <div class="bd-container-inner">
        <?php if (isset($data['author-icon']) && strlen($data['author-icon'])) : ?>
<div class="data-control-id-1038 bd-posticonauthor-3">
    <span class="data-control-id-1037 bd-icon-38">
        <span><?php echo $data['author-icon']; ?></span>
    </span>
</div>
<?php endif; ?>
	
		<?php if (isset($data['date-icons']) && count($data['date-icons'])) : ?>
<div class="data-control-id-825359 bd-posticondate-16">
    <span class="data-control-id-825358 bd-icon-10">
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
<?php endif; ?>
    </div>
</div>
	
		<div class="data-control-id-846504 bd-layoutbox-4 clearfix">
    <div class="bd-container-inner">
        <?php if (isset($data['data-image'])) : ?>
    <?php
    $image = $data['data-image'];
    $caption = $image['caption'];
    ?>
<div class="data-control-id-545466 bd-extendedpostimage-1">
    
    <?php if (isset($image['link']) && $image['link'] !== '') : ?>
    <a href="<?php echo $image['link']; ?>">
        <?php endif; ?>
        <img src="<?php echo $image['image']; ?>" alt="<?php echo $image['alt']; ?>" class="data-control-id-545465 bd-imagestyles-6"/>
        <?php if (isset($image['link']) && $image['link'] !== '') : ?>
    </a>
    <?php endif; ?>
    
    <?php if ($caption): ?>
    <div class="data-control-id-1083477 bd-container-50 bd-tagstyles ">
        <?php echo $caption; ?>
    </div>
    <?php endif; ?>
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
	
		<?php if (isset($data['readmore-link']) && isset($data['readmore-text']) ) : ?>
<a class="bd-postreadmore-1 bd-button data-control-id-1056428" href="<?php echo $data['readmore-link'] ?>" >
    <?php echo $data['readmore-text'] ?></a>
<?php endif; ?>
    </div>
</div>
	
		<div class="data-control-id-846641 bd-layoutbox-6 clearfix">
    <div class="bd-container-inner">
        <?php if (isset($data['tags-icon'])) : ?>
<div class="data-control-id-830525 bd-posticontags-19">
            <span class="data-control-id-830524 bd-icon-12"><span>
            <?php foreach($data['tags-icon'] as $key => $item) : ?>
            <a href="<?php echo $item['href'];?>">
                <?php echo $item['title']; ?>
            </a>
                <?php if($key !== count($data['tags-icon']) - 1) : ?>
                <?php echo ','; ?>
                <?php endif; ?>
                <?php endforeach; ?>
            </span></span>
</div>
<?php endif; ?>
    </div>
</div>
        </article>
        <div class="bd-container-inner"><?php if (isset($data['pager'])) : ?>
<div class="data-control-id-2926 bd-pager-2">
    <ul class="data-control-id-2925 bd-pagination pager">
        <?php if (preg_match('/<li[^>]*previous[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $prevMatches)) : ?>
        <li class="data-control-id-2924 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($prevMatches[1]); ?></li>
        <?php endif; ?>
        <?php if (preg_match('/<li[^>]*next[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $nextMatches)) : ?>
        <li class="data-control-id-2924 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($nextMatches[1]); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?></div>
        
<?php
    return ob_get_clean();
}