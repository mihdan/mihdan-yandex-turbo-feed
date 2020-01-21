<?php
/**
 * @package mihdan-yandex-turbo-feed
 * @link https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 */
namespace Mihdan\YandexTurboFeed;

class SiteHealth {
	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

		$this->hooks();
	}

	public function hooks() {
		add_filter( 'site_status_tests', [ $this, 'add_site_status_tests' ] );
		add_filter( 'debug_information', [ $this, 'add_debug_information' ] );
	}

	public function add_site_status_tests( $tests ) {
		$tests['direct'][ MIHDAN_YANDEX_TURBO_FEED_SLUG ] = array(
			'label' => __( 'My Caching Test' ),
			'test'  => [ $this, 'myplugin_caching_test' ],
		);

		return $tests;
	}

	function myplugin_caching_test() {
		$result = array(
			'label'       => __( 'Caching is enabled' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance' ),
				'color' => 'red',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Caching can help load your site more quickly for visitors.' )
			),
			'actions'     => '',
			'test'        => 'caching_plugin',
		);

		if ( 1 ) {
			$result['status'] = 'critical';
			$result['label'] = __( 'Yandex Turbo' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Caching is not currently enabled on your site. Caching can help load your site more quickly for visitors.' )
			);
			$result['actions'] .= sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( admin_url( 'admin.php?page=cachingplugin&action=enable-caching' ) ),
				__( 'Enable Caching' )
			);
		}

		return $result;
	}

	public function add_debug_information( $debug_info ) {
		$debug_info[ MIHDAN_YANDEX_TURBO_FEED_SLUG ] = array(
			'label'  => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
			'fields' => array(
				'license' => array(
					'label'   => __( 'DOMDocument', 'mihdan-yandex-turbo-feed' ),
					'value'   => class_exists( '\DOMDocument' ),
					'private' => true,
				),
			),
		);

		return $debug_info;
	}

}

// eol.
