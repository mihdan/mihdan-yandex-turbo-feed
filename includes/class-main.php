<?php
/**
 * Основной класс плагина.
 *
 * @package mihdan-yandex-turbo-feed
 */

namespace Mihdan_Yandex_Turbo_Feed;

/**
 * Class Main
 *
 * @package mihdan-yandex-turbo-feed
 */
class Main {
	/**
	 * @var string слюг плагина
	 */
	private $slug = 'mihdan_yandex_turbo_feed';

	/**
	 * @var string $feedname слюг фида
	 */
	public $feedname;

	/**
	 * @var array $allowable_tags массив разрешенных тегов для контента
	 */
	private $allowable_tags = array(
		'<h1>',
		'<h2>',
		'<h3>',
		'<h4>',
		'<h5>',
		'<h6>',
		'<p>',
		'<br>',
		'<ul>',
		'<ol>',
		'<li>',
		'<b>',
		'<strong>',
		'<i>',
		'<em>',
		'<sup>',
		'<sub>',
		'<ins>',
		'<del>',
		'<small>',
		'<big>',
		'<pre>',
		'<abbr>',
		'<u>',
		'<a>',
		'<figure>',
		'<img>',
		'<figcaption>',
		'<video>',
		'<source>',
		'<iframe>',
		'<blockquote>',
		'<table>',
		'<tr>',
		'<th>',
		'<td>',
		'<menu>',
		'<hr>',
		'<div>',
		'<code>',
		'<dl>',
		'<dt>',
		'<dd>',
	);

	/**
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * @var Assets
	 */
	private $assets;

	/**
	 * @var array $enclosure для хранения фото у поста
	 */
	private $enclosure = array();

	/**
	 * @var array Массив похожих постов
	 */
	private $related = array();

	/**
	 * Путь к плагину
	 *
	 * @var string
	 */
	public $dir_path;

	/**
	 * URL до плагина
	 *
	 * @var string
	 */
	public $dir_uri;

	/**
	 * Соотношение категорий.
	 *
	 * @var
	 */
	private $categories;

	/**
	 * Таксономия для соотношений.
	 *
	 * @var array
	 */
	private $taxonomy = array( 'category', 'post_tag' );

	/**
	 * @var array список постов для вывода
	 */
	private $post_type = array( 'post' );

	/**
	 * @var Settings
	 */
	private $redux;

	/**
	 * @var Notifications
	 */
	private $notifications;

	/**
	 * Инициализируем нужные методы
	 *
	 * Mihdan_FAQ constructor.
	 */
	public function __construct() {

		$this->assets  = new Assets();
		$this->helpers = new Helpers();

		$this->includes();
		$this->setup();
		$this->hooks();
	}

	/**
	 * Установка основных переменных плагина
	 */
	private function setup() {
		$this->dir_path = trailingslashit( MIHDAN_YANDEX_TURBO_FEED_PATH );
		$this->dir_uri  = trailingslashit( MIHDAN_YANDEX_TURBO_FEED_URL );
	}

	/**
	 * Фильтры для переопределения настроек внутри темы
	 */
	public function after_setup_theme() {

		// Подключить конфиг Redux после фильтрации,
		// чтобы работали переопределения полей, сделанный фильтрами ранее.
		$this->redux         = new Settings();
		$this->notifications = new Notifications( $this->redux );

		$this->categories = apply_filters( 'mihdan_yandex_turbo_feed_categories', array() );

		$this->post_type = $this->redux->get_option( 'feed_post_type' );
		$this->taxonomy  = $this->redux->get_option( 'feed_taxonomy' );
		$this->feedname  = $this->redux->get_option( 'feed_slug' );
	}

	/**
	 * Регистрируем новую область меню
	 * для создания меню в админке
	 */
	public function register_nav_menu() {
		register_nav_menu( $this->slug, 'Яндекс.Турбо' );
	}

	/**
	 * Подключаем зависимости
	 */
	private function includes() {

		// Для работы с переводами.
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	}

