=== Mihdan: Yandex Turbo Feed ===
Author: mihdan
Contributors: mihdan
Donate link: https://www.kobzarev.com/donate/
Tags: wordpress, feed, yandex, turbo, rss, yandex-turbo, yandex-turbo-pages, rss-feed
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 4.9.2
Stable tag: 1.1.2
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

== Frequently Asked Questions ==

=Как изменить количество постов в ленте=

Согласно [спеке](https://yandex.ru/support/webmaster/turbo/feed.html) Яндекса, материалов в RSS-ленте для Турбо-страниц может быть до 500. Добавил фильтр на тот случай, если вы хотите выводить их меньше:

`
add_filter( 'mihdan_yandex_turbo_feed_posts_per_rss', function( $posts_per_rss ) {
  return 500;
} );
`

=Как изменить ярлык ленты=

По умолчанию ярлык для ленты выглядит как `mihdan-yandex-turbo-feed`, если вам не нравится такое название, можете его переименовать через фильтр:

`
add_filter( 'mihdan_yandex_turbo_feed_feedname', function( $slug ) {
  return 'yandex-turbo';
} );
`

Стоит отметить, что в качестве разделителя всегда используется тире, подчеркивание запрещено, это связано с некоторыми конфигурациями старых серверов, мало ли 🙂

=Как изменить список разрешенных тегов=

По спеке внутри тега `<turbo:content>` не должно быть никаких лишних тегов, типа `<iframe>`, поэтому плагин вырезает лишнее, оставляя только необходимый для разметки минимум. Для переопределения есть фильтр:

`
add_filter( 'mihdan_yandex_turbo_feed_allowable_tags', function( $allowable_tags ) {
  // Добавить тег <kbd>
  $allowable_tags[] = 'kbd';

  return $allowable_tags;
} );
`

=Аргументы поиска похожих постов=

`
add_filter( 'mihdan_yandex_turbo_feed_related_args', function( $args ) {
    // Делаем что-то с запросом
    return $args;
} );
`

=Таксономии для вывода категорий=

По умолчанию для вывода категорий используется таксономия `category`, которая переопределяется через фильтр:

`
add_filter( 'mihdan_yandex_turbo_feed_taxonomy', function( $taxonomy ) {
  return array( 'tag' );
} );
`

== Changelog ==

= 1.1.2 (2018-08-08) =
* Для Турбо лимит на 1000 item по-умолчанию

= 1.1.1 =
* Добавил поддержку комментариев к постам

= 1.1.0 =
* Добавил поддержку элемента `turbo:cms_plugin` для указания идентификатора плагина
* Перенёс тег `<menu>` внутрь тега `<header>` в связи с изменением спеки
* Добавлен блок с шерами

= 1.0.15 =
* Обновил readme.txt

= 1.0.14 =
* cdata для названий похожих постов

= 1.0.13 =
* Решение проблемы с readme.txt

= 1.0.12 =
* Решение проблемы с readme.txt

= 1.0.11 =
* Добавлена поддержка меню
* Добавлена поддержка цитат
* Добавлена поддержка таблиц
* Добавлена поддержка встраиваний
* Пофиксил работу с кастомными такономиями
* Пофиксил поиск таксономий в похожих постах

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
* Deploy to wp.org

= 1.0.2 =
* Update readme.txt

== System Requirements ==

* WordPress 4.6+
* PHP 5.6+