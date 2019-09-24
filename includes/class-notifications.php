<?php
/**
 * @package mihdan-yandex-turbo-feed
 */
namespace Mihdan\YandexTurboFeed;
use WPTRT\AdminNotices\Notices;
/**
 * Class Notifications
 *
 * @package Mihdan_Yandex_Turbo_Feed
 */
class Notifications {
	const URL = 'https://wordpress.org/support/plugin/mihdan-yandex-turbo-feed/reviews/?rate=5#new-post';
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var Notices
	 */
	private $notices;

	public function __construct( $settings ) {

		$this->settings = $settings;
		$this->notices  = new Notices();

		$template  = '<p>';
		$template .= __( 'Hello!', 'mihdan-yandex-turbo-feed' );
		$template .= '<br />';
		$template .= __( 'We are very pleased that you by now have been using the <strong>Mihdan Yandex Turbo Feed</strong> plugin a few days. Please <a href="" target="_blank">rate plugin</a>. It will help us a lot.', 'mihdan-yandex-turbo-feed' );
		$template .= '</p>';

		$this->notices->add(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			false,
			$template
		);

		$this->notices->boot();
	}
}

// eol.
