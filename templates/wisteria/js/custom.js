/*!
 * Custom v1.0
 * Contains handlers for the different site functions
 *
 * Copyright (c) 2013-2016 WPFriendship.com
 * License: GNU General Public License v2 or later
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

/* global enquire:true */

( function( $ ) {

	var wisteria = {

		// Menu
		menuInit: function() {

			// Superfish Menu
			$( 'ul.sf-menu' ).superfish( {
				delay:        1500,
				animation:    { opacity : 'show', height : 'show' },
				speed:        'fast',
				autoArrows:   false,
				cssArrows:    true
			} );

		},

		// Responsive Menu
		responsiveMenuInit: function() {

			// Clone the Primary Menu and remove classes from clone to prevent css issues
			var $primaryMenuClone = $( '.primary-menu' ).clone().removeAttr( 'class' ).addClass( 'primary-menu-responsive' );
			$primaryMenuClone.removeAttr( 'style' ).find( '*' ).each( function( i,e ) {
				$( e ).removeAttr( 'style' );
			} );

			// Responsive Menu Close Button
			var $responsiveMenuClose = $( '<div class="primary-menu-responsive-close">&times;</div>' );

			// Insert the cloned menu before the site content
			$( '<div class="site-primary-menu-responsive" />' ).insertBefore( '.site-content' );
			$primaryMenuClone.appendTo( '.site-primary-menu-responsive' );
			$responsiveMenuClose.appendTo( '.site-primary-menu-responsive' );

			// Add dropdown toggle that display child menu items.
			$( '.site-primary-menu-responsive .page_item_has_children > a, .site-primary-menu-responsive .menu-item-has-children > a' ).append( '<button class="dropdown-toggle" aria-expanded="false"/>' );
			$( '.site-primary-menu-responsive .dropdown-toggle' ).off( 'click' ).on( 'click', function( e ) {
				e.preventDefault();
				$( this ).toggleClass( 'toggle-on' );
				$( this ).parent().next( '.children, .sub-menu' ).toggleClass( 'toggle-on' );
				$( this ).attr( 'aria-expanded', $( this ).attr( 'aria-expanded' ) === 'false' ? 'true' : 'false' );
			} );

		},

		// Open Slide Panel - Responsive Mobile Menu
		slidePanelInit: function() {

			// Elements
			var menuResponsive      = $( '.site-primary-menu-responsive' );
			var menuResponsiveClose = $( '.primary-menu-responsive-close' );

			// Responsive Menu Slide
			$( '.toggle-menu-control' ).off( 'click' ).on( 'click', function( e ) {

				// Prevent Default
				e.preventDefault();
				e.stopPropagation();

				// ToggleClass
				menuResponsive.toggleClass( 'show' );

			} );

			// Responsive Menu Close
			menuResponsiveClose.off( 'click' ).on( 'click', function() {
				wisteria.slidePanelCloseInit();
			} );

		},

		// Close Slide Panel
		slidePanelCloseInit: function() {

			// Elements
			var menuResponsive = $( '.site-primary-menu-responsive' );

			// For Menu
			if( menuResponsive.hasClass( 'show' ) ) {
				menuResponsive.toggleClass( 'show' );
			}

		},

		// Media Queries
		mqInit: function() {

			enquire.register( 'screen and ( max-width: 767px )' , {

			    deferSetup : true,
			    setup : function() {

			        // Responsive Menu
					wisteria.responsiveMenuInit();

			    },
			    match : function() {

					// Sliding Panels for Menu
					wisteria.slidePanelInit();

					// Responsive Tables
					$( '.entry-content, .sidebar, .footer-sidebar' ).find( 'table' ).wrap( '<div class="table-responsive"></div>' );

			    },
			    unmatch : function() {

			        // Responsive Menu Close
					wisteria.slidePanelCloseInit();

					// Responsive Tables Undo
					$( '.entry-content, .sidebar, .footer-sidebar' ).find( 'table' ).unwrap( '<div class="table-responsive"></div>' );

			    }

			});

		}

	};

	// Document Ready
	$( document ).ready( function() {

		// Menu
		wisteria.menuInit();

	    // Media Queries
	    wisteria.mqInit();

	} );

} )( jQuery );
