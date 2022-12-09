# Changelog

### Version 0.2.8
- Added shortcuts to plug-in's entry from the plug-in listing page;
- Added maintenance page with the following tools: clear cached track data, clear all trip summary info, detect posts that have missing track files;
- Added JSON-LD structured data to posts or pages that have trip summary track data (type Place, with box GeoShape);
- Added additional REST API field to WP-JSON post endpoint to return trip summary data;
- Fixed MysqliDb dependency -  The MysqliDb generates deprectation warnings and needs to be updated ([Issue #79](https://github.com/alexboia/WP-Trip-Summary/issues/79));
- Fixed JS warnings caused by including editor scripts in non-editor pages ([Issue #78](https://github.com/alexboia/WP-Trip-Summary/issues/78));
- Added trip summary audit log to post edit and post listing pages ([Issue #80](https://github.com/alexboia/WP-Trip-Summary/issues/80));
- Updated UI + UX of the lookup data management editor page ([Issue #77](https://github.com/alexboia/WP-Trip-Summary/issues/77));
- Fixed trip summary shortcode block not rendering in post/page view;
- Fixed trip summary shortcode block editor widget not showing up.

### Version 0.2.7
- Refactoring: the plug-in now has a more manageable and extensible structure, with the most important change being the splitting of all the code previously in `abp01-plugin-main.php`, into separate plugin modules;
- Feature: Added an about page ([Issue 59](https://github.com/alexboia/WP-Trip-Summary/issues/59));
- Feature: Enhanced display for the frontend viewer information items ([Issue 75](https://github.com/alexboia/WP-Trip-Summary/issues/75));
- Feature: Added support for GeoJSON file import ([Issue 76](https://github.com/alexboia/WP-Trip-Summary/issues/76));
- Improved plug-in documentation;
- Improved usability and UI for the settings page;
- Improved usability and UI for the help page;
- Updated help contents;
- Improved stability and various bug fixes.

### Version 0.2.6
- Usability improvement: added a control to the front-end viewer map that allows one to re-center the map to the GPS track bounding area - basically the initial state of the map when it's first loaded ([Issue 68](https://github.com/alexboia/WP-Trip-Summary/issues/68)).
- Usability improvement: within the WP-Trip-Summary, the `Clear Track` and `Clear Info` buttons have been grouped using a `Quick Actions` control, such as in the metabox used to display the summary in the post page ([Issue 69](https://github.com/alexboia/WP-Trip-Summary/issues/69)).
- Usability improvement: added confirmation when attempting to remove trip summary information as well as when attempting to remove track data ([Issue 72](https://github.com/alexboia/WP-Trip-Summary/issues/72)).
- Usability improvement: when removing a lookup data item with existing associations (that is, associated with at least a post), WP-Trip-Summary no longer issues a hard denial, but asks the user to confirm whether he/she wishes to proceed removing the item, as well as sever its associations with the posts ([Issue 67](https://github.com/alexboia/WP-Trip-Summary/issues/67)).
- Feature: added an option (to the WP-Trip-Summary settings page) to specify the initially selected WP-Trip-Summary front-end viewer tab ([Issue 71](https://github.com/alexboia/WP-Trip-Summary/issues/71)).
- Feature: added an option (to the WP-Trip-Summary settings page) to specify the front-end viewer map height ([Issue 70](https://github.com/alexboia/WP-Trip-Summary/issues/70)).
- Improved stability;
- Improved documentation.

### Version 0.2.5
- Trip summary front-end viewer can now inserted at a custom location in the post content for which it has been defined using the [abp01_trip_summary_viewer] shortcode (or a special block, if you're using the block editor);
- Support for trip summary front-end viewer customization: https://github.com/alexboia/WP-Trip-Summary/wiki/Customizing-the-front-end-viewer.
- Refined error reporting when uploading a new GPS track;
- Altitude profile now available;
- Min/max altitude info box now available;
- Refactoring and stability improvements;
- Fixed track uploader not opening on Microsoft Edge browsers;
- Fixed Waymark compatibility issue. 

### Version 0.2.4
- French translation now available!
- The trip summary editor is now launched from a side metabox, which also displays relevant information and features some quick actions;
- The plug-in is now smoothly integrated with the block editor as well;
- In the plug-in settings editor a user can now specify the weight used to plot the GPS track on the map;
- Added automated tests;
- Tested compatibility with WordPress 5.4;
- Fixed a GPX file upload issue that occured with certain GPX files;
- Updated dependencies: URI.js.

### Version 0.2.3
- In the plug-in settings editor a user can now specify the color used to plot the GPX track on the map
- The post and page listing now have two columns that describe whether or not an article has route information and, respectively, whether or not it has an uploaded GPX track
- The plug-in now correctly works for WP pages as well (previously, it would not correctly render on the frontend)
- Added automated tests
- Added compatibility with Mysql 8.0+
- Fixed an activation issue that occured with certain PHP versions
- Updated dependencies: Leaftlet Js, Leaflet Js Magnifying Glass component, NProgress js, MysqliDb

### Version 0.2.2
- The storage directories have received index.php and .htaccess guard access files to prevent direct access of stored files. These are copied on install and on upgrade, but also created upon storing files, if they do not exist.
- Refactoring of view file names: replaced "techbox-" prefix with "wpts-" prefix.
- Removed deprecated uploader runtimes (flash and silverlight) from track uploader.
- Minor refactoring.

### Version 0.2.1
- Moved plug-in track & cache storage to a sub-directory of wp-content/uploads, as, previously, the plug-in stored its track & cache files to its own directory, which caused this data to be lost upon upgrade, since WordPress, when upgrading a plug-in, removes all the files that belong to the previous plug-in version.
- Minor refactoring

### Version 0.2.0
- Fixed An activation issue which occurred under certain conditions.
- Fixed the trip summary editor not being re-centered upon window resize;
- Fixed the settings page not displaying the progress bar when saving, if the page has been scrolled.

### Version 0.2b
- First officially distributed version.

## Upgrade notices
<a name="wpts-upgrade-notices"></a>  

### Updating to 0.2.1

__[Please see here notes on updating to plug-in version 0.2.1](https://github.com/alexboia/WP-Trip-Summary/blob/master/README-UPDATE-021.md)__