<?php
/**
 *
 */
namespace Mihdan_Yandex_Turbo_Feed;

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
	private $redux;

	/**
	 * @var boolean
	 */
	private $is_show;

	public function __construct( $redux ) {

		$this->redux = $redux;

		$this->hooks();
	}
	public function hooks() {
		add_action( 'admin_notices', array( $this, 'stars' ) );
		add_action( 'wp_ajax_mihdan_yandex_turbo_feed_hide_notice', array( $this, 'hide_notice' ) );
	}
	public function is_show() {
		if ( null === $this->is_show ) {
			$time = $this->redux->get_option( 'rate_time' );
			if ( ! $time ) {
				$this->redux->set_option( 'rate_time', time() + DAY_IN_SECONDS );
				$this->is_show = false;
			} else {
				$this->is_show = time() > $time;
			}
		}

		return $this->is_show;
	}

	public function stars() {
		if ( ! $this->is_show() ) {
			return;
		}
		?>
		<div class="notice notice-info">
			<p><?php _e( 'Hello!<br />We are very pleased that you by now have been using the <b>Mihdan Yandex Turbo Feed</b> plugin a few days.<br />Please rate plugin. It will help us a lot.', 'mihdan-yandex-turbo-feed' ); ?></p>
			<p>
				<a target="_blank" class="button button-primary js-mytf-hide-notice" data-time="<?php echo esc_attr( MONTH_IN_SECONDS ); ?>" href="<?php echo esc_url( self::URL ); ?>"><?php _e( 'Rate the plugin', 'mihdan-yandex-turbo-feed' ); ?></a>
				<button class="button button-secondary js-mytf-hide-notice" data-time="<?php echo esc_attr( WEEK_IN_SECONDS ); ?>" type="button"><?php _e( 'Remind later', 'mihdan-yandex-turbo-feed' ); ?></button>
				<button class="button button-secondary js-mytf-hide-notice" data-time="<?php echo esc_attr( YEAR_IN_SECONDS ); ?>" type="button"><?php _e( 'Don\'t show again', 'mihdan-yandex-turbo-feed' ); ?></button>
			</p>
			<p><b><?php _e( 'Thank you very much!', 'mihdan-yandex-turbo-feed' ); ?></b></p>
		</div>
		<?php
	}

	public function hide_notice() {
		$time = $_POST['time'];

		$this->redux->set_option( 'rate_time', time() + $time );

		wp_send_json_success(
			array(
				't' => $time,
			)
		);
	}
}

// eol.
