<?php
namespace Mihdan_Yandex_Turbo_Feed;
/**
 * Class Assets
 */
class Assets {
	public function __construct() {
		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function assets() {
		wp_enqueue_script( MIHDAN_YANDEX_TURBO_FEED_SLUG, MIHDAN_YANDEX_TURBO_FEED_URL . 'admin/js/app.js', array( 'wp-util' ), filemtime( MIHDAN_YANDEX_TURBO_FEED_PATH . 'admin/js/app.js' ) );
	}
}