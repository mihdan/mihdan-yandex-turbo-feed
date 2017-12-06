<?php
/**
 * Mihdan: Yandex Turbo Feed
 *
 * @package   mihdan-yandex-turbo-feed
 * @author    Mikhail Kobzarev
 * @link      https://github.com/mihdan/mihdan-yandex-turbo-feed/
 * @copyright Copyright (c) 2017
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

/**
 * Plugin Name: Mihdan: Yandex Turbo Feed
 * Plugin URI: https://www.kobzarev.com/projects/yandex-turbo-feed/
 * Description: Плагин генерирует фид для сервиса Яндекс Турбо
 * Version: 1.0.1
 * Author: Mikhail Kobzarev
 * Author URI: https://www.kobzarev.com/
 * License: GNU General Public License v2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mihdan-yandex-turbo-feed
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-yandex-turbo-feed/
 * GitHub Branch:     master
 * Requires WP:       4.6
 * Requires PHP:      5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//require_once __DIR__ . '/vendor/autoload.php';

//use DiDom\Document;
//use DiDom\Element;
//use DiDom\Query;

if ( ! class_exists( 'Mihdan_Yandex_Turbo_Feed' ) ) {

	/**
	 * Class Mihdan_Yandex_Turbo_Feed
	 */
	class Mihdan_Yandex_Turbo_Feed {

		/**
		 * @var string слюг плагина
		 */
		private $slug = 'mihdan_yandex_turbo_feed';

		/**
		 * @var string $feedname слюг фида
		 */
		public $feedname;

		/**
		 * @var string $copyright текст копирайта для фото
		 */
		private $copyright;

		/**
		 * @var integer $posts_per_rss максимальное количество постов в ленте
		 */
		private $posts_per_rss;

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
		);

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
		 * Хранит экземпляр класса
		 *
		 * @var $instance
		 */
		private static $instance;

		/**
		 * Соотношение категорий.
		 *
		 * @var
		 */
		private $categories;

		/**
		 * Таксономия для соотношений.
		 *
		 * @var string
		 */
		private $taxonomy = array( 'category' );

		/**
		 * Вернуть единственный экземпляр класса
		 *
		 * @return Mihdan_Yandex_Turbo_Feed
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Инициализируем нужные методы
		 *
		 * Mihdan_FAQ constructor.
		 */
		private function __construct() {
			$this->setup();
			$this->includes();
			$this->hooks();
		}

		/**
		 * Установка основных переменных плагина
		 */
		private function setup() {
			$this->dir_path = apply_filters( 'mihdan_yandex_turbo_feed_dir_path', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			$this->dir_uri   = apply_filters( 'mihdan_yandex_turbo_feed_dir_uri', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		}

		/**
		 * Фильтры для переопределения настроек внутри темы
		 */
		public function after_setup_theme() {
			$this->posts_per_rss = apply_filters( 'mihdan_yandex_turbo_feed_posts_per_rss', 500 );
			$this->categories = apply_filters( 'mihdan_yandex_turbo_feed_categories', array() );
			$this->taxonomy = apply_filters( 'mihdan_yandex_turbo_feed_taxonomy', $this->taxonomy );
			$this->feedname = apply_filters( 'mihdan_yandex_turbo_feed_feedname', $this->slug );
			$this->allowable_tags = apply_filters( 'mihdan_yandex_turbo_feed_allowable_tags', $this->allowable_tags );
			$this->copyright = apply_filters( 'mihdan_yandex_turbo_feed_copyright', parse_url( get_home_url(), PHP_URL_HOST ) );

			// Подчеркивание нельзя использовать на старых серверах.
			$this->feedname = str_replace( '_', '-', $this->feedname );
		}

		/**
		 * Подключаем зависимости
		 */
		private function includes() {}

		/**
		 * Хукаем.
		 */
		private function hooks() {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'pre_get_posts', array( $this, 'alter_query' ) );
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
			add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_enclosure' ) );
			add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_related' ) );
			add_filter( 'the_content_feed', array( $this, 'content_feed' ) );
			add_filter( 'wp_get_attachment_image_attributes', array( $this, 'image_attributes' ), 10, 3 );
			add_filter( 'wpseo_include_rss_footer', array( $this, 'hide_wpseo_rss_footer' ) );

			register_activation_hook( __FILE__, array( $this, 'on_activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );

			if ( is_admin() ) {
				// Страница настроек в админке
				require ( $this->dir_path . 'admin/settings.php' );
			}
		}

		/**
		 * Удалить ненужные атрибуты при генерации картинок
		 *
		 * @param array $attr
		 * @param WP_Post $attachment объект вложения
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
			return sprintf( '<link url="%s" img="%s">%s</link>', esc_url( $url ), esc_url( $src ), esc_html( $title ) );
		}

		public function insert_related() {
			$related = $this->get_related();

			if ( $related->have_posts() ) {
				echo '<yandex:related>';
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
				'src' => $url,
				'caption' => esc_attr( get_the_title( $post_id ) ),
			);

		}

		/**
		 * Генерим валидный тег <figure>
		 *
		 * @param $src
		 * @param $caption
		 * @param $copyright
		 *
		 * @return Element
		 */
		public function create_valid_structure( $src, $caption, $copyright, $width, $height ) {

			// Создаем тег <figure>
			$figure = new Element( 'figure' );

			// Создаем тег <img>
			$img = new Element( 'img', null, array(
				'src' => $src,
				'width' => $width,
				'height' => $height,
			) );

			// Создаем тег <figcaption>
			$figcaption = new Element( 'figcaption', $caption );

			// Создаем тег <span class="copyright">
			$copyright = new Element( 'span', $copyright, array(
				'class' => 'copyright',
			) );

			// Вкладываем тег <img> в <figure>
			$figure->appendChild( $img );

			// Вкладываем тег <span class="copyright"> в <figcaption>
			$figcaption->appendChild( $copyright );

			// Вкладываем тег <figcaption> в <figure>
			$figure->appendChild( $figcaption );

			return $figure;
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
			if ( 1 == 2 && is_feed( $this->feedname ) ) {

				$this->enclosure = array();

				$content = $this->strip_tags( $content, $this->allowable_tags );
				$content = $this->clear_xml( $content );

				$document = new Document();
				//$document->format( true );

				// Не добавлять теги <html>, <body>, <doctype>
				$document->loadHtml( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS );

				$copyright = $this->copyright;

				/**
				 * Получить тумбочку поста
				 */
				if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail() ) {
					$this->get_futured_image( get_the_ID() );
				}

				/**
				 * Если включена поддержка тегов <figure> на уровне двигла,
				 * то теги <figure>, <figcaption> уже есть и надо добавить
				 * только span.copyright
				 */
				if ( current_theme_supports( 'html5', 'caption' ) ) {

					$figures = $document->find( 'figure.wp-caption' );

					foreach ( $figures as $figure ) {

						/** @var Element $figure */
						/** @var Element $image */

						// Ищем картинку <img class="wp-image-*">
						$image = $figure->first( 'img[class*="wp-image"]' );
						$src = $image->attr( 'src' );
						$size = $this->get_image_size( $src );

						// Ищем подпись <figcaption class="wp-caption-text">
						$figcaption = $image->nextSibling( 'figcaption.wp-caption-text' );
						$caption = $figcaption->text();

						$this->enclosure[] = array(
							'src' => $src,
							'caption' => $caption,
						);

						$figure->replace( $this->create_valid_structure( $src, $caption, $copyright, $size[0], $size[1] ) );
					}
				} else {
					$figures = $document->find( 'div.wp-caption' );

					foreach ( $figures as $figure ) {

						/** @var Element $figure */
						/** @var Element $image */

						// Ищем картинку <img class="wp-image-*">
						$image = $figure->first( 'img[class*="wp-image-"]' );
						$src = $image->attr( 'src' );
						$size = $this->get_image_size( $src );

						// Ищем подпись <figcaption class="wp-caption-text">
						$figcaption = $image->nextSibling( 'p.wp-caption-text' );
						$caption = $figcaption->text();

						$this->enclosure[] = array(
							'src' => $src,
							'caption' => $caption,
						);

						$figure->replace( $this->create_valid_structure( $src, $caption, $copyright, $size[0], $size[1] ) );
					}
				} // End if().

				/**
				 * Если нет ни HTML5 ни HTML4 нотации,
				 * ищем простые теги <img>, ставим их ALT
				 * в <figcaption>, и добавляем <span class="copyright">
				 */
				$images = $document->find( 'p > img[class*="wp-image-"]' );

				if ( $images ) {
					foreach ( $images as $image ) {
						/** @var Element $image */
						/** @var Element $paragraph */
						$paragraph = $image->parent();
						$src = $image->attr( 'src' );
						$size = $this->get_image_size( $src );

						$caption = $image->attr( 'alt' );

						$this->enclosure[] = array(
							'src' => $src,
							'figcaption' => $caption,
						);

						// Заменяем тег <img> на сгенерированую конструкцию
						$paragraph->replace( $this->create_valid_structure( $src, $caption, $copyright, $size[0], $size[1] ) );

					}
				}

				/**
				 * Если нет ни HTML5 ни HTML4 нотации,
				 * ищем простые теги <img> внутри <div>
				 */
				$images = $document->find( 'div > img' );

				if ( $images ) {
					foreach ( $images as $image ) {
						/** @var Element $image */
						/** @var Element $paragraph */
						$paragraph = $image->parent();
						$src = $image->attr( 'src' );
						$size = $this->get_image_size( $src );
						$caption = $image->attr( 'alt' );

						$this->enclosure[] = array(
							'src' => $src,
							'figcaption' => $caption,
						);

						// Заменяем тег <img> на сгенерированую конструкцию
						$paragraph->replace( $this->create_valid_structure( $src, $caption, $copyright, $size[0], $size[1] ) );

					}
				}

				$content = $document->format( true )->html();
			} // End if().

			return $content;
		}

		/**
		 * Регистрация нашего фида
		 */
		public function init() {
			// Добавить новый фид
			add_feed( $this->feedname, array( $this, 'add_feed' ) );
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
		 * @param WP_Query $wp_query объект запроса
		 */
		public function alter_query( WP_Query $wp_query ) {
			if ( $wp_query->is_main_query() && $wp_query->is_feed() && $this->slug === $wp_query->get( 'feed' ) ) {

				// Ограничить посты 50-ю
				$wp_query->set( 'posts_per_rss', $this->posts_per_rss );
			}
		}

		public function add_feed() {
			require $this->dir_path . 'templates/feed.php';
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

			$str = str_replace( array(
				'&', '>', '<', '"', '\'', '&nbsp;',
			), array(
				'&amp;', '&gt;', '&lt;', '&quot;', '&apos;', ' ',
			), $str );

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
		 * @return string
		 */
		public function get_taxonomy() {
			return $this->taxonomy;
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
				if ( $needle === $value or ( is_array( $value ) && $this->array_search( $needle, $value ) !== false ) ) {
					return $current_key;
				}
			}

			return false;
		}

		/**
		 * Сбросить реврайты при активации плагина.
		 */
		public function on_activate() {
			if ( current_user_can( 'activate_plugins' ) ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Сбросить реврайты при деактивации плагина.
		 */
		public function on_deactivate() {
			if ( current_user_can( 'activate_plugins' ) ) {
				flush_rewrite_rules();
			}
		}

		public function get_categories( $args = [] ) {

			$taxonomy = $this->taxonomy;

			$default = [
				'hide_empty' => false
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

		public function get_related() {

			$post = get_post();

			$args = array(
				'post_type' => 'post',
				'posts_per_page' => 10,
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'post__not_in' => array( $post->ID ),
				'orderby' => 'rand',
			);

			// Получить посты из той же категории.
			$categories = $this->get_categories( array(
				'post_id' => $post->ID
			));

			if ( ! empty( $categories ) ) {
				$category = array_shift( $categories );
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'category',
						'field' => 'id',
						'terms' => $category->term_id,
					),
				);
			}

			$query = new WP_Query( $args );

			return $query;
		}
	}

	function mihdan_yandex_turbo_feed() {
		return Mihdan_Yandex_Turbo_Feed::get_instance();
	}

	mihdan_yandex_turbo_feed();
}