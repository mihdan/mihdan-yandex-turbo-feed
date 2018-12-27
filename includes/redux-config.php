<?php
/**
 * @var Mihdan_Yandex_Turbo_Feed $this
 */

if ( ! class_exists( 'Redux' ) ) {
	return;
}

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

$post_type_array = array();
$args            = array(
	'public' => true,
);

// Список всех публичных CPT.
$post_types = get_post_types( $args, 'objects' );

if ( $post_types ) {
	foreach ( $post_types as $key => $value ) {
		$post_type_array[ $key ] = $value->label;
	}
}

$taxonomy_array = array();
$args           = array(
	'public' => true,
);

// Список всех зареганных таксономий.
$taxonomies = get_taxonomies( $args, 'objects' );

if ( $taxonomies ) {
	foreach ( $taxonomies as $key => $value ) {
		$taxonomy_array[ $key ] = $value->label;
	}
}

/**
 * @link https://docs.reduxframework.com/core/arguments/
 * @link http://elusiveicons.com/icons/
 * */
$args = array(
	'opt_name'           => $this->slug,
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
	'page_slug'          => $this->slug,
	'save_defaults'      => true,
	'default_show'       => true,
	'default_mark'       => '',
	'show_import_export' => true,
	'transient_time'     => 60 * MINUTE_IN_SECONDS,
	'output'             => true,
	'output_tag'         => true,
	'database'           => '',
	'use_cdn'            => true,
	'footer_credit'      => __( 'Если вам нравится проект - станьте спонсором!', 'mihdan-yandex-turbo-feed' ),

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

Redux::setArgs( $this->slug, $args );

/**
 * Секции
 */
Redux::setSection(
	$this->slug,
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
				'default'  => apply_filters( 'mihdan_yandex_turbo_feed_feedname', str_replace( '_', '-', $this->slug ) ),
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
				'options' => $post_type_array,
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
				'options' => $taxonomy_array,
			),
		),
	)
);

Redux::setSection(
	$this->slug,
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

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Header', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-header',
		'icon'  => 'el el-photo',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Links', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-links',
		'icon'  => 'el el-link',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Related Posts', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-related-posts',
		'icon'  => 'el el-fork',
	)
);

Redux::setSection(
	$this->slug,
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

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Gallery', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-gallery',
		'icon'  => 'el el-website',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Slider', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-slider',
		'icon'  => 'el el-slideshare',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Video', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-video',
		'icon'  => 'el el-video',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Share', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-share',
		'icon'  => 'el el-share',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Blockquote', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-blockquote',
		'icon'  => 'el el-quotes',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Table', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-table',
		'icon'  => 'el el-th',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Menu', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-menu',
		'icon'  => 'el el-lines',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Social Content', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-social-content',
		'icon'  => 'el el-group',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Yandex Map', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-yandex-map',
		'icon'  => 'el el-map-marker',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Yandex Music', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-yandex-music',
		'icon'  => 'el el-music',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Comments', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-comments',
		'icon'  => 'el el-comment',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Rating', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-rating',
		'icon'  => 'el el-star',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Widget Feedback', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-widget-feedback',
		'icon'  => 'el el-phone',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Search', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-search',
		'icon'  => 'el el-search',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Callback', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-callback',
		'icon'  => 'el el-envelope',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Button', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-button',
		'icon'  => 'el el-hand-down',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Accordion', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-accordion',
		'icon'  => 'el el-chevron-down',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Ad Network', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-ad-network',
		'icon'  => 'el el-usd',
	)
);

Redux::setSection(
	$this->slug,
	array(
		'title' => __( 'Analytics', 'mihdan-yandex-turbo-feed' ),
		'id'    => 'elements-analytics',
		'icon'  => 'el el-graph',
	)
);

// eof;
