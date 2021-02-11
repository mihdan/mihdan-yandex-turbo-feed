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

namespace Mihdan\YandexTurboFeed;

/**
 * Plugin Name: Mihdan: Yandex Turbo Feed
 * Plugin URI: https://www.kobzarev.com/projects/yandex-turbo-feed/
 * Description: Плагин создаёт настраиваемые ленты для сервиса Яндекс Турбо
 * Version: 1.3.5
 * Author: Mikhail Kobzarev
 * Author URI: https://www.kobzarev.com/
 * License: GNU General Public License v2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mihdan-yandex-turbo-feed
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-yandex-turbo-feed/
 * GitHub Branch:     master
 * Requires WP:       5.0
 * Requires PHP:      5.6.20
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Слаг плагина
define( 'MIHDAN_YANDEX_TURBO_FEED_SLUG', 'mihdan_yandex_turbo_feed' );
define( 'MIHDAN_YANDEX_TURBO_FEED_VERSION', '1.3.5' );
define( 'MIHDAN_YANDEX_TURBO_FEED_PATH', __DIR__ );
define( 'MIHDAN_YANDEX_TURBO_FEED_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MIHDAN_YANDEX_TURBO_FEED_FILE', __FILE__ );

/**
 * Init plugin class on plugin load.
 */
static $plugin;

if ( ! isset( $plugin ) ) {
	require_once MIHDAN_YANDEX_TURBO_FEED_PATH . '/vendor/autoload.php';
	require_once MIHDAN_YANDEX_TURBO_FEED_PATH . '/vendor/advanced-custom-fields/acf.php';
	$plugin = new Main();
}

// eof;
