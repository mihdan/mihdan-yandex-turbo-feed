<?php
/**
 * @link https://rudrastyh.com/wordpress/quick-edit-tutorial.html
 * @link https://rudrastyh.com/wordpress/bulk-edit.html
 */

namespace Mihdan\YandexTurboFeed\Models;

use Mihdan\YandexTurboFeed\Settings;

class Feed {
	/**
	 * Слаг/идентификатор типа записи.
	 */
	private const POST_TYPE = 'mihdan_yandex_turbo';

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

	private $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get post type slug.
	 *
	 * @return string
	 */
	public static function get_post_type(): string {
		return self::POST_TYPE;
	}

	public function setup_hooks(): void {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'manage_' . self::get_post_type() . '_posts_columns', [ $this, 'add_column' ] );
		add_action( 'manage_' . self::get_post_type() . '_posts_custom_column', [ $this, 'populate_column' ], 10, 2 );
	}

	/**
	 * Регистрция произвольных типов записей и таксономий.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Yandex Turbo', 'Post Type General Name', 'mihdan-yandex-turbo-feed' ),
			'singular_name'         => _x( 'Лента', 'Post Type Singular Name', 'mihdan-yandex-turbo-feed' ),
			'menu_name'             => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
			'name_admin_bar'        => __( 'Yandex Turbo Feed', 'mihdan-yandex-turbo-feed' ),
			'archives'              => __( 'Архивы', 'mihdan-yandex-turbo-feed' ),
			'attributes'            => __( 'Item Attributes', 'mihdan-yandex-turbo-feed' ),
			'parent_item_colon'     => __( 'Parent Item:', 'mihdan-yandex-turbo-feed' ),
			'all_items'             => __( 'All Feeds', 'mihdan-yandex-turbo-feed' ),
			'add_new_item'          => __( 'Add New Feed', 'mihdan-yandex-turbo-feed' ),
			'add_new'               => __( 'Add Feed', 'mihdan-yandex-turbo-feed' ),
			'new_item'              => __( 'New Item', 'mihdan-yandex-turbo-feed' ),
			'edit_item'             => __( 'Edit Feed', 'mihdan-yandex-turbo-feed' ),
			'update_item'           => __( 'Update Item', 'mihdan-yandex-turbo-feed' ),
			'view_item'             => __( 'View Feed', 'mihdan-yandex-turbo-feed' ),
			'view_items'            => __( 'View Items', 'mihdan-yandex-turbo-feed' ),
			'search_items'          => __( 'Search Feed', 'mihdan-yandex-turbo-feed' ),
			'not_found'             => __( 'Not found', 'mihdan-yandex-turbo-feed' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'mihdan-yandex-turbo-feed' ),
			'items_list'            => __( 'Items list', 'mihdan-yandex-turbo-feed' ),
			'items_list_navigation' => __( 'Items list navigation', 'mihdan-yandex-turbo-feed' ),
			'filter_items_list'     => __( 'Filter items list', 'mihdan-yandex-turbo-feed' ),
		);

		$rewrite = array(
			'slug'       => 'turbo',
			'with_front' => false,
			'pages'      => true,
			'feeds'      => false,
		);

		$args = array(
			'label'               => __( 'Лента', 'mihdan-yandex-turbo-feed' ),
			'description'         => __( 'Post Type Description', 'mihdan-yandex-turbo-feed' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 80,
			'menu_icon'           => 'dashicons-rss',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'page',
			'show_in_rest'        => false,
		);

		register_post_type( self::get_post_type(), $args );
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

		// Все записи удаляются из Турбо.
		$remove = $this->settings->get_option( 'remove_all_turbo_posts', $post_id, false ) === true;
		?>
		<ul>
			<?php if ( $remove ) : ?>
				<li class="mytf-post-status mytf-post-status--danger">
				<span
					class="dashicons dashicons-dismiss"
					title="<?php esc_html_e( 'Турбо-страницы для всех записей в RSS-ленте отключены', 'mihdan-yandex-turbo-feed' ); ?>">
				</span>
				</li>
			<?php else : ?>
				<li class="mytf-post-status mytf-post-status--success">
					<span
						class="dashicons dashicons-yes-alt"
						title="<?php esc_html_e( 'Турбо-страницы для всех записей в RSS-ленте включены', 'mihdan-yandex-turbo-feed' ); ?>">
					</span>
				</li>
			<?php endif; ?>
		</ul>
		<?php
	}
}