/**
 * MOON Core — admin scripts (media picker + colour picker).
 */
( function ( $ ) {
	'use strict';

	$( function () {
		$( '.moon-color-field' ).wpColorPicker();

		$( document ).on( 'click', '.moon-media-select', function ( event ) {
			event.preventDefault();

			var $field = $( this ).closest( '.moon-media-field' );
			var frame = wp.media( {
				title: 'انتخاب تصویر',
				button: { text: 'انتخاب' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$field.find( 'input[type="hidden"]' ).val( attachment.id );

				var preview = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				$field.find( '.moon-media-preview' ).html( '<img src="' + preview + '" alt="" style="max-width:120px;height:auto;display:block;margin-top:8px;" />' );
			} );

			frame.open();
		} );

		$( document ).on( 'click', '.moon-media-clear', function ( event ) {
			event.preventDefault();
			var $field = $( this ).closest( '.moon-media-field' );
			$field.find( 'input[type="hidden"]' ).val( '' );
			$field.find( '.moon-media-preview' ).empty();
		} );
	} );
} )( jQuery );
