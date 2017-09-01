<?php
/**
 * ShopIsle setup functions
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

define( 'SHOP_ISLE_PHP_INCLUDE',  get_template_directory() . '/inc' );

/**
 * Assign the ShopIsle version to a var
 */

if ( ! defined( 'SI_VERSION' ) ) {
	define( 'SI_VERSION', '1.1.24' );
}

/**
 * Sets the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function shop_isle_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'shop_isle_content_width', 980 );
}
add_action( 'after_setup_theme', 'shop_isle_content_width', 0 );

/**
 * Define Allowed Files to be included.
 */
function shop_isle_filter_features( $array ) {
	return array_merge(
		$array, array(
			'/customizer/features/customizer-theme-info',
			'/flag-wporg',
			'/frontpage-manager',

			'/customizer/features/feature-colors-palette',
			'/customizer/features/feature-header-controls',
			'/customizer/features/feature-footer-controls',
			'/customizer/features/feature-advanced-controls',

			'/customizer/features/feature-frontpage-big-title-section-controls',
			'/customizer/features/feature-frontpage-slider-section-controls',
			'/customizer/features/feature-frontpage-banners-section-controls',
			'/customizer/features/feature-frontpage-products-section-controls',
			'/customizer/features/feature-frontpage-services-section-controls',
			'/customizer/features/feature-frontpage-ribbon-section-controls',
			'/customizer/features/feature-frontpage-video-section-controls',
			'/customizer/features/feature-frontpage-products-slider-section-controls',
			'/customizer/features/feature-frontpage-map-section-controls',
			'/customizer/features/feature-frontpage-categories-section-controls',
			'/customizer/features/feature-frontpage-shortcodes-section-controls',

			'/customizer/features/feature-blog-header-controls',
			'/customizer/features/feature-contact-controls',
			'/customizer/features/feature-about-us-controls',
			'/customizer/features/feature-404-controls',

			'/customizer/customize-pro/class-shopisle-customizer-upsell',
			'/customizer/customizer-upsell/class-shopisle-customizer-upsell',
			'/customizer/features/customizer-manager-pro',
			'/customizer/features/customizer-manager-lite',
			'/customizer/features/feature-slider-shortcode',
			'/feature-page-description-meta',

		)
	);
}
add_filter( 'shop_isle_filter_features', 'shop_isle_filter_features' );

/**
 * Include features files.
 */
function shop_isle_include_features() {
	$shop_isle_inc_dir = rtrim( SHOP_ISLE_PHP_INCLUDE, '/' );
	$shop_isle_allowed_phps = array();
	$shop_isle_allowed_phps = apply_filters( 'shop_isle_filter_features',$shop_isle_allowed_phps );
	foreach ( $shop_isle_allowed_phps as $file ) {
		$shop_isle_file_to_include = $shop_isle_inc_dir . $file . '.php';
		if ( file_exists( $shop_isle_file_to_include ) ) {
			include_once( $shop_isle_file_to_include );
		}
	}
}
add_action( 'after_setup_theme','shop_isle_include_features' );

if ( ! function_exists( 'shop_isle_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function shop_isle_setup() {
		/*
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 */

		// wp-content/languages/themes/shop-isle-it_IT.mo
		load_theme_textdomain( 'shop-isle', trailingslashit( WP_LANG_DIR ) . 'themes/' );

		// wp-content/themes/child-theme-name/languages/it_IT.mo
		load_theme_textdomain( 'shop-isle', get_stylesheet_directory() . '/languages' );

		// wp-content/themes/theme-name/languages/it_IT.mo
		load_theme_textdomain( 'shop-isle', get_template_directory() . '/languages' );

		/**
		 * Add default posts and comments RSS feed links to head.
		 */
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'shop_isle_blog_image_size', 750, 500, true );
		add_image_size( 'shop_isle_banner_homepage', 360, 235, true );
		add_image_size( 'shop_isle_category_thumbnail', 500, 500, true );
		add_image_size( 'shop_isle_cart_item_image_size', 58, 72, true );

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'primary'       => __( 'Primary Menu', 'shop-isle' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, comments, galleries, captions and widgets
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5', array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'widgets',
			)
		);

		// Setup the WordPress core custom background feature.
		add_theme_support(
			'custom-background', apply_filters(
				'shop_isle_custom_background_args', array(
					'default-color' => apply_filters( 'shop_isle_default_background_color', 'fcfcfc' ),
					'default-image' => '',
				)
			)
		);

		// Add support for the Site Logo plugin and the site logo functionality in JetPack
		// https://github.com/automattic/site-logo
		// http://jetpack.me/
		// Declare WooCommerce support
		add_theme_support( 'woocommerce' );

		// Declare support for title theme feature
		add_theme_support( 'title-tag' );

		/* Custom header */
		add_theme_support(
			'custom-header', array(
				'default-image' => get_template_directory_uri() . '/assets/images/header.jpg',
				'width'         => 1200,
				'height'        => 280,
				'flex-height'   => true,
			)
		);

		/* tgm-plugin-activation */
		require_once get_template_directory() . '/class-tgm-plugin-activation.php';

		if ( class_exists( 'WooCommerce' ) ) {
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
		}
	}
