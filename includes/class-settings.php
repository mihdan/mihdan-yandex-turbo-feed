<?php
/**
 * @package mihdan-yandex-turbo-feed
 */

namespace Mihdan\YandexTurboFeed;

use StoutLogic\AcfBuilder\FieldsBuilder;

class Settings {
	/**
	 * @var Utils
	 */
	private $utils;

	/**
	 * @var array $post_types
	 */
	private $post_types;

	/**
	 * @var array $taxonomies
	 */
	private $taxonomies;

	/**
	 * @var array $share_networks
	 */
	private $share_networks;

	/**
	 * @var array $providers Массив провайдеров поиска.
	 */
	public $providers;

	/**
	 * @var array $languages Массив всех языков сайта.
	 */
	private $languages = array();

	/**
	 * @var string @language Дефолтный язык - из настроек сайта.
	 */
	private $language;

	/**
	 * Settings constructor.
	 *
	 * @param Utils $utils
	 */
	public function __construct( Utils $utils ) {
		$this->utils = $utils;
		$this->hooks();
	}

	public function setup() {
		// Список всех публичных CPT.
		$args = array(
			'public' => true,
		);

		$this->post_types = wp_list_pluck( get_post_types( $args, 'objects' ), 'label', 'name' );

		// Удалить сами ленты из доступных CPT.
		if ( isset( $this->post_types[ $this->utils->get_post_type() ] ) ) {
			unset( $this->post_types[ $this->utils->get_post_type() ] );
		}

		// Список всех зареганных таксономий.
		$args = array(
			'public' => true,
		);

		$this->taxonomies = wp_list_pluck( get_taxonomies( $args, 'objects' ), 'label', 'name' );

		// Список всех возможных переводов.
		$translations = wp_get_available_translations();

		$this->language = substr( get_bloginfo_rss( 'language' ), 0, 2 );

		if ( $translations ) {
			foreach ( $translations as $translation ) {
				// Нас интересует только двухбуквенный код языка.
				$key = substr( $translation['language'], 0, 2 );

				// Соберем массив: array( code => name ).
				$this->languages[ $key ] = $translation['native_name'];
			}
		}

		// Доступные соцсети для шеров.
		$this->share_networks = array(
			'facebook'      => __( 'Facebook', 'mihdan-yandex-turbo-feed' ),
			'google'        => __( 'Google', 'mihdan-yandex-turbo-feed' ),
			'odnoklassniki' => __( 'Odnoklassniki', 'mihdan-yandex-turbo-feed' ),
			'telegram'      => __( 'Telegram', 'mihdan-yandex-turbo-feed' ),
			'vkontakte'     => __( 'Vkontakte', 'mihdan-yandex-turbo-feed' ),
		);

		/**
		 * Провайдеры поиска.
		 */
		$this->providers = array(
			'site'   => array(
				'id'   => 'site',
				'name' => __( 'Site', 'mihdan-yandex-turbo-feed' ),
				'url'  => get_bloginfo_rss( 'url' ) . '/?s={text}',
			),
			'bing'   => array(
				'id'   => 'bing',
				'name' => __( 'Bing', 'mihdan-yandex-turbo-feed' ),
				'url'  => 'https://www.bing.com/search?q={text}%20site:' . $this->utils->get_site_domain(),
			),
			'yahoo'  => array(
				'id'   => 'yahoo',
				'name' => __( 'Yahoo', 'mihdan-yandex-turbo-feed' ),
				'url'  => 'https://search.yahoo.com/search?p={text}%20site:' . $this->utils->get_site_domain(),
			),
			'yandex' => array(
				'id'   => 'yandex',
				'name' => __( 'Yandex', 'mihdan-yandex-turbo-feed' ),
				'url'  => 'https://yandex.ru/search/?text={text}&site=' . $this->utils->get_site_domain(),
			),
			'google' => array(
				'id'   => 'google',
				'name' => __( 'Google', 'mihdan-yandex-turbo-feed' ),
				'url'  => 'https://google.com/search?q={text}%20site:' . $this->utils->get_site_domain(),
			),
		);
	}

