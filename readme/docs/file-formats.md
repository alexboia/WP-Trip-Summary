# File formats accepted for import
<a name="wpts-features-file-format"></a>  

WP-Trip-Summary supports the following file formats when importing GPS track data that should be attached to a post:
- GPX ([see more details here](https://en.wikipedia.org/wiki/GPS_Exchange_Format));
- GeoJSON ([see more details here](https://en.wikipedia.org/wiki/GeoJSON));
- KML ([see more details here](https://developers.google.com/kml/documentation/kmlreference)).

## GPX

An uploaded file is processed as a GPX file (and validated as such) if it has any of the following mime types:

- `application/gpx`;
- `application/x-gpx+xml`;
- `application/xml-gpx`;
- `application/xml`;
- `application/gpx+xml`;
- `text/xml`.

GPX documents are expected to comply with the [GPX 1.1 schema](https://www.topografix.com/gpx/1/1/) and are parsed as follows:

- Only document name, description and keywords metadata elements are read;
- The following information is read for a point (`<wpt>` or `<trkpt>`), besides latitude and longitude:
   - altitude/elevation (`<ele>` element);
   - name (`<name>` element).
- The following information is read for a track part (`<trk>` element), besides the list of track segments;
   - name (`<name>` element).
- For a track segment (`<trkseg>` elements), only its points are read.

For the more technically inclined, [the parser can be consulted here](https://github.com/alexboia/WP-Trip-Summary/blob/master/lib/route/track/documentParser/Gpx.php).

## GeoJSON

An uploaded file is processed as a GeoJSON file (and validated as such) if it has any of the following mime types:

- `application/json`;
- `application/geo+json`;
- `application/vnd.geo+json`. 

GeoJson documents are assumed to comply with [RFC 7946](https://tools.ietf.org/html/rfc7946) and are parsed as follows:
- Document metadata is only searched for if the root object is a `FeatureCollection` and the first `Feature` of that collection:
   - has a `properties` property;
   - its `geometry` property is `null`.
- A `LineString` is read as a track part with a single track segment;
- If a `LineString` geometry is contained within a `Feature` object, then the resulting track part's name is searched for in the feature object's `properties` property.

- A `MultiLineString` is read as a track part, and each comprising line string is added to the track part as a track segment;
- If a `MultiLineString` geometry is contained within a `Feature` object, then the resulting track part's name is searched for in the feature object's `properties` property.

- A `Point` is read as a document-level waypoint, regardless of where it is found in the geoJSON file;
- If a `Point` geometry is contained within a `Feature` object, then the waypoint's name is searched for in the feature object's `properties` property.

- All the points in a `MultiPoint` are each added as a document-level waypoint, regardless of where it is found in the geoJSON file;
- If a `MultiPoint` geometry is contained within a `Feature` object, then a name is searched for in the feature object's `properties` property and assigned to each of the resulting waypoints;

- A `Polygon` is read as a track part with a track segment for each of the polygon's contour lines;
- If a `Polygon` geometry is contained within a `Feature` object, then the resulting track part's name is searched for in the feature object's `properties` property.

- A `MultiPolygon` is read as multiple track parts, one for each comprising polygon; each track part is then comprised of the corresponding polygon's contour lines;
- If a `MultiPolygon` geometry is contained within a `Feature` object, then a name is searched for in the feature object's `properties` property and assigned to each of the resulting track parts.

For the more technically inclined, [the parser can be consulted here](https://github.com/alexboia/WP-Trip-Summary/blob/master/lib/route/track/documentParser/GeoJson.php).

## KML

An uploaded file is processed as a KML file (and validated as such) if it has any of the following mime types:

- `application/vnd.google-earth.kml+xml`.

The KML parser is based on [Stepan Dalecky's KML parser on GitLab](https://gitlab.com/stepandalecky/kml-parser), which I further built upon in two areas:

- support for additional KML objects;
- a basic processing infrastructure.

It might be worth your while to [take a look at it here](https://github.com/alexboia/WP-Trip-Summary/tree/master/lib/3rdParty/kml-parser/KmlParser).

KML documents are parsed as follows:

- Either root KML folder or root KML document is considered, not both (first it checks for a root folder and, if not found for a root document);
- A KML container is searched, in this order, for: folders, documents and placemarks;
- For a placemark, `Point`, `LineString`, `LinearRing`, `Polygon` and `MultiGeometry` geometries are supported;
- Neither folder, nor document metadata is stored;

- A `Point` geometry is read as a document-level waypoint, regardless of where it is found in the KML file;
- For a `Point` geometry, the name and description metadata are stored;

- A `LineString` is read as a track part with a single track segment;
- For a `LineString` geometry, only the name metadata is stored;

- A `LinearRing` is read as a track part with a single track segment;
- For a `LinearRing` geometry, only the name metadata is stored;

- A `Polygon` is read as two track parts: one for the outer boundary `LinearRing`, the other for the inner boundary `LinearRing`;
- For a `Polygon` geometry, only the name metadata is stored, for each of the resulting track parts;

- A `MultiGeometry` is processed by reading its individual parts, not as a whole, obeying the above-mentioned rules.

For the more technically inclined, [the parser can be consulted here](https://github.com/alexboia/WP-Trip-Summary/blob/master/lib/route/track/documentParser/Kml.php).
