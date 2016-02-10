jQuery( document ).ready( function($) {

	/**
	 * Search Widget
	 */

	// Onclick for 'Show Fields' links to show/hide checkbox container
	$( document ).on( 'click', '.show-hide-fields-area', function() {
		toggleFieldsArea( $, this );
	});

	// onchange for field checkboxes: display the field details section
	$( document).on( 'change', 'div#cptd-search-form div.cptd-search-field-filter-select div.cptd-search-widget-field input[type=checkbox]', 
		function() {
			toggleSearchFilterDetails( $, this );
	});

	// init routine that can be re-called when widget is saved
	initSearchWidget( $ );


	/**
	 * Random Posts Widget
	 */

	// onchange for taxonomy checkboxes: show terms details section
	$( document ).on( 'change', 'div#cptd-random-posts-form .taxonomy-select input[type=checkbox]', 
	 	function() {
	 		toggleTaxonomyTermDetails( $, this );
	 });

	// initialize taxonomy term details on page load
	$('div#cptd-random-posts-form .taxonomy-select input[type=checkbox]').each( function() {
		toggleTaxonomyTermDetails( $, this );
	});
}); // end: document ready


/**
 * Subroutines
 *
 * - initSearchWidget()
 * - toggleSearchFilterDetails()
 * - toggleTaxonomyTermDetails()
 * - toggleFieldsArea()
 */

/**
 * Initialize the search widget on page load and when widget is saved
 *
 * @param 	jQuery 	$ 	The main jquery object
 * @since 	2.0.0
 */
function initSearchWidget( $ ) {

	// initialize field filter checkboxes: display field details for any checked fields
	$('div.cptd-search-field-filter-select div.cptd-search-widget-field').find('input[type=checkbox]')
		.each( function() { toggleSearchFilterDetails( $, this ) } );

	// draggable
	$( 'div.cptd-draggable-fields-container .cptd-fields-area .cptd-search-widget-field' ).draggable( {
		axis: 'y',
		scope: 'search-result-fields-scope',
		revert: 'invalid',
		helper: 'clone'
	});

	// droppable
	$( '.cptd-fields-drop' ).droppable( {
		scope: 'search-result-fields-scope',
		accept: '.cptd-search-widget-field',
		tolerance: 'touch',
		cursor: 'move',
		hoverClass: 'hover-over-draggable',
		drop: function( event, ui ) {
			
			//define items for use
			var drop_helper = $('.cptd-droppable-helper');
			var field_item = ui.draggable.clone(); 

			//on drop trigger actions
			field_item.find('.remove_item').addClass('active');

			// the name of the hidden field to append 
			var field_name = ui.draggable.find('label').data( 'field-name' );
			var field_key = ui.draggable.find('label').data('field-key');

			// append the hidden field and the 'Remove' helper div
			field_item.append('<div class="dashicons dashicons-no-alt cptd-remove-field"></div><input type="hidden" name="' + field_name + '[]" value="' + field_key + '"/>');
			
			//add this new item to the end of the droppable list
			drop_helper.before(field_item);
			drop_helper.removeClass('active');
			
			//trigger_remove_field_item_action();
			
		},
		over: function(event,ui){
			//when hovering over the droppable area, display the drop helper
			$('.cptd-fields-drop').find('.cptd-droppable-helper').addClass('active');
			
		},
		out: function(event,ui){
			$('.cptd-fields-drop').find('.cptd-droppable-helper').removeClass('active');
		}
	}); // end: droppable

	// sortable
	$( '.cptd-fields-drop' ).sortable( {
		items: '.cptd-search-widget-field',
		cursor: 'move',
		placeholder: 'cptd-search-fields-placeholder'
	});

	// onclick for removing fields from the droppable area
	$(document).on( 'click', 'div.cptd-remove-field', function() {
		$(this).closest( '.cptd-search-widget-field' )
			.hide( 'slow', function() { $(this).remove() } );
	});

} // end: initSearchWidget()

