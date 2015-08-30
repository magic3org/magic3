<?php

function pagination_list_render_1($list)
{
    // Initialise variables.
    $lang = JFactory::getLanguage();
?>
    <ul class=" bd-pagination-4 pagination">
    <?php if ($list['start']['active']) : ?>
        <li class=" bd-paginationitem-4">
        <?php echo preg_replace("/class=(\"|\').*?(\"|\')/", "", $list['start']['data']); ?>
        </li>
    <?php endif; ?>
    <?php if ($list['previous']['active']) : ?>
        <li class=" bd-paginationitem-4">
        <?php echo preg_replace("/class=(\"|\').*?(\"|\')/", "", $list['previous']['data']); ?>
        </li>
    <?php endif; ?>

    <?php foreach($list['pages'] as $page) : ?>
        <?php if (!$page['active']) : ?>
            <li class="active  bd-paginationitem-4">
        <?php else : ?>
            <li class=" bd-paginationitem-4">
        <?php endif; ?>
        <?php echo preg_replace("/class=(\"|\').*?(\"|\')/", "", $page['data']); ?>
        </li>
    <?php endforeach; ?>

    <?php if ($list['next']['active']) : ?>
        <li class=" bd-paginationitem-4">
        <?php echo preg_replace("/class=(\"|\').*?(\"|\')/", "", $list['next']['data']); ?>
        </li>
    <?php endif; ?>
    <?php if ($list['end']['active']) : ?>
        <li class=" bd-paginationitem-4">
        <?php echo preg_replace("/class=(\"|\').*?(\"|\')/", "", $list['end']['data']); ?>
        </li>
    <?php endif; ?>
</ul>
<?php
}