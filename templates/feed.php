<?php
/**
 * @link https://yandex.ru/support/webmaster/turbo/feed.html
 * @link https://yandex.ru/support/webmaster/turbo/rss-elements.html
 *
 * @var Template $this
 * @var Settings $this->settings
 */

use Mihdan\YandexTurboFeed\Settings;
use Mihdan\YandexTurboFeed\Template;

header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . $this->settings->get_option( 'charset' ), true );
echo '<?xml version="1.0" encoding="' . esc_html( $this->settings->get_option( 'charset' ) ) . '"?' . '>';
?>
<rss version="2.0" xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru">
	<channel>
		<title><?php echo esc_html( $this->settings->get_option( 'channel_title' ) ); ?></title>
		<link><?php echo esc_url( $this->settings->get_option( 'channel_link' )['url'] ); ?></link>
		<description><?php echo esc_html( $this->settings->get_option( 'channel_description' ) ); ?></description>
		<language><?php echo esc_html( $this->settings->get_option( 'channel_language' ) ); ?></language>
		<turbo:cms_plugin>7391CC2B1408947EFD5084459F5BD0CA</turbo:cms_plugin>
		<?php do_action( 'mihdan_yandex_turbo_feed_channel' ); ?>
		<?php
		$args  = apply_filters( 'mihdan_yandex_turbo_feed_args', array() );
		$items = new WP_Query( $args );
		?>
		<?php if ( $items->have_posts() ) : ?>
			<?php while ( $items->have_posts() ) : ?>
				<?php $items->the_post(); ?>
				<item<?php $this->item_attributes( get_the_ID() ); ?>>
					<link><?php the_permalink_rss(); ?></link>
					<title><![CDATA[<?php the_title_rss(); ?>]]></title>
					<author><![CDATA[<?php the_author(); ?>]]></author>
					<pubDate><?php echo esc_html( get_post_time( 'r', true ) ); ?></pubDate>
					<turbo:content>
						<![CDATA[
						<header>
							<?php if ( has_post_thumbnail() ) : ?>
								<figure>
									<?php the_post_thumbnail( 'large' ); ?>
								</figure>
							<?php endif; ?>
							<h1><?php the_title_rss(); ?></h1>
							<?php do_action( 'mihdan_yandex_turbo_feed_item_header', get_the_ID() ); ?>
						</header>
						<?php if ( get_option( 'rss_use_excerpt' ) ) : ?>
							<?php echo apply_filters( 'mihdan_yandex_turbo_feed_item_excerpt', get_the_excerpt(), get_the_ID() ); ?>
						<?php else : ?>
							<?php echo apply_filters( 'mihdan_yandex_turbo_feed_item_content', get_the_content_feed(), get_the_ID() ); ?>
						<?php endif; ?>
						<?php do_action( 'mihdan_yandex_turbo_feed_item_turbo_content', get_the_ID() ); ?>
						]]>
					</turbo:content>
					<?php do_action( 'mihdan_yandex_turbo_feed_item', get_the_ID() ); ?>
				</item>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</channel>
</rss>
