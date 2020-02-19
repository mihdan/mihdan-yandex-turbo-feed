<?php
/**
 * Fired when the plugin is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove all plugin options.
delete_option( 'mihdan_yandex_turbo_feed' );
delete_option( 'mihdan_yandex_turbo_feed-transients' );
delete_option( 'mihdan_yandex_turbo_feed_version' );

delete_metadata( 'user', 0, 'mihdan_yandex_turbo_feed_review_dismissed', '', true );

// Delete all posts.
$args = [
	'post_type'        => 'mihdan_yandex_turbo',
	'numberposts'      => -1,
	'post_status'      => 'any',
	'suppress_filters' => true,
	'fields'           => 'ids',
];

$feeds = get_posts( $args );

foreach ( $feeds as $feed_id ) {
	wp_delete_post( $feed_id, true );
}

// eof;
