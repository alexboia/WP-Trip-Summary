# E04 — Overriding the viewer subtitle

This standalone example plugin uses `abp01_show_frontend_viewer_subtitle` to
display the current post's title below the WP Trip Summary viewer heading.
For a post titled **A day in the mountains**, the subtitle is
**Exploring: A day in the mountains**.

## Installation

1. Copy this directory to
   `wp-content/plugins/e04-overriding-viewer-subtitle`.
2. Activate WP Trip Summary **0.3.3 or newer** and **WP Trip Summary - E04 -
   Overriding viewer subtitle** in WordPress. The nested example is not discovered
   automatically inside the main plugin's `examples` directory.
3. Open a post with saved trip information or a GPS track. The main plugin needs
   one of those to render its viewer; this example does not create trip data.

## How it works

The registration uses priority `10` and accepts **two arguments**:

```php
add_filter('abp01_show_frontend_viewer_subtitle',
	'wpts_e04_override_viewer_subtitle',
	10,
	2);
```

- `$subtitle` is the value returned by any earlier callbacks. Its initial value
  is `null`, which hides the subtitle.
- `$data` is the current viewer's data object. The example uses `$data->postId`
  with `get_the_title()` so it targets the displayed trip's post, independently
  of the global WordPress Loop.
- When a post title is available, the callback replaces the incoming subtitle.
  Otherwise, it preserves that value. Later callbacks can override the result.
- The callback returns plain text. The viewer applies `esc_html()` when rendering
  it; do not echo markup or pre-escape the returned text.

## Customize the example

Edit `wpts_e04_override_viewer_subtitle()` in `plugin.php`. To use a fixed subtitle,
replace its body with:

```php
return __('Notes from the trail', 'abp01-trip-summary');
```

To hide the subtitle, return `null`. The current default viewer also omits empty
strings and the string `'0'`; use a non-empty descriptive string to show text.
For post-specific rules, branch on `$data->postId` inside the callback.

## Manual checks

Open two posts with different titles and trip data, and confirm that each viewer
shows the matching subtitle. Try a title containing `&` or angle brackets to
check that the subtitle remains text. Return `null` and confirm that the subtitle
element disappears; then restore the example callback. Clear any full-page cache
after changing or deactivating the example.
