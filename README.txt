=== WP Trip Summary ===
Contributors: alexandruboia
Donate link: https://github.com/alexboia/WP-Trip-Summary
Tags: trip-summary, map, gpx, travel-plugin
Requires at least: 5.0
Tested up to: 5.3.2
Stable tag: 0.2.3
Requires PHP: 5.6.2
License: BSD New License
License URI: https://opensource.org/licenses/BSD-3-Clause

A WordPress trip summary plugin to help travel bloggers manage and display structured information about their train rides and biking or hiking trips.

== Description ==

This plug-in provides two basic features:
- allow some structured information to be filled in, according to a selected trip type;
- allow some GPX track to be uploaded and then rendered on a map.

Structured information is supported for the following types of trips:
- Bike trips;
- Hiking trips;
- Train rides.

For bike trips the following fields are available:
- Total distance;
- Total climb;
- Difficulty level;
- Access information (how to get to the start point and return from the end point);
- Open during seasons;
- Path surface type (eg: dirt, asphalt, grass etc.);
- Recommended bike type (eg: MTB, road bike etc.).

For hiking trips the following fields are available:
- Total distance;
- Total climb;
- Difficulty level;
- Access information;
- Open during seasons;
- Path surface type;
- Route markers.

For train rides the following fields are available:
- Total distance;
- How many trains were exchanged;
- Line gauge (mm);
- Railroad operators used;
- Line status (closed, operational etc.);
- Whether the line is electrified or not;
- Line type.

Requirements:
- PHP version 5.6.2 or greater;
- MySQL version 5.7 or greater (with spatial support);
- Wordpress 5.0;
- libxml extension;
- SimpleXml extension;
- mysqli extension;
- mbstring - not strictly required, but recommended;
- zlib - not strictly required, but recommended.

Available in English and Romanian.

Important note:
For those with plug-in versions older than 0.2.1, please see here notes on updating to plug-in version 0.2.1: https://github.com/alexboia/WP-Trip-Summary/blob/master/README-UPDATE-021.md

== Frequently Asked Questions ==

= How can I contribute? =
Head over to the plug-in's GitHub page (https://github.com/alexboia/WP-Trip-Summary) and let's talk!

== Screenshots ==

1. Frontend Viewer - Trip information
2. Frontend Viewer - Trip Map
3. Frontend Viewer - Top Teaser
4. Admin - Trip Editor - Map
5. Admin - Trip Editor - Trip information
6. Admin - Trip Editor - No trip type selected yet
7. Admin - Plug-in settings editor

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/abp01-wp-trip-summary` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the `Trip Summary -> Settings` sidebar menu item to access the plug-in configuration page.
4. Use the `Trip Summary -> Help` sidebar menu item to access the plug-in help page.
5. For those with plug-in versions older than 0.2.1, please see here notes on updating to plug-in version 0.2.1: https://github.com/alexboia/WP-Trip-Summary/blob/master/README-UPDATE-021.md

== Changelog ==

= 0.2.3 =
* In the plug-in settings editor a user can now specify the color used to plot the GPX track on the map
* The post and page listing now have two columns that describe whether or not an article has route information and, respectively, whether or not it has an uploaded GPX track
* The plug-in now correctly works for WP pages as well (previously, it would not correctly render on the frontend)
* Added automated tests
* Added compatibility with Mysql 8.0+
* Fixed an activation issue that occured with certain PHP versions
* Updated dependencies: Leaftlet Js, Leaflet Js Magnifying Glass component, NProgress js, MysqliDb.

= 0.2.2 =
* The storage directories have received index.php and .htaccess guard access files to prevent direct access of stored files. These are copied on install and on upgrade, but also created upon storing files, if they do not exist.
* Refactoring of view file names: replaced "techbox-" prefix with "wpts-" prefix.
* Removed deprecated uploader runtimes (flash and silverlight) from track uploader.
* Minor refactoring.

= 0.2.1 = 
* Moved plug-in track & cache storage to a sub-directory of wp-content/uploads, as, previously, the plug-in stored its track & cache files to its own directory, which caused this data to be lost upon upgrade, since WordPress, when upgrading a plug-in, removes all the files that belong to the previous plug-in version.
* Minor refactoring

= 0.2.0 =
* Fixed An activation issue which occurred under certain conditions.
* Fixed the trip summary editor not being re-centered upon window resize;
* Fixed the settings page not displaying the progress bar when saving, if the page has been scrolled.

= 0.2b =
First officially distributed version.

== Upgrade Notice ==

= 0.2.3 = 
Upgrade to this version for additional features and improved plug-in stability

= 0.2.2 =
Upgrade to this version for improved security of the track and cache file storage directory

= 0.2.1 =
Please see here notes on updating to plug-in version 0.2.1: https://github.com/alexboia/WP-Trip-Summary/blob/master/README-UPDATE-021.md

= 0.2.0 =
This version fixes a plug-in activation issue under certain conditions and other minor bugs.

= 0.2b =
Use this version as the first officially distributed version.