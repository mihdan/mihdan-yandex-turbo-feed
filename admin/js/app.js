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
	}
);

// eol.
