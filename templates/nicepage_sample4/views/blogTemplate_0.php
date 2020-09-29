<?php ob_start(); ?>
<section class="u-clearfix u-section-1" id="sec-cf41">
  <div class="u-clearfix u-sheet u-sheet-1">
    <?php if ($image0) : ?><img <?php $link = $titleLink0 ? $titleLink0 : $readmoreLink0; if($link): ?>data-post-url="<?php echo $link; ?>"<?php endif; ?> src="<?php echo $image0; ?>" alt="" class="u-blog-control u-expanded-width u-image u-image-default u-image-1" data-image-width="1080" data-image-height="1080"><?php else: ?><div class="hidden-image"></div><?php endif; ?>
    <?php if ($title0): ?><h2 class="u-blog-control u-expanded-width u-text u-text-1">
      <?php if ($titleLink0): ?><a class="u-post-header-link" href="<?php echo $titleLink0; ?>"><?php endif; ?><?php echo $title0; ?><?php if ($titleLink0): ?></a><?php endif; ?>
    </h2><?php endif; ?>
    <?php if (count($metadata0) > 0): ?>
<div class="u-metadata u-metadata-1">
      <?php if (isset($metadata0['date'])): ?><span class="u-meta-date u-meta-icon"><?php echo $metadata0['date']; ?></span><?php endif; ?>
      <?php if (isset($metadata0['category'])): ?><span class="u-meta-category u-meta-icon"><?php echo $metadata0['category']; ?></span><?php endif; ?>
      
    </div>
<?php endif; ?>
    <div class="u-blog-control u-expanded-width u-post-content u-text u-text-2"><?php echo $content0; ?></div>
    <?php if ($readmoreLink0): ?><a href="<?php echo $readmoreLink0; ?>" class="u-blog-control u-link u-link-1"><?php echo $readmore0; ?></a><?php endif; ?>
  </div>
</section><?php if (!($image0)) : ?><style>.u-section-1 .u-sheet { min-height: auto; }</style><?php endif; ?>
<?php $tmpl = ob_get_clean(); ?>
<?php  echo $tmpl; ?>