<?php
class Mihdan_Yandex_Turbo_Feed_Settings {
	/**
	 * @var $post_types
	 */
	private $post_types;
	private $taxonomies;
	private $share_networks;

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'redux/construct', array( $this, 'disable_dev_mode' ) );
		add_action( 'redux/pro/welcome/admin/menu', array( $this, 'remove_redux_menu' ), 10, 2 );
		add_action( 'init', array( $this, 'init' ) );
	}

	public function setup() {
		// Список всех публичных CPT.
		$args            = array(
			'public' => true,
		);

		$this->post_types = wp_list_pluck( get_post_types( $args, 'objects' ), 'label', 'name' );

		// Список всех зареганных таксономий.
		$args           = array(
			'public' => true,
		);

		$this->taxonomies = wp_list_pluck( get_taxonomies( $args, 'objects' ), 'label', 'name' );

		// Доступные соцсети для шеров.
		$this->share_networks = array(
			'facebook' => __( 'Facebook', 'mihdan-yandex-turbo-feed' ),
			'google' => __( 'Google', 'mihdan-yandex-turbo-feed' ),
			'odnoklassniki' => __( 'Odnoklassniki', 'mihdan-yandex-turbo-feed' ),
			'telegram' => __( 'Telegram', 'mihdan-yandex-turbo-feed' ),
			'vkontakte' => __( 'Vkontakte', 'mihdan-yandex-turbo-feed' ),
		);
	}

	public function init() {
		$this->setup();
		$this->config();
	}

	/**
	 * Отключаем режима разработки
	 *
	 * @param ReduxFramework $redux
	 */
	public function disable_dev_mode( $redux ) {
		if ( $redux instanceof ReduxFramework ) {
			$redux->args['dev_mode']            = false;
			$redux->args['forced_dev_mode_off'] = false;
		}
	}

	/**
	 * Удаляем меню Redux
	 *
	 * @param string        $page
	 * @param Redux_Welcome $welcome
	 */
	public function remove_redux_menu( $page, Redux_Welcome $welcome ) {
		remove_submenu_page( 'tools.php', 'redux-framework' );
	}

	public function footer_credit() {
		ob_start();
		?>
		<div style="display: flex; align-items: center;">

			<h3 style="margin: 0;">Помочь проекту</h3>

			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="BENCPARA8S224">
				<input
					type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif"
					name="submit" alt="PayPal - The safer, easier way to pay online!">

			</form>
		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public function config() {
		// Массив всех языков сайта.
		$languages = array();

		// Список всех возможных переводов.
		$translations = wp_get_available_translations();

		// Дефолтный язык - из настроек сайта.
		$language = substr( get_bloginfo_rss( 'language' ), 0, 2 );

		if ( $translations ) {
			foreach ( $translations as $translation ) {
				// Нас интересует только двухбуквенный код языка.
				$key = substr( $translation['language'], 0, 2 );

				// Соберем массив: array( code => name ).
				$languages[ $key ] = $translation['native_name'];
			}
		}

		/**
		 * @link https://docs.reduxframework.com/core/arguments/
		 * @link http://elusiveicons.com/icons/
		 * */
		$args = array(
			'opt_name'           => MIHDAN_YANDEX_TURBO_FEED_SLUG,
			'display_name'       => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
			'display_version'    => MIHDAN_YANDEX_TURBO_FEED_VERSION,
			'menu_type'          => 'menu',
			'allow_sub_menu'     => false,
			'menu_title'         => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
			'page_title'         => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
			'async_typography'   => false,
			'admin_bar'          => false,
			'global_variable'    => false,
			'dev_mode'           => false,
			'update_notice'      => true,
			'hide_reset'         => true,
			'hide_expand'        => true,
			'customizer'         => false,
			'page_priority'      => null,
			'page_permissions'   => 'manage_options',
			'menu_icon'          => 'dashicons-rss',
			'last_tab'           => '',
			'page_slug'          => MIHDAN_YANDEX_TURBO_FEED_SLUG,
			'save_defaults'      => true,
			'default_show'       => true,
			'default_mark'       => '',
			'show_import_export' => true,
			'transient_time'     => 60 * MINUTE_IN_SECONDS,
			'output'             => true,
			'output_tag'         => true,
			'database'           => '',
			'use_cdn'            => true,
			'footer_credit'      => $this->footer_credit(),
			//'admin_theme' => 'classic',

			// HINTS
			'hints'              => array(
				'icon'          => 'el el-question-sign',
				'icon_position' => 'right',
				'icon_color'    => 'lightgray',
				'icon_size'     => 'normal',
				'tip_style'     => array(
					'color'   => 'red',
					'shadow'  => true,
					'rounded' => false,
					'style'   => '',
				),
				'tip_position'  => array(
					'my' => 'top left',
					'at' => 'bottom right',
				),
				'tip_effect'    => array(
					'show' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'mouseover',
					),
					'hide' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'click mouseleave',
					),
				),
			),
		);

		Redux::set_args( MIHDAN_YANDEX_TURBO_FEED_SLUG, $args );

		/**
		 * Секции
		 *
		 * @link https://yandex.ru/dev/turbo/doc/rss/elements/index-docpage/
		 */
		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title'  => __( 'Feed', 'mihdan-yandex-turbo-feed' ),
				'id'     => 'feed',
				'icon'   => 'el el-rss',
				'desc'   => __( 'Настройки ленты', 'mihdan-yandex-turbo-feed' ),
				'fields' => array(
					array(
						'id'       => 'feed_slug',
						'type'     => 'text',
						'title'    => __( 'Feed Slug', 'mihdan-yandex-turbo-feed' ),
						'default'  => apply_filters( 'mihdan_yandex_turbo_feed_feedname', str_replace( '_', '-', MIHDAN_YANDEX_TURBO_FEED_SLUG ) ),
						'validate' => 'not_empty',
						'desc'     => get_bloginfo_rss( 'url' ) . '/feed/%slug%/',
					),
					array(
						'id'      => 'feed_charset',
						'type'    => 'select',
						'title'   => __( 'Feed Charset', 'mihdan-yandex-turbo-feed' ),
						'desc'    => __( 'Рекомендуемая кодировка UTF-8', 'mihdan-yandex-turbo-feed' ),
						'default' => 'UTF-8',
						'options' => array(
							'UTF-8'        => 'UTF-8',
							'KOI8-R'       => 'KOI8-R',
							'Windows-1251' => 'Windows-1251',
						),
					),
					array(
						'id'      => 'feed_orderby',
						'type'    => 'select',
						'title'   => __( 'Order By', 'mihdan-yandex-turbo-feed' ),
						'default' => 'date',
						'options' => array(
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
					),
					array(
						'id'      => 'feed_order',
						'type'    => 'select',
						'title'   => __( 'Order', 'mihdan-yandex-turbo-feed' ),
						'default' => 'DESC',
						'options' => array(
							'DESC' => __( 'DESC', 'mihdan-yandex-turbo-feed' ),
							'ASC'  => __( 'ASC', 'mihdan-yandex-turbo-feed' ),
						),
					),
					array(
						'id'      => 'feed_total_posts',
						'type'    => 'spinner',
						'title'   => __( 'Total Posts', 'mihdan-yandex-turbo-feed' ),
						'default' => apply_filters( 'mihdan_yandex_turbo_feed_posts_per_rss', 1000 ),
						'min'     => 1,
						'max'     => 1000,
						'step'    => 1,
					),
					array(
						'id'      => 'feed_post_type',
						'type'    => 'select',
						'multi'   => true,
						'title'   => __( 'Post type', 'mihdan-yandex-turbo-feed' ),
						'default' => apply_filters(
							'mihdan_yandex_turbo_feed_post_type',
							array( 'post' )
						),
						'options' => $this->post_types,
					),
					array(
						'id'      => 'feed_taxonomy',
						'type'    => 'select',
						'multi'   => true,
						'title'   => __( 'Taxonomy', 'mihdan-yandex-turbo-feed' ),
						'default' => apply_filters(
							'mihdan_yandex_turbo_feed_taxonomy',
							array(
								'category',
								'post_tag',
							)
						),
						'options' => $this->taxonomies,
					),
				),
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title'  => __( 'Channel', 'mihdan-yandex-turbo-feed' ),
				'id'     => 'channel',
				'icon'   => 'el el-adjust-alt',
				'desc'   => __( 'Настройки канала', 'mihdan-yandex-turbo-feed' ),
				'fields' => array(
					array(
						'id'       => 'channel_title',
						'type'     => 'text',
						'title'    => __( 'Channel Title', 'mihdan-yandex-turbo-feed' ),
						'default'  => get_bloginfo_rss( 'name' ),
						'validate' => 'not_empty',
					),
					array(
						'id'       => 'channel_link',
						'type'     => 'text',
						'title'    => __( 'Channel Link', 'mihdan-yandex-turbo-feed' ),
						'default'  => get_bloginfo_rss( 'url' ),
						'validate' => 'url',
					),
					array(
						'id'       => 'channel_description',
						'type'     => 'textarea',
						'title'    => __( 'Channel Description', 'mihdan-yandex-turbo-feed' ),
						'default'  => get_bloginfo_rss( 'description' ),
						'validate' => 'not_empty',
					),
					array(
						'id'      => 'channel_language',
						'type'    => 'select',
						'title'   => __( 'Channel Language', 'mihdan-yandex-turbo-feed' ),
						'default' => $language,
						'options' => $languages,
					),
				),
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title'  => __( 'Images', 'mihdan-yandex-turbo-feed' ),
				'id'     => 'elements-images',
				'icon'   => 'el el-picture',
				'fields' => array(
					array(
						'id'      => 'images_copyright',
						'type'    => 'text',
						'title'   => __( 'Copyright', 'mihdan-yandex-turbo-feed' ),
						'default' => apply_filters( 'mihdan_yandex_turbo_feed_copyright', wp_parse_url( get_home_url(), PHP_URL_HOST ) ),
					),
				),
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title'  => __( 'Share', 'mihdan-yandex-turbo-feed' ),
				'id'     => 'share',
				'icon'   => 'el el-share',
				'fields' => array(
					array(
						'id'      => 'share_enable',
						'type'    => 'switch',
						'title'   => __( 'Enable', 'mihdan-yandex-turbo-feed' ),
						'subtitle'   => __( 'Switch On', 'mihdan-yandex-turbo-feed' ),
						'on'      => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'off'     => __( 'Off', 'mihdan-yandex-turbo-feed' ),
						'default' => false,
					),
					array(
						'id'      => 'share_networks',
						'type'    => 'select',
						'title'   => __( 'Share Networks', 'mihdan-yandex-turbo-feed' ),
						'multi'   => true,
						'sortable' => true,
						'default' => array_keys( $this->share_networks ),
						'options' => $this->share_networks,
						'required' => array(
							array( 'share_enable', '=', '1' )
						),
					),
				),
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Comments', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'comments',
				'icon'  => 'el el-comment',
				'fields' => array(
					array(
						'id'      => 'comments_enable',
						'type'    => 'switch',
						'title'   => __( 'Enable', 'mihdan-yandex-turbo-feed' ),
						'subtitle'   => __( 'Switch On', 'mihdan-yandex-turbo-feed' ),
						'on'      => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'off'     => __( 'Off', 'mihdan-yandex-turbo-feed' ),
						'default' => false,
					),
				),
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Callback', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'callback',
				'icon'  => 'el el-envelope',
				'fields' => array(
					array(
						'id'      => 'callback_enable',
						'type'    => 'switch',
						'title'   => __( 'Enable', 'mihdan-yandex-turbo-feed' ),
						'subtitle'   => __( 'Switch On', 'mihdan-yandex-turbo-feed' ),
						'on'      => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'off'     => __( 'Off', 'mihdan-yandex-turbo-feed' ),
						'default' => false,
					),
					array(
						'id'       => 'callback_send_to',
						'type'     => 'text',
						'title'    => __( 'Callback Send To', 'mihdan-yandex-turbo-feed' ),
						'default'  => get_bloginfo_rss( 'admin_email' ),
						'validate' => 'email',
						'required' => array(
							array( 'callback_enable', '=', '1' )
						),
					),
					array(
						'id'       => 'callback_agreement_company',
						'type'     => 'text',
						'title'    => __( 'Callback Agreement Company', 'mihdan-yandex-turbo-feed' ),
						'default'  => get_bloginfo_rss( 'name' ),
						'validate' => 'not_empty',
						'required' => array(
							array( 'callback_enable', '=', '1' )
						),
					),
					array(
						'id'       => 'callback_agreement_link',
						'type'     => 'text',
						'title'    => __( 'Callback Agreement Link', 'mihdan-yandex-turbo-feed' ),
						'default'  => get_privacy_policy_url(),
						'validate' => 'url',
						'required' => array(
							array( 'callback_enable', '=', '1' )
						),
					),
				),
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title'  => __( 'Menu', 'mihdan-yandex-turbo-feed' ),
				'id'     => 'menu',
				'icon'   => 'el el-lines',
				'fields' => array(
					array(
						'id'       => 'menu_enable',
						'type'     => 'switch',
						'title'    => __( 'Enable', 'mihdan-yandex-turbo-feed' ),
						'subtitle' => __( 'Switch On', 'mihdan-yandex-turbo-feed' ),
						'on'       => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'off'      => __( 'Off', 'mihdan-yandex-turbo-feed' ),
						'default'  => false,
					),
					array(
						'id'       => 'menu_attention',
						'type'     => 'raw',
						'title'    => __( 'Attention', 'mihdan-yandex-turbo-feed' ),
						'desc'     => sprintf( __( 'For adding menu to your feed, first <a href="%s">created it</a> and attach to "Yandex.Turbo" location', 'mihdan-yandex-turbo-feed' ), admin_url( 'nav-menus.php' ) ),
						'required' => array(
							array( 'menu_enable', '=', '1' ),
						),
					),
				),
			)
		);

		/**
		 * @link https://yandex.ru/dev/turbo/doc/settings/analytics-docpage
		 * @link https://yandex.ru/dev/turbo/doc/settings/find-counter-id-docpage/
		 */
		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title'  => __( 'Analytics', 'mihdan-yandex-turbo-feed' ),
				'id'     => 'analytics',
				'icon'   => 'el el-graph',
				'fields' => array(
					array(
						'id'       => 'analytics_enable',
						'type'     => 'switch',
						'title'    => __( 'Enable', 'mihdan-yandex-turbo-feed' ),
						'subtitle' => __( 'Switch On', 'mihdan-yandex-turbo-feed' ),
						'on'       => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'off'      => __( 'Off', 'mihdan-yandex-turbo-feed' ),
						'default'  => false,
					),
					array(
						'id'          => 'analytics_yandex_metrika',
						'type'        => 'multi_text',
						'show_empty'  => false,
						'title'       => __( 'Yandex.Metrika', 'mihdan-yandex-turbo-feed' ),
						'placeholder' => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
						'desc'        => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
						'add_text'    => __( 'Add More', 'mihdan-yandex-turbo-feed' ),
						'required'    => array(
							array( 'analytics_enable', '=', '1' ),
						),
					),
					array(
						'id'          => 'analytics_live_internet',
						'show_empty'  => false,
						'type'        => 'multi_text',
						'title'       => __( 'LiveInternet', 'mihdan-yandex-turbo-feed' ),
						'placeholder' => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
						'desc'        => __( 'Укажите имя именованного счётчика. Например, <code>example.com</code>.', 'mihdan-yandex-turbo-feed' ),
						'add_text'    => __( 'Add More', 'mihdan-yandex-turbo-feed' ),
						'required'    => array(
							array( 'analytics_enable', '=', '1' ),
						),
					),
					array(
						'id'          => 'analytics_google',
						'type'        => 'multi_text',
						'show_empty'  => false,
						'title'       => __( 'Google Analytics', 'mihdan-yandex-turbo-feed' ),
						'placeholder' => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
						'desc'        => __( 'Укажите идентификатор отслеживания. Например, <code>UA-12345678-9</code>.', 'mihdan-yandex-turbo-feed' ),
						'add_text'    => __( 'Add More', 'mihdan-yandex-turbo-feed' ),
						'required'    => array(
							array( 'analytics_enable', '=', '1' ),
						),
					),
					array(
						'id'          => 'analytics_mail_ru',
						'type'        => 'multi_text',
						'show_empty'  => false,
						'title'       => __( 'Rating Mail.RU', 'mihdan-yandex-turbo-feed' ),
						'placeholder' => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
						'desc'        => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
						'add_text'    => __( 'Add More', 'mihdan-yandex-turbo-feed' ),
						'required'    => array(
							array( 'analytics_enable', '=', '1' ),
						),
					),
					array(
						'id'          => 'analytics_rambler',
						'type'        => 'multi_text',
						'show_empty'  => false,
						'title'       => __( 'Rambler Top-100', 'mihdan-yandex-turbo-feed' ),
						'placeholder' => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
						'desc'        => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
						'add_text'    => __( 'Add More', 'mihdan-yandex-turbo-feed' ),
						'required'    => array(
							array( 'analytics_enable', '=', '1' ),
						),
					),
					array(
						'id'          => 'analytics_mediascope',
						'type'        => 'multi_text',
						'show_empty'  => false,
						'title'       => __( 'Mediascope (TNS)', 'mihdan-yandex-turbo-feed' ),
						'placeholder' => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
						'desc'        => __( 'Идентификатор проекта <code>tmsec</code> с окончанием «-<code>turbo</code>». Например, если для обычных страниц сайта установлен счетчик <code>example_total</code>, то для Турбо-страниц указывается <code>example_total-turbo</code>.', 'mihdan-yandex-turbo-feed' ),
						'add_text'    => __( 'Add More', 'mihdan-yandex-turbo-feed' ),
						'required'    => array(
							array( 'analytics_enable', '=', '1' ),
						),
					),
				),
			)
		);

		/**
		 * Похожие записи.
		 */
		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title'  => __( 'Related Posts', 'mihdan-yandex-turbo-feed' ),
				'id'     => 'related_posts',
				'icon'   => 'el el-fork',
				'fields' => array(
					array(
						'id'       => 'related_posts_enable',
						'type'     => 'switch',
						'title'    => __( 'Enable', 'mihdan-yandex-turbo-feed' ),
						'subtitle' => __( 'Switch On', 'mihdan-yandex-turbo-feed' ),
						'on'       => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'off'      => __( 'Off', 'mihdan-yandex-turbo-feed' ),
						'default'  => false,
					),
					array(
						'id'       => 'related_posts_infinity',
						'type'     => 'switch',
						'title'    => __( 'Infinity Feed', 'mihdan-yandex-turbo-feed' ),
						'subtitle' => __( 'Switch On', 'mihdan-yandex-turbo-feed' ),
						'on'       => __( 'On', 'mihdan-yandex-turbo-feed' ),
						'off'      => __( 'Off', 'mihdan-yandex-turbo-feed' ),
						'default'  => false,
						'required' => array(
							array( 'related_posts_enable', '=', '1' ),
						),
					),
					array(
						'id'       => 'related_posts_total',
						'type'     => 'spinner',
						'title'    => __( 'Total Posts', 'mihdan-yandex-turbo-feed' ),
						'default'  => 10,
						'min'      => 1,
						'max'      => 30,
						'step'     => 1,
						'required' => array(
							array( 'related_posts_enable', '=', '1' ),
						),
					),
				),
			)
		);

		/*

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Header', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-header',
				'icon'  => 'el el-photo',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Links', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-links',
				'icon'  => 'el el-link',
			)
		);



		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Gallery', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-gallery',
				'icon'  => 'el el-website',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Slider', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-slider',
				'icon'  => 'el el-slideshare',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Video', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-video',
				'icon'  => 'el el-video',
			)
		);



		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Blockquote', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-blockquote',
				'icon'  => 'el el-quotes',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Table', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-table',
				'icon'  => 'el el-th',
			)
		);



		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Social Content', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-social-content',
				'icon'  => 'el el-group',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Yandex Map', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-yandex-map',
				'icon'  => 'el el-map-marker',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Yandex Music', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-yandex-music',
				'icon'  => 'el el-music',
			)
		);



		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Rating', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-rating',
				'icon'  => 'el el-star',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Widget Feedback', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-widget-feedback',
				'icon'  => 'el el-phone',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Search', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-search',
				'icon'  => 'el el-search',
			)
		);



		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Button', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-button',
				'icon'  => 'el el-hand-down',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Accordion', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-accordion',
				'icon'  => 'el el-chevron-down',
			)
		);

		Redux::set_section(
			MIHDAN_YANDEX_TURBO_FEED_SLUG,
			array(
				'title' => __( 'Ad Network', 'mihdan-yandex-turbo-feed' ),
				'id'    => 'elements-ad-network',
				'icon'  => 'el el-usd',
			)
		);


		*/
	}

	/**
	 * Получаем данные из поля Redux по ключу.
	 *
	 * @param string $key ключ поля.
	 * @return string|array
	 */
	public function get_option( $key ) {
		$option = Redux::get_option( MIHDAN_YANDEX_TURBO_FEED_SLUG, $key );

		// Если пусто - берем настройку по дефолту.
		if ( empty( $option ) ) {
			$field = Redux::get_field( MIHDAN_YANDEX_TURBO_FEED_SLUG, $key );

			if ( $field && isset( $field['default'] ) ) {
				$option = $field['default'];
			}
		}

		return $option;
	}
}