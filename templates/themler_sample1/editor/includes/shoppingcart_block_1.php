<?php
function shoppingcart_block_1($caption, $content, $classes = '', $id = '')
{
    $hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
    $hasContent = (null !== $content && strlen(trim($content)) > 0);
    $isPreview  = $GLOBALS['theme_settings']['is_preview'];
    if (!$hasCaption && !$hasContent)
        return '';
    if (!empty($id))
        $id = $isPreview ? (' data-block-id="' . $id . '"') : '';
    ob_start();
    ?>
    <div class="data-control-id-3568 bd-block-8 <?php echo $classes; ?>" <?php echo $id; ?>>
        <div class="bd-container-inner">
        <?php if ($hasCaption) : ?>
    
    <div class="data-control-id-3535 bd-container-55 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-imagestyles bd-custom-table">
        <h4><?php echo $caption; ?></h4>
    </div>
    
<?php endif; ?>
        <?php if ($hasContent) : ?>
    
    <div class="data-control-id-3567 bd-container-56 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-imagestyles bd-custom-table">
    <?php echo funcPostprocessBlockContent($content); ?>
    </div>
    
<?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}