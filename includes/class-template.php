<?php
/**
 * @package mihdan-yandex-turbo-feed
 */
namespace Mihdan\YandexTurboFeed;

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
	 * @var int $feed_id Идентификатор фида.
	 */
	private $feed_id;

	/**
	 * @var string $slug Слаг плагина.
	 */
	private $slug;

	/**
	 * Template constructor.
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
		$this->slug     = MIHDAN_YANDEX_TURBO_FEED_SLUG;

		$this->hooks();
	}

	/**
	 * Hooks Init
	 */
	public function hooks() {
		add_action( 'template_redirect', array( $this, 'render' ) );
		add_action( 'mihdan_yandex_turbo_feed_channel', array( $this, 'insert_analytics' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_share' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_search' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_comments' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_callback' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_content', array( $this, 'insert_rating' ) );
		add_action( 'mihdan_yandex_turbo_feed_item_header', array( $this, 'insert_menu' ) );
		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_category' ) );
		add_action( 'mihdan_yandex_turbo_feed_item', array( $this, 'insert_related' ) );
		add_action( 'wp', array( $this, 'authenticate' ) );
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
	public function insert_rating() {
		// Если модуль выключен.
		if ( ! $this->settings->get_option( 'rating_enable', $this->feed_id ) ) {
			return;
		}
		?>
		<div itemscope itemtype="http://schema.org/Rating">
			<meta itemprop="ratingValue" content="<?php echo esc_attr( wp_rand( $this->settings->get_option( 'rating_min', $this->feed_id ), $this->settings->get_option( 'rating_max', $this->feed_id ) ) ); ?>">
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
		if ( $this->settings->get_option( 'menu_enable' ) && has_nav_menu( $this->slug ) ) {

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

		if ( ! $this->settings->get_option( 'related_posts_enable', $this->feed_id ) ) {
			return;
		}

		$related = $this->get_related();

		if ( $related->have_posts() ) {
			// Если включена бесконечная лента.
			if ( $this->settings->get_option( 'related_posts_infinity', $this->feed_id ) ) {
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
	 * Получить массив похожих постов
	 *
	 * @return \WP_Query
	 */
	public function get_related() {

		$post = get_post();

		$args = array(
			'post_type'           => $this->settings->cpt_key,
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

		// Фильтруем аргументы запроса похожих постов.
		$args = apply_filters( 'mihdan_yandex_turbo_feed_related_args', $args );

		$query = new \WP_Query( $args );

		return $query;
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
		}// print_r($args);print_r($result);

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
		if ( is_singular( $this->settings->cpt_key ) ) {
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
}

// eol.
