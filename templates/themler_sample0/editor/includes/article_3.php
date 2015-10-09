<?php function article_3($data) {
    ob_start();
    ?>
        
        <article class="data-control-id-3116 bd-article-3">
            <h2 class="data-control-id-1101 bd-postheader-3">
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
	
		<div class="data-control-id-847019 bd-layoutbox-8 clearfix">
    <div class="bd-container-inner">
        <?php if (isset($data['date-icons']) && count($data['date-icons'])) : ?>
<div class="data-control-id-1109 bd-posticondate-4">
    <span class="data-control-id-1108 bd-icon-41">
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
	
		<?php if (isset($data['author-icon']) && strlen($data['author-icon'])) : ?>
<div class="data-control-id-1118 bd-posticonauthor-5">
    <span class="data-control-id-1117 bd-icon-43">
        <span><?php echo $data['author-icon']; ?></span>
    </span>
</div>
<?php endif; ?>
	
		<?php if (isset($data['print-icon'])) : ?>
<div class="data-control-id-1127 bd-posticonprint-6 print-action">
    <?php preg_match('/<a([^>]+)>[\s\S]+<\/a>/', $data['print-icon']['content'], $matches); ?>
    <a<?php echo $matches[1];?>>
    <?php if ($data['print-icon']['showIcon']) : ?>
    <span class="data-control-id-1126 bd-icon-45"><span></span></span>
    <?php else: ?>
    <span><?php echo JText::_('JGLOBAL_PRINT'); ?></span>
    <?php endif; ?>
    </a>
</div>
<?php endif; ?>
	
		<?php if (isset($data['email-icon'])) : ?>
<div class="data-control-id-1136 bd-posticonemail-7">
    <?php preg_match('/<a([^>]+)>[\s\S]+<\/a>/', $data['email-icon']['content'], $matches); ?>
    <a<?php echo $matches[1];?>>
    <?php if ($data['email-icon']['showIcon']) : ?>
    <span class="data-control-id-1135 bd-icon-47"><span></span></span>
    <?php else: ?>
    <span><?php echo JText::_('JGLOBAL_EMAIL'); ?></span>
    <?php endif; ?>
    </a>
</div>
<?php endif; ?>
	
		<?php if (isset($data['edit-icon'])) : ?>
<div class="data-control-id-1145 bd-posticonedit-8">
    <?php preg_match('/<a([^>]+)>[\s\S]+<\/a>/', $data['edit-icon']['content'], $matches); ?>
    <a<?php echo $matches[1];?>>
    <?php if ($data['edit-icon']['showIcon']) : ?>
    <span class="data-control-id-1144 bd-icon-49"><span></span></span>
    <?php else: ?>
    <span><?php echo JText::_('JGLOBAL_EDIT'); ?></span>
    <?php endif; ?>
    </a>
</div>
<?php endif; ?>
    </div>
</div>
	
		<div class="data-control-id-847222 bd-layoutbox-10 clearfix">
    <div class="bd-container-inner">
        <?php if (isset($data['data-image'])) : ?>
    <?php
    $image = $data['data-image'];
    $caption = $image['caption'];
    ?>
<div class="data-control-id-830811 bd-extendedpostimage-3">
    
    <?php if (isset($image['link']) && $image['link'] !== '') : ?>
    <a href="<?php echo $image['link']; ?>">
        <?php endif; ?>
        <img src="<?php echo $image['image']; ?>" alt="<?php echo $image['alt']; ?>" class="data-control-id-830810 bd-imagestyles"/>
        <?php if (isset($image['link']) && $image['link'] !== '') : ?>
    </a>
    <?php endif; ?>
    
    <?php if ($caption): ?>
    <div class="data-control-id-1083555 bd-container-54 bd-tagstyles ">
        <?php echo $caption; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
	
		<div class="data-control-id-1193 bd-postcontent-3 bd-tagstyles">
    <div class="bd-container-inner">
        <?php if (isset($data['content']) && strlen($data['content'])) : ?>
            <?php
                $content = funcPostprocessPostContent($data['content']);
                echo funcContentRoutesCorrector($content);
            ?>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>
	
		<div class="data-control-id-847351 bd-layoutbox-12 clearfix">
    <div class="bd-container-inner">
        <?php if (isset($data['category-icon']) && strlen($data['category-icon'])) : ?>
<div class="data-control-id-1201 bd-posticoncategory-9">
    <span class="data-control-id-1200 bd-icon-50">
        <span><?php echo $data['category-icon']; ?></span>
    </span>
</div>
<?php endif; ?>
	
		<?php if (isset($data['tags-icon'])) : ?>
<div class="data-control-id-825390 bd-posticontags-18">
            <span class="data-control-id-825389 bd-icon-15"><span>
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
<div class="data-control-id-3085 bd-pager-3">
    <ul class="data-control-id-3084 bd-pagination pager">
        <?php if (preg_match('/<li[^>]*previous[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $prevMatches)) : ?>
        <li class="data-control-id-3083 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($prevMatches[1]); ?></li>
        <?php endif; ?>
        <?php if (preg_match('/<li[^>]*next[^>]*>([\S\s]*?)<\/li>/', $data['pager'], $nextMatches)) : ?>
        <li class="data-control-id-3083 bd-paginationitem-1"><?php echo funcContentRoutesCorrector($nextMatches[1]); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?></div>
        
<?php
    return ob_get_clean();
}