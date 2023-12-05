<?php
/**
 * Интеграция с плагином Lite Video Embed.
 *
 * @package mihdan-yandex-turbo-feed
 * @link https://yandex.ru/dev/turbo/doc/rss/elements/multimedia.html
 * @link https://dzen.ru/help/website/rss-modify.html#publication
 */

namespace Mihdan\YandexTurboFeed\Models;

/**
 * Класс WooCommerce.
 */
class LiteVideoEmbed {
	/**
	 * Ищем плеер от плагина.
	 */
	private const PLAYER_REGEX = '#<figure[^>]+><div[^>]+>[\s]+<lite-youtube class="[^"]+" video-id="([^"]+)"[^>]+>.*?<\/figure>#si';

	/**
	 * Меняем плеер на стандартный от YouTube.
	 */
	private const PLAYER_REPLACEMENT = '<iframe width="560" height="315" src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

	/**
	 * Инициализирует хуки.
	 *
	 * @return void
	 */
	public function setup_hooks(): void {
		add_filter( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'video_embed' ) );
	}

	/**
	 * Интеграция с плагином Lite Video Embed.
	 *
	 * @param string $content Содержимое записи.
	 *
	 * @return string
	 */
	public function video_embed( string $content ): string {

		$content = preg_replace( self::PLAYER_REGEX, self::PLAYER_REPLACEMENT, $content );

		return $content;
	}
}