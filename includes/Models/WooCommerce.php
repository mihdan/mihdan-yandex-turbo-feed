<?php
/**
 * Дополнительные настройки для WooCommerce.
 *
 * @package mihdan-yandex-turbo-feed
 */

namespace Mihdan\YandexTurboFeed\Models;

use Mihdan\YandexTurboFeed\Settings;
use Mihdan\YandexTurboFeed\Utils;
use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Класс WooCommerce.
 */
class WooCommerce {
	/**
	 * @var Utils;
	 */
	private Utils $utils;

	/**
	 * @var Settings;
	 */
	private Settings $settings;

	/**
	 * Конструктор класса.
	 *
	 * @param Utils $utils       Утилиты.
	 * @param Settings $settings Настройки.
	 */
	public function __construct( Utils $utils, Settings $settings ) {
		$this->utils    = $utils;
		$this->settings = $settings;
	}

	/**
	 * Инициализирует хуки.
	 *
	 * @return void
	 */
	public function setup_hooks(): void {
		add_filter( 'mihdan_yandex_turbo_feed_feed_settings', [ $this, 'add_feed_settings_tab' ] );
		add_filter( 'mihdan_yandex_turbo_feed_item_content', [ $this, 'add_product_excerpt' ], 10, 3 );
	}

	/**
	 * Добавляет вкладку WooCommerce в настройки ленты.
	 *
	 * @param FieldsBuilder $feed_settings Экземпляр класса полей.
	 *
	 * @return FieldsBuilder
	 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
	 */
	public function add_feed_settings_tab( FieldsBuilder $feed_settings ): FieldsBuilder {
		$feed_settings->addTab(
			'woocommerce',
			[
				'placement' => 'left',
				'label'     => __( 'WooCommerce', 'mihdan-yandex-turbo-feed' ),
			]
		)
			->addTrueFalse(
				$this->utils->get_slug() . '_woocommerce_use_product_excerpt',
				array(
					'label'         => __( 'Use excerpt', 'mihdan-yandex-turbo-feed' ),
					'default_value' => false,
					'ui'            => true,
					'instructions'  => __( 'This adds a product excerpt before the full product content', 'mihdan-yandex-turbo-feed' ),
				)
			);

		return $feed_settings;
	}

	/**
	 * Добавляет краткое описание товара над полным текстом во фронтенде.
	 *
	 * @param string $content    Содержимое по умолчанию.
	 * @param int    $product_id Идентификатор товара.
	 * @param int    $feed_id    Идентификатор ленты.
	 *
	 * @return string
	 */
	public function add_product_excerpt( string $content, int $product_id, int $feed_id ): string {
		if ( get_post_type( $product_id ) !== 'product' ) {
			return $content;
		}

		if ( ! $this->settings->get_option( 'woocommerce_use_product_excerpt', $feed_id ) ) {
			return $content;
		}

		return wpautop( get_the_excerpt( $product_id ) ) . $content;
	}
}