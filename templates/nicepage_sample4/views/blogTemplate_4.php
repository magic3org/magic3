<?php ob_start(); ?>
<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?><section class="u-clearfix u-section-5" id="sec-2800">
  <div class="u-clearfix u-sheet u-sheet-1">
    <?php 
$GLOBALS['theme_pagination_styles'] = array(
    'ul' => 'style="" class="responsive-style1 u-pagination u-unstyled u-pagination-1"',
    'li' => 'style="" class="u-nav-item u-pagination-item"',
    'link' => 'style="padding: 16px 28px;" class="u-button-style u-nav-link"'
);
?><?php if (property_exists($this, 'pagination')) { echo $this->pagination->getPagesLinks();  }  ?>
  </div>
</section><?php endif; ?>
<?php $tmpl = ob_get_clean(); ?>
<?php  echo $tmpl; ?>