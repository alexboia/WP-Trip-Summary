# E03 — Custom frontend viewer tabs

This standalone example plugin adds five tabs to the WP Trip Summary frontend
viewer. Each tab has a Dashicons icon, a heading, a paragraph and a local SVG
illustration. No external image service or additional JavaScript is needed.

## Installation

1. Copy this entire directory (including `assets`) to
   `wp-content/plugins/e03-custom-frontend-viewer-tabs`.
2. Activate WP Trip Summary and **WP Trip Summary - E03 - Custom frontend viewer
   tabs** in WordPress. The nested example is not discovered automatically while
   it remains inside the main plugin's `examples` directory.
3. Open a post with saved trip information or a GPS track. WP Trip Summary needs
   one of those to render its viewer; this example does not create trip data.

Copying the directory also keeps its SVG assets outside the main plugin's
`.htaccess` rules, which currently deny direct access to SVG files. Loading the
nested example with `require` alone can therefore leave its images blocked.

## Select how many tabs to show

Set the constant in `wp-config.php`, before WordPress is loaded:

```php
define('WPTS_E03_TAB_COUNT', -1);
```

Alternatively, edit its default near the top of `plugin.php`.

| Value | Result |
| --- | --- |
| `-1` (default) | All five example tabs |
| `0` | No example tabs |
| `1`–`5` | The first N entries, in data source order |
| Greater than `5` | All five available entries |

Use an integer. Negative values other than `-1` disable the example tabs.
This limit never removes the built-in tabs or tabs registered by other plugins.

## How it works

- `wpts_e03_get_tabs_data_source()` provides an ordered associative array. Its
  keys are unique tab IDs, `label` is the tab caption, and `icon` is a Dashicons
  class. The other fields supply the example's text and image content.
- `wpts_e03_get_selected_tabs()` applies the constant while preserving those IDs.
- `abp01_additional_frontend_viewer_tabs` appends the selected entries, preserving
  existing registrations. Its `$postId` is `null` when settings request all
  available choices. This example exposes the same selection there and on every
  post, so a custom tab can also be selected as the initial viewer tab.
- `abp01_additional_frontend_viewer_tab_content` renders only this example's IDs
  and escapes the text, image URL and alternative text. The supplied
  `$viewerData->postId` can be used when adapting it to post-specific content.
- `wp_enqueue_scripts` loads Dashicons for logged-out visitors and a small
  stylesheet for the example content. The viewer owns tab navigation and layout.

## Manual checks

Try `-1`, `0`, `2` and `10`, refreshing the page after each change. With `-1`,
click all five tabs and confirm their icons, text and images. Check a narrow
viewport too, to observe how the current viewer handles a crowded tab bar.
The existing information, map and log tabs should remain available whenever
the main plugin normally provides them. In WP Trip Summary settings, check that
the initial-tab choices match the enabled example tabs. If a page cache is
enabled, clear it after changing the constant.

With all five example tabs plus information, map and log, the viewer keeps a
single horizontally scrollable row. Arrows appear only when the tabs do not fit
and are disabled at the corresponding ends. Check scrolling in both directions,
touch/trackpad scrolling and keyboard focus, then resize the viewer: the active
tab should remain visible and the arrows should disappear when all tabs fit.
