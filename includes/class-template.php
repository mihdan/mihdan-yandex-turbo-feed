<?php
/**
 * @package mihdan-yandex-turbo-feed
 */

namespace Mihdan\YandexTurboFeed;

use WP_Query;

/**
 * Class Template
 *
 * @package Mihdan\YandexTurboFeed
 */
class Template {
	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var Utils
	 */
	private $utils;

	/**
	 * @var int $feed_id Идентификатор фида.
	 */
	private $feed_id;

	/**
	 * Template constructor.
	 *
	 * @param Utils    $utils
	 * @param Settings $settings
	 */
	public function __construct( Utils $utils, Settings $settings ) {
		$this->utils    = $utils;
		$this->settings = $settings;

		$this->hooks();
	}

	/**
	 * Hooks Init
	 */
	public function hooks() {
		add_action( 'template_redirect', [ $this, 'render' ], 50 );
		add_action( 'mihdan_yandex_turbo_feed_channel', array( $this, 'insert_analytics' ) );

		add_filter( 'render_block', [ $this, 'insert_gallery' ], 10, 2 );

		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_share' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_search' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_comments' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_callback' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_rating' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_extended_html' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_turbo_source' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_turbo_content', array( $this, 'insert_turbo_topic' ) );

		add_filter( 'mihdan_yandex_turbo_feed_item_pre_get_the_content', array( $this, 'exclude_shortcodes' ), 10, 2 );
		add_filter( 'mihdan_yandex_turbo_feed_item_pre_get_the_content', array( $this, 'exclude_bad_symbols' ), 10, 2 );
		add_filter( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'exclude_regex' ), 1000, 2 );

		add_action( 'mihdan_yandex_turbo_feed_item_header', array( $this, 'insert_menu' ) );
		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_category' ) );
		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_related' ) );
		add_action( 'wp', array( $this, 'authenticate' ) );

		// The SEO Framework.
		add_action( 'the_seo_framework_after_front_init', [ $this, 'disable_seo_framework_for_feed'] );

		// All In One SEO Pack.
		add_action( 'template_redirect', array( $this, 'send_headers_for_aio_seo_pack' ), 20 );

		// SEO by Yoast.
		add_filter( 'wpseo_include_rss_footer', array( $this, 'hide_wpseo_rss_footer' ) );
		add_filter( 'wpseo_sitemap_exclude_post_type', array( $this, 'hide_wpseo_cpt' ), 10, 2 );
		add_filter( 'wpseo_accessible_post_types', array( $this, 'hide_wpseo_metabox' ) );
	}

	/**
     * Конвертирует залерею Gutenberg в галерею Turbo.
     *
	 * @param string $block_content HTML блока.
	 * @param array  $block         Массив с данными блока.
	 *
	 * @return string
	 */
	public function insert_gallery ( $block_content, $block ) {

	    if ( ! is_singular( $this->utils->get_post_type() ) ) {
	        return $block_content;
        }

		if ( 'core/gallery' === $block['blockName'] ) {
		    $size = ( isset( $block['attrs']['sizeSlug'] ) ) ? $block['attrs']['sizeSlug'] : 'thumbnail';

		    if ( isset( $block['attrs']['ids'] ) && is_array( $block['attrs']['ids'] ) ) {
		        $block_content = '<div data-block="gallery">';

		        foreach ( $block['attrs']['ids'] as $attachment_id ) {
			        $block_content .= sprintf(
                        '<img src="%s"/>',
                        wp_get_attachment_image_url( $attachment_id, $size )
                    );
                }

		        $block_content .= '</div>';
		    }
		}

		return $block_content;
	}

	/**
     * Добавляет тег turbo:source в ленту,
     * если данная настройка включена для записи.
     *
	 * @param int $post_id Идентификатор записи.
	 */
	public function insert_turbo_source( $post_id ) {
		if ( empty( $this->settings->get_option( 'turbo_source', $post_id ) ) ) {
			return;
		}
        ?>
        <turbo:source><?php echo esc_url( $this->settings->get_option( 'turbo_source', $post_id ) ); ?></turbo:source>
        <?php
    }

	/**
	 * Добавляет тег turbo:topic в ленту,
	 * если данная настройка включена для записи.
	 *
	 * @param int $post_id Идентификатор записи.
	 */
	public function insert_turbo_topic( $post_id ) {
		if ( empty( $this->settings->get_option( 'turbo_topic', $post_id ) ) ) {
			return;
		}
		?>
        <turbo:topic><?php echo esc_html( $this->settings->get_option( 'turbo_topic', $post_id ) ); ?></turbo:topic>
		<?php
	}

	/**
	 * Исключает блоки из ленты по регулярному выражению.
	 *
	 * @param string $content Содержимое записи.
	 * @param int    $post_id Идентификатор записи.
	 *
	 * @return string
	 */
	public function exclude_shortcodes( $content, $post_id ) {

		if ( ! $this->settings->get_option( 'exclude_enable', $this->feed_id ) ) {
			return $content;
		}

        $shortcodes = $this->settings->get_option( 'excluded_shortcodes', $this->feed_id );

		if ( empty( $shortcodes ) ) {
			return $content;
		}

		// Удаляем из текста только указанные шорткоды.
		foreach ( $shortcodes as $shortcode ) {
			$regex   = get_shortcode_regex( [ $shortcode ] );
			$regex   = sprintf( '/%s/', $regex );
			$content = preg_replace( $regex, '', $content );
		}

		return $content;
	}

	/**
	 * Исключает плохие символы из айтемов ленты,
     * например, злосчастный #more-1234.
	 *
	 * @param string $content Содержимое записи.
	 * @param int    $post_id Идентификатор записи.
	 *
	 * @return string
	 */
	public function exclude_bad_symbols( $content, $post_id ) {

	    $content = preg_replace( '/#more\-[0-9]+/is', '', $content );

	    return $content;
	}

	/**
     * Исключает блоки из ленты по регулярному выражению.
     *
     * TODO: прикрутить xpath/DiDOM.
     *
	 * @param string $content Содержимое записи.
	 * @param int    $post_id Идентификатор записи.
	 *
	 * @return array|mixed|string|string[]|null
	 */
	public function exclude_regex( $content, $post_id ) {

		if ( ! $this->settings->get_option( 'exclude_enable', $this->feed_id ) ) {
			return $content;
		}

		$rules = trim( $this->settings->get_option( 'excluded_regex', $this->feed_id ) );

		if ( empty( $rules ) ) {
			return $content;
		}

		foreach ( explode( PHP_EOL, $rules ) as $rule ) {
		    $content = preg_replace( sprintf( '#%s#si', $rule ), '', $content );
		}

	    return $content;
    }

	/**
	 * Добавляет тег turbo:extendedHtml в ленту,
	 * если данная настройка включена для записи.
	 *
	 * @param int $post_id Идентификатор записи.
	 */
	public function insert_extended_html( $post_id ) {
		if ( ! $this->settings->get_option( 'turbo_extended_html', $post_id ) ) {
			return;
		}
		?>
        <turbo:extendedHtml>true</turbo:extendedHtml>
		<?php
	}

	/**
     * Exclude One Content Type From Yoast SEO Sitemap.
     *
	 * @param bool   $boolean   Hide or no.
	 * @param string $post_type Post type.
	 *
	 * @return bool
	 */
	public function hide_wpseo_cpt( $boolean, $post_type ) {
	    if ( $post_type === $this->utils->get_post_type() ) {
	        $boolean = true;
        }

	    return $boolean;
    }

	/**
	 * Hide WPSEO metabox from our CPT.
     *
     * @param array $post_types Post types for filter.
     *
     * @return array
	 */
	public function hide_wpseo_metabox( $post_types ) {

        if ( is_array( $post_types ) && isset( $post_types[ $this->utils->get_post_type() ] ) ) {
	        unset( $post_types[ $this->utils->get_post_type() ] );
        }

        return $post_types;
    }

	/**
	 * Hide RSS footer created by WordPress SEO from our RSS feed.
	 *
	 * @param  boolean $include_footer Default inclusion value
	 *
	 * @return boolean                 Modified inclusion value
	 */
	public function hide_wpseo_rss_footer( $include_footer = true ) {

		if ( is_singular( $this->utils->get_post_type() ) ) {
			$include_footer = false;
		}

		return $include_footer;
	}

	/**
	 * Добавим заголовок `X-Robots-Tag`
	 * для решения проблемы с сеошными плагинами.
	 */
	public function send_headers_for_aio_seo_pack() {
		if ( is_singular( $this->utils->get_post_type() ) ) {
			header( 'X-Robots-Tag: index, follow', true );
		}
	}

	/**
	 * Disable inserting source link
	 * by The SEO Framework plugin from excerpt.
	 */
	public function disable_seo_framework_for_feed() {

		if ( false === strpos( $_SERVER['REQUEST_URI'], '/turbo/' ) ) {
			return;
		}

		$instance = the_seo_framework();
		remove_filter( 'the_content_feed', [ $instance, 'the_content_feed' ] );
		remove_filter( 'the_excerpt_rss', [ $instance, 'the_content_feed' ] );
	}

	/**
	 * Вставляет блок с шерами
	 */
	public function insert_share() {

		// Если модуль выключен.
		if ( ! $this->settings->get_option( 'share_enable', $this->feed_id ) ) {
			return;
		}

		echo sprintf( '<div data-block="share" data-network="%s"></div>', implode( ',', $this->settings->get_option( 'share_networks', $this->feed_id ) ) );
	}

	/**
	 * Вставляет форму поиска.
	 */
	public function insert_search() {
		// Если модуль выключен.
		if ( ! $this->settings->get_option( 'search_enable', $this->feed_id ) ) {
			return;
		}
		if ( ! isset( $this->settings->providers[ $this->settings->get_option( 'search_provider', $this->feed_id ) ] ) ) {
			return;
		}
		?>
		<form action="<?php echo esc_attr( $this->settings->providers[ $this->settings->get_option( 'search_provider', $this->feed_id ) ]['url'] ); ?>" method="GET">
			<input type="search" name="text" placeholder="<?php echo esc_attr( $this->settings->get_option( 'search_placeholder', $this->feed_id ) ); ?>>" />
		</form>
		<?php
	}

	/**
	 * Вставляет комментарийй к записям.
	 */
	public function insert_comments() {

		// Если модуль выключен.
		if ( ! $this->settings->get_option( 'comments_enable', $this->feed_id ) ) {
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
	 * Вставка форма обратной связи
	 */
	public function insert_callback() {
		// Если модуль выключен.
		if ( ! $this->settings->get_option( 'callback_enable', $this->feed_id ) ) {
			return;
		}

		printf(
			'<form data-type="callback" data-send-to="%s" data-agreement-company="%s" data-agreement-link="%s"></form>',
			esc_attr( $this->settings->get_option( 'callback_send_to', $this->feed_id ) ),
			esc_attr( $this->settings->get_option( 'callback_agreement_company', $this->feed_id ) ),
			esc_url( $this->settings->get_option( 'callback_agreement_link', $this->feed_id )['url'] )
		);
	}

	/**
	 * Вставляет рейтинг звёздами.
	 */
	public function insert_rating( $post_id ) {
		// Если модуль выключен.
		if ( ! $this->settings->get_option( 'rating_enable', $this->feed_id ) ) {
			return;
		}

		$min = $this->settings->get_option( 'rating_min', $this->feed_id );
		$max = $this->settings->get_option( 'rating_max', $this->feed_id );

		if ( 'random' === $this->settings->get_option( 'rating_value', $this->feed_id ) ) {
		    $value = wp_rand( $min, $max );
        } else {
		    $value = get_post_meta( $post_id, $this->settings->get_option( 'rating_value', $this->feed_id ), true );

		    if ( $value < $min ) {
		        $value = $min;
		    }

			if ( $value > $max ) {
				$value = $max;
			}
        }
		?>
		<div itemscope itemtype="http://schema.org/Rating">
			<meta itemprop="ratingValue" content="<?php echo esc_attr( $value ); ?>">
			<meta itemprop="worstRating" content="<?php echo esc_attr( $this->settings->get_option( 'rating_min', $this->feed_id ) ); ?>">
			<meta itemprop="bestRating" content="<?php echo esc_attr( $this->settings->get_option( 'rating_max', $this->feed_id ) ); ?>">
		</div>
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
		if ( $this->settings->get_option( 'menu_enable', $this->feed_id ) && has_nav_menu( $this->utils->get_slug() ) ) {

			// Получить меню
			$menu = wp_nav_menu(
				array(
					'theme_location' => $this->utils->get_slug(),
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
				//'fields'  => 'id=>name',
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
	 * Генерит валидный тег <link />
	 *
	 * @param string $url   Ссылка на запись.
	 * @param string $title Текст ссылки.
	 * @param ?string $src   Ссылка на изображение.
	 *
	 * @return string
	 */
	public function create_related( string $url, string $title, ?string $src = '' ): string {
		if ( ! empty( $url ) && ! empty( $title ) ) {
			// Если есть изображение.
			if ( ! empty( $src ) ) {
				return sprintf( '<link url="%s" img="%s"><![CDATA[%s]]></link>', esc_url( $url ), esc_url( $src ), esc_html( $title ) );
			} else {
				return sprintf( '<link url="%s"><![CDATA[%s]]></link>', esc_url( $url ), esc_html( $title ) );
			}
		}

		return '';
	}

	/**
	 * Вставляет похожие записи.
	 */
	public function insert_related() {

		if ( ! $this->settings->get_option( 'related_posts_enable', $this->get_feed_id() ) ) {
			return;
		}

		$related = $this->get_related();

		if ( $related->have_posts() ) {
			// Если включена бесконечная лента.
			if ( $this->settings->get_option( 'related_posts_infinity', $this->get_feed_id() ) ) {
				echo '<yandex:related type="infinity">';
			} else {
				echo '<yandex:related>';
			}
			while ( $related->have_posts() ) {
				$related->the_post();
				echo $this->create_related(
					get_permalink(),
					get_the_title(),
					get_the_post_thumbnail_url(),
				);
			}
			echo '</yandex:related>';
		}
	}

	/**
	 * Получить массив похожих постов
	 *
	 * @return WP_Query
	 */
	public function get_related() {

		$post = get_post();

		$args = array(
			'post_type'           => $this->settings->get_option( 'post_type', $this->get_feed_id() ),
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
			$taxonomies = $this->settings->get_taxonomy();

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

		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => $this->utils->get_slug() . '_exclude',
				'compare' => '=',
				'value'   => '0',
			),
			array(
				'key'     => $this->utils->get_slug() . '_exclude',
				'compare' => 'NOT EXISTS',
			),
		);

		// Фильтруем аргументы запроса похожих постов.
		$args = apply_filters( 'mihdan_yandex_turbo_feed_related_args', $args );

		return new WP_Query( $args );
	}

	public function get_categories( $args = [] ) {

		$taxonomy = $this->settings->get_option( 'taxonomy', $this->feed_id );

		$default = [
			'hide_empty' => false,
			'orderby'    => 'none',
		];

		$args = wp_parse_args( $args, $default );

		if ( ! empty( $args['post_id'] ) ) {
			$post_id = $args['post_id'];
			unset( $args['post_id'] );
			$result = wp_get_object_terms( $post_id, $taxonomy, $args );
		} else {
			$result = get_terms( $taxonomy, $args );
		}

		if ( is_wp_error( $result ) ) {
			$result = false;
		}

		return $result;
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
			'<turbo:analytics type="%s" id="%s" params="%s"></turbo:analytics>' . PHP_EOL,
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( $params )
		);
	}

	/**
	 * Вставка счетчиков аналитики.
	 */
	public function insert_analytics() {
		if ( ! $this->settings->get_option( 'analytics_enable', $this->feed_id ) ) {
			return;
		}

		$yandex_metrika = $this->settings->get_option( 'analytics_yandex_metrika', $this->feed_id );
		$live_internet  = $this->settings->get_option( 'analytics_live_internet', $this->feed_id );
		$google         = $this->settings->get_option( 'analytics_google', $this->feed_id );
		$mail_ru        = $this->settings->get_option( 'analytics_mail_ru', $this->feed_id );
		$rambler        = $this->settings->get_option( 'analytics_rambler', $this->feed_id );
		$mediascope     = $this->settings->get_option( 'analytics_mediascope', $this->feed_id );

		if ( $yandex_metrika ) {
			echo $this->create_analytics( 'Yandex', $yandex_metrika );
		}

		if ( $live_internet ) {
			echo $this->create_analytics( 'LiveInternet', '', $live_internet );
		}

		if ( $google ) {
			echo $this->create_analytics( 'Google', $google );
		}

		if ( $mail_ru ) {
			echo $this->create_analytics( 'MailRu', $mail_ru );
		}

		if ( $rambler ) {
			echo $this->create_analytics( 'Rambler', $rambler );
		}

		if ( $mediascope ) {
			echo $this->create_analytics( 'Mediascope', $mediascope );
		}
	}

	/**
	 * Render Feed Template.
	 */
	public function render() {
		if ( is_singular( $this->utils->get_post_type() ) ) {
			require MIHDAN_YANDEX_TURBO_FEED_PATH . '/templates/feed.php';
			die;
		}
	}

	/**
	 * Отправляет заголовки авторизации.
	 */
	public function auth_headers() {
		header( 'WWW-Authenticate: Basic realm="Access Denied"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		die( __( 'Для доступа к ленте нужно ввести пароль', 'mihdan-yandex-turbo-feed' ) );
	}

	/**
	 * Проверка введенного логина и пароля.
	 */
	public function authenticate() {

		$this->feed_id = get_the_ID();

		if ( ! is_singular( $this->utils->get_post_type() ) ) {
			return;
		}

		if ( ! $this->settings->get_option( 'access_enable', $this->feed_id ) ) {
			return;
		}

		if ( empty( $_SERVER['PHP_AUTH_USER'] ) || empty( $_SERVER['PHP_AUTH_PW'] ) ) {
			$this->auth_headers();
		} else {
			$login    = $this->settings->get_option( 'access_login', $this->feed_id );
			$password = $this->settings->get_option( 'access_password', $this->feed_id );

			if ( $_SERVER['PHP_AUTH_USER'] !== $login || $_SERVER['PHP_AUTH_PW'] !== $password ) {
				$this->auth_headers();
			}
		}
	}

	/**
	 * Генерим атрибуты для тега <item>
	 *
	 * @param int $post_id идентификатор поста
	 *
	 * @return void
	 */
	public function item_attributes( int $post_id ): void {

		$atts = array(
			'turbo' => ! (bool) get_post_meta( $post_id, $this->utils->get_slug() . '_remove', true ),
		);

		// Если активна опция отключения всех тербо-страниц.
		$disable_all_turbo_pages = (bool) $this->settings->get_option( 'remove_all_turbo_posts', $this->get_feed_id(), false );

		if ( $disable_all_turbo_pages ) {
			$atts['turbo'] = false;
		}

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

	public function get_excerpt( $content, $post_id, $more_link_text ) {
		$content  = apply_filters( 'mihdan_yandex_turbo_feed_item_pre_get_the_excerpt', $content, $post_id );
		$content = get_extended( $content );
        $content  = apply_filters( 'the_content', $content['main'] );

		$content = sprintf(
            '%s<p><a href="%s" target="_blank">%s</a></p>',
            $content,
            esc_url( add_query_arg( 'utm_source', 'turbo', get_permalink( $post_id ) ) ),
            $more_link_text
        );

	    return apply_filters( 'mihdan_yandex_turbo_feed_item_get_the_excerpt', $content, $post_id );
    }
	public function get_content( $content, $post_id ) {
		$content  = apply_filters( 'mihdan_yandex_turbo_feed_item_pre_get_the_content', $content, $post_id );
		$extended = get_extended( $content );

		return apply_filters(
            'mihdan_yandex_turbo_feed_item_get_the_content',
            apply_filters( 'the_content', $extended['main'] . $extended['extended'] ),
            $post_id
        );
    }

	/**
	 * Возвращает идентификатор ленты.
	 *
	 * @return int
	 */
	public function get_feed_id(): int {
		return $this->feed_id;
	}
}

// eol.
