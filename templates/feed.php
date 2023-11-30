<?php
/**
 * @link https://yandex.ru/support/webmaster/turbo/feed.html
 * @link https://yandex.ru/support/webmaster/turbo/rss-elements.html
 *
 * @var Template $this
 */

use Mihdan\YandexTurboFeed\Template;

$use_post_author    = $this->settings->get_option( 'use_post_author' );
$use_post_date      = $this->settings->get_option( 'use_post_date' );
$use_post_modified  = $this->settings->get_option( 'use_post_modified' );
$use_post_thumbnail = $this->settings->get_option( 'use_post_thumbnail' );
$use_excerpt        = $this->settings->get_option( 'use_excerpt' );
$more_link_text     = $this->settings->get_option( 'more_link_text' );
$charset            = $this->settings->get_option( 'charset' );
$feed_id            = get_the_ID();

header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . $charset, true );
echo '<?xml version="1.0" encoding="' . esc_html( $charset ) . '"?' . '>';
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
				<?php
                $items->the_post();
                $item = get_post();
                ?>
				<item<?php $this->item_attributes( get_the_ID() ); ?>>
					<link><?php the_permalink_rss(); ?></link>
					<title><![CDATA[<?php the_title_rss(); ?>]]></title>
                    <?php if ( $use_post_author ) : ?>
					    <author><![CDATA[<?php the_author(); ?>]]></author>
                    <?php endif; ?>
					<?php if ( $use_post_date ) : ?>
					    <pubDate><?php echo esc_html( $use_post_modified ? get_post_modified_time( 'r', true ) : get_post_time( 'r', true ) ); ?></pubDate>
					<?php endif; ?>
					<turbo:content>
						<![CDATA[
						<header>
							<?php if ( $use_post_thumbnail && has_post_thumbnail() ) : ?>
								<figure>
									<?php the_post_thumbnail( 'large' ); ?>
								</figure>
							<?php endif; ?>
							<h1><?php the_title_rss(); ?></h1>
							<?php do_action( 'mihdan_yandex_turbo_feed_item_header', get_the_ID(), $feed_id ); ?>
						</header>
						<?php if ( 'yes' === $use_excerpt ) : ?>
							<?php echo apply_filters( 'mihdan_yandex_turbo_feed_item_excerpt', $this->get_excerpt( $item->post_content, get_the_ID(), $more_link_text ), get_the_ID(), $feed_id ); ?>
						<?php else : ?>
							<?php echo apply_filters( 'mihdan_yandex_turbo_feed_item_content', $this->get_content( $item->post_content, get_the_ID() ), get_the_ID(), $feed_id ); ?>
						<?php endif; ?>
						<?php do_action( 'mihdan_yandex_turbo_feed_item_turbo_content', get_the_ID(), $feed_id ); ?>
						]]>
					</turbo:content>
					<?php do_action( 'mihdan_yandex_turbo_feed_item', get_the_ID(), $feed_id ); ?>
				</item>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</channel>
</rss>
