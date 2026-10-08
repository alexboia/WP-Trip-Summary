=== WP Trip Summary ===
Contributors: alexandruboia
Donate link: https://ko-fi.com/alexandruboia
Tags: trip, summary, map, gpx, travel
Requires at least: 6.0.0
Tested up to: 6.5.2
Stable tag: 0.3.3
Requires PHP: 8.0.0
License: Modified BSD License
License URI: https://opensource.org/licenses/BSD-3-Clause

Structured trip summaries, self-hosted GPS tracks and interactive maps for travel bloggers.

== Description ==

= Why WP Trip Summary =

Readers of a travel post want answers before they read the story: how long is the route, how much climbing does it involve, how hard is it and how do I get there? WP Trip Summary puts those answers in a clean, consistent box at the top of every trip post, next to an interactive map of the route.

- 🧭 **Every trip post becomes a route sheet.** Add distance, total climb, difficulty, seasons, surface type and more. The fields are tailored to each trip type and displayed the same way on every post.
- 🗺️ **Your GPS tracks stay on your server.** Upload a GPX, KML or GeoJSON file and it is parsed, stored and rendered by your own WordPress site. You need no third-party map service account, subscription or API key.
- ⛰️ **Interactive maps with elevation profiles.** Visitors can pan, zoom, go full screen, magnify parts of the route, see its highest and lowest points and explore the altitude profile.
- 📓 **Rider's log.** Keep a record of everyone who rode or walked the route, including dates, time spent, gear and notes. You choose which entries are public.
- 🔎 **Search-engine friendly.** Optionally add schema.org `Place` structured data (JSON-LD) to posts that have a track.
- 🌍 **Ready for your audience.** The plugin is available in English, French, German and Romanian, and it supports metric and imperial units.

= Who it is for =

- **Cycling and bikepacking bloggers** who want every tour to show distance, climb, surface and recommended bike at a glance.
- **Hiking and trekking bloggers** who document trails, route markers, seasons and access routes.
- **Railway enthusiasts** who write about train journeys, including gauge, operators, electrification and line status.
- **Small tour operators, guides and outdoor clubs** that publish routes on a WordPress site and want them presented consistently.

WP Trip Summary attaches the trip information to the post itself. You write your story as usual, and the summary, map and log travel with it.

= Features =

= Structured trip information =

Choose a trip type for each post and fill in the fields that matter for it:

| Bike trips | Hiking trips | Train rides |
| --- | --- | --- |
| Total distance | Total distance | Total distance |
| Total climb | Total climb | Number of train changes |
| Difficulty level | Difficulty level | Line gauge |
| Access information | Access information | Railroad operators |
| Open during seasons | Open during seasons | Line status |
| Path surface type | Path surface type | Electrification |
| Recommended bike type | Route markers | Line type |

Options such as difficulty levels, seasons and surface types are fully editable from the admin area, in every supported language.

= GPS tracks and maps =

