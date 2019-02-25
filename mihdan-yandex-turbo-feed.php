<?php
/**
 * Mihdan: Yandex Turbo Feed
 *
 * @package   mihdan-yandex-turbo-feed
 * @author    Mikhail Kobzarev
 * @link      https://github.com/mihdan/mihdan-yandex-turbo-feed/
 * @link      https://yandex.ru/support/webmaster/turbo/rss-elements.html
 * @copyright Copyright (c) 2017
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

/**
 * Plugin Name: Mihdan: Yandex Turbo Feed
 * Plugin URI: https://www.kobzarev.com/projects/yandex-turbo-feed/
 * Description: Плагин генерирует фид для сервиса Яндекс Турбо
 * Version: 1.2
 * Author: Mikhail Kobzarev
 * Author URI: https://www.kobzarev.com/
 * License: GNU General Public License v2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mihdan-yandex-turbo-feed
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-yandex-turbo-feed/
 * GitHub Branch:     master
 * Requires WP:       4.6
 * Requires PHP:      5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mihdan_Yandex_Turbo_Feed' ) ) {

	// Слюг плагина
	define( 'MIHDAN_YANDEX_TURBO_FEED_SLUG', 'mihdan_yandex_turbo_feed' );
	define( 'MIHDAN_YANDEX_TURBO_FEED_VERSION', '1.2' );
	define( 'MIHDAN_YANDEX_TURBO_FEED_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'MIHDAN_YANDEX_TURBO_FEED_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

	/**
	 * Init plugin class on plugin load.
	 */

	static $plugin;

	if ( ! isset( $plugin ) ) {
		if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
			require_once MIHDAN_YANDEX_TURBO_FEED_PATH . 'vendor/autoload.php';
		} else {
			require_once MIHDAN_YANDEX_TURBO_FEED_PATH . 'vendor/autoload_52.php';
		}

		$plugin = new Mihdan_Yandex_Turbo_Feed_Main();
	}
}

// eof;
