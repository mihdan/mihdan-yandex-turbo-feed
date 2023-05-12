<?php
/**
 * @link https://rudrastyh.com/wordpress/quick-edit-tutorial.html
 * @link https://rudrastyh.com/wordpress/bulk-edit.html
 */

namespace Mihdan\YandexTurboFeed;

class BulkEdit {
	/**
	 * Идентификатор колонки.
	 */
	const COLUMN_ID = 'mihdan_yandex_turbo_feed';

	/**
	 * Название колонки.
	 */
	const COLUMN_NAME = 'Yandex Turbo';

	/**
	 * Иконка колонки.
	 */
	const COLUMN_ICON = '<span title="Yandex Turbo"><svg width="22" height="22" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#777fa8" d="M36 68.4c17.894 0 32.4-14.506 32.4-32.4S53.894 3.6 36 3.6 3.6 18.106 3.6 36 18.106 68.4 36 68.4Z"/><path fill="#777fa8" d="M29.939 46.154c1.336 1.336 1.625 2.954 1.347 4.458a6.267 6.267 0 0 1-.65 1.815c-.356.68-.843 1.387-1.468 2.18-.996 1.263-2.394 2.796-3.945 4.347-.924.924 1.483 2.24.587 2.96-.426.344-3.012.499-3.479.788-3.043 1.888-6.9 2.688-11.055 3.113l-5.721.585.59-5.721c.378-3.669 1.929-8.786 3.93-11.93.219-.344.44-2.275.68-2.595.779-1.037 1.483 1.464 2.424.523 1.582-1.581 3.037-2.927 4.228-3.884.777-.625 1.465-1.107 2.138-1.464 2.103-1.115 4.098-1.47 6.265.698.945.944 1.28 1.806 1.375 2.754.945.1 1.824.444 2.754 1.373Z"/><path fill="#fff" d="M21.375 46.86c-.454-.454-2.947 1.715-5.435 4.202-2.487 2.487-4.707 5.9-5.14 10.097 5.14-.525 7.556-2.497 10.043-4.983 2.488-2.488 4.716-5.133 4.262-5.586-.12-.12-1.713-.357-2.616-1.259-.847-.847-1.008-2.364-1.114-2.47Z"/><path fill="#fff" d="M30.286 32.862c1.386-1.767 3.07-3.635 4.992-5.567 7.519-7.56 15.854-8.01 16.647-7.21.793.8.295 9.072-7.224 16.633-1.935 1.945-3.804 3.641-5.57 5.031v5.303c0 2.184.05 2.256-1.328 3.275-.738.546-3.912 3.15-7.317 5.233-.838.513-1.528.089-.996-.662 1.437-2.028 2.474-3.838 3.11-5.432.582-1.46.58-2.76-.008-3.895-2.173.692-3.991.488-5.327-.815-1.347-1.314-1.547-3.145-.829-5.348-1.14-.597-2.445-.603-3.915-.017-1.594.636-3.405 1.672-5.433 3.108-.751.532-1.176-.157-.663-.995 2.084-3.404 4.69-6.577 5.235-7.315 1.02-1.378 1.092-1.327 3.276-1.327h5.35Z"/></svg></span>';

	/**
	 * @var Utils
	 */
	private $utils;

	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var array
	 */
	private $post_types;

	public function __construct( Utils $utils, Settings $settings ) {
		$this->utils    = $utils;
		$this->settings = $settings;
	}

	/**
	 * Инициализация хуков.
	 *
	 * @return void
	 */
    public function setup_hooks(): void {
		add_action(
			'init',
			function () {
				$args = array(
					'public' => true,
				);

				$this->post_types = wp_list_pluck( get_post_types( $args, 'objects' ), 'name' );

				// Удалить сами ленты из доступных CPT.
				if ( isset( $this->post_types[ $this->utils->get_post_type() ] ) ) {
					unset( $this->post_types[ $this->utils->get_post_type() ] );
				}

				foreach ( $this->post_types as $post_type ) {
					add_filter( 'manage_' . $post_type . '_posts_columns', [ $this, 'add_column' ] );
					add_action( 'manage_' . $post_type . '_posts_custom_column', [ $this, 'populate_column' ], 10, 2 );
				}
			},
			100
		);

	    // Быстрое редактирование.
		add_action( 'quick_edit_custom_box', [ $this, 'quick_edit_fields' ], 10, 2 );

		// Массовое редактирование.
		add_action( 'bulk_edit_custom_box', [ $this, 'quick_edit_fields' ], 10, 2 );

	    // Сохранение быстрого и массового редактирования.
		add_action( 'save_post', [ $this, 'save_post' ] );
    }

