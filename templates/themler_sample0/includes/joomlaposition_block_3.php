<?php
function joomlaposition_block_3($caption, $content, $classes = '', $id = '')
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
    <div class=" bd-block-4 <?php echo $classes; ?>" <?php echo $id; ?>>
<div class="bd-container-inner">
    <?php if ($hasCaption) : ?>
    
    <div class=" bd-container-13 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-effectscollection bd-custom-imagestyles bd-custom-bootstrapinput bd-custom-bulletlist bd-custom-orderedlist bd-custom-table">
        <h4><?php echo $caption; ?></h4>
    </div>
    
<?php endif; ?>
    <?php if ($hasContent) : ?>
    
    <div class=" bd-container-41 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-effectscollection bd-custom-imagestyles bd-custom-bootstrapinput bd-custom-bulletlist bd-custom-orderedlist bd-custom-table">
        <?php echo funcPostprocessBlockContent($content); ?>
    </div>
    
<?php endif; ?>
</div>
</div>
    <?php
    return ob_get_clean();
}