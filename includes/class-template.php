<?php
/**
 * @package mihdan-yandex-turbo-feed
 */
namespace Mihdan\YandexTurboFeed;

/**
 * Class Template
 *
 * @package Mihdan\YandexTurboFeed
 */
class Template {
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * Template constructor.
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;

		$this->hooks();
	}

	/**
	 * Hooks Init
	 */
	public function hooks() {
		add_action( 'template_redirect', array( $this, 'render' ) );
	}

	/**
	 * Render Feed Template.
	 */
	public function render() {
		if ( is_singular( $this->settings->cpt_key ) ) {
			require MIHDAN_YANDEX_TURBO_FEED_PATH . '/templates/feed.php';
			die;
		}
	}
}

// eol.
