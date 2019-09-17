<?php
/**
 *
 */

class Mihdan_Yandex_Turbo_Feed_Notifications {
	public function __construct() {
		$this->hooks();
	}
	public function hooks() {
		add_action( 'admin_notices', array( $this, 'stars' ) );
	}
	public function stars() {
		?>
		<div class="notice notice-info is-dismissible">
			<p><?php _e( 'Hello!<br />We are very pleased that you by now have been using the <b>Mihdan Yandex Turbo Feed</b> plugin a few days.<br />Please rate plugin. It will help us a lot.', 'mihdan-yandex-turbo-feed' ); ?></p>
			<p><a target="_blank" class="button button-primary" href="https://ru.wordpress.org/plugins/mihdan-yandex-turbo-feed/reviews/?rate=5#new-post"><?php _e( 'Rate the plugin', 'mihdan-yandex-turbo-feed' ); ?></a></p>
			<p><b><?php _e( 'Thank you very much!', 'mihdan-yandex-turbo-feed' ); ?></b></p>
			<div style="display: none">
				<button class="button button-link" type="button">Remind later</button>
				<button class="button button-link" type="button">Don't show again</button>
			</div>
		</div>
		<?php
	}
}