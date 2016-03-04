<?php
function joomlaposition_block_14($caption, $content, $classes = '', $id = '', $extraClass = '')
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
    <div class="data-control-id-808559 bd-block-4 <?php echo $classes; ?>" <?php echo $id; ?>>
<div class="bd-container-inner">
    <?php if ($hasCaption) : ?>
    
    <div class="data-control-id-808560 bd-blockheader bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-inputs bd-custom-bulletlist bd-custom-orderedlist bd-custom-table">
        <h4><?php echo $caption; ?></h4>
    </div>
    
<?php endif; ?>
    <?php if ($hasContent) : ?>
    
    <div class="bd-blockcontent bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-inputs bd-custom-bulletlist bd-custom-orderedlist bd-custom-table<?php echo $extraClass;?>">
        <?php echo funcPostprocessBlockContent($content); ?>
    </div>
    
<?php endif; ?>
</div>
</div>
    <?php
    return ob_get_clean();
}