jQuery(document).ready( function($) {


	/*
	 * SETTINGS PAGE
	 * 	taxonomy and terms dropdown
	 */
	settings_taxonomy_ajax_update = function() {
		post_type = $('#ftp-post-type').val();

		$('#ftp-post-taxonomy').parent().html('<p id="ftp-post-taxonomy">Loading taxonomies ...</p>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ftp_get_object_taxonomies',
				post_type: post_type,
				source: 'settings'
			},
			complete: function( jqXHR, status ) {
				data = jqXHR.responseText;
				// Update the element with the data recieved from server
				$('#ftp-post-taxonomy').parent().html( data );
				// RE-ATTACH HANDLERS
				$('select#ftp-post-taxonomy').on( 'change', settings_tax_terms_ajax_update );
				// Update the terms
				settings_tax_terms_ajax_update();
			},
			dataType: 'json'
		});
	};
	$('select#ftp-post-type').ready( settings_taxonomy_ajax_update );
	$('select#ftp-post-type').on( 'change', settings_taxonomy_ajax_update );


	settings_tax_terms_ajax_update = function() {
		taxonomy = $('#ftp-post-taxonomy').val();
		$('#ftp-post-terms').parent().html('<p id="ftp-post-terms">Loading taxonomy terms ...</p>');
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ftp_get_taxonomy_terms',
				taxonomy: taxonomy,
				source: 'settings'
			},
			complete: function( jqXHR, status ) {
				data = jqXHR.responseText;
				// Update the element with the data recieved from server
				$('#ftp-post-terms').parent().html( data );
				$('#ftp-post-terms').prop('disabled', false);
			},
			dataType: 'json'
		});
	};
	$('select#ftp-post-taxonomy').on( 'change', settings_tax_terms_ajax_update );




	/*
	 * META FIELDS
	 *	taxonomy and terms dropdown
	 */
	metabox_taxonomy_ajax_update = function() {
		post_type = $('select#wprss_ftp_post_type').val();
		$('#wprss_ftp_post_taxonomy').parent().html('<p id="wprss_ftp_post_taxonomy">Loading taxonomies ...</p>');
		$('#wprss_ftp_post_terms').prop( 'disabled', true );
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ftp_get_object_taxonomies',
				post_type: post_type,
				source: 'meta',
				post_id: $('#wprss-ftp-post-id').attr('data-post-id'),
			},
			complete: function( jqXHR, status ) {
				data = jqXHR.responseText;
				// Update the element with the data recieved from server
				$('#wprss_ftp_post_taxonomy').parent().html( data );
				// RE-ATTACHED THE HANDLERS
				$('select#wprss_ftp_post_taxonomy').on( 'change', metabox_terms_ajax_update );
				// Update the terms
				metabox_terms_ajax_update();
			},
			dataType: 'json'
		});
	};
	$('select#wprss_ftp_post_type').ready( metabox_taxonomy_ajax_update );
	$('select#wprss_ftp_post_type').on( 'change', metabox_taxonomy_ajax_update );
	


	metabox_terms_ajax_update = function() {
		tax = ( $('#wprss_ftp_post_taxonomy').is('select') )? $('#wprss_ftp_post_taxonomy').val() : '';
		$('#wprss_ftp_post_terms').parent().html('<p id="wprss_ftp_post_terms">Loading taxonomies ...</p>');
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ftp_get_taxonomy_terms',
				taxonomy: tax,
				post_id: $('#wprss-ftp-post-id').attr('data-post-id'),
				source: 'meta'
			},
			complete: function( jqXHR, status ) {
				data = jqXHR.responseText;
				// Update the element with the data recieved from server
				$('#wprss_ftp_post_terms').parent().html( data );
			},
			dataType: 'json'
		});
	};
	$('select#wprss_ftp_post_terms').on( 'change', metabox_terms_ajax_update );


	var post_type_label = $('td label[for="wprss_ftp_post_type"]');
	var post_type_dropdown = $('#wprss_ftp_post_type');
	var original_post_type_label = post_type_label.text();

	var checkIfFeedItemPostType = function(){
		if ( post_type_dropdown.val() === 'wprss_feed_item' ) {
			post_type_label.html('You are importing into WP RSS Aggregator\'s <strong>Feed Items</strong>.<br/>'
								+ 'The Feed to Post settings for this feed source will <strong>not</strong> affect the items imported.');
		} else {
			post_type_label.text( original_post_type_label );
		}
	};
	
	post_type_dropdown.on( 'change', checkIfFeedItemPostType );
	checkIfFeedItemPostType();
	
});