endif; // shop_isle_setup

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function shop_isle_widgets_init() {

	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'shop-isle' ),
			'id'            => 'sidebar-1',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Footer area 1', 'shop-isle' ),
			'id'            => 'sidebar-footer-area-1',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Footer area 2', 'shop-isle' ),
			'id'            => 'sidebar-footer-area-2',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Footer area 3', 'shop-isle' ),
			'id'            => 'sidebar-footer-area-3',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Footer area 4', 'shop-isle' ),
			'id'            => 'sidebar-footer-area-4',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Sidebar Shop Page', 'shop-isle' ),
			'id'            => 'shop-isle-sidebar-shop-archive',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

}

/**
 * Enqueue scripts and styles.
 *
 * @since  1.0.0
 */
function shop_isle_scripts() {

	wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/assets/bootstrap/css/bootstrap.min.css', array(), '20120206', 'all' );

	wp_enqueue_style( 'magnific-popup', get_template_directory_uri() . '/assets/css/vendor/magnific-popup.css', array(), '20120206', 'all' );

	wp_enqueue_style( 'flexslider', get_template_directory_uri() . '/assets/css/vendor/flexslider.css', array( 'magnific-popup' ), '20120206', 'all' );

	wp_enqueue_style( 'owl-carousel', get_template_directory_uri() . '/assets/css/vendor/owl.carousel.min.css', array( 'flexslider' ), '2.1.6', 'all' );

	wp_enqueue_style( 'shop-isle-animate', get_template_directory_uri() . '/assets/css/vendor/animate.css', array( 'owl-carousel' ), '20120206', 'all' );

	wp_enqueue_style( 'shop-isle-main-style', get_template_directory_uri() . '/assets/css/style.css', array( 'bootstrap' ), '20120207', 'all' );

	wp_enqueue_style( 'shop-isle-style', get_stylesheet_uri(), '', SI_VERSION );

	wp_enqueue_script( 'jquery' );

	wp_enqueue_script( 'bootstrap-js', get_template_directory_uri() . '/assets/bootstrap/js/bootstrap.min.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'jquery-mb-YTPlayer', get_template_directory_uri() . '/assets/js/vendor/jquery.mb.YTPlayer.min.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'jqBootstrapValidation', get_template_directory_uri() . '/assets/js/vendor/jqBootstrapValidation.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'flexslider', get_template_directory_uri() . '/assets/js/vendor/jquery.flexslider-min.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'magnific-popup', get_template_directory_uri() . '/assets/js/vendor/jquery.magnific-popup.min.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'fitvids', get_template_directory_uri() . '/assets/js/vendor/jquery.fitvids.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'smoothscroll', get_template_directory_uri() . '/assets/js/vendor/smoothscroll.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'owl-carousel-js', get_template_directory_uri() . '/assets/js/vendor/owl.carousel.min.js', array( 'jquery' ), '2.1.6', true );

	wp_enqueue_script( 'shop-isle-custom', get_template_directory_uri() . '/assets/js/custom.js', array( 'jquery', 'flexslider', 'jquery-mb-YTPlayer' ), '20120206', true );

	wp_enqueue_script( 'shop-isle-navigation', get_template_directory_uri() . '/js/navigation.min.js', array(), '20120207', true );

	wp_enqueue_script( 'shop-isle-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.min.js', array(), '20130115', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

/**
 * Enqueue Admin Styles
 */
function shop_isle_admin_styles() {
	wp_enqueue_media();
	wp_enqueue_style( 'shop_isle_admin_stylesheet', get_template_directory_uri() . '/assets/css/admin-style.css' );
}

add_action( 'tgmpa_register', 'shop_isle_register_required_plugins' );

/**
 * Register TGMP Required Plugins
 */
function shop_isle_register_required_plugins() {

	$plugins = array(
		array(
			'name'      => 'WooCommerce',
			'slug'      => 'woocommerce',
			'required'  => false,
		),
	);

	$config = array(
		'default_path' => '',
		'menu' => 'tgmpa-install-plugins',
		'has_notices' => true,
		'dismissable' => true,
		'dismiss_msg' => '',
		'is_automatic' => false,
		'message' => '',
	);
	tgmpa( $plugins, $config );
}


/**
 * Add ID-s for migration
 */
function shop_isle_add_id() {

	$migrate = get_option( 'shop_isle_migrate_translation' );

	if ( isset( $migrate ) && $migrate == false ) {

		/* Slider section */
		$shop_isle_slider = get_theme_mod(
			'shop_isle_slider', json_encode(
				array(
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/slide1.jpg',
						'link' => '#',
						'text' => __( 'Shop Isle','shop-isle' ),
						'subtext' => __( 'WooCommerce Theme','shop-isle' ),
						'label' => __( 'Read more','shop-isle' ),
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/slide2.jpg',
						'link' => '#',
						'text' => __( 'Shop Isle','shop-isle' ),
						'subtext' => __( 'WooCommerce Theme','shop-isle' ),
						'label' => __( 'Read more','shop-isle' ),
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/slide3.jpg',
						'link' => '#',
						'text' => __( 'Shop Isle','shop-isle' ),
						'subtext' => __( 'WooCommerce Theme','shop-isle' ),
						'label' => __( 'Read more','shop-isle' ),
					),
				)
			)
		);

		if ( ! empty( $shop_isle_slider ) ) {

			$shop_isle_slider_decoded = json_decode( $shop_isle_slider );
			foreach ( $shop_isle_slider_decoded as &$it ) {
				if ( ! array_key_exists( 'id' , $it ) || ! ($it->id) ) {
					$it = (object) array_merge(
						(array) $it, array(
							'id' => 'shop_isle_' . uniqid(),
						)
					);
				}
			}

			$shop_isle_slider = json_encode( $shop_isle_slider_decoded );
			set_theme_mod( 'shop_isle_slider', $shop_isle_slider );
		}

		/* Banners section */
		$shop_isle_banners = get_theme_mod(
			'shop_isle_banners', json_encode(
				array(
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/banner1.jpg',
						'link' => '#',
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/banner2.jpg',
						'link' => '#',
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/banner3.jpg',
						'link' => '#',
					),
				)
			)
		);

		if ( ! empty( $shop_isle_banners ) ) {

			$shop_isle_banners_decoded = json_decode( $shop_isle_banners );
			foreach ( $shop_isle_banners_decoded as &$it ) {
				if ( ! array_key_exists( 'id' , $it ) || ! ($it->id) ) {
					$it = (object) array_merge(
						(array) $it, array(
							'id' => 'shop_isle_' . uniqid(),
						)
					);
				}
			}

			$shop_isle_banners = json_encode( $shop_isle_banners_decoded );
			set_theme_mod( 'shop_isle_banners', $shop_isle_banners );
		}

		/* Footer socials */
		$shop_isle_socials = get_theme_mod(
			'shop_isle_socials', json_encode(
				array(
					array(
						'icon_value' => 'social_facebook',
						'link' => '#',
					),
					array(
						'icon_value' => 'social_twitter',
						'link' => '#',
					),
					array(
						'icon_value' => 'social_dribbble',
						'link' => '#',
					),
					array(
						'icon_value' => 'social_skype',
						'link' => '#',
					),
				)
			)
		);

		if ( ! empty( $shop_isle_socials ) ) {

			$shop_isle_socials_decoded = json_decode( $shop_isle_socials );
			foreach ( $shop_isle_socials_decoded as &$it ) {
				if ( ! array_key_exists( 'id' , $it ) || ! ($it->id) ) {
					$it = (object) array_merge(
						(array) $it, array(
							'id' => 'shop_isle_' . uniqid(),
						)
					);
				}
			}

			$shop_isle_socials = json_encode( $shop_isle_socials_decoded );
			set_theme_mod( 'shop_isle_socials', $shop_isle_socials );
		}

		/* Our team */
		$shop_isle_team_members = get_theme_mod(
			'shop_isle_team_members', json_encode(
				array(
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/team1.jpg',
						'text' => 'Eva Bean',
						'subtext' => 'Developer',
						'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/team2.jpg',
						'text' => 'Maria Woods',
						'subtext' => 'Designer',
						'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/team3.jpg',
						'text' => 'Booby Stone',
						'subtext' => 'Director',
						'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/team4.jpg',
						'text' => 'Anna Neaga',
						'subtext' => 'Art Director',
						'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
					),
				)
			)
		);

		if ( ! empty( $shop_isle_team_members ) ) {

			$shop_isle_team_members_decoded = json_decode( $shop_isle_team_members );
			foreach ( $shop_isle_team_members_decoded as &$it ) {
				if ( ! array_key_exists( 'id' , $it ) || ! ($it->id) ) {
					$it = (object) array_merge(
						(array) $it, array(
							'id' => 'shop_isle_' . uniqid(),
						)
					);
				}
			}

			$shop_isle_team_members = json_encode( $shop_isle_team_members_decoded );
			set_theme_mod( 'shop_isle_team_members', $shop_isle_team_members );
		}

		/* Our advantages */
		$shop_isle_advantages = get_theme_mod(
			'shop_isle_advantages', json_encode(
				array(
					array(
						'icon_value' => 'icon_lightbulb',
						'text' => __( 'Ideas and concepts','shop-isle' ),
						'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
					),
					array(
						'icon_value' => 'icon_tools',
						'text' => __( 'Designs & interfaces','shop-isle' ),
						'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
					),
					array(
						'icon_value' => 'icon_cogs',
						'text' => __( 'Highly customizable','shop-isle' ),
						'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
					),
					array(
						'icon_value' => 'icon_like',
						'text' => __( 'Easy to use','shop-isle' ),
						'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
					),
				)
			)
		);

		if ( ! empty( $shop_isle_advantages ) ) {

			$shop_isle_advantages_decoded = json_decode( $shop_isle_advantages );
			foreach ( $shop_isle_advantages_decoded as &$it ) {
				if ( ! array_key_exists( 'id' , $it ) || ! ($it->id) ) {
					$it = (object) array_merge(
						(array) $it, array(
							'id' => 'shop_isle_' . uniqid(),
						)
					);
				}
			}

			$shop_isle_advantages = json_encode( $shop_isle_advantages_decoded );
			set_theme_mod( 'shop_isle_advantages', $shop_isle_advantages );
		}

		update_option( 'shop_isle_migrate_translation', true );
	}// End if().
}
add_action( 'shutdown', 'shop_isle_add_id' );