- Upload **GPX 1.1**, **KML** or **GeoJSON (RFC 7946)** files. Every coordinate is validated before the track is saved. [See how each format is read](https://github.com/alexboia/WP-Trip-Summary/blob/master/readme/docs/file-formats.md).
- The map is powered by [Leaflet](https://leafletjs.com/) and uses [OpenStreetMap](https://www.openstreetmap.org/) by default. You can choose a predefined tile layer or set up your own.
- Visitors can use full-screen mode, a magnifying glass and the button that re-centers the map on the track.
- An altitude profile chart and a box with the minimum and maximum altitude are available.
- You can let visitors download the track as a GPX file.
- You can change the track's color and line weight.

= Rider's log =

Add as many log entries as you need to each route, with the rider, date, time spent, vehicle, gear and notes. Public entries appear in a dedicated tab of the viewer.

= Easy to place, easy to configure =

- The trip summary viewer is added automatically to the post, or you can place it yourself with the `[abp01_trip_summary_viewer]` shortcode.
- A **Gutenberg block** and a **classic editor button** insert the shortcode for you.
- A **teaser** at the top of the post can invite readers to jump to the trip summary.
- You can choose the tab that the viewer opens on and the unit system (metric or imperial).

= For site owners =

- In the post listing, you can **filter posts by trip type** and see a **trip summary audit log**.
- The **maintenance tools** clear cached track data, find posts with missing track files or remove all plugin data.
- The **system logs** page lets you view and manage the plugin's error and debug logs.

= For developers =

- Trip summary data is exposed through the WordPress REST API, in the `wpts_trip_summary` field of posts.
- [Actions and filters are documented](https://github.com/alexboia/WP-Trip-Summary/tree/master/hook-docs), so you can customize the plugin.
- Optional JSON-LD output (schema.org `Place` with a `GeoShape` bounding box):

```html
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

The box is described by its south-west and north-east corners: `Lat1 Lng1 Lat2 Lng2`.

= Languages =

| Language | Code |
| --- | --- |
| English (default) | en_US |
| French | fr_FR |
| German | de_DE |
| Romanian | ro_RO |

The German translation was partly contributed by [Nico](https://wordpress.org/support/users/nida78/).

Would you like WP Trip Summary in your language? [Open an issue](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) and help translate it.

= Roadmap =

Version 0.4 focuses on flexibility and on making the plugin your own:

- [ ] **Custom trip types**, plus new built-in ones such as road trips and water activities.
- [ ] **Viewer color schemes** that you can customize from the settings page or in code.
- [ ] **GPX waypoints** displayed on the map.
- [ ] **A public API** for plugin and theme developers.
- [ ] **More customizable settings.**

You can follow the work in the [milestones](https://github.com/alexboia/WP-Trip-Summary/milestones). Have an idea? [Suggest a feature](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose).

= Support and community =

- 🐞 **Found a bug or have a question?** [Open an issue](https://github.com/alexboia/WP-Trip-Summary/issues/new/choose) or use the [WordPress.org support forum](https://wordpress.org/support/plugin/wp-trip-summary/).
- ⭐ **Enjoying the plugin?** A [review on WordPress.org](https://wordpress.org/support/plugin/wp-trip-summary/reviews/#new-post) helps other travel bloggers find it.
- 🛠️ **Want to contribute code or translations?** [Read the contribution guide](https://github.com/alexboia/WP-Trip-Summary/blob/master/CONTRIBUTING.md).

= Support the project =

WP Trip Summary is built and maintained in my free time. If it helps you share your journeys, you can support its development:

[Support me on Ko-fi](https://ko-fi.com/Q5Q01KGLM)

You can also support my paid work on [Gumroad](https://alexboia.gumroad.com/).

== Installation ==

= Getting started =

1. In your WordPress admin area, go to `Plugins` -> `Add New` and search for **WP Trip Summary**.
2. Click `Install Now`, then `Activate`.
3. Open a post, choose the trip type in the **Trip Summary** box, fill in the details and upload your GPS track.
4. Publish your post. The trip summary and map appear on the post.

You can fine-tune the plugin from `Trip Summary` -> `Settings`.

Prefer a manual install? Download the latest package from the [WordPress plugin directory](https://wordpress.org/plugins/wp-trip-summary/) or from [GitHub releases](https://github.com/alexboia/WP-Trip-Summary/releases), then upload it from `Plugins` -> `Add New` -> `Upload Plugin`.

= Requirements =

- WordPress 6.0 or newer
- PHP 8.0 or newer, with the `libxml`, `SimpleXML` and `mysqli` extensions
- MySQL 5.7 or newer, with spatial support
- Recommended: the `mbstring` and `zlib` PHP extensions

Developer setup and test instructions are in [CONTRIBUTING.md](https://github.com/alexboia/WP-Trip-Summary/blob/master/CONTRIBUTING.md).

== Frequently Asked Questions ==

= Does it work with the block editor (Gutenberg)? =

Yes. The plugin works with both the block editor and the classic editor.

= Do I need an account with a map provider? =

No. The map uses OpenStreetMap tiles by default, and your tracks are stored on your own server. You can switch to another tile provider if you want to.

= Which GPS file formats can I upload? =

You can upload GPX 1.1, KML and GeoJSON files. [See the parsing details](https://github.com/alexboia/WP-Trip-Summary/blob/master/readme/docs/file-formats.md).

= Can my readers download the track? =

Yes, if you allow it. Track downloads are optional and can be turned on from the plugin settings.

= Can I show the trip summary somewhere else in the post? =

Yes. Use the `[abp01_trip_summary_viewer]` shortcode, the Gutenberg block or the classic editor button. The shortcode takes no parameters and is supported once per post, in the post for which the trip summary was defined.

= Can I use it with post types other than posts and pages? =

Yes, with a filter hook. [See the example in the wiki](https://github.com/alexboia/WP-Trip-Summary/wiki/Changing-the-editor-availability-per-post-type).

= Can I customize how the trip summary looks? =

Yes. [See how to customize the front-end viewer](https://github.com/alexboia/WP-Trip-Summary/wiki/Customizing-the-front-end-viewer).

= Why doesn't the trip summary appear on archive pages? =

The viewer is shown only on the post's own page. It does not appear on listing pages, such as archives, even when they display the full post content.

= Does it support multisite installations? =

WP Trip Summary is designed and tested for single-site installations. Multisite is not officially supported.

= Is it free? =

Yes. WP Trip Summary is free and open source, under the BSD 3-Clause license.

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

== Changelog ==

= 0.3.2 =
- Added German translation (issue #90);
- Added support for KML files (issue #91);
- Bring help contents (mostly) up-to-date with the new features, for ro_RO and en_US (issue #89);
- Add route type filter in admin area post listing (issue #92);
- Some embarrassing bug fixes;
- Add proper error and debug logging infrastructure, including dedicated log management page (go to `Trip Summary` -> `System logs`);
- Other stuff which I may not remember.

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
- Added shortcuts to plug-in's entry from the plug-in listing page;
- Added maintenance page with the following tools: clear cached track data, clear all trip summary info, detect posts that have missing track files;
- Added JSON-LD structured data to posts or pages that have trip summary track data (type Place, with box GeoShape);
- Added additional REST API field to WP-JSON post endpoint to return trip summary data;
- Fixed MysqliDb dependency -  The MysqliDb generates deprectation warnings and needs to be updated (Issue #79);
- Fixed JS warnings caused by including editor scripts in non-editor pages (Issue #78);
- Added trip summary audit log to post edit and post listing pages (Issue #80);
- Updated UI + UX of the lookup data management editor page (Issue #77);
- Fixed trip summary shortcode block not rendering in post/page view;
- Fixed trip summary shortcode block editor widget not showing up.

= 0.2.7 =
- Refactoring: the plug-in now has a more manageable and extensible structure, with the most important change being the splitting of all the code previously in `abp01-plugin-main.php`, into separate plugin modules;
- Feature: Added an about page (Issue 59);
- Feature: Enhanced display for the frontend viewer information items (Issue 75);
- Feature: Added support for GeoJSON file import (Issue 76);
- Improved plug-in documentation;
- Improved usability and UI for the settings page;
- Improved usability and UI for the help page;
- Updated help contents;
- Improved stability and various bug fixes.

= 0.2.6 =
- Usability improvement: added a control to the front-end viewer map that allows one to re-center the map to the GPS track bounding area - basically the initial state of the map when it's first loaded (Issue 68).
- Usability improvement: within the WP-Trip-Summary, the `Clear Track` and `Clear Info` buttons have been grouped using a `Quick Actions` control, such as in the metabox used to display the summary in the post page (Issue 69).
- Usability improvement: added confirmation when attempting to remove trip summary information as well as when attempting to remove track data (Issue 72).
- Usability improvement: when removing a lookup data item with existing associations (that is, associated with at least a post), WP-Trip-Summary no longer issues a hard denial, but asks the user to confirm whether he/she wishes to proceed removing the item, as well as sever its associations with the posts (Issue 67).
- Feature: added an option (to the WP-Trip-Summary settings page) to specify the initially selected WP-Trip-Summary front-end viewer tab (Issue 71).
- Feature: added an option (to the WP-Trip-Summary settings page) to specify the front-end viewer map height (Issue 70).
- Improved stability;
- Improved documentation.

= 0.2.5 =
- Trip summary front-end viewer can now inserted at a custom location in the post content for which it has been defined using the [abp01_trip_summary_viewer] shortcode (or a special block, if you're using the block editor);
- Support for trip summary front-end viewer customization: https://github.com/alexboia/WP-Trip-Summary/wiki/Customizing-the-front-end-viewer.
- Refined error reporting when uploading a new GPS track;
- Altitude profile now available;
- Min/max altitude info box now available;
- Refactoring and stability improvements;
- Fixed track uploader not opening on Microsoft Edge browsers;
- Fixed Waymark compatibility issue.

= 0.2.4 =
- French translation now available!
- The trip summary editor is now launched from a side metabox, which also displays relevant information and features some quick actions;
- The plug-in is now smoothly integrated with the block editor as well;
- In the plug-in settings editor a user can now specify the weight used to plot the GPS track on the map;
- Added automated tests;
- Tested compatibility with WordPress 5.4;
- Fixed a GPX file upload issue that occured with certain GPX files;
- Updated dependencies: URI.js.

= 0.2.3 =
- In the plug-in settings editor a user can now specify the color used to plot the GPX track on the map
- The post and page listing now have two columns that describe whether or not an article has route information and, respectively, whether or not it has an uploaded GPX track
- The plug-in now correctly works for WP pages as well (previously, it would not correctly render on the frontend)
- Added automated tests
- Added compatibility with Mysql 8.0+
- Fixed an activation issue that occured with certain PHP versions
- Updated dependencies: Leaftlet Js, Leaflet Js Magnifying Glass component, NProgress js, MysqliDb

= 0.2.2 =
- The storage directories have received index.php and .htaccess guard access files to prevent direct access of stored files. These are copied on install and on upgrade, but also created upon storing files, if they do not exist.
- Refactoring of view file names: replaced "techbox-" prefix with "wpts-" prefix.
- Removed deprecated uploader runtimes (flash and silverlight) from track uploader.
- Minor refactoring.

= 0.2.1 =
- Moved plug-in track & cache storage to a sub-directory of wp-content/uploads, as, previously, the plug-in stored its track & cache files to its own directory, which caused this data to be lost upon upgrade, since WordPress, when upgrading a plug-in, removes all the files that belong to the previous plug-in version.
- Minor refactoring

= 0.2.0 =
- Fixed An activation issue which occurred under certain conditions.
- Fixed the trip summary editor not being re-centered upon window resize;
- Fixed the settings page not displaying the progress bar when saving, if the page has been scrolled.

= 0.2b =
- First officially distributed version.

== Upgrade Notice ==

= 0.3.2 =
Upgrade to this version for additional features, better user experience and improved plug-in stability

= 0.2.8 =
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
