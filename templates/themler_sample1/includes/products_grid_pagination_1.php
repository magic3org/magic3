<?php
function products_grid_pagination_1($object) {
?>
    <?php if ($object->vmPagination->get('pages.total') > 1) : ?>
        <div class=" bd-productsgridpagination-1">
            <?php
                $GLOBALS['theme_settings']['active_paginator'] = 'grid_1';
                echo $object->vmPagination->getPagesLinks();
            ?>
        </div>
    <?php endif; ?>
<?php
}