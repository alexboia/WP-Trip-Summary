<img align="left" width="210" height="200" src="https://raw.githubusercontent.com/alexboia/WP-Trip-Summary/master/logo.png" style="margin-bottom: 20px; margin-right: 20px;" />

# WP-Trip-Summary

An opinionated, multi-language, WordPress trip summary plugin to help travel bloggers manage and display structured information about their train rides and biking or hiking trips.

[![WP compatibility](https://plugintests.com/plugins/wp-trip-summary/wp-badge.svg)](https://plugintests.com/plugins/wp-trip-summary/latest)
[![PHP compatibility](https://plugintests.com/plugins/wp-trip-summary/php-badge.svg)](https://plugintests.com/plugins/wp-trip-summary/latest) 

## Contents

   1. [Progress](#wpts-progress)
   2. [What it does](#wpts-what-does)
   3. [Supported languages](#wpts-langs)
   4. [Changelog](#wpts-changelog)
   5. [Upgrade notices](#wpts-upgrade-notices)
   6. [Roadmap](#wpts-roadmap)
   7. [Requirements](#wpts-requirements)
   8. [Limitations](#wpts-limitations)
   9. [Screenshots](#wpts-screenshots)
   10. [Contributing](#wpts-contributing)
   11. [Credits](#wpts-credits)
   12. [License](#wpts-license)

## Progress  
<a name="wpts-progress"></a>  

Please see the [issues](https://github.com/alexboia/WP-Trip-Summary/issues) area for the progress on any on-going issues.

## What it does  
<a name="wpts-what-does"></a>  

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
   - [ ] improve the overall usability of the plug-in (meaning the minimum amount of features to render it usable enough for most of the users):
      - [x] allow users to see at a glance, when listing posts, which post has trip summary information and track data and which has not;
      - [ ] improve trip summary editing experience;
   - [ ] add some options to allow for quick customization:
      - [x] customize the colour of the line used to plot the track on the map;
      - [ ] customize the weight of the line used to plot the track on the map.
   - [ ] improve localization by adding at least one more language:
      - [ ] currently, French is in the progress.
   - [ ] publish a guide for customizing the front-end trip summary viewer;
   - [ ] add some nice to have features, which can be quickly implemented and provide some value, such as:
      - [ ] display minimum and maximum altitude;
      - [ ] compute and display an altitude profile;
      - [ ] display GPX waypoints;
      - [ ] allow the trip summary viewer to be added as a shortcode anywhere in the text.
   - [ ] publish a list of things with which anyone can help, if he or she desires to do so;
   - [ ] refactoring.

### Version 0.3 and onwards

There are a lot of features I would like to see implemented in this plug-in and [I would also be curious about your input on this matter](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose).
However, there is no definitive plan for it, but, as a general rule, I would like to see a maintenance realease every other release, to keep things stable enough.

## Requirements
<a name="wpts-requirements"></a>  

### For running the plug-in itself

1. PHP version 5.6.2 or greater;
2. MySQL version 5.7 or greater (with spatial support);
3. WordPress 5.0 or greater;
4. libxml extension;
5. SimpleXml extension;
6. mysqli extension;
7. mbstring - not strictly required, but recommended;
8. zlib - not strictly required, but recommended.

### For development

All of the above, with the following amendments:

1. PHP version 5.4.0 or greater is required;
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

1. Currently it only works with the classic WordPress Editor. An update is planned for 0.3.
2. Not designed for (and not tested with) multi-site installations. No update is currently planned.

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

## How can you help
<a name="wpts-contributing"></a>  

[Help is always wanted and here's how you can help too](https://github.com/alexboia/WP-Trip-Summary/blob/master/CONTRIBUTING.md).

## Credits
<a name="wpts-credits"></a>  

1. [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class) - small mysqli wrapper for PHP. I used it instead of the builtin wpdb class
2. [MimeReader](http://social-library.org/) - PHP mime sniffer written by Shane Thompson
3. [jQuery EasyTabs](https://github.com/JangoSteve/jQuery-EasyTabs)
4. [jQuery.SumoSelect](https://github.com/HemantNegi/jquery.sumoselect) - A jQuery Single/Multi Select plugin
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

## License
<a name="wpts-license"></a> 

The source code is published under the terms of the [BSD New License](https://opensource.org/licenses/BSD-3-Clause) licence.

## Donate

I put some of my free time into developing and maintaining this plugin.
If helped you in your projects and you are happy with it, you can...

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/Q5Q01KGLM)