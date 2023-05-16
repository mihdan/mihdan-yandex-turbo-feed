/**
 * global jQuery
 */
jQuery(
	function ( $ ) {

		'use strict';

		var wp = window.wp || [];

		$( '.js-mytf-hide-notice' ).on(
			'click',
			function ( e ) {

				var $button = $( this );

				$button.parents( '.notice' ).remove();

				wp.ajax.send(
					"mihdan_yandex_turbo_feed_hide_notice",
					{
						success: function ( data ) {
							console.log( data );
						},
						error:   function ( data ) {
							console.log( data );
						},
						data: {
							nonce: '31231',
							time: $button.data( 'time' )
						}
					}
				);
			}
		);

		const wp_inline_edit_function = inlineEditPost.edit;

		inlineEditPost.edit = function( post_id ) {

			wp_inline_edit_function.apply( this, arguments );

			if ( typeof( post_id ) == 'object' ) {
				post_id = parseInt( this.getId( post_id ) );
			}

			const edit_row = $( '#edit-' + post_id );
			const post_row = $( '#post-' + post_id );

			const exclude = ( $( '.column-mihdan_yandex_turbo_feed .mytf-post-status--warning', post_row ).size() === 1 );
			const remove  = ( $( '.column-mihdan_yandex_turbo_feed .mytf-post-status--danger', post_row ).size() === 1 );

			$( ':input[name="mihdan_yandex_turbo_feed_exclude"]', edit_row ).prop( 'checked', exclude );
			$( ':input[name="mihdan_yandex_turbo_feed_remove"]', edit_row ).prop( 'checked', remove );
		}
	}
);

// eol.