	/**
	 * Хукаем.
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'add_feed' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ), 99 );
		add_action( 'pre_get_posts', array( $this, 'alter_query' ) );
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		add_action( 'after_setup_theme', array( $this, 'register_nav_menu' ) );
		add_action( 'plugins_loaded', array( $this, 'load_translations' ) );
		add_action( 'redux/options/' . $this->slug . '/saved', array( $this, 'on_redux_saved' ) );
		add_action( 'mihdan_yandex_turbo_feed_channel', array( $this, 'insert_analytics' ) );
		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_enclosure' ) );
		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_related' ) );
		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_category' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_header', array( $this, 'insert_menu' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_share' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_comments' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_callback' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_search' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_rating' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
		add_filter( 'the_content_feed', array( $this, 'content_feed' ) );
		add_filter( 'the_content_feed', array( $this, 'invisible_border' ) );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'image_attributes' ), 10, 3 );
		add_filter( 'wpseo_include_rss_footer', array( $this, 'hide_wpseo_rss_footer' ) );
		add_action( 'template_redirect', array( $this, 'send_headers_for_aio_seo_pack' ), 20 );
		register_activation_hook( MIHDAN_YANDEX_TURBO_FEED_FILE, array( $this, 'on_activate' ) );
		register_deactivation_hook( MIHDAN_YANDEX_TURBO_FEED_FILE, array( $this, 'on_deactivate' ) );
	}

	public function www_authenticate() {
		if ( $this->redux->get_option( 'access_enable' ) ) {
			header( 'WWW-Authenticate: Basic realm="My Realm"' );
			header( 'HTTP/1.0 401 Unauthorized' );
		}
	}

	/**
	 * Генерим атрибуты для тега <item>
	 *
	 * @param int $post_id идентификатор поста
	 */
	public function item_attributes( $post_id ) {

		$atts = array(
			'turbo' => ! get_post_meta( $post_id, $this->slug . '_remove', true ),
		);

		$atts = apply_filters( 'mihdan_yandex_turbo_feed_item_attributes', $atts, $post_id );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			$value = ( 'href' === $attr ) ? esc_url( $value ) : ( is_bool( $value ) ? $value : esc_attr( $value ) );

			if ( true === $value ) {
				$value = 'true';
			}

			if ( false === $value ) {
				$value = 'false';
			}

			$attributes .= ' ' . $attr . '="' . $value . '"';
		}

		echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Добавим заголовок `X-Robots-Tag`
	 * для решения проблемы с сеошными плагинами.
	 */
	public function send_headers_for_aio_seo_pack() {
		if ( is_feed( $this->feedname ) ) {
			header( 'X-Robots-Tag: index, follow', true );
		}
	}

	/**
	 * Добавить атрибут для всех таблиц,
	 * чтобы сделать рамки прозрачне
	 *
	 * @param string $content Сожержимое записи.
	 *
	 * @return string
	 */
	public function invisible_border( $content ) {

		if ( $this->redux->get_option( 'invisible_border_enable' ) ) {
			$content = str_replace( '<table', '<table data-invisible="true"', $content );
		}

		return $content;
	}

	/**
	 * Всплывает при сохранении настроек в Redux.
	 */
	public function on_redux_saved() {
		update_option( $this->slug . '_flush_rewrite_rules', 1, true );
	}

	/**
	 * Регистрируем переводы.
	 */
	public function load_translations() {
		load_plugin_textdomain( 'mihdan-yandex-turbo-feed', false, $this->dir_path . 'languages' );
	}

	/**
	 * Скидываем реврайты, если в базе есть опция.
	 */
	public function flush_rewrite_rules() {

		// Ищем опцию.
		if ( get_option( $this->slug . '_flush_rewrite_rules' ) ) {

			// Скидываем реврайты.
			flush_rewrite_rules();

			// Удаляем опцию.
			delete_option( $this->slug . '_flush_rewrite_rules' );
		}
	}



	/**
	 * Добавляем метабок с настройками поста.
	 */
	public function add_meta_box() {

		// На каких экранах админки показывать.
		$screen = $this->post_type;

		// Добавляем метабокс.
		add_meta_box( $this->slug, 'Турбо-страницы', array( $this, 'render_meta_box' ), $screen, 'side', 'high' );
	}

