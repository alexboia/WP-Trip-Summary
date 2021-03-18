=== WP Trip Summary ===
Contributors: alexandruboia
Donate link: https://ko-fi.com/alexandruboia
Tags: trip, summary, map, gpx, travel
Requires at least: 5.3.0
Tested up to: 5.7.0
Stable tag: 0.2.6
Requires PHP: 5.6.2
License: BSD New License
License URI: https://opensource.org/licenses/BSD-3-Clause

A WordPress trip summary plugin to help travel bloggers manage and display structured information about their train rides and biking or hiking trips.

== Description ==

Is this plug-in a good fit for you?
------------------------------------
This plug-in is very good for you if:

- you are a travel blogger and you're writing a lot about your trips, as this is a very good way of also providing a bit of extra information (actually, I'm the occasional travel blogger myself and I wrote it with this very purpose in mind).
- you are a niche travel agency, as this would be a very helpful tool to have a highly professional approach to presenting your trips;
- you are a hotel or an accomodation unit and want to present the options your guests would have for spending time around you;
- you are government agency concerned with promoting turistic attractions, as you can have a website up and running in no time: just install WordPress, add this plug-in and you are ready to go.

Features
--------
- attach technical information to a post (ex. how long was your trip, how much did you totally climb, where from and where to, how hard do you think it has been, what kind of roads or trails did you encounter etc.);
- attach a GPS track to a post (GPX files are currently accepted) and display that track on a map;
- allows management of the look-up data used to populate the fields presented as single or multi-selection options list (ex. `Difficulty Level`, `Open During Seasons` etc.);
- allows customization of the map layer:
   - map tile source (comes by default configured with [OpenStreetMap](https://www.openstreetmap.org/)); 
   - enabling/disabling of available map controls; 
   - customizing the visual representation of the track).
- allows customization of the measurement unit system used to represent various values (ex. `Total distance`, `Total climb` etc.);
- multi-language.

More details
------------
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
- Wordpress 5.0 or greater;
- libxml extension;
- SimpleXml extension;
- mysqli extension;
- mbstring - not strictly required, but recommended;
- zlib - not strictly required, but recommended.

Available in English and Romanian.

Important note:
For those with plug-in versions older than 0.2.1, please see here notes on updating to plug-in version 0.2.1: https://github.com/alexboia/WP-Trip-Summary/blob/master/README-UPDATE-021.md

== Frequently Asked Questions ==

= Does it support KML files? =
The plug-in currently only supports GPX files as a way to upload GPS tracks. KML will be supported round about 0.3, maybe earlier.

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

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/abp01-wp-trip-summary` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the `Trip Summary -> Settings` sidebar menu item to access the plug-in configuration page.
4. Use the `Trip Summary -> Help` sidebar menu item to access the plug-in help page.
5. For those with plug-in versions older than 0.2.1, please see here notes on updating to plug-in version 0.2.1: https://github.com/alexboia/WP-Trip-Summary/blob/master/README-UPDATE-021.md

== Upgrade Notice ==

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