<p align="center">
   <img align="center" width="210" height="200" src="https://raw.githubusercontent.com/alexboia/WP-Trip-Summary/master/logo.png" style="margin-bottom: 20px; margin-right: 20px;" />
</p>

# WP-Trip-Summary

An opinionated, multi-language, WordPress trip summary plugin to help travel bloggers manage and display structured information about their train rides and biking or hiking trips.

[![WP compatibility](https://plugintests.com/plugins/wporg/wp-trip-summary/wp-badge.svg)](https://plugintests.com/plugins/wporg/wp-trip-summary/latest)
[![PHP compatibility](https://plugintests.com/plugins/wporg/wp-trip-summary/php-badge.svg)](https://plugintests.com/plugins/wporg/wp-trip-summary/latest)

## Contents

1. [Is it good for you?](#wpts-isitgoodforyou)
2. [Features](#wpts-features)
3. [Downloading the plug-in](#wpts-get-it)
4. [Alternatives](#wpts-alternatives)
5. [Progress & Management](#wpts-progress)
6. [What it does](#wpts-what-does)
7. [Supported languages](#wpts-langs)
8. [Changelog](#wpts-changelog)
9. [Upgrade notices](#wpts-upgrade-notices)
10. [Roadmap](#wpts-roadmap)
11. [Requirements](#wpts-requirements)
12. [Limitations](#wpts-limitations)
13. [Screenshots](#wpts-screenshots)
14. [Contributing](#wpts-contributing)
15. [Credits](#wpts-credits)
16. [License](#wpts-license)

## Is it good for you?
<a name="wpts-isitgoodforyou"></a>  

This plug-in is very good for you if:

- you are a travel blogger and you're writing a lot about your trips, as this is a very good way of also providing a bit of extra information (actually, I'm the occasional travel blogger myself and I wrote it with this very purpose in mind).
- you are a niche travel agency, as this would be a very helpful tool to have a highly professional approach to presenting your trips;
- you are a hotel or an accomodation unit and want to present the options your guests would have for spending time around you;
- you are government agency concerned with promoting turistic attractions, as you can have a website up and running in no time: just install WordPress, add this plug-in and you are ready to go.

Do you think this would be right for you but there's that extra thing that's missing? [Let's chat!](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose).

## Features
<a name="wpts-features"></a>  

- attach technical information to a post (ex. how long was your trip, how much did you totally climb, where from and where to, how hard do you think it has been, what kind of roads or trails did you encounter etc.);
- attach some GPS data to a post ([see below for a detailed discussion on accepted file formats](#wpts-features-file-format)) and display that track on a map;
- allows management of the look-up data used to populate the fields presented as single or multi-selection options list (ex. `Difficulty Level`, `Open During Seasons` etc.);
- allows customization of the map layer:
   - map tile source (comes by default configured with [OpenStreetMap](https://www.openstreetmap.org/)); 
   - enabling/disabling of available map controls; 
   - customizing the visual representation of the track).
- allows customization of the measurement unit system used to represent various values (ex. `Total distance`, `Total climb` etc.);
- multi-language.

### File formats accepted for import
<a name="wpts-features-file-format"></a>  

WP-Trip-Summary supports the following file formats when importing GPS track data that should be attached to a post:
- GPX ([see more details here](https://en.wikipedia.org/wiki/GPS_Exchange_Format));
- GeoJSON ([see more details here](https://en.wikipedia.org/wiki/GeoJSON)).

#### GPX

An uploaded file is processed as a GPX file (and validated as such) if it has any of the following mime types:

- `application/gpx`;
- `application/x-gpx+xml`;
- `application/xml-gpx`;
- `application/xml`;
- `application/gpx+xml`;
- `text/xml`.

GPX documents are expected to comply with the [GPX 1.1 schema](https://www.topografix.com/gpx/1/1/) and are parsed as follows:

- Only document name, description and keywords metadata elements are read;
- The following information is read for a point (`<wpt>` or `<trkpt>`), besides latitude and longitude:
   - altitude/elevation (`<ele>` element);
   - name (`<name>` element).
- The following information is read for a track part (`<trk>` element), besides the list of track segments;
   - name (`<name>` element).
- For a track segment (`<trkseg>` elements), only its points are read.

For the more technically inclined, [the parser can be consulted here](https://github.com/alexboia/WP-Trip-Summary/blob/master/lib/route/track/documentParser/Gpx.php).

#### GeoJSON

An uploaded file is processed as a GeoJSON file (and validated as such) if it has any of the following mime types:

- `application/json`;
- `application/geo+json`;
- `application/vnd.geo+json`. 

GeoJson documents are assumed to comply with [RFC 7946](https://tools.ietf.org/html/rfc7946) and are parsed as follows:
- Document metadata is only searched for is the root object is a `FeatureCollection` and the first `Feature` of that collection:
   - has a `properties` property;
   - its `geometry` property is `null`.
- A `LineString` is read as a track part with a single track segment;
- If a `LineString` geometry is contained within a `Feature` object, then the resulting track part's name is searched for in the feature object's `properties` property.

- A `MultiLineString` is read as a track part, and each comprising line string is added to the track part as a track segment;
- If a `MultiLineString` geometry is contained within a `Feature` object, then the resulting track part's name is searched for in the feature object's `properties` property.

- A `Point` is read as a document-level waypoint, regardless of where it is found in the geoJSON file;
- If a `Point` geometry is contained within a `Feature` object, then the waypoint's name is searched for in the feature object's `properties` property.

- All the points in a `MultiPoint` are each added as a document-level waypoint, regardless of where it is found in the geoJSON file;
- If a `MultiPoint` geometry is contained within a `Feature` object, then a name is searched for in the feature object's `properties` property and assigned to each of the resulting waypoints;

- A `Polygon` is read as a track part with a track segment for each of the polygon's contour lines;
- If a `Polygon` geometry is contained within a `Feature` object, then the resulting track part's name is searched for in the feature object's `properties` property.

- A `MultiPolygon` is read as multiple track parts, one for each comprising polygon; each track part is then comprised of the corresponding polygon's contour lines;
- If a `MultiPolygon` geometry is contained within a `Feature` object, then a name is searched for in the feature object's `properties` property and assigned to each of the resulting track parts.

For the more technically inclined, [the parser can be consulted here](https://github.com/alexboia/WP-Trip-Summary/blob/master/lib/route/track/documentParser/GeoJson.php).

## Downloading the plug-in
<a name="wpts-get-it"></a>

You can get the plug-in:

- either from the WordPress plug-in directory: [https://wordpress.org/plugins/wp-trip-summary/](https://wordpress.org/plugins/wp-trip-summary/);
- or from the Releases section of the project's page: [https://github.com/alexboia/WP-Trip-Summary/releases](https://github.com/alexboia/WP-Trip-Summary/releases).

## Alternatives to WP Trip Summary
<a name="wpts-alternatives"></a>  

So I figured it would be nice to list here what other WordPress plugins can be used, if WP Trip Summary does not cover all your needs or its philosophy just isn't for you.
These certainly are not the only ones, but these are the ones I consider the most relevant.

### Waymark

Waymark allows you to create maps as you would create posts, add various shapes to them. These can then be embedded to any post using shortcodes.
It also knows how to import data (markers and lines) from GPX/KML/GeoJSON files, to be displyed on a map.  
It's still constantly updated and I recommend it if all you want to define and organize general purpose maps, possibly a lot of them, and use them in any post.  
Find out more and get it here: [https://wordpress.org/plugins/waymark/](https://wordpress.org/plugins/waymark/).

### WP GPX Maps

WP GPX Maps allows you to upload a GPX track and display it on a map, along with a couple of graphs: altitude, speed, heart rate, temperature, cadence, grade.
It also looks up the media gallery for picture files that would match the coordinates on the track and display those on the map as well.
You get central management of these tracks and you can embed them anywhere using shortcodes.  
It's a bit behind with the updates, but I recommed it for a similar reason I recommended Waymark - if you'd like to manage your stuff centrally and use it anywhere - as well as for the wealth of graphs it provides out of the box.
Find out more and get it here: [https://wordpress.org/plugins/wp-gpx-maps/](https://wordpress.org/plugins/wp-gpx-maps/).

### Lf Hiker

Lf Hiker is somwehat closer to WP Trip Summary's philosophy: it's a plugin that allows you to quickly display your gpx tracks with their profile elevation on an interactive map.
It's also linked to a post; not directly, but through the media gallery, through which you upload your GPX files. Ultimately, you can embed those anywhere using shortcodes and also provide some custom information for each post, to be displayed alongside the core track data.  
It's a bit behind with the updates as well, but I recommend it for a simpler, more track-centered approach.  
Find out more and get it here: [https://wordpress.org/plugins/lf-hiker/](https://wordpress.org/plugins/lf-hiker/).

## Progress & Management
<a name="wpts-progress"></a>  

The [milestones](https://github.com/alexboia/WP-Trip-Summary/milestones) area usually paints a good outlook on the workload for the current release, as well as the past and planned releases.  
You might also be interested in the [issues](https://github.com/alexboia/WP-Trip-Summary/issues) area, for the gruesome details about what's currently on the table.

### Project board

Another area that might interest you is [the project board](https://github.com/users/alexboia/projects/1).   
Since, for some reason, the project does not appear in the `Projects` tab, I don't have any other option other than to announce it here.  
At any rate, that's where you shall find pretty much every idea I have for this project along the way in a - for now - semi-formal to informal aspect.  
Project notes that I commit to as actual development work will be converted to github issues and shall [also appear here](https://github.com/alexboia/WP-Trip-Summary/issues).  

Stucture of the project board:
- `To do bucket` column: any idea enters the project management workflow using this column;
- `To do for current version (x.y.z)` column: ideas that are good candidates for implementation in the current version are moved here; not all may remain and they can either fall back to the `To do bucket` or move to `Nice to have for current version (x.y.z)` (see below);
- `Nice to have for current version (x.y.z)` column: ideas that may or may not be implemented in the current version, due to time constraints vs. usefulness ratio;
- `In progress` & `Done` columns: pretty self-explanatory.

## What it does  
<a name="wpts-what-does"></a>  

This plug-in provides two basic features:

- allow some structured information to be filled in, according to a selected trip type;
- allow some GPS track to be uploaded and then rendered on a map.

### Structured technical information

Structured technical information is supported for the following types of trips:

- Bike trips;
- Hiking trips;
- Train rides.

#### For bike trips

The following fields are available:

- Total distance;
- Total climb;
- Difficulty level;
- Access information (how to get to the start point and return from the end point);
- Open during seasons;
- Path surface type (eg: dirt, asphalt, grass etc.);
- Recommended bike type (eg: MTB, road bike etc.).

#### For hiking trips

The following fields are available:

- Total distance;
- Total climb;
- Difficulty level;
- Access information;
- Open during seasons;
- Path surface type;
- Route markers.

#### For train rides

The following fields are available:

- Total distance;
- How many trains were exchanged;
- Line gauge (mm);
- Railroad operators used;
- Line status (closed, operational etc.);
- Whether the line is electrified or not;
- Line type.

### The track

I really wanted to host the GPS tracks myself for various reasons:

- Didn't want to depend on any third party provider;
- It was good fun writing this feature;
- I want to use the resulting data in the near future to do some other stuff on my website.

Thus, I developed a module to do just that: upload a GPS track (currently only GPX and GeoJSON files can be uploaded), parse it and display it.

## Supported languages
<a name="wpts-langs"></a>  

The following languages are supported:

| Language | Code | Notes |
| --- | --- | --- |
| English | en_US | Also serves as default language |
| French | fr_FR | - |
| Romanian | ro_RO | - |

## Changelog
<a name="wpts-changelog"></a>  

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

## Roadmap
<a name="wpts-roadmap"></a>  

### Road to Version 0.3

Version 0.2.0 was the first I deemed fit to actually be published as a plug-in. 
From there on the main purpose of all 0.2.* versions is to:

   - [ ] improve stability of the plug-in by:
      - [ ] actual bug-fixing (of either reported or found as result of manual testing);
      - [ ] expand the set of available automated tests (in-progress);
      - [ ] gradually update the set of third party components (in-progress).
   - [x] improve the overall usability of the plug-in (meaning the minimum amount of features to render it usable enough for most of the users):
      - [x] allow users to see at a glance, when listing posts, which post has trip summary information and track data and which has not;
      - [x] improve trip summary editing experience;
   - [x] add some options to allow for quick customization:
      - [x] customize the colour of the line used to plot the track on the map;
      - [x] customize the weight of the line used to plot the track on the map.
   - [x] improve localization by adding at least one more language:
      - [x] French translation has been completed.
   - [x] publish a guide for customizing the front-end trip summary viewer (planned for 0.2.5);
   - [ ] add some nice to have features, which can be quickly implemented and provide some value, such as:
      - [x] display minimum and maximum altitude (planned for 0.2.5);
      - [x] compute and display an altitude profile;
      - [ ] display GPX waypoints;
      - [x] allow the trip summary viewer to be added as a shortcode anywhere in the text (planned for 0.2.5).
   - [x] publish a list of things with which anyone can help, if he or she desires to do so;
   - [ ] refactoring (on-going).

### Version 0.3 and onwards

There are a lot of features I would like to see implemented in this plug-in and [I would also be curious about your input on this matter](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose).
However, there is no definitive plan for it, but, as a general rule, I would like to see a maintenance realease every other release, to keep things stable enough.

## Requirements
<a name="wpts-requirements"></a>  

### For running the plug-in itself

1. PHP version 7.0.2 or greater;
2. MySQL version 5.7 or greater (with spatial support);
3. WordPress 5.3.0 or greater;
4. libxml extension;
5. SimpleXml extension;
6. mysqli extension;
7. mbstring - not strictly required, but recommended;
8. zlib - not strictly required, but recommended.

### For development

All of the above, with the following amendments:

1. PHP version 7.0.2 or greater is required;
2. xdebug extension is recommended;
3. phpunit version 5.x installed and available in your $PATH, for running the tests;
4. wp (wp-cli) version 2.x installed and available in your $PATH, for initializing the test environment, if needed
5. phpcompatinfo version 5.x installed and available in your $PATH, for generating the compatibility information files
6. cygwin, for Windows users, such as myself, for setting up the development environment, running unit tests and the build scripts, with the following requirements itself:
   - wget command;
   - curl command;
   - gettext libraries;
   - php core engine and the above-mentioned php extensions;
   - mysql command line client;
   - subversion command line client;
   - zip command.

## Limitations
<a name="wpts-limitations"></a>  

1. ~~Currently it only works with the classic WordPress Editor. An update is planned for 0.3.~~ (Now available since 0.2.4).
2. Not designed for (and not tested with) multi-site installations. No update is currently planned.
3. Currently only supports GPX and GeoJSON files as a way to upload GPS tracks. KML will be supported round about 0.3, maybe earlier.

## Screenshots
<a name="wpts-screenshots"></a>  

##### Editor - Info

![Editor - Info](/screenshots/E1.png?raw=true)

##### Editor - Map

![Editor - Map](/screenshots/E2.png?raw=true)

##### Viewer - Info

![Viewer - Info](/screenshots/V1.png?raw=true)

##### Viewer - Map

![Viewer - Map](/screenshots/V2.png?raw=true)

##### Viewer - Map with altitude profile

![Viewer - Map with altitude profile](/screenshots/V3.gif?raw=true)

## How can you help
<a name="wpts-contributing"></a>  

Despite my best intentions, it would be really hard to come up with a stellar product without any help from those who would either be really interested in using it or would like to work on such a product.  
[I welcome all, I thank you all.](https://github.com/alexboia/WP-Trip-Summary/blob/master/CONTRIBUTING.md)

## Credits
<a name="wpts-credits"></a>  

1. [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class) - small mysqli wrapper for PHP. I used it instead of the builtin wpdb class
2. [MimeReader](http://social-library.org/) - PHP mime sniffer written by Shane Thompson
3. [jQuery EasyTabs](https://github.com/JangoSteve/jQuery-EasyTabs)
4. [Select2](https://select2.org/) - A jQuery Single/Multi Select plugin
5. [Leaflet](https://github.com/Leaflet/Leaflet) - open source JavaScript library for interactive maps
6. [Machina](https://github.com/ifandelse/machina.js/tree/master) - JavaScript state machine
7. [NProgress](https://github.com/rstacruz/nprogress) - slim JavaScript progress bars
8. [Toastr](https://github.com/CodeSeven/toastr) - Javascript library for non-blocking notifications
9. [URI.js](https://github.com/medialize/URI.js) - JavaScript URI builder and parser.
10. [Visible](https://github.com/teamdf/jquery-visible) - jQuery plugin which allows us to quickly check if an element is within the browsers visual viewport regardless of the window scroll position
11. [blockUI](https://github.com/malsup/blockui/) - jQuery modal view plug-in
12. [kite](http://code.google.com/p/kite/) - super small and simple JavaScript template engine
13. [Leaflet.MagnifyingGlass](https://github.com/bbecquet/Leaflet.MagnifyingGlass) - Leaflet plug-in that adds the magnifying glass feature: enlarging a discrete area on the map
14. [Leaflet.fullscreen](https://github.com/Leaflet/Leaflet.fullscreen) - Leaflet plug-in that allows the map to be displayed in full-screen mode
15. [Tipped JS](https://github.com/staaky/tipped) - A Complete Javascript Tooltip Solution
16. [PHPUnit](https://github.com/sebastianbergmann/phpunit) - The PHP Unit Testing framework
17. [Parsedown](https://github.com/erusev/parsedown) - Better Markdown Parser in PHP. [http://parsedown.org](http://parsedown.org)
18. [Faker](https://github.com/fzaninotto/Faker) - Faker is a PHP library that generates fake data for you
19. [Mockery](https://github.com/mockery/mockery) - A simple yet flexible PHP mock object framework for use in unit testing with PHPUnit
20. [Parsedown Extra](https://github.com/erusev/parsedown-extra) - Markdown Extra Extension for Parsedown

## License
<a name="wpts-license"></a> 

The source code is published under the terms of the [BSD New License](https://opensource.org/licenses/BSD-3-Clause) licence.

## Donate

I put some of my free time into developing and maintaining this plugin.
If helped you in your projects and you are happy with it, you can...

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/Q5Q01KGLM)