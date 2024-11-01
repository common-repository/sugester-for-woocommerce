=== Sugester for WooCommerce ===
Contributors: Sugester
Tags: ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, woocommerce, sugester, crm
Requires at least: 4.4
Tested up to: 4.5.0
Stable tag: 1.0.9

Sugester for WooCommerce is an integration plugin that will integrate your WooCommerce shop with Sugester.

== Description ==
Sugester for WooCommerce is an integration plugin that will help you with managing your clients.
Every new registered client will be added to your Sugester account.

If there is any feature that you would like to have, please inform us! :)

== Installation ==
= Minimum Requirements =
* WordPress 3.8 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater
* WooCommerce version 3.0.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don\'t need to leave your web browser. To do an automatic install of Sugester for WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type \"Sugester for WooCommerce\" and click Search Plugins. Once you\'ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking \"Install Now\".

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

However, if you encounter any issue, please contact us at info@sugester.pl or via phone: +48 22 397 09 33

== Frequently Asked Questions ==
= Where are the settings located? =

Settings are located in your WordPress dashboard at WooCommerce -> Settings -> Integration -> Sugester tab.

= What is Sugester API Token? =

Sugester API Token is a token from your Sugester account that is required for this plugin to work. If it will not be correct then this plugin will not correctly.


== Changelog ==
= 1.0.9 - 12/08/17
* Bugfixes

= 1.0.7 - 12/04/17 =
* Bugfixes - WooCommerce 3.0.0 or greater required

= 1.0.6 - 05/04/17 =
* Bugfixes

= 1.0.5 - 30/03/17 =
* Feature - statuses

= 1.0.4 - 25/03/17 =
* Fix - postal code is now integrade

= 1.0.3 - 01/03/17 =
* Fix - backwards compatibility for PHP 5.2

= 1.0.2 - 24/11/16 =
* Fix - 'user_register' hook now registers only WooCommerce customers.

= 1.0.1 - 07/09/16 =
* Fix - bug fix with integration tests (constants rename)

= 1.0.0 - 06/09/16 =
* Feature - create a client on your Sugester account whenever new client registers
* Feature - create an order on your Sugester account (and client, if he decided not to register)
* Feature - Users page: click "Sugester" to show client on Sugester account (hover over user in a row)
                        click "Sugester+" to create new client on Sugester account.
* Feature - User page: click "Show user orders" and "Show client on Sugester".
* Feature - Order page: click "Show this client on Sugester"
                        click "Show user orders"
                        click "Show this order"
* Feature - translation to Polish language
* Dev - Created wp_sugester_client/orders tables
* Dev - Container + integration
* Dev - Created wp_sugester_client table and methods to it
* Dev - Sugester Settings class along with validation.
* Dev - Sugester Errors class
* Dev - Sugester API class
* Dev - Sugester Tools Class

== Upgrade Notice ==

= 1.0.7 =
Plugin now requires WooCommerce version 3.0.0 or greater.
If upon upgrade, your site will not start - remove the folder "sugester-for-woocommerce" folder from wp-content/plugins/
