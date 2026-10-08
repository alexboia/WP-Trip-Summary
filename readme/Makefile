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
screenshot[viewer-summary.png] = Frontend Viewer - Trip information
screenshot[viewer-map.png] = Frontend Viewer - Trip Map
screenshot[viewer-teaser-top.png] = Frontend Viewer - Top Teaser
screenshot[admin-edit-map.png] = Admin - Trip Editor - Map
screenshot[admin-edit-summary-bike.png] = Admin - Trip Editor - Trip information
screenshot[admin-edit-summary-empty.png] = Admin - Trip Editor - No trip type selected yet
screenshot[admin-settings.png] = Admin - Plug-in settings editor
screenshot[admin-settings-predefined-tile-layer.png] = Admin - Plug-in settings editor - chose a pre-defined tile configuration
screenshot[viewer-map-alt-profile.png] = Frontend Viewer - Trip Map with altitude profile
screenshot[maintenance.png] = Admin - Maintenance page
screenshot[admin-log-entry-add.png] = Admin - Add rider log entry
screenshot[admin-log-entry-listing.png] = Admin - List rider log entries
screenshot[viewer-log-entries.png] = Frontend viewer - List rider log entries