/**
 * Callback for field checkbox onchange for search widget form
 * 
 * @param 	jQuery 	$			The main jQuery object
 * @param 	input 	checkbox	The field checkbox being toggled
 */
function toggleSearchFilterDetails( $, checkbox ) {

	// whether we're checking the checkbox (if false, we are unchecking)
	var on = $( checkbox ).prop('checked');

	// the main container for this field
	var $container = $( checkbox ).closest( '.cptd-search-widget-field' );

	// the div we are showing or hiding
	var $target = $container.find('.field-type-select');

	// for turning on
	if( on ) {
		$target.css({display: 'block'});
		$container.addClass('cptd-highlight');

	} // end if: turning on

	// for turning off
	else {
		$target.css({display: 'none'});
		$container.removeClass('cptd-highlight');
	} // end else: turning off

} // end: toggleSearchFilterDetails

/**
 * Onclick for 'Show Fields' link to show/hide the fields container
 */
function toggleFieldsArea( $, elem ) {
	
	// the clicked link
	var $link = $( elem );

	// the parent container we are inside
	$container = $( elem ).closest('.cptd-draggable-fields-container');

	// the checkbox container to toggle
	var $checkboxes = $link.siblings( '.cptd-fields-area' );

	// the droppable area
	var $drop = $container.find('.ui-droppable');

	// if the checkboxes are currently visible, then hide them
	if( 'block' == $checkboxes.css('display') ) {

		$drop.css( { display: 'none' } );
		$checkboxes.css( { display: 'none' } );
		$link.html( 'Show Fields' );
	}

	// if the checkboxes are currently hidden, then show them
	else {
		if( $drop.length > 0 ) $drop.css( { display: 'block' } );
		$checkboxes.css( { display: 'block' } );
		$link.html( 'Hide Fields' );
	}

} // end: toggleFieldsArea()

/**
 * Callback for taxonomy checkbox onchange
 * 		- Makes an ajax call to load terms checkboxes if first time activated
 * 		- Toggles the terms checkbox area open or closed each additional time
 * 
 * @param 	jQuery 	$			The main jQuery object
 * @param 	input 	checkbox	The taxonomy checkbox being toggled
 */
function toggleTaxonomyTermDetails( $, checkbox ) {

	// the widget ID
	var widgetId = $(checkbox).closest('.cptd-widget-form').data('widget-id');
	var widgetNumber = $(checkbox).closest('.cptd-widget-form').data('widget-number');

	// the taxonomy post ID being toggled
	var taxId = $( checkbox ).val();

	// whether we're checking the checkbox (if false, we are unchecking)
	var on = $( checkbox ).prop('checked');

	// the terms checkboxes container for the taxonomy being checked or unchecked (may not exist)
		$termsDiv = $( checkbox ).closest('#cptd-random-posts-form')
			.find('div.cptd-terms-list[data-tax-id=' + taxId + ']');

	// if we're turning off the taxonomy checkbox
	if( ! on ) {

		if( $termsDiv.length > 0 ) {
			$termsDiv.css( {display: 'none'} );
		}
		return;
	}

	// if we are turning on the taxonomy checkbox
	else {

		// check if terms checkboxes already exist
		if(  $termsDiv.length > 0 ) {
			$termsDiv.css( {display: 'block'} );
			return;
		}

		// if not, do an AJAX request to get the terms checkboxes HTML		
		$.ajax( {
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cptd_toggle_taxonomy_term_details',
				widget_id: widgetId,
				widget_number: widgetNumber,
				tax_id: taxId,
			},
			success: function( data ) {

				$label = $( checkbox ).closest('label');
				//var html = $.parseHTML( data )
				$( data ).insertAfter( $label );
			}
		}); // end: ajax
	} // end else: turning checkbox on

} // end: toggleTaxonomyTermDetails()