<?php
/**
 * Theme info control for customizer.
 *
 * @package Shopisle
 */
if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}
/**
 * Class ShopIsle_Info
 */
class ShopIsle_Info extends WP_Customize_Control {
	/**
	 * The links for the control.
	 *
	 * @var links links to add to the control.
	 */
	public $links;
	/**
	 * Enqueue required scripts and styles.
	 */
	public function enqueue() {
		wp_enqueue_style( 'shopisle-theme-info-control', get_template_directory_uri() . '/assets/css/admin-style.css','1.0.0' );
	}
	/**
	 * The render function for the controler
	 */
	public function render_content() {
		?>


		<div class="shopisle-theme-info">
			<?php
			foreach ( $this->links as $item ) {
			?>
				<a href="<?php echo esc_url( $item['link'] ); ?>" target="_blank"><?php echo esc_html( $item['name'] ); ?></a>
				<hr/>
				<?php
			}
			?>
		</div>
		<?php
	}
}
