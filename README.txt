=== WP Trip Summary ===
Contributors: alexandruboia
Donate link: https://ko-fi.com/alexandruboia
Tags: trip, summary, map, gpx, travel
Requires at least: 6.0.0
Tested up to: 6.5.2
Stable tag: 0.3.2
Requires PHP: 8.0.0
License: BSD New License
License URI: https://opensource.org/licenses/BSD-3-Clause

A WordPress trip summary plugin to help travel bloggers manage and display structured information about their train rides and biking or hiking trips.

== Description ==

🌟 [GitHub](https://github.com/alexboia/WP-Trip-Summary)
❤️ [WordPress](https://wordpress.org/plugins/wp-trip-summary/)

### Features

- attach technical information to a post (ex. how long was your trip, how much did you totally climb, where from and where to, how hard do you think it has been, what kind of roads or trails did you encounter etc.);
- attach GPS data to a post (GPX, GeoJSON and KML files are currently accepted as data sources) and display that track on a map;
- maintain rider's log entries, while optionally specifying some of them as public;
- allows management of the look-up data used to populate the fields presented as single or multi-selection options list (ex. `Difficulty Level`, `Open During Seasons` etc.);
- allows customization of the map layer:
   - map tile source (comes by default configured with [OpenStreetMap](https://www.openstreetmap.org/)); 
   - enabling/disabling of available map controls; 
   - customizing the visual representation of the track).
- allows customization of the measurement unit system used to represent various values (ex. `Total distance`, `Total climb` etc.);
- multi-language.

### More details

This plug-in provides three basic features:
- allow some structured information to be filled in, according to a selected trip type;
- allow some GPS track to be uploaded and then rendered on a map;
- maintain rider's log entries, while optionally specifying some of them as public.

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

### Requirements

- PHP version 8.0.0 or greater (as of version 0.3.2);
- MySQL version 5.7 or greater (with spatial support);
- Wordpress 6.0.0 or greater;
- libxml extension;
- SimpleXml extension;
- mysqli extension;
- mbstring - not strictly required, but recommended;
- zlib - not strictly required, but recommended.

### Multi language

Available in English, Romanian, French and (since 0.3.2) German.

### Helping out

- by contributing: head over tot he project's GitHub page: [https://github.com/alexboia/WP-Trip-Summary](https://github.com/alexboia/WP-Trip-Summary);
- by donating: [https://ko-fi.com/alexandruboia](https://ko-fi.com/alexandruboia);
- support my paid work: [https://alexboia.gumroad.com/](https://alexboia.gumroad.com/).

== Frequently Asked Questions ==

= How can it be displayed for other post types than page and post? =
Out of the box this is not possible, but, as of 0.2.8 you can customize it using a filter hook. Please see this wiki page for an example: https://github.com/alexboia/WP-Trip-Summary/wiki/Changing-the-editor-availability-per-post-type.

= Does it support KML files? =
Yes, as of version 0.3.2.

= Can I insert the trip summary front-end viewer at a custom location? =
You can insert the trip summary viewer at a custom location *in the same post for which you have defined it* using the following shortcode: [abp01_trip_summary_viewer] (no parameters required). Only one such shortcode allowed and supported per post.

= Can it be customized? =
Yes, the front-end viewer of the plug-in (i.e. the one that shows trip summary data to your visitors can be customized). See here how: https://github.com/alexboia/WP-Trip-Summary/wiki/Customizing-the-front-end-viewer.

= Why does the trip summary viewer not show up on post listing pages, such as archive pages? =
WP-Trip-Summary looks at the current page and only activates itself when on the actual post details page, so it won’t work on archive pages, even if the entire post content is displayed.

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
8. Admin - Plug-in settings editor - chose a pre-defined tile configuration
9. Frontend Viewer - Trip Map with altitude profile
10. Admin - Maintenance page
11. Admin - Add rider log entry
12. Admin - List rider log entries
13. Frontend viewer - List rider log entries

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/abp01-wp-trip-summary` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the `Trip Summary -> Settings` sidebar menu item to access the plug-in configuration page.
4. Use the `Trip Summary -> Help` sidebar menu item to access the plug-in help page.
5. For those with plug-in versions older than 0.2.1, please see here notes on updating to plug-in version 0.2.1: https://github.com/alexboia/WP-Trip-Summary/blob/master/README-UPDATE-021.md

== Changelog ==

= 0.3.2 =
- Added German translation (de_DE, many thanks to Nico - nida78);
- Added support for KML files;
- Bring help contents (mostly) up-to-date with the new features, for ro_RO and en_US;
- Add route type filter in admin area post listing;
- Some small bug fixes;
- Add proper error and debug logging infrastructure, including dedicated log management page.

= 0.3.1 =
- Fixed bug on some PHP versions.

= 0.3.0 =
- Fixed bug on unix platforms due to wrong directory naming (upper case first letter, instead of expected lower case).

= 0.2.9 =
- Added rider's log feature;
- Bug fix when validating GPX files that begin with XML comments (courtesy of Philip Flohr);
- Fixed some warnings on PHP 8;
- Internal installer rework.

= 0.2.8 =
- Added trip summary audit log to post edit and post listing pages;
- Added maintenance page with the following tools: clear cached track data, clear all trip summary info, detect posts that have missing track files;
- Added JSON-LD structured data to posts or pages that have trip summary track data (type Place, with box GeoShape);
- Added shortcuts to plug-in's entry from the plug-in listing page;
- Added additional REST API field to WP-JSON post endpoint to return trip summary data;
- Updated UI + UX of the lookup data management editor page;
- Usability improvements;
- Updated help contents.

= 0.2.7 =
- Refactoring: the plug-in now has a more manageable and extensible structure;
- Feature: Added an about page;
- Feature: Enhanced display for the frontend viewer information items;
- Feature: Added support for GeoJSON file import;
- Improved plug-in documentation;
- Improved usability and UI for the settings page;
- Improved usability and UI for the help page;
- Updated help contents;
- Improved stability and various bug fixes.

= 0.2.6 =
- Usability improvement: added a control to the front-end viewer map that allows one to re-center the map to the GPS track bounding area - basically the initial state of the map when it's first loaded.
- Usability improvement: within the WP-Trip-Summary, the "Clear Track" and "Clear Info" buttons have been grouped using a `Quick Actions` control, such as in the metabox used to display the summary in the post page.
- Usability improvement: added confirmation when attempting to remove trip summary information as well as when attempting to remove track data.
- Usability improvement: when removing a lookup data item with existing associations (that is, associated with at least a post), WP-Trip-Summary no longer issues a hard denial, but asks the user to confirm whether he/she wishes to proceed removing the item, as well as sever its associations with the posts.
- Feature: added an option (to the WP-Trip-Summary settings page) to specify the initially selected WP-Trip-Summary front-end viewer tab.
- Feature: added an option (to the WP-Trip-Summary settings page) to specify the front-end viewer map height.
- Improved stability;
- Improved documentation.

= 0.2.5 =
* Trip summary front-end viewer can now inserted at a custom location in the post content for which it has been defined using the [abp01_trip_summary_viewer] shortcode (or a special block, if you're using the block editor);
* Support for trip summary front-end viewer customization: https://github.com/alexboia/WP-Trip-Summary/wiki/Customizing-the-front-end-viewer.
* Refined error reporting when uploading a new GPS track;
* Altitude profile now available;
* Min/max altitude info box now available;
* Refactoring and stability improvements;
* Fixed track uploader not opening on Microsoft Edge browsers;
* Fixed Waymark compatibility issue. 

= 0.2.4 =
* French translation now available!
* The trip summary editor is now launched from a side metabox, which also displays relevant information and features some quick actions;
* The plug-in is now smoothly integrated with the block editor as well;
* In the plug-in settings editor a user can now specify the weight used to plot the GPS track on the map;
* Added automated tests;
* Tested compatibility with WordPress 5.4;
* Fixed a GPX file upload issue that occured with certain GPX files;
* Updated dependencies: URI.js.

= 0.2.3 =
* In the plug-in settings editor a user can now specify the color used to plot the GPS track on the map
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

= 0.3.2 = 
Upgrade to this version for additional features, better user experience and improved plug-in stability

= 0.2.8. = 
Upgrade to this version for additional features, better user experience and improved plug-in stability

= 0.2.7 =
Upgrade to this version for additional features, better user experience and improved plug-in stability

= 0.2.6 =
Upgrade to this version for additional features, better user experience and improved plug-in stability

= 0.2.5 =
Upgrade to this version for additional features and improved plug-in stability

= 0.2.4 =
Upgrade to this version for additional features (including block editor integration) and improved plug-in stability

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