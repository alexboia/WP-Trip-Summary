# What it does  
<a name="wpts-what-does"></a>  

This plug-in provides three basic features:

- allow some structured information to be filled in, according to a selected trip type;
- allow some GPS track to be uploaded and then rendered on a map;
- maintain rider's log entries, while optionally specifying some of them as public.

## Structured technical information

Structured technical information is supported for the following types of trips:

- Bike trips;
- Hiking trips;
- Train rides.

### For bike trips

The following fields are available:

- Total distance;
- Total climb;
- Difficulty level;
- Access information (how to get to the start point and return from the end point);
- Open during seasons;
- Path surface type (eg: dirt, asphalt, grass etc.);
- Recommended bike type (eg: MTB, road bike etc.).

### For hiking trips

The following fields are available:

- Total distance;
- Total climb;
- Difficulty level;
- Access information;
- Open during seasons;
- Path surface type;
- Route markers.

### For train rides

The following fields are available:

- Total distance;
- How many trains were exchanged;
- Line gauge (mm);
- Railroad operators used;
- Line status (closed, operational etc.);
- Whether the line is electrified or not;
- Line type.

## The track

I really wanted to host the GPS tracks myself for various reasons:

- Didn't want to depend on any third party provider;
- It was good fun writing this feature;
- I want to use the resulting data in the near future to do some other stuff on my website.

Thus, I developed a module to do just that: upload a GPS track (GPX, GeoJSON and KML files can be uploaded - [see the accepted file formats](file-formats.md)), parse it and display it.

## Rider's log entries

For each post you can add unlimited log entries, each corresponding to someone travelling that route, specifying the following details:

- Who (the rider's name);
- When (date);
- Time (how many hours spent);
- Vehicle used (e.g. bike make and model);
- Gear (notes about what equipment was used - backpack configuration and the like);
- Other (random) notes;
- Whether or not the entry should be displayed publicly (if so, then the entry will be shown in the frontend viewer in a separate tab dedicated to log entries).

## JSON-LD front-end data

The plug-in, as of version `0.2.8` inserts structured JSON-LD data in the post and page details page, 
if there is track data attached to that post or page.

This behaviour is configurable and can be disabled or enabled in the plug-in's configuration page.
By default, it is disabled.

Here is a sample JSON-LD data set inserted by this plug-in:

```javascript
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

The box is described by the south-west and north-east points, in lat-lng format: `Lat1 Lng1 Lat2 Lng2`.

## Maintenance

As of version `0.2.8`, there is a new `Maintenance` section, which allows you to carry out various maintenance tasks:
- Clear all cached track data information;
- Clear all plug-in related information (all post trip summary, all cached data and all stored track files);
- Detect which posts that should have track data information are actually missing track files.

Menu: `Trip Summary` -> `Maintenance`.

## System logs

As of version `0.3.2`, there is a new `System logs` section, which allows you to manage the log files to which WP Trip Summary writes its debug and error messages.

There are two sections for each of the log types (debug and error, respectively, as mentioned), BUT debug logs are only produced if:

- `ABP01_ENABLE_DEBUG_LOGGING` is defined (in `wp-config.php`) and set to `true` (if defined, it will supersede `WP_DEBUG`) or;
- `WP_DEBUG` is defined and set to `true`.

Menu: `Trip Summary` -> `System logs`.
