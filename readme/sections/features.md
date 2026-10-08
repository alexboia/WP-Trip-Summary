## Features
<a name="wpts-features"></a>

### Structured trip information

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

### GPS tracks and maps

- Upload **GPX 1.1**, **KML** or **GeoJSON (RFC 7946)** files. Every coordinate is validated before the track is saved. [See how each format is read](readme/docs/file-formats.md).
- The map is powered by [Leaflet](https://leafletjs.com/) and uses [OpenStreetMap](https://www.openstreetmap.org/) by default. You can choose a predefined tile layer or set up your own.
- Visitors can use full-screen mode, a magnifying glass and the button that re-centers the map on the track.
- An altitude profile chart and a box with the minimum and maximum altitude are available.
- You can let visitors download the track as a GPX file.
- You can change the track's color and line weight.

### Rider's log

Add as many log entries as you need to each route, with the rider, date, time spent, vehicle, gear and notes. Public entries appear in a dedicated tab of the viewer.

### Easy to place, easy to configure

- The trip summary viewer is added automatically to the post, or you can place it yourself with the `[abp01_trip_summary_viewer]` shortcode.
- A **Gutenberg block** and a **classic editor button** insert the shortcode for you.
- A **teaser** at the top of the post can invite readers to jump to the trip summary.
- You can choose the tab that the viewer opens on and the unit system (metric or imperial).

### For site owners

- In the post listing, you can **filter posts by trip type** and see a **trip summary audit log**.
- The **maintenance tools** clear cached track data, find posts with missing track files or remove all plugin data.
- The **system logs** page lets you view and manage the plugin's error and debug logs.

### For developers

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