	/**
	 * Отрисовываем содержимое метабокса с настройками поста.
	 */
	public function render_meta_box() {
		$exclude = (bool) get_post_meta( get_the_ID(), $this->slug . '_exclude', true );
		$remove  = (bool) get_post_meta( get_the_ID(), $this->slug . '_remove', true );
		?>
		<label for="<?php echo esc_attr( $this->slug ); ?>_exclude" title="Включить/Исключить запись из ленты">
			<input type="checkbox" value="1" name="<?php echo esc_attr( $this->slug ); ?>_exclude" id="<?php echo esc_attr( $this->slug ); ?>_exclude" <?php checked( $exclude, true ); ?>> Исключить из ленты
		</label>
		<br/>
		<label for="<?php echo esc_attr( $this->slug ); ?>_remove" title="Удалить запись из Яндекса. В ленте будет добавлен атрибут turbo=false">
			<input type="checkbox" value="1" name="<?php echo esc_attr( $this->slug ); ?>_remove" id="<?php echo esc_attr( $this->slug ); ?>_remove" <?php checked( $remove, true ); ?>> Удалить из Яндекса
		</label>
		<?php
	}

	/**
	 * Созраняем данные метабокса.
	 *
	 * @param int $post_id идентификатор записи.
	 */
	public function save_meta_box( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST[ $this->slug . '_exclude' ] ) ) {
			update_post_meta( $post_id, $this->slug . '_exclude', 1 );
		} else {
			delete_post_meta( $post_id, $this->slug . '_exclude' );
		}

		if ( isset( $_POST[ $this->slug . '_remove' ] ) ) {
			update_post_meta( $post_id, $this->slug . '_remove', 1 );
		} else {
			delete_post_meta( $post_id, $this->slug . '_remove' );
		}
	}

	/**
	 * Генерим тег категории
	 *
	 * @param string $category название категории
	 *
	 * @return string
	 */
	public function create_category( $category ) {
		return sprintf( '<category><![CDATA[%s]]></category>', html_entity_decode( $category, ENT_COMPAT, 'UTF-8' ) );
	}

	/**
	 * Вставляем категории поста в фид
	 *
	 * @param integer $post_id идентификатор поста
	 */
	public function insert_category( $post_id ) {

		// Получить категории текущего поста
		$categories = $this->get_categories(
			array(
				'post_id' => $post_id,
				'fields'  => 'names',
			)
		);

		// Сгенерить тег категории
		if ( $categories ) {
			// Выбрать уникальные термы, так как они
			// могут совпадать в разных таксономиях
			$categories = array_unique( $categories );
			foreach ( $categories as $category ) {
				echo $this->create_category( $category );
			}
		}
	}

	/**
	 * Удалить ненужные атрибуты при генерации картинок
	 *
	 * @param array $attr
	 * @param \WP_Post $attachment объект вложения
	 * @param string|array $size размер
	 *
	 * @return array
	 */
	public function image_attributes( $attr, $attachment, $size ) {

		if ( is_feed( $this->feedname ) ) {
			unset( $attr['srcset'] );
			unset( $attr['sizes'] );
		}

		return $attr;
	}

	/**
	 * Хелпер для создания тега <enclosure>
	 *
	 * @param string $url ссылка
	 *
	 * @return string
	 */
	public function create_enclosure( $url ) {
		$filetype = wp_check_filetype( $url );
		return sprintf( '<enclosure url="%s" type="%s" />', esc_url( $url ), esc_attr( $filetype['type'] ) );
	}

	/**
	 * Вставка <enclosure> в шаблон
	 */
	public function insert_enclosure() {
		foreach ( $this->enclosure as $image ) {
			echo $this->create_enclosure( $image['src'] );
		}

		$this->enclosure = array();
	}

	/**
	 * Генерит валидный тег <link />
	 *
	 * @param string $url ссылка на пост
	 * @param string $src ссылка на кртинку
	 * @param string $title текст ссылки
	 *
	 * @return string
	 */
	public function create_related( $url, $src, $title ) {
		if ( ! empty( $title ) && ! empty( $src ) ) {
			return sprintf( '<link url="%s" img="%s"><![CDATA[%s]]></link>', esc_url( $url ), esc_url( $src ), esc_html( $title ) );
		}
	}

	/**
	 * Вставляет похожие записи.
	 */
	public function insert_related() {

		if ( ! $this->redux->get_option( 'related_posts_enable' ) ) {
			return;
		}

		$related = $this->get_related();

		if ( $related->have_posts() ) {
			// Если включена бесконечная лента.
			if ( $this->redux->get_option( 'related_posts_infinity' ) ) {
				echo '<yandex:related type="infinity">';
			} else {
				echo '<yandex:related>';
			}
			while ( $related->have_posts() ) {
				$related->the_post();
				echo $this->create_related(
					get_permalink(),
					get_the_post_thumbnail_url(),
					get_the_title()
				);
			}
			echo '</yandex:related>';
		}
	}

	/**
	 * Вставка форма обратной связи
	 */
	public function insert_callback() {
		// Если модуль выключен.
		if ( ! $this->redux->get_option( 'callback_enable' ) ) {
			return;
		}

		printf(
			'<form data-type="callback" data-send-to="%s" data-agreement-company="%s" data-agreement-link="%s"></form>',
			esc_attr( $this->redux->get_option( 'callback_send_to' ) ),
			esc_attr( $this->redux->get_option( 'callback_agreement_company' ) ),
			esc_url( $this->redux->get_option( 'callback_agreement_link' ) )
		);
	}

	/**
	 * Вставляет форму поиска.
	 */
	public function insert_search() {
		// Если модуль выключен.
		if ( ! $this->redux->get_option( 'search_enable' ) ) {
			return;
		}
		?>
		<form action="https://yandex.ru/search/?text={text}&site=<?php echo esc_attr( $this->helpers->get_site_domain() ); ?>" method="GET">
			<input type="search" name="text" placeholder="<?php echo esc_attr( $this->redux->get_option( 'search_placeholder' ) ); ?>>" />
		</form>
		<?php
	}

	/**
	 * Вставляет рейтинг звёздами.
	 */
	public function insert_rating() {
		// Если модуль выключен.
		if ( ! $this->redux->get_option( 'rating_enable' ) ) {
			return;
		}
		?>
		<div itemscope itemtype="http://schema.org/Rating">
			<meta itemprop="ratingValue" content="<?php echo esc_attr( wp_rand( $this->redux->get_option( 'rating_min' ), $this->redux->get_option( 'rating_max' ) ) ); ?>">
			<meta itemprop="bestRating" content="<?php echo esc_attr( $this->redux->get_option( 'rating_max' ) ); ?>">
		</div>
		<?php
	}

	/**
	 * Вставляет комментарийй к записям.
	 */
	public function insert_comments() {

		// Если модуль выключен.
		if ( ! $this->redux->get_option( 'comments_enable' ) ) {
			return;
		}

		if ( comments_open() || have_comments() ) {

			// Аргументы получения комментариев
			$comments_args = array(
				'post_id' => get_the_ID(),
				'status'  => 'approve',
				'type'    => 'comment',
			);

			// Фильтруем аргументы получения комментариев
			$comments_args = apply_filters( 'mihdan_yandex_turbo_feed_comments_args', $comments_args );

			// Получаем комментарии
			$comments = get_comments( $comments_args );

			$args = array(
				'style'        => 'div',
				'avatar_size'  => 64,
				'per_page'     => 40, // яндекс обрабатывает не более 40 комментов
				'callback'     => array( $this, 'comments_callback' ),
				'end-callback' => array( $this, 'comments_end_callback' ),
			);

			printf( '<div data-block="comments" data-url="%s#comments">', get_permalink() );
			wp_list_comments( $args, $comments );
			echo '</div>';
		}
	}

	/**
	 * @param $comment
	 * @param $args
	 * @param $depth
	 */
	public function comments_callback( $comment, $args, $depth ) {
		?>
		<div
		data-block="comment"
		data-author="<?php comment_author(); ?>"
		data-avatar-url="<?php echo esc_url( get_avatar_url( $comment, 64 ) ); ?>"
		data-subtitle="<?php echo get_comment_date(); ?> в <?php echo get_comment_time(); ?>"
		>
		<div data-block="content">
			<?php comment_text(); ?>
		</div>
		<?php if ( $args['has_children'] ) : ?>
			<div data-block="comments">
		<?php endif; ?>
		<?php

		return;
	}

	public function comments_end_callback( $comment, $args, $depth ) {
		?>
		</div>
		<?php if ( 1 === $depth ) : ?>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Генерим тег <menu>
	 *
	 * @param string $menu строка с меню
	 *
	 * @return string
	 */
	public function create_menu( $menu ) {
		return sprintf( '<menu>%s</menu>', $menu );
	}

	/**
	 * Вставлем пользовательское меню
	 * в каждый item фида
	 */
	public function insert_menu() {

		// Если юзер сделал меню
		if ( $this->redux->get_option( 'menu_enable' ) && has_nav_menu( $this->slug ) ) {

			// Получить меню
			$menu = wp_nav_menu(
				array(
					'theme_location' => $this->slug,
					'container'      => false,
					'echo'           => false,
					'depth'          => 1,
				)
			);

			// Оставить в меню только ссылки
			$menu = strip_tags( $menu, '<a>' );

			// Вывести меню
			echo $this->create_menu( $menu );
		}
	}

	/**
	 * Создаёт тег для вставки аналитики.
	 *
	 * @param string $type Тип счётчика.
	 * @param string $id Идентификатор счётчика у провайдера.
	 * @param string $params JSON с параметрами.
	 *
	 * @return string
	 */
	public function create_analytics( $type, $id = '', $params = '' ) {
		return sprintf(
			'<turbo:analytics type="%s" id="%s" params="%s"></turbo:analytics>',
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( $params )
		);
	}

	/**
	 * Вставка счетчиков аналитики.
	 */
	public function insert_analytics() {
		if ( $this->redux->get_option( 'analytics_enable' ) ) {
			$yandex_metrika = $this->redux->get_option( 'analytics_yandex_metrika' );
			$live_internet  = $this->redux->get_option( 'analytics_live_internet' );
			$google         = $this->redux->get_option( 'analytics_google' );
			$mail_ru        = $this->redux->get_option( 'analytics_mail_ru' );
			$rambler        = $this->redux->get_option( 'analytics_rambler' );
			$mediascope     = $this->redux->get_option( 'analytics_mediascope' );

			if ( false !== $yandex_metrika && is_array( $yandex_metrika ) ) {
				foreach ( $yandex_metrika as $item ) {
					if ( ! empty( $item ) ) {
						echo $this->create_analytics( 'Yandex', $item );
					}
				}
			}

			if ( false !== $live_internet && is_array( $live_internet ) ) {
				foreach ( $live_internet as $item ) {
					if ( ! empty( $item ) ) {
						echo $this->create_analytics( 'LiveInternet', '', $item );
					}
				}
			}

			if ( false !== $google && is_array( $google ) ) {
				foreach ( $google as $item ) {
					if ( ! empty( $item ) ) {
						echo $this->create_analytics( 'Google', $item );
					}
				}
			}

			if ( false !== $mail_ru && is_array( $mail_ru ) ) {
				foreach ( $mail_ru as $item ) {
					if ( ! empty( $item ) ) {
						echo $this->create_analytics( 'MailRu', $item );
					}
				}
			}

			if ( false !== $rambler && is_array( $rambler ) ) {
				foreach ( $rambler as $item ) {
					if ( ! empty( $item ) ) {
						echo $this->create_analytics( 'Rambler', $item );
					}
				}
			}

			if ( false !== $mediascope && is_array( $mediascope ) ) {
				foreach ( $mediascope as $item ) {
					if ( ! empty( $item ) ) {
						echo $this->create_analytics( 'Mediascope', $item );
					}
				}
			}
		}
	}

	/**
	 * Вставляет блок с шерами
	 */
	public function insert_share() {

		// Если модуль выключен.
		if ( ! $this->redux->get_option( 'share_enable' ) ) {
			return;
		}

		echo sprintf( '<div data-block="share" data-network="%s"></div>', implode( ',', $this->redux->get_option( 'share_networks' ) ) );
	}

	/**
	 * Превращаем абсолютный URL в относительный
	 *
	 * @param string $url исходный URL
	 *
	 * @return mixed
	 */
	public function get_relative_url( $url ) {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . str_replace( $upload_dir['baseurl'], '', $url );
	}


	/**
	 * Получить размеры фотки по абсолютному URL
	 *
	 * @param string $url абсолютный URL
	 *
	 * @return array|bool
	 */
	public function get_image_size( $url ) {
		$relative = $this->get_relative_url( $url );

		return getimagesize( $relative );
	}

	/**
	 * Получить тумбочку поста по его ID
	 *
	 * @param integer $post_id идентификатор поста
	 */
	public function get_futured_image( $post_id ) {

		$url = get_the_post_thumbnail_url( $post_id, 'large' );

		$this->enclosure[] = array(
			'src'     => $url,
			'caption' => esc_attr( get_the_title( $post_id ) ),
		);

	}

	/**
	 * Форматируем контент <item>'а в соответствии со спекой
	 *
	 * Преобразуем HTML-контент в DOM-дерево,
	 * проводим нужные манипуляции с тегами,
	 * преобразуем DOM-дерево обратно в HTML-контент
	 *
	 * @param string $content содержимое <item> фида
	 *
	 * @return string
	 */
	public function content_feed( $content ) {

		if ( is_feed( $this->feedname ) ) {
			$content = $this->strip_tags( $content, $this->allowable_tags );

			/**
			 * Получить тумбочку поста
			 */
			if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail() ) {
				$this->get_futured_image( get_the_ID() );
			}
		}

		return $content;
	}

	/**
	 * Регистрация нашего фида
	 */
	public function add_feed() {

		// Добавить новый фид
		add_feed( $this->feedname, array( $this, 'require_feed_template' ) );
	}

	/**
	 * Hide RSS footer created by WordPress SEO from our RSS feed
	 *
	 * @param  boolean $include_footer Default inclusion value
	 *
	 * @return boolean                 Modified inclusion value
	 */
	public function hide_wpseo_rss_footer( $include_footer = true ) {

		if ( is_feed( $this->feedname ) ) {
			$include_footer = false;
		}

		return $include_footer;
	}

	/**
	 * Подправляем основной луп фида
	 *
	 * @param \WP_Query $wp_query объект запроса
	 */
	public function alter_query( \WP_Query $wp_query ) {

		if ( $wp_query->is_main_query() && $wp_query->is_feed( $this->feedname ) ) {

			// Ограничить посты 50-ю
			$wp_query->set( 'posts_per_rss', $this->redux->get_option( 'feed_total_posts' ) );

			// Впариваем нужные нам типы постов
			$wp_query->set( 'post_type', $this->post_type );

			// Указываем поле для сортировки.
			$wp_query->set( 'orderby', $this->redux->get_option( 'feed_orderby' ) );

			// Указываем направление сортировки.
			$wp_query->set( 'order', $this->redux->get_option( 'feed_order' ) );

			// Получаем текущие мета запросы.
			$meta_query = $wp_query->get( 'meta_query' );

			if ( empty( $meta_query ) ) {
				$meta_query = array();
			}

			// Добавляем исключения.
			$meta_query[] = array(
				'key'     => $this->slug . '_exclude',
				'compare' => 'NOT EXISTS',
			);

			// Исключаем записи с галочкой в админке
			$wp_query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Подключаем шаблон фида
	 */
	public function require_feed_template() {
		require MIHDAN_YANDEX_TURBO_FEED_PATH . 'templates/feed.php';
	}

	/**
	 * Удалить все теги из строки
	 *
	 * Расширенная версия функции `strip_tags` в PHP,
	 * но удаляет также <script>, <style>
	 *
	 * @param string $string исходная строка
	 * @param null|array $allowable_tags массив разрешенных тегов
	 *
	 * @return string
	 */
	public function strip_tags( $string, $allowable_tags = null ) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
		$string = strip_tags( $string, implode( ',', $allowable_tags ) );

		return $string;
	}

	/**
	 * Чистит контент фида от грязи наших плагинов.
	 *
	 * @param string $str строка для очистки
	 * @author mikhail@kobzarev.com
	 * @return string
	 */
	public function clear_xml( $str ) {

		$str = strtr(
			$str,
			array(
				'&'      => '&amp;',
				'>'      => '&gt;',
				'<'      => '&lt;',
				'"'      => '&quot;',
				'\''     => '&apos;',
				'&nbsp;' => ' ',
			)
		);

		$str = force_balance_tags( $str );

		return trim( $str );
	}

	/**
	 * Найти название категории, исходя из соотношений в теме сайта.
	 *
	 * @param integer $category_id идентификатор категории.
	 *
	 * @return bool|int|string
	 */
	public function get_category( $category_id ) {

		return $this->array_search( $category_id, $this->categories );
	}

	/**
	 * Получить название такосномии для соотношений.
	 * По-умолчанию, это category.
	 *
	 * @return array
	 */
	public function get_taxonomy() {
		return (array) $this->taxonomy;
	}

	/**
	 * Рекурсивный поиск в массиве.
	 * Возвращает ключ первого найденного вхождения.
	 *
	 * @param string $needle строка поиска.
	 * @param array $haystack массив, в котором искать.
	 *
	 * @return bool|int|string
	 */
	public function array_search( $needle, $haystack ) {

		foreach ( $haystack as $key => $value ) {
			$current_key = $key;
			if ( $needle === $value || ( is_array( $value ) && $this->array_search( $needle, $value ) !== false ) ) {
				return $current_key;
			}
		}

		return false;
	}

	/**
	 * Сбросить реврайты при активации плагина.
	 */
	public function on_activate() {

		// Добавим флаг, свидетельствующий о том,
		// что нужно сбросить реврайты.
		update_option( $this->slug . '_flush_rewrite_rules', 1, true );

		// Спрячем тур от Redux.
		update_user_meta( get_current_user_id(), 'redux_tour', time() );
	}

	/**
	 * Сбросить реврайты при деактивации плагина.
	 */
	public function on_deactivate() {

		// Сбросить правила реврайтов
		flush_rewrite_rules();
	}

	public function get_categories( $args = [] ) {

		$taxonomy = $this->get_taxonomy();

		$default = [
			'hide_empty' => false,
		];

		$args = wp_parse_args( $args, $default );

		if ( ! empty( $args['post_id'] ) ) {
			$result = wp_get_object_terms( $args['post_id'], $taxonomy, $args );
		} else {
			$result = get_terms( $taxonomy, $args );
		}

		if ( is_wp_error( $result ) ) {
			$result = false;
		}

		return $result;
	}

	/**
	 * Получить массив похожих постов
	 *
	 * @return \WP_Query
	 */
	public function get_related() {

		$post = get_post();

		$args = array(
			'post_type'           => $this->post_type,
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post__not_in'        => array( $post->ID ),
		);

		// Получить ID всех термов поста
		// во всех его таксономиях
		$ids = $this->get_categories(
			array(
				'post_id' => $post->ID,
				'fields'  => 'ids',
			)
		);

		if ( ! empty( $ids ) ) {

			// Получить массив слагов таксономий
			$taxonomies = $this->get_taxonomy();

			// Если переданы таксономии
			if ( $taxonomies ) {

				// Если таксономий больше одной,
				// ставим логику ИЛИ
				if ( count( $taxonomies ) > 1 ) {
					$args['tax_query']['relation'] = 'OR';
				}

				// Формируем запрос на поиск по термам
				// для каждой таксономии
				foreach ( $taxonomies as $taxonomy ) {
					$args['tax_query'][] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'id',
						'terms'    => $ids,
					);
				}
			}
		}

		// Фильтруем аргументы запроса похожих постов.
		$args = apply_filters( 'mihdan_yandex_turbo_feed_related_args', $args );

		$query = new \WP_Query( $args );

		return $query;
	}
}

// eol.
