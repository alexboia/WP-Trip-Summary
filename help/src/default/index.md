# Table of Contents {#help-root}

<div class="abp01-help-section" markdown="1">
- [General Information](#general-information)
- [The Viewer Component](#viewer-component)
- [The Editor Component](#editor-component)
- [Configuration & Management](#configuration-management)
- [Maintenance](#maintenance-component)
</div>

# General Information {#general-information}

## About {#dg-about}

<div class="abp01-help-section" markdown="1">
### Purpose of This Project {#dg-project-purpose}

I initially started this project for my own personal use, as something to help me keep some structured information about my trips.
Along the way, though, I thought there might be a chance of it being of use to some other people as well; so I decided to open-source it under an unrestrictive license.
By and large, this plug-in manages the following sections:

- a technical summary - details such as: total distance, total climb, surface/terrain type etc.;
- the GPS track for the trip, displayed on a map with no additional ceremony besides two marker that pinpoint the start (green marker) and the end (red marker).

### Supported Trip Types {#dg-trip-types}

The technical summary supports data management for the following trip types:

- bike trips;
- trekking/hiking trips;
- train rides.

So you can only document this sort of trips.

### Main Plug-in Components {#dg-main-plugin-components}

Depending on the various functions performed by various plug-in components, we can define three major ones:

- The Viewer Component: the box displayed just below the post; this box displayed the kind of information I just mentioned above ([More details here](#viewer-component));
- The Editor Component: it is attached to the main post editing form and allows adding/modifying/deleting of trip related data ([More details here](#editor-component));
- The Configuration Component: handles plug-in option management, but also the look-up data management (various lists of values from which one can be selected when filling various selector fields in the trip data editor)([More details here](#configuration-management));
- The Maintenance Component: allows you to perform some maintenance-related tasks ([More details here](#maintenance-component)).

[Back to the Table of Contents](#help-root)
</div>

## Screenshots {#dg-screenshots}

<div class="abp01-help-section abp01-help-image-slideshow" markdown="1">
#### The configuration page {.abp01-gallery-item-header}
![The configuration page]($helpDataDirUrl$/screenshots/admin-settings.png "The configuration page")

#### The configuration page - pre-defined tyle layer selection {.abp01-gallery-item-header}
![The configuration page - pre-defined tyle layer selection]($helpDataDirUrl$/screenshots/admin-settings-predefined-tile-layer.png "The configuration page - pre-defined tyle layer selection")

#### Edit post - Trip data - Select trip type {.abp01-gallery-item-header}
![Edit post - Trip data - Select trip type]($helpDataDirUrl$/screenshots/admin-edit-summary-empty.png "Edit post - Trip data - Select trip type")

#### Edit post - Trip data - Bike trip-related information {.abp01-gallery-item-header}
![Edit post - Trip data - Bike trip-related information]($helpDataDirUrl$/screenshots/admin-edit-summary-bike.png "Edit post - Trip data - Bike trip-related information")

#### Edit post - Trip data - Map {.abp01-gallery-item-header}
![Edit post - Trip data - Map]($helpDataDirUrl$/screenshots/admin-edit-map.png "Edit post - Trip data - Map")

#### Edit post - Trip data - Route log listing {.abp01-gallery-item-header}
![Edit post - Trip data - Route log listing]($helpDataDirUrl$/screenshots/admin-log-entry-listing.png "Edit post - Trip data - Route log listing")

#### Edit post - Trip data - Route log add/edit {.abp01-gallery-item-header}
![Edit post - Trip data - Route log add/edit]($helpDataDirUrl$/screenshots/admin-log-entry-add.png "Edit post - Trip data - Route log add/edit")

#### Post view - Top teaser {.abp01-gallery-item-header}
![Post view - Top teaser]($helpDataDirUrl$/screenshots/viewer-teaser-top.png "Post view - Top teaser")

#### Post view - Technical summary {.abp01-gallery-item-header}
![Post view - Technical summary]($helpDataDirUrl$/screenshots/viewer-summary.png "Post view - Technical summary")

#### Post view - Map {.abp01-gallery-item-header}
![Post view - Map]($helpDataDirUrl$/screenshots/viewer-map.png "Post view - Map")

#### Post view - Map With Altitude Profile {.abp01-gallery-item-header}
![Post view - Map With Altitude Profile]($helpDataDirUrl$/screenshots/viewer-map-alt-profile.png "Post view - Map With Altitude Profile")

#### Post listing - Additional info columns {.abp01-gallery-item-header}
![Post listing - Additional info columns]($helpDataDirUrl$/screenshots/post-listing-columns.png "Post listing - Additional info columns")

#### Post View - Route log {.abp01-gallery-item-header}
![Post view - Route log]($helpDataDirUrl$/screenshots/viewer-log-entries.png "Post view - Route log")

#### Maintenance {.abp01-gallery-item-header}
![Maintenance]($helpDataDirUrl$/screenshots/maintenance.png "Maintenance")

[Back to the Table of Contents](#help-root)
</div>

## Technical Requirements {#dg-technical-requirements}

<div class="abp01-help-section" markdown="1">
To run this module, the following technical requirements must be met:

- PHP version 7.0.2 or greater;
- MySQL version 5.7 or greater (with spatial support);
- WordPress 5.3.0;
- libxml extension;
- SimpleXml extension;
- mysqli extension;
- mbstring - not strictly required, but recommended;
- zlib - not strictly required, but recommended.

Basically all these requirements are checked upon installation and the process stops if they are not met.

[Back to the Table of Contents](#help-root)
</div>

## Licensing Terms {#dg-licensing-terms}

<div class="abp01-help-section" markdown="1">
This plug-in is distribute under the terms of the [BSD New License](https://opensource.org/licenses/BSD-3-Clause). What this means:

- that you may use it free of charge and without any kind of royalty;
- that you may distribute free of charge;
- that you must keep a copy of the licensing terms wherever you install it and every time you distribute it;
- that there is no warranty whatsoever, neither explicit, nor implicit.

[Back to the Table of Contents](#help-root)
</div>

## Credits {#dg-credits}

<div class="abp01-help-section" markdown="1">
This plug-in would have required much more work on my said without the following awesome components:

1. [PHP-MySQLi-Database-Class](https://github.com/joshcam/PHP-MySQLi-Database-Class) - small and practical mysqli wrapper; I use it instead of the awful wpdb;
2. [MimeReader](http://social-library.org/) - MIME-Type detector written by Shane Thompson.
3. [jQuery EasyTabs](https://github.com/JangoSteve/jQuery-EasyTabs) - jQuery plug-in used to organize content into tabs;
4. [Select2](https://select2.org/) - jQuery plug-in for multiple-selection-enabled dropdown elements;
5. [Leaflet](https://github.com/Leaflet/Leaflet) - map component;
6. [Machina](https://github.com/ifandelse/machina.js/tree/master) - JavaScript state machine;
7. [NProgress](https://github.com/rstacruz/nprogress) - visual progressbar component;
8. [Toastr](https://github.com/CodeSeven/toastr) - toast notifications library for JavaScript;
9. [URI.js](https://github.com/medialize/URI.js) - JavaScript URI manipulation library;
10. [Visible](https://github.com/teamdf/jquery-visible) - jQuery plug-in for checking if an element is visible within the browser window;
11. [blockUI](https://github.com/malsup/blockui/) - jQuery plug-in for displaying modal content;
12. [kite](http://code.google.com/p/kite/) - small JavaScript template engine;
13. [Leaflet.MagnifyingGlass](https://github.com/bbecquet/Leaflet.MagnifyingGlass) - magnifying glass plug-in for the LeafletJS map component;
14. [Leaflet.fullscreen](https://github.com/Leaflet/Leaflet.fullscreen) - full-screen plug-in for the LeafletJS map component.
15. [Tipped JS](https://github.com/staaky/tipped) - a Complete Javascript Tooltip Solution
16. [PHPUnit](https://github.com/sebastianbergmann/phpunit) - the PHP Unit Testing framework
17. [Parsedown](https://github.com/erusev/parsedown) - better Markdown Parser in PHP. [http://parsedown.org](http://parsedown.org)
18. [Faker](https://github.com/fzaninotto/Faker) - faker is a PHP library that generates fake data for you
19. [Mockery](https://github.com/mockery/mockery) - A simple yet flexible PHP mock object framework for use in unit testing with PHPUnit
20. [Parsedown Extra](https://github.com/erusev/parsedown-extra) - Markdown Extra Extension for Parsedown

[Back to the Table of Contents](#help-root)
</div>

# The Viewer Component {#viewer-component}

<div class="abp01-help-section" markdown="1">
The viewer component is comprised of three distinct areas:

- the top teaser;
- the bottom teaser;
- the technical box itself.
</div>

## The Top Teaser

<div class="abp01-help-section" markdown="1">
This is a small box (yellowish, by default) displayed above the post content, but below the post title.
Its purpose is to guide the readers to the technical box. 
The idea is to let everyone know that there such thing as a technical box and, if that's the only thing they're searching for, quickly take them there.

[Back to the Table of Contents](#help-root)
</div>

## The Bottom Teaser

<div class="abp01-help-section" markdown="1">
Yet another small box (also yellowish, by default), but displayed below the post content.
It is not always displayed, but only when the system detects that the user might have skipped the content (i.e. scrolled too fast), in which case the reader is encouraged to jump back to the beginning of the post to read the post.

[Back to the Table of Contents](#help-root)
</div>

## The Technical Box

<div class="abp01-help-section" markdown="1">
This is the area where, each on a distinct tab, the following sections are displayed:

- the map that displays the route;
- technical stuff (total distance, total climb etc.);
- the trip summary log tab (named, simply, `Log`).

Each tab is only displayed when the corresponding information has been provided by the post author.
If there is not any kind of information for any of these tabs, then the entire component is hidden, including the teasers.

[Back to the Table of Contents](#help-root)
</div>

# The Editor Component {#editor-component}

<div class="abp01-help-section" markdown="1">
The editing component allows editing the trip's technical summary, as well as uploading the GPS track.
Thus, it to is organized in two tabs, one for each category of information:

- the technical summary details editing form;
- the GPS track upload & preview area.
</div>

## The Editor Launcher

<div class="abp01-help-section" markdown="1">
The editor launcher smoothly integrates the trip summary editing experience into WordPress post editing workflow.  
It is presented as a metabox, rendered in the sidebar of the post editing screen, titled: `Trip summary`. 
At a glance, it allows access to the following information and actions:

- whether or not the current post has trip summary information:
    - marked with a white check mark on a green round background if so;
    - marked with a white X mark on a red round background if not.
- whether or the the current post has a trip summary GPS track attached to it:
    - marked with a white check mark on a green round background if so;
    - marked with a white X mark on a red round background if not.
- whether or the the current post has any trip summary log entries attached to it:
    - marked with a white check mark on a green round background if so;
    - marked with a white X mark on a red round background if not.
- quickly remove the current post's trip summary information (via the `Quick actions` link button);
- quickly remove the current post's trip summary GPS track (via the `Quick actions` link button);
- download the current post's trip summary GPS track (via the `Quick actions` link button);
- open the trip summary editor form for the current post (via the `Edit` button)
- scroll down to the box from which the trip summary log can be managed (by clicking the `Trip summary log` editor launcher entry).

[Back to the Table of Contents](#help-root)
</div>

## The Technical Summary Details Editing Form

<div class="abp01-help-section" markdown="1">
The corresponding tab is simply named "Info".
If no information has been filled in, the form only displays three buttons, one for each supported trip type:

- "Biking" - configures the form with the fields required to input bike trip related details;
- "Hiking" - configures the form with the fields required to input hiking trip related details;
- "Train ride" - configures the form with the fields required to input train ride related details.

Also note that, regardless of the form, if any of the fields that require an existing lookup data set to select from does not have any such value defined, then a link will be displayed pointing towards the management page for that set.

Besides the form, on the bottom side of the screen two additional control buttons will be shown, right after a trip type has been selected:

- "Save" - used to save the changes;
- "Clear" - used to when the entire trip information set needs to be removed.
</div>

[Back to the Table of Contents](#help-root)

## The GPS Track Upload & Preview Area

<div class="abp01-help-section" markdown="1">
The corresponding tab is simply named "Map".
If no track has been uploaded yet, then this screen only displays a button that allows browsing for a GPS track file on the local computer.

Once the track has been uploaded, the map will be centered and the zoom level adjusted to the maximum value for which the entire track is displayed. GPS files that contain disconnected multiple segments are also supported.

Besides the form, on the bottom side of the screen two additional control buttons will be shown, right after a trip type has been selected:

- "Save" - used to save the changes;
- "Clear" - used to when the entire track needs to be removed.

[Back to the Table of Contents](#help-root)
</div>

## The trip summary log editor box

<div class="abp01-help-section" markdown="1">
This area contains a list of all the trip summary log entries (records) entered for this post, as well as access to the following actions:

#### Global:

- Add new log entry (via the `Add log entry` button);
- Clear all log entries (via the `Clear all log entries` button, which is only displayed if there are any log entries).

#### For each existing log entry:

- Edit (via the `Edit` link);
- Delete (via the `Delete` link).

### The log entry add/edit form

Every field from this form (apart from the `When` field - which requires a valid date and the `Time` field, which requies a valid integer) can be freely edited.

**HTML code is not allowed.**

If a log entry is not marked as public (`Display publicly` is not checked), then the log entry will not appear in the `Log` frontend viewer tab.

The system attempts to provide default values for the following fields:

- `Who` - The current user display name;
- `When` - The current date;
- `Vehicle used` - The last used vehiche for the current post.

For convenience, when adding or editing multiple log entries in a row, the system will maintain between edits the values of following fields:

- `Who`;
- `When`;
- `Vehicle used`;
- `Gear`,
- `Display publicly`.

[Back to the Table of Contents](#help-root)
</div>

# Configuration & Management {#configuration-management}

<div class="abp01-help-section" markdown="1">
- [General Options](#configure-general-options)
- [Lookup Data Management](#configure-lookup-data)

The configuration elements can be seen as forming two discrete sections:

- general options - measurement units, enabling or disabling various interface elements etc.;
- lookup data management - these are data sets of predefined options from which some fields are filled in, such as the Difficulty Level.
</div>

## General Options {#configure-general-options}

<div class="abp01-help-section" markdown="1">
There is a dedicated page where these options may be modified to best suit your needs. One can get there by accessing Trip Summary -> Settings in the main menu.

Once there, the following settings are made available.

#### The Measurement Unit System

The plug-in supports the metric system (m/km) as well as the imperial system (mile/inch).
It is worth mentioning that the plug-in does not perform any conversion and it assumes that any value provided is already expressed in the chosen measurement unit. Also, when the measurement units are changed, the values are not automatically converted.

#### Whether to Display the Teasers or Not

Once the field is unchecked and the changes are saved, the teasers will no longer be displayed (nor the top teaser, nor the bottom teaser).

#### The Top Teaser Text

The text shown in the top teaser (above the post content, but right below the title).

#### The Bottom Teaser Text

The text shown in the bottom teaser (right below the technical summary box).

#### Initial viewer tab

This field allows setting the front-end viewer tab selected when the user visits the post page.
Default value: Map.

#### Chose how multi-value items are laid out

This field allows specifying how multiple values of the same item are laid out in the front-end viewer:
    - horizontally, one after another;
    - vertically, one beneath another.

#### Chose how many values of a multi-valued item are displayed

This field allows setting how many values are displayed for items that have multiple values. 
If an item has more values than the what is set in this field, then they are hidden and a `(show)` button is displayed.

#### Map Tile URL Template

The discussion here is a bit longer.

First of all, one has to keep in mind that the map is not displayed as only one physical image, but using multiple images. These images - called tiles - when put together in a specific order, form the image of the map itself.

Also, there are multiple set of tiles, one for each zoom level and, within each set, each tile is located using two coordinates - let us name them x & y - sort of like squares on a chess board.

Therefore, in order to access & load a tile from whatever server provides them, we need to request it using the following pieces of information:

- z - the zoom level;
- x - horizontal position;
- y - vertical position.

There is one more problem, though: in order to load that many images in a reasonable time, they are replicated between multiple machines/servers and the tile requests are split between them. That's not mandatory, but often used.
These servers are also numbered - for instance: 1, 2, 3, 4 etc.

This, then, might add another variable when requesting tiles and also when supplying the URL template that will be used to display the map wherever this plug-in requires it. 
Add this to the set of coordinates described above and we have this list of supported URL template variables:

- {s} - to specify where the tile server number should be added (eg. {s}.tile.osm.org would translate to 1.tile.osm.org, 2.tile.osm.org etc.);
- {z} - to specify where the zoom level needs to be inserted;
- {x} - to specify where the tile x coordinate needs to be inserted;
- {y} - to specify where the tile y coordinate needs to be inserted.

These variables can occur anywhere in the string and one must consult the map tile provider to find out the supported URL template.

#### Map Tile Layer Attribution URL

Depending on where you chose to display the map from, the attribution may or may not be mandatory. It is, at a minimum, a nice thing to have, so I encourage you to do it.
The attribution is placed in the lower right corner of the map area and this field allows you to add a URL to the provider's page.

#### Map Tile Layer Attribution Text

Depending on where you chose to display the map from, the attribution may or may not be mandatory. It is, at a minimum, a nice thing to have, so I encourage you to do it.
The attribution is placed in the lower right corner of the map area and this field allows you to add a text that describes the provider (a copyright notice of sorts).

#### Enable Map Full-screen Mode?

Once the field is unchecked and the changes are saved, the full-screen button will not be displayed in the Viewer Component anymore.
By default, this field is checked, so the button is displayed.

#### Show Magnifying Glass?

Once the field is unchecked and the changes are saved, the magnifying glass switch button will no longer be displayed.
By default, this field is checked, so the button is displayed.

#### Show Map Scale?

Once the field is unchecked and the changes are saved, the map scale will no longer be displayed in the lower left corner of the map area.
By default, this field is checked, so the map scale is displayed.

#### Allow Track Download?

Once the field is unchecked and the changes are saved, the GPS track download button will no longer be displayed.
By default, this field is checked, so the button is displayed.

#### Track line colour

This field allows setting the colour used to plot the GPS track on the map. 
Applies to both front-end viewer and back-end trip summary editor.
The default value is the previously used colour: `#0033ff`.

#### Track line weight

This field allows setting the thickness, in pixels, of the line used to plot the GPS track on the map.
Applies to both front-end viewer and back-end trip summary editor.
The default value is the previously used thickness: 3 pixels.

#### Map height

This field allows setting the height, in pixels, of the actual map component. 
Applies only to the front-end viewer.
The default value is the former default map height: 350 pixels.

[Back to the Table of Contents](#help-root)
</div>

## Lookup Data Management {#configure-lookup-data}

<div class="abp01-help-section" markdown="1">
Lookup data represents a couple of sets of predefined options out of which some fields are filled in. Some fields only required one value, some multiple.
The plug-in supports per-language values for each option in a data set. 
Also, one can define said values for any WordPress-supported language.
Then, there is the possibility of selecting a default value: this is the value displayed when, for any given option, no explicit value is found in the context of the current language.

### Managed Fields

The fields for which look-up data management is necessary are as follows:

- Difficulty level;
- Open during seasons;
- Path surface type;
- Bike type;
- Railroad operators;
- Electrification status;
- Line type;
- Line status.

#### The "Difficulty Level" Field

This field is available for the following trip types:

- bike trips;
- trekking/hiking trips.

It reflects one's subjective evaluation of how hard the trip was, in terms of effort.
The plug-in provides the following pre-defined options (with English and Romanian translations):

- Easy;
- Medium;
- Hard;
- Medieval torture.

#### The "Open During Seasons" Field

This field is available for the following trip types:

- bike trips;
- trekking/hiking trips.

It allows specifying the seasons during which the route can be traveled through in decent conditions (without extreme expense in terms of effort and without taking great risks).
The plug-in provides the following pre-defined options (with English and Romanian translations):

- Spring;
- Summer;
- Autumn;
- Winter.

#### The "Path Surface Type" field

This field is available for the following trip types:

- bike trips;
- trekking/hiking trips.

It allows specifying the texture / composition of the roads & trails crossed by the route. Eg.: grass, rocky, tarmac, gravel etc.
The plug-in provides the following pre-defined options (with English and Romanian translations):

- Asphalt;
- Concrete;
- Dust or dirt;
- Grass;
- Stone pavement/Gravel;
- Loose rocks.

#### The "Bike Type" Field

This field is available for the following trip types:

- bike trips.

It allows specifying the bike types that should be used for optimal safety and comfort.
The plug-in provides the following pre-defined options (with English and Romanian translations):

- MTB;
- Road bike;
- Trekking;
- City bike.

#### The "Railroad Operators" Field

This field is available for the following trip types:

- train rides.

It allows specifying what companies operate on the described route, be it in its entirety, be it only partial.
There are no pre-defined options.

#### The "Electrification Status" Field

This field is available for the following trip types:

- train rides.

It allows specifying whether the line was electrified and to what extent.
The plug-in provides the following pre-defined options (with English and Romanian translations):

- Electrified;
- Not electrified;
- Partially electrified.

#### The "Line Type" field

This field is available for the following trip types:

- train rides.

It allows specifying whether the line is simple or double (one set of tracks for each way).
The plug-in provides the following pre-defined options (with English and Romanian translations):

- Simple line;
- Double line.

#### The "Line Status" field

This field is available for the following trip types:

- train rides.

It allows specifying the line status (for instance whether the line is closed, operating normally, undergoing repairs etc.).
The plug-in provides the following pre-defined options (with English and Romanian translations):

- In production;
- Closed;
- Disbanded;
- In rehabilitation.

### Supported Operations

The following operations are supported, each in the context of a chosen language:

- Adding a new option/item in a lookup data set;
- Deleting an existing option/item from a lookup data set;
- Modifying an existing option/item;
- Listing all existing items in a lookup data set.

The following should also be noted:

- when adding a new option/item for the default language, the system only requires the label for that language.
- when adding a new option/item for a specific language, the systems asks requires the label for both that language and the default language.

[Back to the Table of Contents](#help-root)
</div>

# Maintenance {#maintenance-component}

<div class="abp01-help-section" markdown="1">
The maintenance section is actually a set of tools that allows you to perform some, let's say, non-day-to-day maintenance tasks.
Right now (as of version 0.3.1), these are:

- `Clear track data cache`: this clears the internal cache that this plugin maintains when processing uploaded track files (cannot be undone);
- `Clear all trip summary related data`: info, track file data and table records, cache everything (cannot be undone);
- `Detect missing track files`: this tool can detect a situation in which you have uploaded a track to a post, but the file no longer exists (cannot detect the reason, though).

To use these maintenance features, simply navigate to `Trip Summary` - `Maintenance` and select the tool you want to run.
You will be prompted for a confirmation and then the selected tool will start running.

[Back to the Table of Contents](#help-root)
</div>