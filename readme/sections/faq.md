## FAQ
<a name="wpts-faq"></a>

**Does it work with the block editor (Gutenberg)?**

Yes. The plugin works with both the block editor and the classic editor.

**Do I need an account with a map provider?**

No. The map uses OpenStreetMap tiles by default, and your tracks are stored on your own server. You can switch to another tile provider if you want to.

**Which GPS file formats can I upload?**

You can upload GPX 1.1, KML and GeoJSON files. [See the parsing details](readme/docs/file-formats.md).

**Can my readers download the track?**

Yes, if you allow it. Track downloads are optional and can be turned on from the plugin settings.

**Can I show the trip summary somewhere else in the post?**

Yes. Use the `[abp01_trip_summary_viewer]` shortcode, the Gutenberg block or the classic editor button. The shortcode takes no parameters and is supported once per post, in the post for which the trip summary was defined.

**Can I use it with post types other than posts and pages?**

Yes, with a filter hook. [See the example in the wiki](https://github.com/alexboia/WP-Trip-Summary/wiki/Changing-the-editor-availability-per-post-type).

**Can I customize how the trip summary looks?**

Yes. [See how to customize the front-end viewer](https://github.com/alexboia/WP-Trip-Summary/wiki/Customizing-the-front-end-viewer).

**Why doesn't the trip summary appear on archive pages?**

The viewer is shown only on the post's own page. It does not appear on listing pages, such as archives, even when they display the full post content.

**Does it support multisite installations?**

WP Trip Summary is designed and tested for single-site installations. Multisite is not officially supported.

**Is it free?**

Yes. WP Trip Summary is free and open source, under the BSD 3-Clause license.
