=== Mihdan: Yandex Turbo Feed ===
Author: mihdan
Contributors: mihdan
Tags: wordpress, feed, yandex, turbo, rss, yandex-turbo, yandex-turbo-pages, rss-feed
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 4.9.2
Stable tag: 1.0.11
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Mihdan: Yandex Turbo Feed by mihdan – allows you to convert your site materials into Yandex.Turbo format.

== Description ==
Mihdan: Yandex Turbo Feed by mihdan – allows you to convert your site materials into Yandex.Turbo format.

= Recommended Settings =
The default settings that are used on a fresh install of the plugin are what we recommend.

= Support =
Need help with anything? Please create a [support topic](https://wordpress.org/support/plugin/mihdan-yandex-turbo-feed).

= Feature Request =
Want a feature added to this plugin? Create a [support topic](https://wordpress.org/support/plugin/mihdan-yandex-turbo-feed).
We are always looking to add features to improve our plugin.

= Note =
Mihdan: Yandex Turbo Feed **does not** make any changes to your database, it just processes the output. So you will not see these changes within the WYSIWYG editor.

== Installation ==

= From your WordPress dashboard =
1. Visit 'Plugins > Add New'
2. Search for 'Mihdan: Yandex Turbo Feed'
3. Activate Mihdan: Yandex Turbo Feed from your Plugins page.
4. [Optional] Configure Mihdan: Yandex Turbo Feed settings.

= From WordPress.org =
1. Download Mihdan: Yandex Turbo Feed.
2. Upload the 'mihdan-yandex-turbo-feed' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate Mihdan: Yandex Turbo Feed from your Plugins page.
4. [Optional] Configure Mihdan: Yandex Turbo Feed settings.

== Changelog ==

= 1.0.11 =
* Добавлена поддержка меню <menu> в фид
* Добавлена поддержка цитат <blockquote>
* Добавлена поддержка таблиц <table>
* Добавлена поддержка встраиваний <iframe>
* Пофиксил работу с кастомными такономиями

= 1.0.10 =
* Некоторые фильтры не применялись из темы, если переопределен слаг плагина

= 1.0.9 =
* Fix: fatal WP_OSA

= 1.0.8 =
* Add filter `mihdan_yandex_turbo_feed_post_type`

= 1.0.7 =
* Fix: flush rewrite rules on init with conditional

= 1.0.6 =
* Fix: add default feedname

= 1.0.5 =
* Fix: flush rewrite rules on plugin activate

= 1.0.4 =
* New filter `mihdan_yandex_turbo_feed_related_args`
* Remove random argument from related query

= 1.0.3 =
Deploy to wp.org

= 1.0.2 =
Update readme.txt

== System Requirements ==

* WordPress 4.6+
* PHP 5.6+