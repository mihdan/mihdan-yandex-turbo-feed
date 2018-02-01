<?php
/**
 * Страница настроек
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mihdan_Yandex_Turbo_Feed_Admin_Settings' ) ) {

	/**
	 * Class Mihdan_Yandex_Turbo_Feed
	 */
	class Mihdan_Yandex_Turbo_Feed_Admin_Settings {

		private static $instance;

		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		private function __construct() {
			$this->add_settings();
		}

		private function add_settings() {
			$wposa_obj = new WP_OSA();
			$wposa_obj->add_section(
				array(
					'id'    => 'wposa_basic',
					'title' => __( 'Turbo Pages', MIHDAN_YANDEX_TURBO_FEED_SLUG ),
				)
			);
		}
	}

	function mihdan_yandex_turbo_feed_admin_settings() {
		return Mihdan_Yandex_Turbo_Feed_Admin_Settings::get_instance();
	}

	mihdan_yandex_turbo_feed_admin_settings();
}