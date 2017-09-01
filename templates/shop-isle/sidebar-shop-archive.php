<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package shop-isle
 */

if ( ! is_active_sidebar( 'shop-isle-sidebar-shop-archive' ) ) {
	return;
}
?>

<div id="secondary" class="widget-area" role="complementary">
	<?php dynamic_sidebar( 'shop-isle-sidebar-shop-archive' ); ?>
</div><!-- #secondary -->
