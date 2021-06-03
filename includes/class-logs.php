<?php
namespace Mihdan\YandexTurboFeed;

class Logs {
	public function setup_hooks() {
	}

	public function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : ''; // @codingStandardsIgnoreLine
	}
}