=== Extension for Distributor - Multisite Cloner ===
Contributors: hugomoran
Tags: multisite cloner, mucl, distributor
Requires at least: 3.6.0
Tested up to: 4.9.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fixes integration between cloned sites and the Distributor plugin.

== Description ==

Since an original site would not know about any Distributor connections
in the duplicate of a site containing connections, this functions connects the original
with the latest created clone.

= Compatibility =

This plugin requires:

* Multisite Cloner

== Installation ==

1. Copy the `mucl-dt-extension` folder into your `wp-content/plugins` folder
2. Activate the plugin via the plugins admin page
3. Now the sites you clone will carry any Distributor connections

== Changelog ==

= 1.0.1 =
* Initial Release.

= 1.1.0 =
* Fixed bug that made the plugin ineffective on custom post types.
