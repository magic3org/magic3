<?php
/**
 * Front page Video Section
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

$shop_isle_video_hide = get_theme_mod( 'shop_isle_video_hide', false );
if ( ! empty( $shop_isle_video_hide ) && (bool) $shop_isle_video_hide === true ) {
	return;
}
$shop_isle_yt_link      = get_theme_mod( 'shop_isle_yt_link' );
$shop_isle_yt_thumbnail = get_theme_mod( 'shop_isle_yt_thumbnail' );

if ( empty( $shop_isle_yt_thumbnail ) ) {
	$shop_isle_do_video_thumbnail = preg_match( '/\/\/(www\.)?(youtu|youtube)\.(com|be)\/(watch|embed)?\/?(\?v=)?([a-zA-Z0-9\-\_]+)/', $shop_isle_yt_link, $shop_isle_youtube_matches );
	$shop_isle_youtube_id         = ! empty( $shop_isle_youtube_matches ) ? $shop_isle_youtube_matches[6] : '';
	$shop_isle_yt_thumbnail       = 'https://img.youtube.com/vi/' . $shop_isle_youtube_id . '/maxresdefault.jpg';
}

if ( isset( $shop_isle_video_hide ) && $shop_isle_video_hide != 1 && ! empty( $shop_isle_yt_link ) ) :
	echo '<section class="module module-video bg-dark-30">';
elseif ( ! empty( $shop_isle_yt_link ) && is_customize_preview() ) :
	echo '<section class="module module-video bg-dark-30 shop_isle_hidden_if_not_customizer">';
endif;

if ( ( isset( $shop_isle_video_hide ) && $shop_isle_video_hide != 1 && ! empty( $shop_isle_yt_link ) ) || ( ! empty( $shop_isle_yt_link ) && is_customize_preview() ) ) :

	echo '<div class="module-video-thumbnail"' . ( ! empty( $shop_isle_yt_thumbnail ) ? ' style="background-image: url(' . $shop_isle_yt_thumbnail . ')' : '' ) . '"></div>';


	$shop_isle_video_title = get_theme_mod( 'shop_isle_video_title' );

	echo '<div>';

	if ( ! empty( $shop_isle_video_title ) ) {

		echo '<div class="container">';
			echo '<div class="row">';
				echo '<div class="col-sm-12">';
					echo '<h2 class="module-title font-alt mb-0 video-title">' . $shop_isle_video_title . '</h2>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	} elseif ( is_customize_preview() ) {
		echo '<div class="container">';
			echo '<div class="row">';
				echo '<div class="col-sm-12">';
					echo '<h2 class="module-title font-alt mb-0 video-title shop_isle_hidden_if_not_customizer"></h2>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	}

	?>
	<!-- Youtube player start-->
	<div class="video-player"
		 data-property="{videoURL:'<?php echo $shop_isle_yt_link; ?>', containment:'.module-video', startAt:0, mute:true, autoPlay:true, loop:true, opacity:1, showControls:false, showYTLogo:false, vol:25}"></div>
	<!-- Youtube player end -->
	<?php
	echo '</div>';
endif;
echo '</section>';


?>
