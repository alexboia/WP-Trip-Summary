<p align="center">
   <img align="center" width="210" height="200" src="https://raw.githubusercontent.com/alexboia/WP-Trip-Summary/master/logo.png" style="margin-bottom: 20px; margin-right: 20px; border-radius: 5px;" />
</p>

<h1 align="center">WP-Trip-Summary</h1>

<p align="center">
   An opinionated, multi-language, WordPress trip summary plugin to help travel bloggers manage and display structured information about their train rides and biking or hiking trips.
</p>

<p align="center">
   <img align="center" src="https://raw.githubusercontent.com/alexboia/WP-Trip-Summary/master/screenshots/MAIN.png?raw=true" style="margin-bottom: 20px; margin-right: 20px;" />
</p>

## Status

[![WP compatibility](https://plugintests.com/plugins/wporg/wp-trip-summary/wp-badge.svg?_v=0.3.1)](https://plugintests.com/plugins/wporg/wp-trip-summary/latest)
[![PHP compatibility](https://plugintests.com/plugins/wporg/wp-trip-summary/php-badge.svg?_v=0.3.1)](https://plugintests.com/plugins/wporg/wp-trip-summary/latest)

## Features
<a name="wpts-features"></a>  

- attach technical information to a post (ex. how long was your trip, how much did you totally climb, where from and where to, how hard do you think it has been, what kind of roads or trails did you encounter etc.);
- attach some GPS data to a post ([see below for a detailed discussion on accepted file formats](#wpts-features-file-format)) and display that track on a map;
- maintain rider's log entries, while optionally specifying some of them as public;
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
- KML ([see more details here](https://developers.google.com/kml/documentation/kmlreference));

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

#### KML

An uploaded file is processed as a KML file (and validated as such) if it has any of the following mime types:

- `application/vnd.google-earth.kml+xml`.

The KML parse is based on [Stepan Daleky's KML parser on GitLab](https://gitlab.com/stepandalecky/kml-parser), which I further built upon in two areas:

- support for additional KML objects;
- a basic processing infrastructure.

It might worth your while (to take a loot at it here)[https://github.com/alexboia/WP-Trip-Summary/tree/master/lib/3rdParty/kml-parser/KmlParser].

KML documents are parsed as follows:

- Either root KML folder or root KML document is considered, not both (first it checks for a root folder and, if not found for a root document);
- A KML container is searched, in this order, for: folders, documents and placemarks;
- For a placemark, `Point`, `Linestring`, `LinearRing`, `Polygon` and `MultiGeometry` geometries are supported;
- Neither folder, nor document metadata is stored;

- A `Point` geometry is read as a document-level waypoint, regardless of where it is found in the KML file;
- For a `Point` geometry, the name and description metadata are stored;

- A `LineString` is read as a track part with a single track segment;
- For a `LineString` geometry, only the name metadata is stored;

- A `LinearRing` is read as a track part with a single track segment;
- For a `LinearRing` geometry, only the name metadata is stored;

- A `Polygon` is read as two track parts: one for the outer boundary `LinearRing`, the other for the inner boundary `LinearRing`;
- For a `Polygon` geometry, only the name metadata is stored, for each of the resulting track parts;

- A `MultiGeometry` is processed by reading its individual parts, not as a whole, obeying the above-mentioned rules.

For the more technically inclined, [the parser can be consulted here](https://github.com/alexboia/WP-Trip-Summary/blob/master/lib/route/track/documentParser/Kml.php).

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

### Lf Hiker (no longer maintained)

Lf Hiker is somwehat closer to WP Trip Summary's philosophy: it's a plugin that allows you to quickly display your gpx tracks with their profile elevation on an interactive map.
It's also linked to a post; not directly, but through the media gallery, through which you upload your GPX files. Ultimately, you can embed those anywhere using shortcodes and also provide some custom information for each post, to be displayed alongside the core track data.  
It's a bit behind with the updates as well, but I recommend it for a simpler, more track-centered approach.  
Find out more and get it here: [https://wordpress.org/plugins/lf-hiker/](https://wordpress.org/plugins/lf-hiker/).

## Progress & Management
<a name="wpts-progress"></a>  

The [milestones](https://github.com/alexboia/WP-Trip-Summary/milestones) area usually paints a good outlook on the workload for the current release, as well as the past and planned releases.  
You might also be interested in the [issues](https://github.com/alexboia/WP-Trip-Summary/issues) area, for the gruesome details about what's currently on the table.

## What it does  
<a name="wpts-what-does"></a>  

This plug-in provides three basic features:

- allow some structured information to be filled in, according to a selected trip type;
- allow some GPS track to be uploaded and then rendered on a map;
- maintain rider's log entries, while optionally specifying some of them as public.

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

### Rider's log entries

For each post you can add unlimited log entries, each corresponding to someone travelling that route, specifying the following details:

- Who (the rider's name);
- When (date);
- Time (how many hours spent);
- Vehicle used (e.g. bike make and model);
- Gear (notes about what equipment was used - backpack configuration and the like);
- Other (random) notes;
- Whether or not the entry should be displayed publicly (if so, then the entry will be shown in the frontend viewer in a separate tab dedicated to log entries).

### JSON-LD front-end data

The plug-in, as of version `0.2.8` inserts structured JSON-LD data in the post and page details page, 
if there is track data attached to that post or page.

This behaviour is configurable and can be disabled or enabled in the plug-in's configuration page.
By default, it is disabled.

Here is a sample JSON-LD data set inserted by this plug-in:

```javascript
<script type="application/ld+json">
{
	"@context": "https://schema.org",
	"@type": "Place",
	"geo": {
		"@type": "GeoShape",
		"box": "45.69152 23.72547 46.01246 25.27592"
	},
	"name": "Towards Eagle's lake"
}
</script>
```

### Maintenance

As of version `0.2.8`, there is a new `Maintenance` section, which allows you to carry out various maintenance tasks:
- Clear all cached track data information;
- Clear all plug-in related information (all post trip summary, all cached data and all stored track files);
- Detect which posts that should have track data information are actually missing track files.

The box is described by the south-west and north-east points, in lat-lng format: `Lat1 Lng1 Lat2 Lng2`.

Menu: `Trip Summary` -> `Maintenance`.

### System logs

As of version `0.3.2`, there is a new `System logs` section, which allows you to manage the log files to which WP Trip Summary writes its debug and error messages.

There are two sections for each of the log types (debug and error, respectively, as mentioned), BUT debug logs are only produced if:

- `ABP01_ENABLE_DEBUG_LOGGING` is defined (in `wp-config.php`) and set to `true` (if defined, it will supersede `WP_DEBUG`) or;
- `WP_DEBUG` is defined and set to `true`.

Menu: `Trip Summary` -> `System logs`.

## Supported languages
<a name="wpts-langs"></a>  

The following languages are supported:

| Language | Code | Notes |
| --- | --- | --- |
| English | en_US | Also serves as default language |
| French | fr_FR | - |
| Romanian | ro_RO | - |
| German | de_DE | Partly contribured by [Nico](https://wordpress.org/support/users/nida78/), partly translated using Google Translate. |

## Changelog
<a name="wpts-changelog"></a>  

### Version 0.3.2
- Added German translation ([issue #90](https://github.com/alexboia/WP-Trip-Summary/issues/90));
- Added support for KML files ([issue #91](https://github.com/alexboia/WP-Trip-Summary/issues/91));
- Bring help contents (mostly) up-to-date with the new features, for ro_RO and en_US ([issue #89](https://github.com/alexboia/WP-Trip-Summary/issues/89));
- Add route type filter in admin area post listing ([issue #92](https://github.com/alexboia/WP-Trip-Summary/issues/92));
- Some embarrassing bug fixes;
- Add proper error and debug logging infrastructure, including dedicated log management page (go to `Trip Summary` -> `System logs`);
- Other stuff which I may not remember.

[See the entire changelog here](https://github.com/alexboia/WP-Trip-Summary/blob/master/CHANGELOG.md)

## Roadmap
<a name="wpts-roadmap"></a>  

### Road to Version 0.4

Moving forward to 0.4, the focus is on extensibility, a slightly improved UI and, as a nice to have, a broader language support:

   - [ ] support custom route types:
      - [ ] also add some more pre-defined types, such as: roadtrips, water-stuff etc.;
   - [ ] allow customization of settings section;
   - [ ] allow viewer color-scheme customization, both programatically and from the settings page.
   - [ ] add some nice to have features, which can be quickly implemented and provide some value, such as:
      - [ ] display GPX waypoints;
   - [ ] an API for plugin developers;
   - [ ] refactoring (on-going);
   
Also, [use the Issues area to submit your idea](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose).

## Requirements
<a name="wpts-requirements"></a>  

### For running the plug-in itself

1. PHP version 8.0.0 or greater;
2. MySQL version 5.7 or greater (with spatial support);
3. WordPress 6.0.0 or greater;
4. libxml extension;
5. SimpleXml extension;
6. mysqli extension;
7. mbstring - not strictly required, but recommended;
8. zlib - not strictly required, but recommended.

### For development

All of the above, with the following amendments:

1. xdebug extension is recommended;
2. phpunit version 5.x installed and available in your $PATH, for running the tests;
3. wp (wp-cli) version 2.x installed and available in your $PATH, for initializing the test environment, if needed
4. phpcompatinfo version 5.x installed and available in your $PATH, for generating the compatibility information files
5. cygwin, for Windows users (or Windows Linux Subsystem, in which case it pretty much works out of the box), such as myself, for setting up the development environment, running unit tests and the build scripts, with the following requirements itself:
   - wget command;
   - curl command;
   - gettext libraries;
   - php core engine and the above-mentioned php extensions;
   - mysql command line client;
   - subversion command line client;
   - zip command.

## Limitations
<a name="wpts-limitations"></a>  

1. ~~Currently it only works with the classic WordPress Editor. An update is planned for 0.3.~~ (Available as of 0.2.4).
2. Not designed for (and not tested with) multi-site installations. No update is currently planned.
3. ~~Currently only supports GPX and GeoJSON files as a way to upload GPS tracks. KML will be supported round about 0.3, maybe earlier~~ (Available as of 0.3.2).

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

##### Viewer - Log entries

![Viewer - Info](/screenshots/V4.png?raw=true)

## How can you help
<a name="wpts-contributing"></a>  

Despite my best intentions, it would be really hard to come up with a stellar product without any help from those who would either be really interested in using it or would like to work on such a product.  
[See here how one can contribute.](https://github.com/alexboia/WP-Trip-Summary/blob/master/CONTRIBUTING.md)

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