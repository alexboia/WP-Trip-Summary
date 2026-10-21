# Recipes for README.md and README.txt, built by bin/tools/build-readme.php.
#
# Syntax:
#   [Target]           starts the recipe of a target
#   key = value        sets a value
#   key += value       appends comma-separated items to a list value
#   key[Name] = value  adds an entry to an ordered map
#   \ at line end      continues the value on the next line
#   # at line start    starts a comment
#
# Values may use {{variable}} placeholders, defined in readme/manifest.json.
# List items are fragment names from the sections directory, or one of:
#   @changelog         every version from the changelog file
#   @changelog:N       the latest N versions
#   @screenshots       the screenshots declared with screenshot[file] entries

[Github]
format = markdown
output = README.md
banner = <!-- Generated from readme/ by bin/tools/build-readme.php. Edit the sources, not this file. -->
sections = hero, why, screenshots, who-is-it-for, features, getting-started, \
	languages, whats-new, roadmap, faq, requirements, support, credits-license

[WpOrg]
format = wporg
output = README.txt
title = {{pluginName}}
short-description = Structured trip summaries, self-hosted GPS tracks and interactive maps for travel bloggers.
link-base = {{repositoryUrl}}/blob/master/
changelog-links = text

header[Contributors] = alexandruboia
header[Donate link] = {{donateUrl}}
header[Tags] = trip, summary, map, gpx, travel
header[Requires at least] = {{requiresWp}}
header[Tested up to] = 6.5.2
header[Stable tag] = {{version}}
header[Requires PHP] = {{requiresPhp}}
header[License] = Modified BSD License
header[License URI] = https://opensource.org/licenses/BSD-3-Clause

section[Description] = why, who-is-it-for, features, languages, roadmap, support
section[Installation] = getting-started, requirements
section[Frequently Asked Questions] = faq
section[Screenshots] = @screenshots
section[Changelog] = @changelog
section[Upgrade Notice] = upgrade-notice

# Numbered in this order on WordPress.org: screenshot-1, screenshot-2 and so on.
screenshot[wpts-sample-post-viewer-info.png] = Your readers get the essentials at a glance: distance, climb, difficulty, access, seasons and surface
screenshot[wpts-sample-post-viewer-map.png] = Your GPS track on an interactive map, served from your own site
screenshot[wpts-sample-post-viewer-map-alt-profile.png] = The altitude profile under the map shows where the climbing happens
screenshot[wpts-sample-post-viewer-log.png] = The public route log shows who did the route, when, how long it took and what they used
screenshot[wpts-sample-post-teaser.png] = An optional teaser at the top of the post takes readers straight to the trip summary
screenshot[wpts-admin-post-edit-launcher.png] = Open the trip summary editor right from the post editor
screenshot[wpts-admin-post-edit-info.png] = Fill in the trip details, choosing from options tailored to each trip type
screenshot[wpts-admin-post-edit-map.png] = Upload a GPX, KML or GeoJSON file and preview the track before publishing
screenshot[wpts-admin-post-edit-route-log.png] = Keep a log of everyone who did the route, alongside the post
screenshot[wpts-admin-post-edit-route-log-form.png] = Add a route log entry and decide whether your readers can see it
screenshot[wpts-admin-post-listing.png] = See which posts have trip information and a track, and filter them by route type
screenshot[wpts-admin-post-listing-audit-window.png] = Check when a post's trip information and track were last changed, and by whom
screenshot[wpts-settings-viewer.png] = Set the teaser texts, the tab readers see first and how multi-value fields are displayed
screenshot[wpts-settings-map.png] = Choose the map tiles and controls, the altitude display, track downloads and the track's look
screenshot[wpts-settings-map-predefined-layers.png] = Start from a pre-defined tile layer instead of typing the configuration yourself
screenshot[wpts-admin-lookup-data.png] = Manage the options offered for difficulty levels, seasons, surfaces and other fields
screenshot[wpts-maintenance-missing-track-files.png] = Find the posts whose track files have gone missing
screenshot[wpts-maintenance-nginx.png] = Generate Nginx rules that block direct web access to stored track files
screenshot[wpts-admin-system-logs.png] = Read and manage the plugin's debug and error logs from the dashboard