	/**
	 * Init hooks.
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'registration' ) );
		add_action( 'init', array( $this, 'setup' ), 100 );
		add_action( 'init', array( $this, 'add_local_field_groups' ), 101 );
		add_filter( 'acf/settings/show_admin', array( $this, 'hide_acf_admin_menu' ) );
	}

	/**
	 * Скрыть меню ACF от нашего плагина.
	 *
	 * Если активирован оригинальный плагин ACF или ACF PRO,
	 * то меню скрыто не будет.
	 *
	 * @param bool $show_hide Флаг скрытости.
	 *
	 * @return bool
	 */
	public function hide_acf_admin_menu( $show_hide ) {
		$backtraces = debug_backtrace();

		if ( ! is_array( $backtraces ) ) {
			return $show_hide;
		}

		foreach ( $backtraces as $backtrace ) {
			if ( strpos( $backtrace['file'], 'mihdan-yandex-turbo-feed/vendor/advanced-custom-fields' ) ) {
				return false;
			}
		}

		return $show_hide;
	}

	/**
	 * Регистрция произвольных типов записей и таксономий.
	 */
	public function registration() {
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
			'with_front' => true,
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

		register_post_type( $this->utils->get_post_type(), $args );
	}

	/**
	 * Создание метабокосов и настроек плагина и записей
	 *
	 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
	 */
	public function add_local_field_groups() {

		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$post_settings = new FieldsBuilder(
			'post_settings',
			array(
				'title'    => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
				'position' => 'side',
			)
		);

		$post_settings
			->addTrueFalse( $this->utils->get_slug() . '_exclude' )
				->setLabel('Исключить из RSS ленты' )
				->setConfig( 'message', __( 'Исключить', 'mihdan-yandex-turbo-feed' ) )
				->setInstructions( __( 'При активации данной настройки запись НЕ будет попадать в RSS ленту.', 'mihdan-yandex-turbo-feed' ) )
			->addTrueFalse( $this->utils->get_slug() . '_remove' )
				->setLabel('Отключить Турбо‑страницу' )
				->setConfig( 'message', __( 'Отключить', 'mihdan-yandex-turbo-feed' ) )
				->setInstructions( __( 'Для отключения Турбо‑страницы включите галочку. Отключить Турбо-страницу можно только, если запись не исключена из RSS-ленты.', 'mihdan-yandex-turbo-feed' ) )
				->conditional( $this->utils->get_slug() . '_exclude', '==', '0' )
			->addTrueFalse( $this->utils->get_slug() . '_turbo_extended_html' )
				->setLabel('Включить поддержку HTML и CSS' )
				->setConfig( 'message', __( 'Включить', 'mihdan-yandex-turbo-feed' ) )
				->setInstructions( __( 'Активация обработки пользовательского HTML и CSS. Обязательный параметр для использования полного набора тегов. Если не передавать этот параметр, то некоторые теги будут игнорироваться.', 'mihdan-yandex-turbo-feed' ) )
				->conditional( $this->utils->get_slug() . '_exclude', '==', '0' )
			->addUrl( $this->utils->get_slug() . '_turbo_source' )
				->setLabel( __( 'Turbo Source', 'mihdan-yandex-turbo-feed' ) )
				->setInstructions( __( 'URL страницы-источника, который можно передать в Яндекс.Метрику.', 'mihdan-yandex-turbo-feed' ) )
				->conditional( $this->utils->get_slug() . '_exclude', '==', '0' )
			->addText( $this->utils->get_slug() . '_turbo_topic' )
				->setLabel( __( 'Turbo Topic', 'mihdan-yandex-turbo-feed' ) )
				->setInstructions( __( 'Заголовок страницы, который можно передать в Яндекс.Метрику.', 'mihdan-yandex-turbo-feed' ) )
				->conditional( $this->utils->get_slug() . '_exclude', '==', '0' )
			->setLocation( 'post_type', '==', '789' );
			//->or( 'post_type', '==', 'post' );

		foreach ( $this->post_types as $post_type => $item ) {
			$post_settings->getLocation()
				->or( 'post_type', '==', $post_type );
		}

		acf_add_local_field_group( $post_settings->build() );

		/**
		 * Настройки ленты.
		 *
		 * @link https://yandex.ru/dev/turbo/doc/rss/elements/index-docpage/
		 */
		$feed_settings = new FieldsBuilder(
			'feed_settings',
			array(
				'title'                 => __( 'Settings', 'mihdan-yandex-turbo-feed' ),
				//'label_placement'       => 'left',
				'instruction_placement' => 'field',
				//'style'    => 'seamless',
			)
		);

		$feed_settings
			/**
			 * Лента
			 */
			->addTab(
				'feed',
				array(
					'placement' => 'left',
					'label'     => __( 'Feed', 'mihdan-yandex-turbo-feed' ),
				)
			);

		// Polylang.
		/*if ( defined( 'POLYLANG_VERSION' ) ) {
			$feed_settings
				->addSelect(
					$this->utils->get_slug() . '_language',
					array(
						'label'         => __( 'Charset', 'mihdan-yandex-turbo-feed' ),
						'default_value' => pll_default_language(),
						'choices'       => wp_list_pluck( get_terms( 'language', array( 'hide_empty' => 0 ) ), 'name', 'slug' ),
					)
				);
		}*/

		// WPML.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {

		}

