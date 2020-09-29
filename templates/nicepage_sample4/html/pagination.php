<?php
defined('_JEXEC') or die;

function pagination_list_render($list) {

    if (!isset($GLOBALS['theme_pagination_styles'])) {
        $GLOBALS['theme_pagination_styles'] = array(
            'ul' => 'class="default"',
            'li' => '',
            'link' => ''
        );
    }

    $ul = str_replace('style1', 'style2', $GLOBALS['theme_pagination_styles']['ul']);

    $li = $GLOBALS['theme_pagination_styles']['li'];
    $li_active = str_replace('class="', 'class="active ', $li);
    $li_start = str_replace('class="', 'class="start ', $li);
    $li_prev = str_replace('class="', 'class="prev ', $li);
    $li_next = str_replace('class="', 'class="next ', $li);
    $li_end = str_replace('class="', 'class="end ', $li);

    $link = $GLOBALS['theme_pagination_styles']['link'];

    ob_start();
    ?>
    <ul <?php echo $ul; ?>>
        <?php if ($list['start']['active']) : ?>
            <li <?php echo $li_start; ?>>
                <?php echo $list['start']['data']; ?>
            </li>
        <?php endif; ?>
        <?php if ($list['previous']['active']) : ?>
            <li <?php echo $li_prev; ?>>
                <?php echo $list['previous']['data']; ?>
            </li>
        <?php endif; ?>
        <?php foreach ($list['pages'] as $page) : ?>
            <?php echo '<li ' . ($page['active'] ? $li : $li_active) . '>' . $page['data'] . '</li>'; ?>
        <?php endforeach; ?>
        <?php if ($list['next']['active']) : ?>
            <li <?php echo $li_next; ?>><?php echo $list['next']['data']; ?></li>
        <?php endif; ?>
        <?php if ($list['end']['active']) : ?>
            <li <?php echo $li_end; ?>><?php echo $list['end']['data']; ?></li>
        <?php endif; ?>
    </ul>
    <?php
    $html = ob_get_clean();

    $html = str_replace('class="pagenav"', '', $html);
    $html = str_replace('class="hasTooltip pagenav"', '', $html);
    $html = str_replace('<a ', '<a ' . $link . ' ', $html);
    $html = str_replace('<span ', '<span ' . $link . ' ', $html);
    $html = str_replace(
        array('>' . JText::_('JLIB_HTML_START'), '>' . JText::_('JPREV'), '>' . JText::_('JNEXT'), '>' . JText::_('JLIB_HTML_END')),
        array('>' . '&#12298', '>' . '&#12296', '>' . '&#12297', '>' . '&#12299'),
        $html
    );
    return $html;
}