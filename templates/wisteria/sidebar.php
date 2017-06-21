<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Wisteria
 */

if ( ! wisteria_has_sidebar() ) {
	return;
}
?>
<div id="site-sidebar" class="sidebar-area <?php wisteria_layout_class( 'sidebar' ); ?>">
	<div id="secondary" class="sidebar widget-area">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div><!-- .sidebar -->
</div><!-- .col-* columns of main sidebar -->
