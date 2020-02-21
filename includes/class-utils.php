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
}