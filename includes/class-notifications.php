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

	/**
	 * @var Utils
	 */
	private $utils;

	public function __construct( Utils $utils, Settings $settings ) {

		$this->utils    = $utils;
		$this->settings = $settings;
		$this->notices  = new Notices();

		$template  = '<p>';
		$template .= __( 'Hello!', 'mihdan-yandex-turbo-feed' );
		$template .= '<br />';
		/* translators: ссылка на голосование */
		$template .= sprintf( __( 'We are very pleased that you by now have been using the <strong>Mihdan Yandex Turbo Feed</strong> plugin a few days. Please <a href="%s" target="_blank">rate ★★★★★ plugin</a>. It will help us a lot.', 'mihdan-yandex-turbo-feed' ), self::URL );
		$template .= '</p>';

		$this->notices->add(
			'review_dismissed',
			false,
			$template,
			[
				'scope'         => 'user',
				'option_prefix' => $this->utils->get_slug(),
			]
		);

		$this->notices->boot();
	}
}

// eol.
