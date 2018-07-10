<?php
/**
 * @link https://yandex.ru/support/webmaster/turbo/feed.html
 * @link https://yandex.ru/support/webmaster/turbo/rss-elements.html
 */
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
?>
<rss version="2.0" xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru">
	<channel>
		<title><?php bloginfo_rss( 'name' ); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<language><?php echo substr( get_bloginfo_rss( 'language' ), 0, strpos( get_bloginfo_rss( 'language' ), '-' ) ); ?></language>
		<turbo:cms_plugin>mihdan_yandex_turbo_feed</turbo:cms_plugin>
		<?php do_action( 'rss2_head' ); ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<item turbo="true">
				<link><?php the_permalink_rss(); ?></link>
				<title><?php the_title_rss(); ?></title>
				<author><?php the_author(); ?></author>
				<pubDate><?php echo get_post_time( 'r', true ); ?></pubDate>
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
						<?php the_content_feed(); ?>
					<?php else : ?>
						<?php the_content_feed(); ?>
					<?php endif; ?>
					<?php do_action( 'mihdan_yandex_turbo_feed_item_content', get_the_ID() ); ?>
					]]>
				</turbo:content>
				<?php do_action( 'mihdan_yandex_turbo_feed_item', get_the_ID() ); ?>
			</item>
		<?php endwhile; ?>
	</channel>

</rss>