/* Polylang repeater translate */

if ( function_exists( 'icl_unregister_string' ) && function_exists( 'icl_register_string' ) ) {

	/* Slider section */

	$shop_isle_slider_pl = get_theme_mod( 'shop_isle_slider' );

	if ( ! empty( $shop_isle_slider_pl ) ) {

		$shop_isle_slider_pl_decoded = json_decode( $shop_isle_slider_pl );

		if ( ! empty( $shop_isle_slider_pl_decoded ) ) {

			foreach ( $shop_isle_slider_pl_decoded as $shop_isle_slider ) {

				if ( ! empty( $shop_isle_slider->id ) ) {
					$id = $shop_isle_slider->id;
				}
				$text = $shop_isle_slider->text;
				$subtext = $shop_isle_slider->subtext;
				$image_url = $shop_isle_slider->image_url;
				$link = $shop_isle_slider->link;
				$label = $shop_isle_slider->label;

				if ( ! empty( $id ) ) {

					if ( ! empty( $image_url ) ) {
						icl_unregister_string( 'Slide ' . $id, 'Slide image' );
						icl_register_string( 'Slide ' . $id, 'Slide image', $image_url );
					} else {
						icl_unregister_string( 'Slide ' . $id, 'Slide image' );
					}

					if ( ! empty( $text ) ) {
						icl_unregister_string( 'Slide ' . $id, 'Slide text' );
						icl_register_string( 'Slide ' . $id, 'Slide text', $text );
					} else {
						icl_unregister_string( 'Slide ' . $id, 'Slide text' );
					}

					if ( ! empty( $subtext ) ) {
						icl_unregister_string( 'Slide ' . $id, 'Slide subtext' );
						icl_register_string( 'Slide ' . $id, 'Slide subtext',$subtext );
					} else {
						icl_unregister_string( 'Slide ' . $id, 'Slide subtext' );
					}

					if ( ! empty( $link ) ) {
						icl_unregister_string( 'Slide ' . $id, 'Slide button link' );
						icl_register_string( 'Slide ' . $id, 'Slide button link', $link );
					} else {
						icl_unregister_string( 'Slide ' . $id, 'Slide button link' );
					}

					if ( ! empty( $label ) ) {
						icl_unregister_string( 'Slide ' . $id, 'Slide button label' );
						icl_register_string( 'Slide ' . $id, 'Slide button label', $label );
					} else {
						icl_unregister_string( 'Slide ' . $id, 'Slide button label' );
					}
				}// End if().
			}// End foreach().
		}// End if().
	}// End if().

	/* Banners section */

	$shop_isle_banners_pl = get_theme_mod( 'shop_isle_banners' );

	if ( ! empty( $shop_isle_banners_pl ) ) {

		$shop_isle_banners_pl_decoded = json_decode( $shop_isle_banners_pl );

		if ( ! empty( $shop_isle_banners_pl_decoded ) ) {

			foreach ( $shop_isle_banners_pl_decoded as $shop_isle_banners ) {

				if ( ! empty( $shop_isle_banners->id ) ) {
					$id = $shop_isle_banners->id;
				}

				$image_url = $shop_isle_banners->image_url;
				$link = $shop_isle_banners->link;

				if ( ! empty( $id ) ) {

					if ( ! empty( $link ) ) {
						icl_unregister_string( 'Banner ' . $id, 'Banner link' );
						icl_register_string( 'Banner ' . $id, 'Banner link', $link );
					} else {
						icl_unregister_string( 'Banner ' . $id, 'Banner link' );
					}

					if ( ! empty( $image_url ) ) {
						icl_unregister_string( 'Banner ' . $id, 'Banner image' );
						icl_register_string( 'Banner ' . $id, 'Banner image', $image_url );
					} else {
						icl_unregister_string( 'Banner ' . $id, 'Banner image' );
					}
				}
			}
		}
	}

	/*Footer socials */

	$shop_isle_socials_pl = get_theme_mod( 'shop_isle_socials' );

	if ( ! empty( $shop_isle_socials_pl ) ) {

		$shop_isle_socials_pl_decoded = json_decode( $shop_isle_socials_pl );

		if ( ! empty( $shop_isle_socials_pl_decoded ) ) {

			foreach ( $shop_isle_socials_pl_decoded as $shop_isle_socials ) {

				if ( ! empty( $shop_isle_socials->id ) ) {
					$id = $shop_isle_socials->id;
				}
				$icon_value = $shop_isle_socials->icon_value;
				$link = $shop_isle_socials->link;

				if ( ! empty( $id ) ) {
					if ( ! empty( $icon_value ) ) {
						icl_unregister_string( 'Social ' . $id, 'Social icon' );
						icl_register_string( 'Social ' . $id, 'Social icon', $icon_value );
					} else {
						icl_unregister_string( 'Social ' . $id, 'Social icon' );
					}
					if ( ! empty( $link ) ) {
						icl_unregister_string( 'Social ' . $id, 'Social link' );
						icl_register_string( 'Social ' . $id, 'Social link', $link );
					} else {
						icl_unregister_string( 'Social ' . $id, 'Social link' );
					}
				}
			}
		}
	}

	/* About us page  */



	/* Our team */
	$shop_isle_team_members_pl = get_theme_mod( 'shop_isle_team_members' );

	if ( ! empty( $shop_isle_team_members_pl ) ) {

		$shop_isle_team_members_pl_decoded = json_decode( $shop_isle_team_members_pl );

		if ( ! empty( $shop_isle_team_members_pl_decoded ) ) {

			foreach ( $shop_isle_team_members_pl_decoded as $shop_isle_team_members ) {

				if ( ! empty( $shop_isle_team_members->id ) ) {
					$id = $shop_isle_team_members->id;
				}
				if ( ! empty( $shop_isle_team_members->image_url ) ) {
					$image_url = $shop_isle_team_members->image_url;
				}
				if ( ! empty( $shop_isle_team_members->text ) ) {
					$text = $shop_isle_team_members->text;
				}
				if ( ! empty( $shop_isle_team_members->subtext ) ) {
					$subtext = $shop_isle_team_members->subtext;
				}
				if ( ! empty( $shop_isle_team_members->description ) ) {
					$description = $shop_isle_team_members->description;
				}

				if ( ! empty( $id ) ) {
					if ( ! empty( $image_url ) ) {
						icl_unregister_string( 'Team member ' . $id, 'Team member image' );
						icl_register_string( 'Team member ' . $id, 'Team member image', $image_url );
					} else {
						icl_unregister_string( 'Team member ' . $id, 'Team member image' );
					}

					if ( ! empty( $text ) ) {
						icl_unregister_string( 'Team member ' . $id, 'Team member name' );
						icl_register_string( 'Team member ' . $id, 'Team member name', $text );
					} else {
						icl_unregister_string( 'Team member ' . $id, 'Team member name' );
					}

					if ( ! empty( $subtext ) ) {
						icl_unregister_string( 'Team member ' . $id, 'Team member job' );
						icl_register_string( 'Team member ' . $id, 'Team member job', $subtext );
					} else {
						icl_unregister_string( 'Team member ' . $id, 'Team member job' );
					}

					if ( ! empty( $description ) ) {
						icl_unregister_string( 'Team member ' . $id, 'Team member description' );
						icl_register_string( 'Team member ' . $id, 'Team member description', $description );
					} else {
						icl_unregister_string( 'Team member ' . $id, 'Team member description' );
					}
				}
			}// End foreach().
		}// End if().
	}// End if().

	// /* Our advantages */
	$shop_isle_advantages_pl = get_theme_mod( 'shop_isle_advantages' );

	if ( ! empty( $shop_isle_advantages_pl ) ) {

		$shop_isle_advantages_pl_decoded = json_decode( $shop_isle_advantages_pl );

		if ( ! empty( $shop_isle_advantages_pl_decoded ) ) {

			foreach ( $shop_isle_advantages_pl_decoded as $shop_isle_advantages ) {

				if ( ! empty( $shop_isle_advantages->id ) ) {
					$id = $shop_isle_advantages->id;
				}
				$icon_value = $shop_isle_advantages->icon_value;
				$text = $shop_isle_advantages->text;
				$subtext = $shop_isle_advantages->subtext;

				if ( ! empty( $id ) ) {
					if ( ! empty( $icon_value ) ) {
						icl_unregister_string( 'Advantage ' . $id, 'Advantage icon' );
						icl_register_string( 'Advantage ' . $id, 'Advantage icon',$icon_value );
					} else {
						icl_unregister_string( 'Advantage ' . $id, 'Advantage icon' );
					}

					if ( ! empty( $text ) ) {
						icl_unregister_string( 'Advantage ' . $id, 'Advantage text' );
						icl_register_string( 'Advantage ' . $id, 'Advantage text', $text );
					} else {
						icl_unregister_string( 'Advantage ' . $id, 'Advantage text' );
					}

					if ( ! empty( $subtext ) ) {
						icl_unregister_string( 'Advantage ' . $id ,'Advantage subtext' );
						icl_register_string( 'Advantage ' . $id ,'Advantage subtext', $subtext );
					} else {
						icl_unregister_string( 'Advantage ' . $id ,'Advantage subtext' );
					}
				}
			}
		}
	}// End if().
}// End if().


