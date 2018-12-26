<?php
/**
 * Fired when the plugin is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Удалить все настройки плагина.
delete_option( 'mihdan_yandex_turbo_feed' );
delete_option( 'mihdan_yandex_turbo_feed-transients' );

// eof;
