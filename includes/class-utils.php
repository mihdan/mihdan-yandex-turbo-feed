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
	 * Get post type slug.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return substr( $this->get_slug(), 0, 19 );
	}
}