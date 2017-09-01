( function( api ) {

	// Extends our custom "shopisle-upsell" section.
	api.sectionConstructor['shopisle-upsell'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

	// Extends our custom "shopisle-upsell-frontpage-sections" section.
	api.sectionConstructor['shopisle-upsell-frontpage-sections'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

	// Extends our custom "shopisle-upsell-frontpage-sections" section.
	api.sectionConstructor['shopisle-upsell-section'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );
