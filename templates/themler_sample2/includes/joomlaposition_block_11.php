<?php
function joomlaposition_block_11($caption, $content, $classes = '', $id = '', $extraClass = '')
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
    <div class=" bd-block <?php echo $classes; ?>" <?php echo $id; ?>>
<div class="bd-container-inner">
    <?php if ($hasCaption) : ?>
    
    <div class=" bd-blockheader bd-tagstyles">
        <h4><?php echo $caption; ?></h4>
    </div>
    
<?php endif; ?>
    <?php if ($hasContent) : ?>
    
    <div class="bd-blockcontent bd-tagstyles<?php echo $extraClass;?>">
        <?php echo funcPostprocessBlockContent($content); ?>
    </div>
    
<?php endif; ?>
</div>
</div>
    <?php
    return ob_get_clean();
}