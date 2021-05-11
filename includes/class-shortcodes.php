<?php
namespace Mihdan\YandexTurboFeed;

class Shortcodes {
	public function setup_hooks() {
		add_shortcode( 'mytf_button', [ $this, 'button_callback' ] );
	}

	public function button_callback( $atts ) {
		$atts = shortcode_atts(
			[
				'formaction'            => 'tel:+7 012 345-67-89',
				'data-background-color' => '#cccccc',
				'data-color'            => '#333333',
				'data-turbo'            => 'true',
				'data-primary'          => 'false',
				'value'                 => 'Отправить',
			],
			$atts
		);

		return sprintf(
			'<button formaction="%s" data-background-color="%s" data-color="%s" data-turbo="%s" data-primary="%s">%s</button>',
			$atts['formaction'],
			$atts['data-background-color'],
			$atts['data-color'],
			$atts['data-turbo'],
			$atts['data-primary'],
			$atts['value']
		);
	}
}