## What's new
<a name="wpts-changelog"></a>

### Version 0.3.3

- Redesigned the front-end viewer, including trip information, trip type labels and rider's log entries;
- Added horizontal scrolling for viewer tabs when they exceed the available width, keeping the active or keyboard-focused tab visible;
- Refreshed the admin settings, maintenance, lookup data, system logs and About pages, as well as the trip summary audit log viewer;
- Improved the trip summary and rider's log editors with updated layouts, action button icons and better modal sizing;
- Added front-end viewer credits, with the `abp01_show_frontend_viewer_credits` filter to control their visibility;
- Added an Nginx Access Directives Helper to the maintenance tools, generating access rules for protecting the plugin's storage directory;
- Updated Romanian, French and German translations, and added German translations for predefined lookup values;
- Fixed translation loading errors caused by initializing translated UI labels too early;
- Fixed GPX imports incorrectly treating valid track segments as empty;
- Added WGS84 coordinate validation for imported GPX, GeoJSON and KML tracks, rejecting files containing invalid coordinates;
- Fixed stale track data being reused from the cache when uploading a replacement track, including after a previously failed upload;
- Fixed the rider's log viewer ignoring the option to hide entry metadata;
- Added password protection checks before rendering the trip summary automatically or through its shortcode, and before serving track data through AJAX;
- Hardened map attribution rendering against HTML injection and restricted attribution URLs in settings to HTTP and HTTPS;
- Hardened the Leaflet script wrapper's path validation, restricting served JavaScript to the intended directory and checking symbolic links and Windows junctions;
- Extended generated storage access rules to protect temporary `.dat` upload files;
- Corrected installation and uninstallation hook execution and error handling, and added the dedicated `abp01_uninstall_table_names` filter for custom table cleanup;
- Fixed the legacy 0.3.0 upgrade step not reporting successful completion;
- Updated Chart.js to 2.9.4 and Monolog to 3.10.0;
- Extracted the KML parser into the Composer-managed `myclar/kamel-php` library;
- Began migrating internal classes to the `WpTripSummary` namespace, with autoloader support, legacy aliases and additional PHP type declarations;
- Migrated several admin scripts and shared UI components to TypeScript;
- Expanded action/filter documentation and automated regression coverage, added a custom viewer tab example, and improved contributor and README generation tooling;
- Added repository skills for coding conventions and license headers, hook documentation, namespace migrations and conservative typing, and product marketing, with supporting references and utilities (see [Repository skills](CONTRIBUTING.md#repository-skills));
- Removed the built-in Help page and its bundled resources;
- Removed bundled styles targeting individual legacy WordPress themes.

[See the full changelog](https://github.com/alexboia/WP-Trip-Summary/blob/master/CHANGELOG.md).
