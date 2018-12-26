<?php
/**
     * ReduxFramework Sample Config File
     * For full documentation, please visit: http://docs.reduxframework.com/
     */

if ( ! class_exists( 'Redux' ) ) {
	return;
}

$opt_name = "mihdan_yandex_turbo_feed";

/**
 * @link https://docs.reduxframework.com/core/arguments/
 * */
$args = array(
	'opt_name'           => $opt_name,
	'display_name'       => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
	'display_version'    => MIHDAN_YANDEX_TURBO_FEED_VERSION,
	'menu_type'          => 'menu',
	'allow_sub_menu'     => true,
	'menu_title'         => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
	'page_title'         => __( 'Yandex Turbo', 'mihdan-yandex-turbo-feed' ),
	'async_typography'   => false,
	'admin_bar'          => false,
	'global_variable'    => false,
	'dev_mode'           => false,
	'update_notice'      => true,
	'customizer'         => false,
	'page_priority'      => null,
	'page_permissions'   => 'manage_options',
	'menu_icon'          => 'dashicons-rss',
	'last_tab'           => '',
	'page_slug'          => $opt_name,
	'save_defaults'      => true,
	'default_show'       => false,
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

Redux::setArgs( $opt_name, $args );

/**
 * Секции
 */
Redux::setSection( $opt_name, array(
	'title'  => __( 'Feed', 'mihdan-yandex-turbo-feed' ),
	'id'     => 'feed',
	'icon'   => 'el el-rss',
	'desc'   => __( 'Настройки ленты', 'mihdan-yandex-turbo-feed' ),
	'fields' => array(
		array(
			'id'       => 'feed_slug',
			'type'     => 'text',
			'title'    => __( 'Feed Slug', 'mihdan-yandex-turbo-feed' ),
			'default'  => $opt_name,
			'readonly' => (boolean) has_filter( 'mihdan_yandex_turbo_feed_posts_per_rss' ),
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
			'id'      => 'feed_total_posts',
			'type'    => 'spinner',
			'title'   => __( 'Total Posts', 'mihdan-yandex-turbo-feed' ),
			'default' => 50,
			'min'     => 10,
			'max'     => 1000,
			'step'    => 1,
			'readonly'    => true,
		),
	),
) );

Redux::setSection( $opt_name, array(
	'title'  => __( 'Channel', 'mihdan-yandex-turbo-feed' ),
	'id'     => 'channel',
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
			'default' => get_locale(),
			'options' => array_map( function ( $value ) {
				return $value['native_name'];
			}, wp_get_available_translations() ),
		),
	),
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Header', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-header',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Links', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-links',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Related Posts', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-related-posts',
) );

Redux::setSection( $opt_name, array(
	'title'  => __( 'Images', 'mihdan-yandex-turbo-feed' ),
	'id'     => 'elements-images',
	'fields' => array(
		array(
			'id'      => 'images_copyright',
			'type'    => 'text',
			'title'   => __( 'Copyright', 'mihdan-yandex-turbo-feed' ),
			'default' => wp_parse_url( get_home_url(), PHP_URL_HOST ),
		),
	),
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Gallery', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-gallery',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Slider', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-slider',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Video', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-slider',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Share', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-share',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Blockquote', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-blockquote',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Table', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-table',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Menu', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-menu',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Social Content', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-social-content',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Yandex Map', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-yandex-map',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Yandex Music', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-yandex-music',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Comments', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-comments',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Rating', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-rating',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Widget Feedback', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-widget-feedback',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Search', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-search',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'callback', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-callback',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Button', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-button',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Accordion', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-accordion',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Ad Network', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-ad-network',
) );

Redux::setSection( $opt_name, array(
	'title' => __( 'Analytics', 'mihdan-yandex-turbo-feed' ),
	'id'    => 'elements-analytics',
) );

    // If Redux is running as a plugin, this will remove the demo notice and links
    //add_action( 'redux/loaded', 'remove_demo' );

    // Function to test the compiler hook and demo CSS output.
    // Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
    //add_filter('redux/options/' . $opt_name . '/compiler', 'compiler_action', 10, 3);

    // Change the arguments after they've been declared, but before the panel is created
    //add_filter('redux/options/' . $opt_name . '/args', 'change_arguments' );

    // Change the default value of a field after it's been set, but before it's been useds
    //add_filter('redux/options/' . $opt_name . '/defaults', 'change_defaults' );

    // Dynamically add a section. Can be also used to modify sections/fields
    //add_filter('redux/options/' . $opt_name . '/sections', 'dynamic_section');

// eof;