			$feed_settings
				->addSelect(
					$this->utils->get_slug() . '_charset',
					array(
						'label'         => __( 'Feed Charset', 'mihdan-yandex-turbo-feed' ),
						'default_value' => 'UTF-8',
						'choices'       => array(
							'UTF-8'        => 'UTF-8',
							'KOI8-R'       => 'KOI8-R',
							'Windows-1251' => 'Windows-1251',
						),
					)
				)
				->addSelect(
					$this->utils->get_slug() . '_orderby',
					array(
						'label'         => __( 'Order By', 'mihdan-yandex-turbo-feed' ),
						'default_value' => 'date',
						'choices'       => array(
							'date'          => __( 'Date', 'mihdan-yandex-turbo-feed' ),
							'modified'      => __( 'Last modified date', 'mihdan-yandex-turbo-feed' ),
							'rand'          => __( 'Random', 'mihdan-yandex-turbo-feed' ),
							'ID'            => __( 'ID', 'mihdan-yandex-turbo-feed' ),
							'author'        => __( 'Author', 'mihdan-yandex-turbo-feed' ),
							'title'         => __( 'Title', 'mihdan-yandex-turbo-feed' ),
							'name'          => __( 'Post name', 'mihdan-yandex-turbo-feed' ),
							'type'          => __( 'Post type', 'mihdan-yandex-turbo-feed' ),
							'comment_count' => __( 'Comment_count', 'mihdan-yandex-turbo-feed' ),
							'relevance'     => __( 'Relevance', 'mihdan-yandex-turbo-feed' ),
							'menu_order'    => __( 'Menu order', 'mihdan-yandex-turbo-feed' ),
						),
					)
				)
				->addSelect(
					$this->utils->get_slug() . '_order',
					array(
						'label'         => __( 'Order', 'mihdan-yandex-turbo-feed' ),
						'default_value' => 'DESC',
						'choices'       => array(
							'DESC' => __( 'DESC', 'mihdan-yandex-turbo-feed' ),
							'ASC'  => __( 'ASC', 'mihdan-yandex-turbo-feed' ),
						),
					)
				)
				->addNumber(
					$this->utils->get_slug() . '_total_posts',
					array(
						'label'         => __( 'Total Posts', 'mihdan-yandex-turbo-feed' ),
						'default_value' => apply_filters( 'mihdan_yandex_turbo_feed_posts_per_rss', 1000 ),
						'min'           => 10,
						'max'           => 1000,
						'step'          => 1,
						'required'      => true,
					)
				)
				->addSelect(
					$this->utils->get_slug() . '_post_type',
					array(
						'label'         => __( 'Post type', 'mihdan-yandex-turbo-feed' ),
						'default_value' => apply_filters(
							'mihdan_yandex_turbo_feed_post_type',
							array(
								'post',
							)
						),
						'multiple'      => true,
						'ui'            => true,
						'choices'       => $this->post_types,
						'required'      => true,
					)
				)
				->addSelect(
					$this->utils->get_slug() . '_taxonomy',
					array(
						'label'         => __( 'Taxonomy', 'mihdan-yandex-turbo-feed' ),
						'default_value' => apply_filters(
							'mihdan_yandex_turbo_feed_taxonomy',
							array(
								'category',
								'post_tag',
							)
						),
						'multiple'      => true,
						'ui'            => true,
						'choices'       => $this->taxonomies,
						'required'      => true,
					)
				)
			/**
			 * Канал
			 */
			->addTab(
				'channel',
				array(
					'placement' => 'left',
					'label'     => __( 'Channel', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addText(
					$this->utils->get_slug() . '_channel_title',
					array(
						'label'         => __( 'Channel Title', 'mihdan-yandex-turbo-feed' ),
						'default_value' => get_bloginfo_rss( 'name' ),
						'required'      => true,
					)
				)
				->addLink(
					$this->utils->get_slug() . '_channel_link',
					array(
						'label'         => __( 'Channel Link', 'mihdan-yandex-turbo-feed' ),
						'default_value' => get_bloginfo_rss( 'url' ),
						'required'      => true,
					)
				)
				->addTextarea(
					$this->utils->get_slug() . '_channel_description',
					array(
						'label'         => __( 'Channel Description', 'mihdan-yandex-turbo-feed' ),
						'default_value' => get_bloginfo_rss( 'description' ),
						'required'      => true,
					)
				)
				->addSelect(
					$this->utils->get_slug() . '_channel_language',
					array(
						'label'         => __( 'Channel Language', 'mihdan-yandex-turbo-feed' ),
						'default_value' => $this->language,
						'choices'       => $this->languages,
					)
				)
			->addTab(
				'images',
				array(
					'placement' => 'left',
					'label'     => __( 'Images', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addText(
					$this->utils->get_slug() . '_images_copyright',
					array(
						'label'         => __( 'Copyright', 'mihdan-yandex-turbo-feed' ),
						'default_value' => apply_filters(
							'mihdan_yandex_turbo_feed_copyright',
							$this->utils->get_site_domain()
						),
						'instructions'  => __( 'Adds Copyright To All Photos', 'mihdan-yandex-turbo-feed' ),
						'required'      => true,
					)
				)
			/**
			 * Настройки для таблиц.
			 */
			->addTab(
				'comments',
				array(
					'placement' => 'left',
					'label'     => __( 'Comments', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addTrueFalse(
					$this->utils->get_slug() . '_comments_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Comments', 'mihdan-yandex-turbo-feed' ),
					)
				)
			/**
			 * Форма обратной связи.
			 *
			 * @link https://yandex.ru/dev/turbo/doc/rss/elements/fos-docpage/
			 */
			->addTab(
				'callback',
				array(
					'placement' => 'left',
					'label'     => __( 'Callback', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addTrueFalse(
					$this->utils->get_slug() . '_callback_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Callback', 'mihdan-yandex-turbo-feed' ),
					)
				)
				->addText( $this->utils->get_slug() . '_callback_send_to' )
					->setRequired()
					->setDefaultValue( get_bloginfo_rss( 'admin_email' ) )
					->setConfig( 'label', __( 'Callback Send To', 'mihdan-yandex-turbo-feed' ) )
					->conditional( $this->utils->get_slug() . '_callback_enable', '==', '1' )
				->addText(
					$this->utils->get_slug() . '_callback_agreement_company',
					array(
						'label'         => __( 'Callback Agreement Company', 'mihdan-yandex-turbo-feed' ),
						'default_value' => get_bloginfo_rss( 'name' ),
						'required'      => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_callback_enable', '==', '1' )
				->addLink(
					$this->utils->get_slug() . '_callback_agreement_link',
					array(
						'label'         => __( 'Callback Agreement Link', 'mihdan-yandex-turbo-feed' ),
						'default_value' => get_privacy_policy_url(),
						'required'      => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_callback_enable', '==', '1' )
			/**
			 * Меню.
			 */
			->addTab(
				'menu',
				array(
					'placement' => 'left',
					'label'     => __( 'Menu', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addMessage(
					__( 'Attention Menu', 'mihdan-yandex-turbo-feed' ),
					/* translators: link to menu */
					sprintf( __( 'For adding menu to your feed, first <a href="%s">created it</a> and attach to "Yandex.Turbo" location', 'mihdan-yandex-turbo-feed' ), admin_url( 'nav-menus.php' ) )
				)
				->addTrueFalse(
					$this->utils->get_slug() . '_menu_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Menu', 'mihdan-yandex-turbo-feed' ),
					)
				)
			/**
			 * Хлебные крошки.
			 *
			 * @link https://yandex.ru/dev/turbo/doc/rss/elements/header-docpage/
			 */
			->addTab(
				'breadcrumbs',
				array(
					'placement' => 'left',
					'label'     => __( 'Breadcrumbs', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->utils->get_slug() . '_breadcrumbs_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Breadcrumbs', 'mihdan-yandex-turbo-feed' ),
				)
			)
			/**
			 * Аналитика.
			 *
			 * @link https://yandex.ru/dev/turbo/doc/settings/analytics-docpage
			 * @link https://yandex.ru/dev/turbo/doc/settings/find-counter-id-docpage/
			 */
			->addTab(
				'analytics',
				array(
					'placement' => 'left',
					'label'     => __( 'Analytics', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addMessage(
					__( 'Attention Analytics', 'mihdan-yandex-turbo-feed' ),
					__( 'Если информация о счетчиках передается в RSS-канале (в элементе <code>turbo:analytics</code>), то настройки счетчиков в Яндекс.Вебмастере не учитываются. Чтобы подключить счетчики в Яндекс.Вебмастере, отключите полность модуль аналитики ниже.', 'mihdan-yandex-turbo-feed' )
				)
				->addTrueFalse(
					$this->utils->get_slug() . '_analytics_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Analytics', 'mihdan-yandex-turbo-feed' ),
					)
				)
				->addText(
					$this->utils->get_slug() . '_analytics_yandex_metrika',
					array(
						'label'        => __( 'Yandex.Metrika', 'mihdan-yandex-turbo-feed' ),
						'instructions' => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
						'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
					)
				)
					->conditional( $this->utils->get_slug() . '_analytics_enable', '==', '1' )
				->addText(
					$this->utils->get_slug() . '_analytics_live_internet',
					array(
						'label'        => __( 'LiveInternet', 'mihdan-yandex-turbo-feed' ),
						'instructions' => __( 'Укажите имя именованного счётчика. Например, <code>example.com</code>.', 'mihdan-yandex-turbo-feed' ),
						'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
					)
				)
					->conditional( $this->utils->get_slug() . '_analytics_enable', '==', '1' )
				->addText(
					$this->utils->get_slug() . '_analytics_google',
					array(
						'label'        => __( 'Google Analytics', 'mihdan-yandex-turbo-feed' ),
						'instructions' => __( 'Укажите идентификатор отслеживания. Например, <code>UA-12345678-9</code>.', 'mihdan-yandex-turbo-feed' ),
						'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
					)
				)
					->conditional( $this->utils->get_slug() . '_analytics_enable', '==', '1' )
				->addText(
					$this->utils->get_slug() . '_analytics_mail_ru',
					array(
						'label'        => __( 'Rating Mail.RU', 'mihdan-yandex-turbo-feed' ),
						'instructions' => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
						'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
					)
				)
					->conditional( $this->utils->get_slug() . '_analytics_enable', '==', '1' )
				->addText(
					$this->utils->get_slug() . '_analytics_rambler',
					array(
						'label'        => __( 'Rambler Top-100', 'mihdan-yandex-turbo-feed' ),
						'instructions' => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
						'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
					)
				)
					->conditional( $this->utils->get_slug() . '_analytics_enable', '==', '1' )
				->addText(
					$this->utils->get_slug() . '_analytics_mediascope',
					array(
						'label'        => __( 'Mediascope (TNS)', 'mihdan-yandex-turbo-feed' ),
						'instructions' => __( 'Идентификатор проекта <code>tmsec</code> с окончанием «-<code>turbo</code>». Например, если для обычных страниц сайта установлен счетчик <code>example_total</code>, то для Турбо-страниц указывается <code>example_total-turbo</code>.', 'mihdan-yandex-turbo-feed' ),
						'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
					)
				)
					->conditional( $this->utils->get_slug() . '_analytics_enable', '==', '1' )
			/**
			 * Похожие записи.
			 */
			->addTab(
				'related_posts',
				array(
					'placement' => 'left',
					'label'     => __( 'Related Posts', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addMessage(
					__( 'Attention Related Posts', 'mihdan-yandex-turbo-feed' ),
					__( 'Если лента формируется в RSS-канале, то настройки ленты в Яндекс.Вебмастере не учитываются. Чтобы включить автоматическую ленту в Яндекс.Вебмастере, отключите данную возможность ниже.', 'mihdan-yandex-turbo-feed' )
				)
				->addTrueFalse(
					$this->utils->get_slug() . '_related_posts_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Related Posts', 'mihdan-yandex-turbo-feed' ),
					)
				)
				->addTrueFalse(
					$this->utils->get_slug() . '_related_posts_infinity',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Infinity Feed', 'mihdan-yandex-turbo-feed' ),
					)
				)
					->conditional( $this->utils->get_slug() . '_related_posts_enable', '==', '1' )
				->addNumber(
					$this->utils->get_slug() . '_related_posts_total',
					array(
						'label'         => __( 'Total Posts', 'mihdan-yandex-turbo-feed' ),
						'default_value' => 10,
						'min'           => 1,
						'max'           => 30,
						'step'          => 1,
						'required'      => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_related_posts_enable', '==', '1' )
			/**
			 * Рейтинг записи.
			 *
			 * @link https://yandex.ru/dev/turbo/doc/rss/elements/rating-docpage/
			 */
			->addTab(
				'rating',
				array(
					'placement' => 'left',
					'label'     => __( 'Rating', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addTrueFalse(
					$this->utils->get_slug() . '_rating_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Rating', 'mihdan-yandex-turbo-feed' ),
					)
				)
				->addNumber(
					$this->utils->get_slug() . '_rating_min',
					array(
						'label'         => __( 'Minimal', 'mihdan-yandex-turbo-feed' ),
						'default_value' => 4,
						'min'           => 1,
						'max'           => 100,
						'step'          => 1,
						'required'      => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_rating_enable', '==', '1' )
				->addNumber(
					$this->utils->get_slug() . '_rating_max',
					array(
						'label'         => __( 'Maximum', 'mihdan-yandex-turbo-feed' ),
						'default_value' => 5,
						'min'           => 2,
						'max'           => 100,
						'step'          => 1,
						'required'      => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_rating_enable', '==', '1' )
			/**
			 * Шеры.
			 */
			->addTab(
				'share',
				array(
					'placement' => 'left',
					'label'     => __( 'Share', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addTrueFalse(
					$this->utils->get_slug() . '_share_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Share', 'mihdan-yandex-turbo-feed' ),
					)
				)
				->addSelect(
					$this->utils->get_slug() . '_share_networks',
					array(
						'label'         => __( 'Share Networks', 'mihdan-yandex-turbo-feed' ),
						'default_value' => array_keys( $this->share_networks ),
						'multiple'      => true,
						'ui'            => true,
						'choices'       => $this->share_networks,
						'required'      => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_share_enable', '==', '1' )
			/**
			 * Форма поиска
			 *
			 * @link https://yandex.ru/dev/turbo/doc/rss/elements/search-block-docpage/
			 */
			->addTab(
				'search',
				array(
					'placement' => 'left',
					'label'     => __( 'Search', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addTrueFalse(
					$this->utils->get_slug() . '_search_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => '',
					)
				)
				->addText(
					$this->utils->get_slug() . '_search_placeholder',
					array(
						'label'         => __( 'Placeholder', 'mihdan-yandex-turbo-feed' ),
						'default_value' => __( 'Search', 'mihdan-yandex-turbo-feed' ),
						'required'      => true,
					)
				)
				->addSelect(
					$this->utils->get_slug() . '_search_provider',
					array(
						'label'         => __( 'Provider', 'mihdan-yandex-turbo-feed' ),
						'default_value' => 'site',
						'choices'       => wp_list_pluck( $this->providers, 'name', 'id' ),
					)
				)
			/**
			 * Настройки для таблиц.
			 */
			->addTab(
				'tables',
				array(
					'placement' => 'left',
					'label'     => __( 'Tables', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->addTrueFalse(
					$this->utils->get_slug() . '_invisible_border_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Invisible Border', 'mihdan-yandex-turbo-feed' ),
					)
				)
			/**
			 * Настройки для таблиц.
			 */
			->addTab(
				'access',
				array(
					'placement' => 'left',
					'label'     => __( 'Access', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->AddMessage(
					__( 'Attention Access', 'mihdan-yandex-turbo-feed' ),
					__( 'Использовать авторизацию для доступа к файлу с данными для формирования Турбо-страниц.', 'mihdan-yandex-turbo-feed' )
				)
				->addTrueFalse(
					$this->utils->get_slug() . '_access_enable',
					array(
						'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'label'   => __( 'Access', 'mihdan-yandex-turbo-feed' ),
					)
				)
				->addText(
					$this->utils->get_slug() . '_access_login',
					array(
						'label'    => __( 'Login', 'mihdan-yandex-turbo-feed' ),
						'required' => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_access_enable', '==', '1' )
				->addText(
					$this->utils->get_slug() . '_access_password',
					array(
						'label'    => __( 'Password', 'mihdan-yandex-turbo-feed' ),
						'required' => true,
					)
				)
					->conditional( $this->utils->get_slug() . '_access_enable', '==', '1' )
			/**
			 * Форма запроса помощи проекту.
			 */
			->addTab(
				'donate',
				array(
					'placement' => 'left',
					'label'     => __( 'Donate', 'mihdan-yandex-turbo-feed' ),
				)
			)
				->AddMessage(
					__( 'Attention Donate', 'mihdan-yandex-turbo-feed' ),
					/* translators: donate link */
					sprintf( __( 'Проект отнимает огромное количество сил, времени и энергии. Чтобы у разработчика была мотивация продолжать разрабатывать плагин и дальше, вы всегда можете <a target="_blank" href="%s">помочь символической суммой</a>.', 'mihdan-yandex-turbo-feed' ), 'https://www.kobzarev.com/donate/' )
				)
			->setLocation( 'post_type', '==', $this->utils->get_post_type() );

		acf_add_local_field_group( $feed_settings->build() );
	}

	/**
	 * @param         $key
	 * @param integer $post_id
	 *
	 * @return mixed
	 */
	public function get_option( $key, $post_id = null ) {

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		return get_field( $this->utils->get_slug() . '_' . $key, $post_id );
	}

	/**
	 * Получить название такосномии для соотношений.
	 * По-умолчанию, это category.
	 *
	 * @return array
	 */
	public function get_taxonomy() {
		return array_keys( $this->taxonomies );
	}
}

// eol.
