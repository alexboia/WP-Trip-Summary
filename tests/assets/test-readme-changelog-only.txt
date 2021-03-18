== Changelog ==

= 0.2.6 =
- usability improvement: added a control to the front-end viewer map that allows one to re-center the map to the GPS track bounding area - basically the initial state of the map when it's first loaded.
- usability improvement: within the WP-Trip-Summary, the "Clear Track" and "Clear Info" buttons have been grouped using a `Quick Actions` control, such as in the metabox used to display the summary in the post page.
- usability improvement: added confirmation when attempting to remove trip summary information as well as when attempting to remove track data.
- usability improvement: when removing a lookup data item with existing associations (that is, associated with at least a post), WP-Trip-Summary no longer issues a hard denial, but asks the user to confirm whether he/she wishes to proceed removing the item, as well as sever its associations with the posts.
- feature: added an option (to the WP-Trip-Summary settings page) to specify the initially selected WP-Trip-Summary front-end viewer tab.
- feature: added an option (to the WP-Trip-Summary settings page) to specify the front-end viewer map height.
- improved stability;
- improved documentation.

= 0.2.5 =
* Trip summary front-end viewer can now inserted at a custom location in the post content for which it has been defined using the [abp01_trip_summary_viewer] shortcode (or a special block, if you're using the block editor);
* Support for trip summary front-end viewer customization: https://github.com/alexboia/WP-Trip-Summary/wiki/Customizing-the-front-end-viewer.
* Refined error reporting when uploading a new GPS track;
* Altitude profile now available;
* Min/max altitude info box now available;
* Refactoring and stability improvements;
* Fixed track uploader not opening on Microsoft Edge browsers;
* Fixed Waymark compatibility issue. 

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
