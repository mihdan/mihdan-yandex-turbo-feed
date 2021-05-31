<?php
/**
 * @package mihdan-yandex-turbo-feed
 */
namespace Mihdan\YandexTurboFeed;
/**
 * Class Helpers
 */
class Utils {
	public function __construct() {
	}

	/**
	 * Получить имя домена для сайта.
	 *
	 * @return string
	 */
	public function get_site_domain() {
		return parse_url( get_bloginfo_rss( 'url' ), PHP_URL_HOST );
	}

	/**
	 * Get plugin slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return MIHDAN_YANDEX_TURBO_FEED_SLUG;
	}

	/**
	 * Get plugin URL.
	 *
	 * @return string
	 */
	public function get_url() {
		return MIHDAN_YANDEX_TURBO_FEED_URL;
	}

	/**
	 * Get plugin path.
	 *
	 * @return string
	 */
	public function get_path() {
		return MIHDAN_YANDEX_TURBO_FEED_PATH;
	}

	/**
	 * Get plugin main file.
	 *
	 * @return string
	 */
	public function get_file() {
		return MIHDAN_YANDEX_TURBO_FEED_FILE;
	}

	/**
	 * Get post type slug.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return substr( $this->get_slug(), 0, 19 );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return MIHDAN_YANDEX_TURBO_FEED_VERSION;
	}

	/**
	 * Changes array of items into string of items, separated by comma and sql-escaped
	 *
	 * @see https://coderwall.com/p/zepnaw
	 * @global wpdb       $wpdb
	 *
	 * @param mixed|array $items  item(s) to be joined into string.
	 * @param string      $format %s or %d.
	 *
	 * @return string Items separated by comma and sql-escaped
	 */
	public static function prepare_in( $items, $format = '%s' ) {
		global $wpdb;

		$items    = (array) $items;
		$how_many = count( $items );
		if ( $how_many > 0 ) {
			$placeholders    = array_fill( 0, $how_many, $format );
			$prepared_format = implode( ',', $placeholders );
			$prepared_in     = $wpdb->prepare( $prepared_format, $items ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$prepared_in = '';
		}

		return $prepared_in;
	}

	/**
	 * Get all public meta fields.
	 *
	 * @return array
	 * @license https://wordpress.stackexchange.com/questions/58834/echo-all-meta-keys-of-a-custom-post-type
	 */
	public static function get_unique_public_meta() {
		global $wpdb;

		$cache_key = MIHDAN_YANDEX_TURBO_FEED_SLUG . '_unique_public_meta';

		// Get data from transient cache.
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$data = [
			'random' => 'random',
		];

		$black_list = [
			'_edit_l',
			'_oembed',
			'_custom',
			'_wp_old',
			'_wp_att',
			'_wp_pag',
			'_mihdan',
			'mihdan_',
		];

		$not_in = self::prepare_in( $black_list );

		$sql = "SELECT meta_key
				FROM {$wpdb->postmeta}
				WHERE SUBSTRING(meta_key, 1, 7) NOT IN ({$not_in})
				ORDER BY meta_key
				LIMIT 5000";

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$meta = $wpdb->get_col( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		$meta = array_unique( $meta );

		if ( $meta ) {
			foreach ( $meta as $item ) {
				$data[ $item ] = $item;
			}
		}

		// Save data to transient cache.
		set_transient( $cache_key, $data, 1 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Получает все зарегистрированные шорткоды.
	 *
	 * @return array
	 */
	public static function get_unique_public_shortcodes() {
		global $shortcode_tags;

		if ( is_array( $shortcode_tags ) ) {
			return array_keys( $shortcode_tags );
		}

		return [];
	}
}