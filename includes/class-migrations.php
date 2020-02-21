<?php
/**
 * @package mihdan-yandex-turbo-feed
 */
namespace Mihdan\YandexTurboFeed;

/**
 * Class Migrations
 *
 * @package Mihdan\YandexTurboFeed
 */
class Migrations {
	private $utils;
	private $settings;

	/**
	 * Migrations constructor.
	 *
	 * @param Utils    $utils
	 * @param Settings $settings
	 */
	public function __construct( Utils $utils, Settings $settings ) {
		$this->utils    = $utils;
		$this->settings = $settings;

		$this->up_1_3();
	}

	public function up_1_3() {

		// Если нет версии в базе - запустим миграцию на новый формат базы.
		if ( false !== get_option( $this->utils->get_slug() . '_version', false ) ) {
			return;
		}

		$old_settings = get_option( 'mihdan_yandex_turbo_feed', false );

		// Если есть настройки от старой версии плагина
		if ( false !== $old_settings ) {

			$args = [
				'post_type'   => $this->utils->get_post_type(),
				'post_title'  => 'All posts',
				'post_name'   => 'all-posts',
				'post_status' => 'publish',
				'meta_input'  => [
					// Feed.
					'mihdan_yandex_turbo_feed_charset'                    => $old_settings['feed_charset'],
					'mihdan_yandex_turbo_feed_orderby'                    => $old_settings['feed_orderby'],
					'mihdan_yandex_turbo_feed_order'                      => $old_settings['feed_order'],
					'mihdan_yandex_turbo_feed_total_posts'                => $old_settings['feed_total_posts'],
					'mihdan_yandex_turbo_feed_post_type'                  => $old_settings['feed_post_type'],
					'mihdan_yandex_turbo_feed_taxonomy'                   => $old_settings['feed_taxonomy'],

					// Channel.
					'mihdan_yandex_turbo_feed_channel_title'              => $old_settings['channel_title'],
					'mihdan_yandex_turbo_feed_channel_link'               => $old_settings['channel_link'],
					'mihdan_yandex_turbo_feed_channel_description'        => $old_settings['channel_description'],
					'mihdan_yandex_turbo_feed_channel_language'           => $old_settings['channel_language'],

					// Images.
					'mihdan_yandex_turbo_feed_images_copyright'           => $old_settings['images_copyright'],

					// Share.
					'mihdan_yandex_turbo_feed_share_enable'               => $old_settings['share_enable'],
					'mihdan_yandex_turbo_feed_share_networks'             => $old_settings['share_networks'],

					// Comments.
					'mihdan_yandex_turbo_feed_comments_enable'            => $old_settings['comments_enable'],

					// Callback.
					'mihdan_yandex_turbo_feed_callback_enable'            => $old_settings['callback_enable'],
					'mihdan_yandex_turbo_feed_callback_send_to'           => $old_settings['callback_send_to'],
					'mihdan_yandex_turbo_feed_callback_agreement_company' => $old_settings['callback_agreement_company'],
					'mihdan_yandex_turbo_feed_callback_agreement_link'    => $old_settings['callback_agreement_link'],

					// Menu.
					'mihdan_yandex_turbo_feed_menu_enable'                => $old_settings['menu_enable'],

					// Related posts.
					'mihdan_yandex_turbo_feed_related_posts_enable'       => $old_settings['related_posts_enable'],
					'mihdan_yandex_turbo_feed_related_posts_infinity'     => $old_settings['related_posts_infinity'],
					'mihdan_yandex_turbo_feed_related_posts_total'        => $old_settings['related_posts_total'],

					// Search.
					'mihdan_yandex_turbo_feed_search_enable'              => $old_settings['search_enable'],
					'mihdan_yandex_turbo_feed_search_placeholder'         => $old_settings['search_placeholder'],

					// Rating.
					'mihdan_yandex_turbo_feed_rating_enable'              => $old_settings['rating_enable'],
					'mihdan_yandex_turbo_feed_rating_min'                 => $old_settings['rating_min'],
					'mihdan_yandex_turbo_feed_rating_max'                 => $old_settings['rating_max'],

					// Tables.
					'mihdan_yandex_turbo_feed_invisible_border_enable'    => $old_settings['invisible_border_enable'],

					// Access.
					'mihdan_yandex_turbo_feed_access_enable'              => $old_settings['access_enable'],
					'mihdan_yandex_turbo_feed_access_login'               => $old_settings['access_login'],
					'mihdan_yandex_turbo_feed_access_password'            => $old_settings['access_password'],
				],
			];

			$post_id = wp_insert_post( $args );

			if ( $post_id ) {
				// Удалим старые настройки.
				delete_option( 'mihdan_yandex_turbo_feed' );
			}

			// Set new plugin version.
			update_option( $this->utils->get_slug() . '_version', '1.3', false );
		}
	}

	public function down() {}
}
