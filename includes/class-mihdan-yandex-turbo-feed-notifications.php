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
		<div class="notice notice-info">
			<p>Hello!<br />
				We are very pleased that you by now have been using the <b>Mihdan Yandex Turbo Feed</b> plugin a few days.<br />
			Please rate plugin. It will help us a lot.
			</p>
			<p>
				<a target="_blank" class="button button-primary" href="https://ru.wordpress.org/plugins/mihdan-yandex-turbo-feed/reviews/?rate=5#new-post">Rate the plugin</a>
				<button class="button button-link" type="button">Remind later</button>
				<button class="button button-link" type="button">Don't show again</button>
			</p>
			<p><b>Thank you very much!</b></p>
		</div>
		<?php
	}
}