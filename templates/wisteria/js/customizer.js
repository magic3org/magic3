/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {

	// Site Title and Description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );

	// Background Color
	wp.customize( 'background_color', function( value ) {
		value.bind( function( to ) {
			$( 'body' ).css( 'background-color', to );
		} );
	} );

	// Header text color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title a, .site-description' ).css( {
					'clip': 'rect(1px, 1px, 1px, 1px)',
					'position': 'absolute'
				} );
			} else {
				$( '.site-title a, .site-description' ).css( {
					'clip': 'auto',
					'position': 'relative'
				} );
				$( '.site-title a, .site-description' ).css( {
					'color': to
				} );
				$( '.site-description' ).css( {
					'opacity': 0.7
				} );
			}
		} );
	} );

	// Copyright Control
	wp.customize( 'wisteria_copyright', function( value ) {
		value.bind( function( to ) {
			$( '.wisteria-copyright' ).html( to );
		} );
	} );

	// Credit Control
	wp.customize( 'wisteria_credit', function( value ) {
		value.bind( function( to ) {
			if ( true === to ) {
				$( '.wisteria-designer' ).css( {
					'clip': 'auto',
					'position': 'relative'
				} );
			} else {
				$( '.wisteria-designer' ).css( {
					'clip': 'rect(1px, 1px, 1px, 1px)',
					'position': 'absolute'
				} );
			}
		} );
	} );

} )( jQuery );