add_action( 'wp_head','shop_isle_php_style' );

/**
 * Add palette picker output.
 */
function shop_isle_php_style() {

	$shop_isle_palette_picker = get_theme_mod( 'shop_isle_palette_picker' );

	if ( ! empty( $shop_isle_palette_picker ) ) {

		$shop_isle_picker = json_decode( $shop_isle_palette_picker );

		$shop_isle_c1 = $shop_isle_picker->color1;
		$shop_isle_c2 = $shop_isle_picker->color2;
		$shop_isle_c3 = $shop_isle_picker->color3;
		$shop_isle_c4 = $shop_isle_picker->color4;
		$shop_isle_c5 = $shop_isle_picker->color5;

	}

	echo '<style id="shop_isle_customizr_pallete" type="text/css">';

	$shop_isle_body_font_size = get_theme_mod( 'shop_isle_font_size' );
	echo ! empty( $shop_isle_body_font_size ) ? 'body, section#latest .amount, section.home-product-slider .amount, section.shortcodes .amount { font-size:' . $shop_isle_body_font_size . '}' : '' ;

	if ( ! empty( $shop_isle_palette_picker ) ) {

		/*Color 1*/
		preg_match_all( '!\d+!', $shop_isle_c1, $shop_isle_c1_rgb );
		echo '
			.navbar-custom,
			.header-shopping-cart,
			.navbar-custom .sub-menu, 
			.navbar-custom .children,
			.onsale,
		    .header-search-input{
				background: rgba(' . $shop_isle_c1_rgb[0][0] . ',' . $shop_isle_c1_rgb[0][1] . ',' . $shop_isle_c1_rgb[0][2] . ',.9);
			}
			.shop_isle_footer_sidebar,
			 footer.footer {
				background-color: ' . $shop_isle_c1 . ' !important;
			}
			.page .navbar-custom {
				background: rgba(' . $shop_isle_c1_rgb[0][0] . ',' . $shop_isle_c1_rgb[0][1] . ',' . $shop_isle_c1_rgb[0][2] . ',.9) !important;
			}
		';

		/*Color 2*/
		echo '
			.module-title,
			.widget .widget-title,
			 .post-title a,
			 .single-product .product_title,
			 .related.products h2, 
			 .upsells.products h2 {
				color: ' . $shop_isle_c2 . ';
			}
		';

		/* Color 3 */
		echo '
			body,
			a,
			.main,
			.module-subtitle {
				color: ' . $shop_isle_c3 . ';
			}
			.widget_calendar thead th {
				background: ' . $shop_isle_c3 . ';
			}
		';

		/* Color 4 */
		echo '
			.product .product-button-wrap .add_to_cart_button, 
			ul.products li.product .product-button-wrap .button.product_type_simple, 
			.shop-item .product-button-wrap .add_to_cart_button,
			.btn.btn-b,
			#comments input[type="submit"], 
			button[type="submit"],
			#comments input[type="submit"]:hover, 
			button[type="submit"]:hover,
			#payment .place-order .button,
			table.cart td.actions input[name="update_cart"],
			.wc-proceed-to-checkout .button.checkout-button:hover,
			.wc-proceed-to-checkout .button.checkout-button,
		    .header-shopping-cart .widget_shopping_cart p.buttons a,
		    .shop-item .product-button-wrap .product_type_simple,
		    ul.products li.product .product-button-wrap .product_type_variable,
	        ul.products li.product .product-button-wrap .button.product_type_grouped, 
	        .shop-item .product-button-wrap .button.product_type_grouped, 
	        .shop-item .product-button-wrap .button.product_type_variable, 
	        ul.products li.product .product-button-wrap .product_type_variable,
	        .widget input[type=submit],
			.widget button[type=submit],
			.widget_shopping_cart_content .buttons a,
			.widget_price_filter .ui-slider .ui-slider-handle {
				background: ' . $shop_isle_c4 . ';
			}
			.product .product-button-wrap .add_to_cart_button:hover, 
			ul.products li.product .product-button-wrap .button.product_type_simple:hover, 
			.shop-item .product-button-wrap .add_to_cart_button:hover,
			.btn.btn-b:hover,
		    .header-shopping-cart .widget_shopping_cart p.buttons a:hover,
		    ul.products li.product .product-button-wrap .product_type_variable:hover.
            ul.products li.product .product-button-wrap .button.product_type_grouped:hover, 
	        .shop-item .product-button-wrap .button.product_type_grouped:hover, 
	        .shop-item .product-button-wrap .button.product_type_variable:hover,
	        .widget input[type=submit]:hover,
			.widget button[type=submit]:hover,
			.widget_shopping_cart_content .buttons a:hover, 
			.widget.woocommerce.widget_shopping_cart_content .button a:hover {
			    background: ' . $shop_isle_c4 . ';
			    opacity: 0.8;
			}
			a:hover {
				color: ' . $shop_isle_c4 . ';
			}
			#comments input[type="submit"]:hover, 
			button[type="submit"]:hover {
				opacity: 0.8;
			}
			.single-product div.product form.cart .button:hover,
			#payment .place-order .button:hover, 
			#payment .place-order .button:focus,
			.wc-proceed-to-checkout .button.checkout-button:hover,
			table.cart td.actions input[name="update_cart"]:hover {
				background: ' . $shop_isle_c4 . ';
				opacity: 0.8;
			}
		';

		/* Color 5 */
		echo '
			body,
			.main,
			.panel,
			.woocommerce-tabs ul.tabs li.active a {
				background-color: ' . $shop_isle_c5 . ';
			}
		';

		echo '
			footer.footer,
			footer.footer a,
			.bg-dark a,
		    .header-shopping-cart .widget_shopping_cart p.total,
	        .header-shopping-cart .mini_cart_item .quantity, .header-shopping-cart .mini_cart_item .quantity span,
            .header-shopping-cart .widget_shopping_cart .product_list_widget li a.remove,
            .widget_shopping_cart .product_list_widget li{
				color: rgba( 255, 255, 255, 0.6 );
			}
			footer.footer a:hover,
			.bg-dark a:hover,
			.header-shopping-cart .mini_cart_item a:hover,
			.header-shopping-cart .widget_shopping_cart .product_list_widget li a.remove:hover {
				color: #FFF;
			}
			footer.footer .divider-d {
				border-top: 1px solid rgba(32, 32, 32, 0.5);
			}
			.navbar-custom .sub-menu > li > a, 
			.navbar-custom .children > li > a,
			 .header-shopping-cart .mini_cart_item a {
				color: rgba(255, 255, 255, .7);
			}
		';

	}// End if().

	$shop_isle_navbar_background = get_theme_mod( 'shop_isle_navbar_background' );
	$shop_isle_menu_items_color = get_theme_mod( 'shop_isle_menu_items_color' );
	$shop_isle_menu_items_hover_color = get_theme_mod( 'shop_isle_menu_items_hover_color' );
	$shop_isle_footer_background = get_theme_mod( 'shop_isle_footer_background' );
	$shop_isle_background = get_theme_mod( 'background_color' );

	if ( ! empty( $shop_isle_navbar_background ) ) {
		echo '.page .navbar-custom, .navbar-custom, .header-shopping-cart, .navbar-custom .sub-menu, .navbar-custom .children, .header-search-input { background-color: ' . esc_attr( $shop_isle_navbar_background ) . '!important; }';
	}

	if ( ! empty( $shop_isle_menu_items_color ) ) {
		echo '.navbar-custom .nav li > a { color: ' . esc_attr( $shop_isle_menu_items_color ) . '; }';
	}

	if ( ! empty( $shop_isle_menu_items_hover_color ) ) {
		echo '.navbar-custom .nav > li > a:focus, .navbar-custom .nav > li > a:hover, .navbar-custom .nav .open > a, .navbar-custom .nav .open > a:focus, .navbar-custom .nav .open > a:hover, .navbar-custom .sub-menu > li > a:focus, .navbar-custom .sub-menu > li > a:hover { color: ' . esc_attr( $shop_isle_menu_items_hover_color ) . '; }';
	}

	if ( ! empty( $shop_isle_footer_background ) ) {
		echo '.shop_isle_footer_sidebar, footer.footer { background: ' . esc_attr( $shop_isle_footer_background ) . '!important; }';
	}

	if ( ! empty( $shop_isle_background ) ) {
		echo '.shop_isle_footer_sidebar, footer.footer { background-color: ' . esc_attr( $shop_isle_background ) . '!important; }';
	}

	$shop_isle_video_section_padding = get_theme_mod( 'shop_isle_video_section_padding', '130' );

	if ( ! empty( $shop_isle_video_section_padding ) ) {
		echo '.module.module-video { padding: ' . esc_attr( $shop_isle_video_section_padding ) . 'px 0px; }';
	}

	$shop_isle_header_textcolor = get_theme_mod( 'header_textcolor' );
	if ( ! empty( $shop_isle_header_textcolor ) ) {
		echo '.shop_isle_header_title h1 a, .shop_isle_header_title h2 a { color: #' . esc_attr( $shop_isle_header_textcolor ) . ' }';
	}

	echo '</style>';

}

