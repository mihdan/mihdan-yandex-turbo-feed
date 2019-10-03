<?php
/**
 * Основной класс плагина.
 *
 * @package mihdan-yandex-turbo-feed
 */

namespace Mihdan\YandexTurboFeed;

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
	 * @var Utils
	 */
	private $utils;

	/**
	 * @var array $enclosure для хранения фото у поста
	 */
	private $enclosure = array();

	/**
	 * @var array Массив похожих постов
	 */
	private $related = array();

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
	private $settings;

	/**
	 * @var Notifications
	 */
	private $notifications;

	/**
	 * @var Template
	 */
	private $template;

	/**
	 * @var integer $feed_id Идентификатор фида
	 */
	private $feed_id;

	/**
	 * Инициализируем нужные методы
	 *
	 * Main constructor.
	 *
	 * @param Utils         $utils    Утилиты/Хелперы.
	 * @param Settings      $settings Ностройки.
	 * @param Template      $template Шаблон.
	 * @param Notifications $notices  Уведомления в админке.
	 */
	public function __construct( Utils $utils = null, Settings $settings = null, Template $template = null, Notifications $notices = null ) {
		$this->includes();
		$this->utils         = new Utils();
		$this->settings      = new Settings( $this->utils );
		$this->notifications = new Notifications( $this->settings );
		$this->template      = new Template( $this->settings );

		$this->categories = apply_filters( 'mihdan_yandex_turbo_feed_categories', array() );

		//$this->feedname  = $this->settings->get_option( 'slug' );

		$this->hooks();
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
		add_action( 'init', array( $this, 'flush_rewrite_rules' ), 99 );
		add_filter( 'mihdan_yandex_turbo_feed_args', array( $this, 'alter_query' ), 9 );
		add_action( 'after_setup_theme', array( $this, 'register_nav_menu' ) );
		add_action( 'plugins_loaded', array( $this, 'load_translations' ) );

		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_enclosure' ) );





		add_filter( 'the_content_feed', array( $this, 'content_feed' ) );
		add_filter( 'the_content_feed', array( $this, 'invisible_border' ) );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'image_attributes' ), 10, 3 );
		add_action( 'template_redirect', array( $this, 'send_headers_for_aio_seo_pack' ), 20 );
		add_action( 'template_redirect', array( $this, 'set_feed_id' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		register_activation_hook( MIHDAN_YANDEX_TURBO_FEED_FILE, array( $this, 'on_activate' ) );
		register_deactivation_hook( MIHDAN_YANDEX_TURBO_FEED_FILE, array( $this, 'on_deactivate' ) );
	}

	public function set_feed_id() {
		$this->feed_id = get_the_ID();
	}

	/**
	 * Загружаем ресурсы для плагина.
	 */
	public function assets() {
		wp_enqueue_script( MIHDAN_YANDEX_TURBO_FEED_SLUG, MIHDAN_YANDEX_TURBO_FEED_URL . 'admin/js/app.js', array( 'wp-util' ), filemtime( MIHDAN_YANDEX_TURBO_FEED_PATH . '/admin/js/app.js' ) );
		wp_enqueue_style( MIHDAN_YANDEX_TURBO_FEED_SLUG, MIHDAN_YANDEX_TURBO_FEED_URL . 'admin/css/app.css', array(), filemtime( MIHDAN_YANDEX_TURBO_FEED_PATH . '/admin/css/app.css' ) );
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
	public function send_headers_for_aio_seo_pack() { var_dump(get_the_ID());die;
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

		if ( $this->settings->get_option( 'invisible_border_enable' ) ) {
			$content = str_replace( '<table', '<table data-invisible="true"', $content );
		}

		return $content;
	}

	/**
	 * Регистрируем переводы.
	 */
	public function load_translations() {
		load_plugin_textdomain( 'mihdan-yandex-turbo-feed', false, MIHDAN_YANDEX_TURBO_FEED_PATH . '/languages' );
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
	 * Подправляем основной луп фида
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function alter_query( $args ) {

		// Ограничить посты 50-ю
		$args['posts_per_page'] = $this->settings->get_option( 'total_posts' );

		// Впариваем нужные нам типы постов
		$args['post_type'] = $this->settings->get_option( 'post_type' );

		// Указываем поле для сортировки.
		$args['orderby'] = $this->settings->get_option( 'orderby' );

		// Указываем направление сортировки.
		$args['order'] = $this->settings->get_option( 'order' );

		// Добавляем исключения.
		$args['meta_query'] = array(
			array(
				'key'     => $this->slug . '_exclude',
				'compare' => '!=',
				'value'   => '1',
			),
		);

		// TODO: потеряли категории и теги.

		return $args;
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
	}

	/**
	 * Сбросить реврайты при деактивации плагина.
	 */
	public function on_deactivate() {

		// Сбросить правила реврайтов
		flush_rewrite_rules();
	}




}

// eol.
