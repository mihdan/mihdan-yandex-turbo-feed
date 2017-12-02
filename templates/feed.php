<?php
/**
 * @link https://yandex.ru/support/webmaster/turbo/feed.html
 */
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
?>
<rss version="2.0"
     xmlns:yandex="http://news.yandex.ru"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:turbo="http://turbo.yandex.ru">

	<channel>
		<title><?php bloginfo_rss( 'name' ); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<language><?php echo substr( get_bloginfo_rss( 'language' ), 0, strpos( get_bloginfo_rss( 'language' ), '-' ) );?></language>
		<?php do_action( 'rss2_head' ); ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<item turbo="true">
				<link><?php the_permalink_rss(); ?></link>
				<author><?php the_author(); ?></author>
				<category>Технологии</category>
				<pubDate><?php echo get_post_time( 'r', true ); ?></pubDate>
				<turbo:content>
					<![CDATA[
					<header>
						<?php if ( has_post_thumbnail() ) : ?>
							<figure>
								<?php the_post_thumbnail(); ?>
							</figure>
						<?php endif; ?>
						<h1><?php the_title_rss(); ?></h1>
					</header>
					<!--h2>Заголовок страницы</h2>
					<p>Текст с <b>выделением</b> и списком:</p>
					<ul>
						<li>пункт 1;</li>
						<li>пункт 2.</li>
					</ul>
					<figure data-turbo-ad-id="first_ad_place"></figure>
					<figure>
						<video>
							<source src="https://example.com/video.mp4"
							        type="video/mp4" />
						</video>
						<img src="http://example.com/img-for-video.jpg" />
						<figcaption>Подпись к видео</figcaption>
					</figure>
					<figure data-turbo-ad-id="second_ad_place"></figure-->
					]]>
				</turbo:content>
				<yandex:related>
					<link
						url="http://www.example.com/other-page.html"
						img="http://www.example.com/image.jpg">Текст ссылки 3
					</link>
				</yandex:related>
			</item>
		<?php endwhile; ?>
	</channel>

</rss>