/**
 * Add style/classes for Mega Menu plugin
 */
function shop_isle_pro_function_for_mega_menu() {

	$shop_isle_palette_picker = get_theme_mod( 'shop_isle_palette_picker' );
	if ( ! empty( $shop_isle_palette_picker ) ) {
		$shop_isle_picker = json_decode( $shop_isle_palette_picker );
		$shop_isle_c1 = $shop_isle_picker->color1;
	}
	if ( ! empty( $shop_isle_palette_picker ) ) {
		preg_match_all( '!\d+!', $shop_isle_c1, $shop_isle_c1_rgb );
		$bg_dropdown = 'background: rgba(' . $shop_isle_c1_rgb[0][0] . ',' . $shop_isle_c1_rgb[0][1] . ',' . $shop_isle_c1_rgb[0][2] . ',.9)';
	} else {
		$bg_dropdown = 'background: rgba(10, 10, 10, .9)';
	}

	/* wr mega menu */
	echo '<style id="shop_isle_footer_css" type="text/css">';
	echo '
		.wr-megamenu-container.bg-tr {
			background: transparent !important;
		}
		.wr-megamenu-container ul.wr-mega-menu ul.sub-menu,
		.wr-megamenu-inner {
		    ' . $bg_dropdown . ' !important;
		    color: #fff !important;
		}
		
		@media (max-width: 767px) {
			.navbar-fixed-top .navbar-collapse {
				' . $bg_dropdown . ' !important;
			}
		}
	';

	echo '</style>';

}
add_action( 'wp_footer', 'shop_isle_pro_function_for_mega_menu', 100 );

/**
 * Remove the frontpage template if the Lite Shop Isle theme was not from wp.org
 */
add_filter( 'theme_page_templates', 'shop_isle_pro_remove_frontpage_template' );


/**
 * Remove frontpage template for wporg.
 *
 * @param page-templates $pages_templates the page templates.
 *
 * @return mixed
 */
function shop_isle_pro_remove_frontpage_template( $pages_templates ) {

	$shop_isle_wporg_flag = get_option( 'shop_isle_wporg_flag' );

	if ( ! isset( $shop_isle_wporg_flag ) || ( ! empty( $shop_isle_wporg_flag ) && ( 'true' != $shop_isle_wporg_flag ) ) ) {

		unset( $pages_templates['template-frontpage.php'] );

	}

	return $pages_templates;

}

