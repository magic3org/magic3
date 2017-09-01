<?php
/**
 * A class to create a dropdown for theme colors
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Class Shop_Isle_Pro_Palette
 */
class Shop_Isle_Pro_Palette extends WP_Customize_Control {

	 /**
	  * Render the content of the category dropdown
	  */
	public function render_content() {

		$values = $this->value();
		$json = json_decode( $values );

		$shop_isle_pro_pallete = array(
			array(
				'pallete_name' => 'p1',
				'color1' => '#2C3E50',
				'color2' => '#6DBCDB',
				'color3' => '#2C3E50',
				'color4' => '#FC4349',
				'color5' => '#FFFFFF',
			),
			array(
				'pallete_name' => 'p2',
				'color1' => '#F2385A',
				'color2' => '#31656B',
				'color3' => '#29474A',
				'color4' => '#4AD9D9',
				'color5' => '#FAFFF4',
			),
			array(
				'pallete_name' => 'p3',
				'color1' => '#DB9E36',
				'color2' => '#105B63',
				'color3' => '#105B63',
				'color4' => '#BD4932',
				'color5' => '#FFFFF5',
			),
		);
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		</label>
		<div class="shop_isle_pro_palette_selected">
			<div class="shop_isle_pro_palette_input">
				<?php
				if ( ! empty( $json ) ) {
					foreach ( $json as $color ) {
						echo '<span style="background-color:' . $color . '"></span>';
					}
				} else {
					esc_html_e( 'Default','shop-isle' );
				}
				?>
			</div>
			<div class="shop_isle_pro_dropdown">&#x25BC;</div>
		</div>
		<ul class="shop_isle_pro_palette_picker">
			<?php
				echo '<li class="shop_isle_pro_pallete_default">';
					 esc_html_e( 'Default','shop-isle' );
				echo '</li>';
			foreach ( $shop_isle_pro_pallete as $pallete ) {
				echo '<li class="' . esc_attr( $pallete['pallete_name'] ) . '">';
				echo '<span style="background-color:' . $pallete['color1'] . '"></span>';
				echo '<span style="background-color:' . $pallete['color2'] . '"></span>';
				echo '<span style="background-color:' . $pallete['color3'] . '"></span>';
				echo '<span style="background-color:' . $pallete['color4'] . '"></span>';
				echo '<span style="background-color:' . $pallete['color5'] . '"></span>';
				echo '</li>';
			}
			?>
		</ul>
		<input class='shop_isle_pro_palette_colector' type='hidden' value='' <?php esc_attr( $this->link() ); ?> />
		<?php
	}
}

?>
