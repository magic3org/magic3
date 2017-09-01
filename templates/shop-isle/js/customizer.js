/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {

	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description a' ).text( to );
		} );
	} );

	/****************************************/
	/********** Big title section ***********/
	/****************************************/
	wp.customize( 'shop_isle_big_title_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== true ) {
				$( '.home-section' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			} else {
				$( '.home-section' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}

		} );
	} );

	/******************************/
	/**********  Colors ***********/
	/******************************/

	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			$( '.shop_isle_header_title h1 a' ).css( {
				'color': to
			} );
			$( '.shop_isle_header_title h2 a' ).css( {
				'color': to
			} );
		} );
	} );
	wp.customize( 'background_color', function( value ) {
		value.bind( function( to ) {
			$( 'body' ).css( {
				'background': to
			} );
			if( $( '.front-page-main' ).length > 0 ) { 
				$( '.front-page-main' ).css( {
					'background': to
				} );
			}
		} );
	} );

    /******************************/
    /**********  Header ***********/
    /******************************/
    wp.customize( 'shop_isle_logo', function( value ) {
        value.bind( function( to ) {

            if( to !== '' ) {
                $( '.shop_isle_header_title .logo-image' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
                $( '.shop_isle_header_title h1' ).addClass( 'shop_isle_hidden_if_not_customizer' );
                $( '.shop_isle_header_title h2' ).addClass( 'shop_isle_hidden_if_not_customizer' );
            }
            else {
                $( '.shop_isle_header_title .logo-image' ).addClass( 'shop_isle_hidden_if_not_customizer' );
                $( '.shop_isle_header_title h1' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
                $( '.shop_isle_header_title h2' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
            }
            $( '.shop_isle_header_title img' ).attr( 'src', to );

        } );
    } );

    wp.customize( 'shop_isle_blog_header_title', function( value ) {
        value.bind( function( to ) {
            if( to !== '' ) {
                $( '.shop-isle-blog-header-title' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
            }
            else {
                $( '.shop-isle-blog-header-title' ).addClass( 'shop_isle_hidden_if_not_customizer' );
            }
            $( '.shop-isle-blog-header-title' ).html( to );
        } );
    } );

    wp.customize( 'shop_isle_blog_header_subtitle', function( value ) {
        value.bind( function( to ) {
            if( to !== '' ) {
                $( '.shop-isle-blog-header-subtitle' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
            }
            else {
                $( '.shop-isle-blog-header-subtitle' ).addClass( 'shop_isle_hidden_if_not_customizer' );
            }
            $( '.shop-isle-blog-header-subtitle' ).html( to );
        } );
    } );
	
	/*******************************/
	/******    Slider section ******/
	/*******************************/
	wp.customize( 'shop_isle_slider_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== '1' ) {
				$( 'section.home-section' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
				$( '.navbar-custom' ).removeClass( 'navbar-color-customizer' );
				$('.main').css('margin-top', 0 );
			}
			else {
				$( 'section.home-section' ).addClass( 'shop_isle_hidden_if_not_customizer' );
				$( '.navbar-custom' ).addClass( 'navbar-color-customizer' );
				$('.main').css('margin-top', $('.navbar-custom').outerHeight() );
			}
		} );
	} );

	/********************************/
    /*********	Banners section *****/
	/********************************/
	wp.customize( 'shop_isle_banners_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== true ) {
				$( '.home-banners' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.home-banners' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
		} );
	} );

	wp.customize( 'shop_isle_banners_title', function( value ) {

		value.bind( function( to ) {
			if( to !== '' ) {
				$( '.product-banners-title' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.product-banners-title' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
			$( '.product-banners-title' ).text( to );
		} );

	} );

	// Add new banner (Repeater)
	wp.customize( 'shop_isle_banners', function( value ) {
		value.bind( function( to ) {
			var obj = JSON.parse( to );
			var result ='';
			obj.forEach(function(item) {
				result += '<div class="col-sm-4"><div class="content-box mt-0 mb-0"><div class="content-box-image"><a href="' + item.link + '"><img src="' + item.image_url + '"></a></div></div></div>';
			});
			$( '.shop_isle_bannerss_section' ).html( result );
		} );
	} );



	/*********************************/
    /*******  Products section *******/
	/********************************/
	wp.customize( 'shop_isle_products_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== true ) {
				$( '#latest' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '#latest' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
		} );
	} );
	wp.customize( 'shop_isle_products_title', function( value ) {
		value.bind( function( to ) {
			$( '.product-hide-title' ).html( to );
		} );
	} );

	/****************************************/
	/*********** Video section **************/
	/****************************************/
	wp.customize( 'shop_isle_video_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== true ) {
				$( '.module-video' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.module-video' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
		} );
	} );
	wp.customize( 'shop_isle_video_title', function( value ) {
		value.bind( function( to ) {
			var $title = $( '.video-title' );
            if  ( to !== '' ) {
                $title.removeClass( 'shop_isle_hidden_if_not_customizer' );
                $title.html( to );
            } else {
                $title.addClass('shop_isle_hidden_if_not_customizer');
			}
		} );
	} );

    wp.customize('shop_isle_video_section_padding', function (value) {
        var $selector = $('.module.module-video');
        value.bind(function (to) {
            $( window ).resize();
            $selector.css('padding', to+'px 0px');
        });
    });

	/****************************************/
    /*******  Products slider section *******/
	/****************************************/
	wp.customize( 'shop_isle_products_slider_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== true ) {
				$( '.home-product-slider' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.home-product-slider' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
		} );
	} );
	wp.customize( 'shop_isle_products_slider_single_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== true ) {
				$( '.module-small-bottom' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.module-small-bottom' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
		} );
	} );
	wp.customize( 'shop_isle_products_slider_title', function( value ) {
		value.bind( function( to ) {
			$( '.home-prod-title' ).text( to );
		} );
	} );
	wp.customize( 'shop_isle_products_slider_subtitle', function( value ) {
		value.bind( function( to ) {
			$( '.home-prod-subtitle' ).text( to );
		} );
	} );
	
	/****************************************/
	/*********  Services section ************/
	/****************************************/

	wp.customize( 'shop_isle_services_title', function( value ) {
		value.bind( function( to ) {
			$( '.services h2' ).text( to );
		} );
	} );
	wp.customize( 'shop_isle_services_subtitle', function( value ) {
		value.bind( function( to ) {
			$( '.services .module-subtitle' ).text( to );
		} );
	} );
	
	/* Our services (Repeater) */
	wp.customize( 'shop_isle_service_box', function( value ) {
		value.bind( function( to ) {
			var obj = JSON.parse( to );
			var result ='';
			obj.forEach(function(item) {
				result += '<div class="col-xs-12 col-sm-4 sip-service-box"><div class="sip-single-service"><a href="'+item.link+'" class="sip-single-service-a"><span class="'+item.icon_value+' sip-single-icon"></span><div class="sip-single-service-text"><h3 class="">'+item.text+'</h3><p class="">'+item.subtext+'</p></div></a></div></div>';
			});
			$( '#our_services_wrap' ).html( result );
		} );
	} );

	/****************************************/
	/*********  Ribbon section **************/
	/****************************************/
	wp.customize( 'shop_isle_ribbon_text', function( value ) {
		value.bind( function( to ) {
			if( to !== '1' ) {
				$( '.ribbon' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.ribbon' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
		} );
	} );
	wp.customize( 'shop_isle_ribbon_text', function( value ) {
		value.bind( function( to ) {
			$( '.ribbon h2' ).text( to );
		} );
	} );

	wp.customize( 'shop_isle_ribbon_button_text', function( value ) {
		value.bind( function( to ) {
			$( '.ribbon .btn-ribbon' ).text( to );
		} );
	} );

	wp.customize( 'shop_isle_ribbon_background', function( value ) {
		value.bind( function( to ) {
			$( '#ribbon' ).css( 'background-image', 'url(' + to + ')' );
		} );
	} );

	/*****************************************/
	/*********  Categories section ***********/
	/*****************************************/

	wp.customize( 'shop_isle_fp_categories_title', function( value ) {
		value.bind( function( to ) {
			$( '.categories#categories h2' ).text( to );
		} );
	} );
	wp.customize( 'shop_isle_fp_categories_subtitle', function( value ) {
		value.bind( function( to ) {
			$( '.categories#categories .module-subtitle' ).text( to );
		} );
	} );

	/*******************************/
    /***********  Footer ***********/
	/*******************************/
	wp.customize( 'shop_isle_copyright', function( value ) {
		value.bind( function( to ) {
			$( '.copyright' ).html( to );
		} );
	} );


	/*******************************************/
	/******    Hide site info from footer ******/
	/*******************************************/
	wp.customize( 'shop_isle_site_info_hide', function( value ) {
		value.bind( function( to ) {
			if( to !== true ) {
				$( '.shop-isle-poweredby-box' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.shop-isle-poweredby-box' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}
		} );
	} );

	// socials (Repeater)
	wp.customize( 'shop_isle_socials', function( value ) {
		value.bind( function( to ) {
			var obj = JSON.parse( to );
			var result ='';
			obj.forEach(function(item) {
				result+=  '<a href="' + item.link + '" class="social-icon"><i class="fa ' + item.icon_value + '"></i></a>';
			});
			$( '.footer-social-links' ).html( result );
		} );
	} );

	/*********************************/
	/******  About us page  **********/
	/*********************************/
	wp.customize( 'shop_isle_our_team_title', function( value ) {
		value.bind( function( to ) {
			$( '.meet-out-team-title' ).text( to );
		} );
	} );
	wp.customize( 'shop_isle_our_team_subtitle', function( value ) {
		value.bind( function( to ) {
			$( '.meet-out-team-subtitle' ).text( to );
		} );
	} );
	wp.customize( 'shop_isle_about_page_video_title', function( value ) {
		value.bind( function( to ) {
			$( '.video-title' ).text( to );
		} );
	} );
	wp.customize( 'shop_isle_about_page_video_subtitle', function( value ) {
		value.bind( function( to ) {
			$( '.video-subtitle' ).text( to );
		} );
	} );
	wp.customize( 'shop_isle_about_page_video_background', function( value ) {
		value.bind( function( to ) {
			$( '.about-page-video' ).css( 'background-image', 'url(' + to + ')' );
		} );
	} );
	wp.customize( 'shop_isle_about_page_video_link', function( value ) {
		value.bind( function( to ) {
			if( to !== '' ) {
				$( '.video-box-icon' ).removeClass( 'shop_isle_hidden_if_not_customizer' );
			}
			else {
				$( '.video-box-icon' ).addClass( 'shop_isle_hidden_if_not_customizer' );
			}

		} );
	} );
	wp.customize( 'shop_isle_our_advantages_title', function( value ) {
		value.bind( function( to ) {
			$( '.our_advantages' ).text( to );
		} );
	} );

	/* Team members (Repeater) */
	wp.customize( 'shop_isle_team_members', function( value ) {
		value.bind( function( to ) {
			var obj = JSON.parse( to );
			var result ='';
			obj.forEach(function(item) {
				result += '<div class="col-sm-6 col-md-3 mb-sm-20 fadeInUp"><div class="team-item"><div class="team-image"><img src="' + item.image_url + '" alt="' + item.text + '"><div class="team-detail"><p class="font-serif">' + item.description + '</p></div><!-- .team-detail --></div><!-- .team-image --><div class="team-descr font-alt"><div class="team-name">' + item.text + '</div><div class="team-role">' + item.subtext + '</div></div><!-- .team-descr --></div><!-- .team-item --></div>';
			});
			$( '.about-team-member .slides' ).html( result );
		} );
	} );

	/* Advantages (Repeater) */
	wp.customize( 'shop_isle_advantages', function( value ) {
		value.bind( function( to ) {
			var obj = JSON.parse( to );
			var result ='';
			obj.forEach(function(item) {
				result += '<div class="col-sm-6 col-md-3 col-lg-3"><div class="features-item"><div class="features-icon"><span class="' + item.icon_value + '"></span></div><h3 class="features-title font-alt">' + item.text + '</h3>' + item.subtext + '</div></div>';
			});
			$( '.module-advantages .multi-columns-row' ).html( result );
		} );
	} );
 

	/*********************************/
	/**********  404 page  ***********/
	/*********************************/
	wp.customize( 'shop_isle_404_background', function( value ) {
		value.bind( function( to ) {
			$( '.error-page-background' ).css( 'background-image', 'url(' + to + ')' );
		} );
	} );
	wp.customize( 'shop_isle_404_title', function( value ) {
		value.bind( function( to ) {
			$( '.error-page-title' ).html( to );
		} );
	} );
	wp.customize( 'shop_isle_404_text', function( value ) {
		value.bind( function( to ) {
			$( '.error-page-text' ).html( to );
		} );
	} );
	wp.customize( 'shop_isle_404_link', function( value ) {
		value.bind( function( to ) {
			$( '.error-page-button-text a' ).attr( 'href', to );
		} );
	} );
	wp.customize( 'shop_isle_404_label', function( value ) {
		value.bind( function( to ) {
			$( '.error-page-button-text a' ).text( to );
		} );
	} );

} )( jQuery );

