WP-Trip-Summary
===============
A WordPress trip summary plugin. I initially wrote this for my own personal use, as I needed something to help me keep structured information about my trips.
However, as I was getting ready to push this to production I realised it would be a good idea to publish the source code.

There are, of course, some things to be done before this would be of any real use to anyone but me and, in the process, some breaking changes may occur.
Please see the [issues](https://github.com/alexboia/WP-Trip-Summary/issues) area for the progress on those things.

## What it does
This plug-in provides two basic features:
- allow some structured information to be filled in, according to a selected trip type;
- allow some GPX track to be uploaded and then rendered on a map.

### Structured information
Structured information is supported for the following types of trips:
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
I really wanted to host the GPX tracks myself for various reasons:
- Didn't want to depend on any third party provider;
- It was good fun writing this feature;
- I want to use the resulting data in the near future to do some other stuff on my website.

Thus, I developed a module to do just that: upload a GPX track, parse it and display it.

## Requirements
1. MySQL spatial support;
2. Wordpress 4.0 (maybe lower, but I haven't checked it up yet so I'd rather err on the safe side for now);
3. libxml extension;
4. mysqli extension.

## Screenshots
##### Editor - Info
![Editor - Info](/screenshots/E1.png?raw=true)

##### Editor - Map
![Editor - Map](/screenshots/E2.png?raw=true)

##### Viewer - Info
![Viewer - Info](/screenshots/V1.png?raw=true)

##### Viewer - Map
![Viewer - Map](/screenshots/V2.png?raw=true)

## Credits
1. [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class) - small mysqli wrapper for PHP. I used it instead of the builtin wpdb class
2. [MimeReader](http://social-library.org/) - PHP mime sniffer written by Shane Thompson
3. [jQuery EasyTabs](https://github.com/JangoSteve/jQuery-EasyTabs)
4. [jQuery.SumoSelect](https://github.com/HemantNegi/jquery.sumoselect) - A jQuery Single/Multi Select plugin
5. [Leaflet](https://github.com/Leaflet/Leaflet) - open source JavaScript library for interactive maps
6. [Lodash](https://github.com/lodash/lodash) - utility library for JavaScript
7. [Machina](https://github.com/ifandelse/machina.js/tree/master) - JavaScript state machine
8. [NProgress](https://github.com/rstacruz/nprogress) - slim JavaScript progress bars
9. [Toastr](https://github.com/CodeSeven/toastr) - Javascript library for non-blocking notifications
10. [URI.js](https://github.com/medialize/URI.js) - JavaScript URI builder and parser.
11. [Visible](https://github.com/teamdf/jquery-visible) - jQuery plugin which allows us to quickly check if an element is within the browsers visual viewport regardless of the window scroll position
12. [blockUI](https://github.com/malsup/blockui/) - jQuery modal view plug-in
13. [kite](http://code.google.com/p/kite/) - super small and simple JavaScript template engine
14. [Leaflet.MagnifyingGlass](https://github.com/bbecquet/Leaflet.MagnifyingGlass) - Leaflet plug-in that adds the magnifying glass feature: enlarging a discrete area on the map
15. [Leaflet.fullscreen](https://github.com/Leaflet/Leaflet.fullscreen) - Leaflet plug-in that allows the map to be displayed in full-screen mode

## Licence
The source code is published under the terms of the [BSD New License](https://opensource.org/licenses/BSD-3-Clause) licence.