	/**
	 * Сохранение быстрого и массового редактирования.
	 *
	 * @param int $post_id Идентификатор записи.
	 *
	 * @return void
	 */
	public function save_post( int $post_id ): void {

		if ( ! ( isset( $_REQUEST[ '_wpnonce' ] ) && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'bulk-posts' ) ) && ! ( isset( $_POST[ '_inline_edit' ] ) && wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$exclude = $this->utils->get_slug() . '_exclude';
		$remove  = $this->utils->get_slug() . '_remove';

		update_post_meta(
			$post_id,
			$exclude,
			(int) $_REQUEST[ $exclude ] ?? 0
		);

		update_post_meta(
			$post_id,
			$remove,
			(int) $_REQUEST[ $remove ] ?? 0
		);
	}

	/**
	 * Добавляет поля в Quick Edit/Bulk Edit.
	 *
	 * @param string $column_name Имя колонки.
	 * @param string $post_type   Название типа записи.
	 *
	 * @return void
	 */
	public function quick_edit_fields( string $column_name, string $post_type ): void {
		if ( self::COLUMN_ID !== $column_name ) {
			return;
		}

		?>
		<fieldset class="inline-edit-col-last">
			<legend class="inline-edit-legend"><?php echo esc_html( self::COLUMN_NAME ); ?></legend>
			<div class="inline-edit-col">
				<label>
					<input value="1" type="checkbox" name="<?php echo esc_attr( $this->utils->get_slug() . '_exclude' ); ?>">
					<span class="checkbox-title">Исключить из ленты</span>
				</label>
				<label>
					<input value="1" type="checkbox" name="<?php echo esc_attr( $this->utils->get_slug() . '_remove' ); ?>">
					<span class="checkbox-title">Удалить турбо-страницу</span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Заполняет новую колонку.
	 *
	 * @param string $column_name Имя колонки.
	 * @param int    $post_id     Идентификатор записи.
	 *
	 * @return void
	 */
	public function populate_column( string $column_name, int $post_id ): void {
		if ( self::COLUMN_ID !== $column_name ) {
			return;
		}
		// Запись исключена из ленты.
		$exclude = $this->settings->get_option( 'exclude', $post_id, false ) === true;

		// Запись удаляется из Турбо.
		$remove = $this->settings->get_option( 'remove', $post_id, false ) === true;
		?>
		<ul>
			<li class="mytf-post-status <?php if ( ! $exclude ) { echo 'mytf-post-status--success'; } ?>">
				<span
					class="dashicons dashicons-yes-alt"
					title="<?php esc_html_e( 'Запись добавлена в RSS ленту', 'mihdan-yandex-turbo-feed' ); ?>">
				</span>
			</li>
			<li class="mytf-post-status <?php if ( $exclude ) { echo 'mytf-post-status--warning'; } ?>">
				<span
					class="dashicons dashicons-info"
					title="<?php esc_html_e( 'Запись исключена из ленты', 'mihdan-yandex-turbo-feed' ); ?>">
				</span>
			</li>
			<li class="mytf-post-status <?php if ( $remove && ! $exclude ) { echo 'mytf-post-status--danger'; } ?>">
				<span
					class="dashicons dashicons-dismiss"
					title="<?php esc_html_e( 'Турбо-страница отключена', 'mihdan-yandex-turbo-feed' ); ?>">
				</span>
			</li>
		</ul>
		<?php
	}

	/**
	 * Добавляет новую колонку.
	 *
	 * @param array $columns Колонки по умолчанию.
	 *
	 * @return array
	 */
	public function add_column( array $columns ): array {
		$columns[ self::COLUMN_ID ] = self::COLUMN_ICON;

		return $columns;
	}
}