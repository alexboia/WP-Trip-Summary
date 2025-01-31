<h1 align="center">KML for PHP library (KamelPhp)</h1>

A PHP KML parser library initially developed as part of the [WP-Trip-Summary WordPress plug-in](https://github.com/alexboia/WP-Trip-Summary/). 
It is based on [Stepan Daleky's KML parser on GitLab](https://gitlab.com/stepandalecky/kml-parser) and has now been extracted as a separate library to ease up on the code base a bit.

## About

Supported KML entities:

- `Kml` class (`<kml>` root element);
- `Folder` and `Document` classes (and elements) as `Container` types and direct children of the KML root;
- Abstract `Feature` class and element, with support for the following attributes: `id`, `styleUrl`, `name`, `description`, `open`, `visibility`, `address`, `phoneNumber`;
- `Placemark` class and element, with support for `Point`, `Linestring`, `LinearRing`, `Polygon` and `MultiGeometry` geometries as well as `Style` and `ExtendedData`.

## Installation

Install the latest version with:

```
composer require myclar/kamel-php
```

### Using the parser directly

The parser class simply parses a KML string (or a file that contains a KML string) and returns an object graph:

```PHP
use KamelPhp\KmlParser\Parser;

$kmlParser = Parser::fromString($fileContents);
//OR
$kmlParser = Parser::fromFile($filePath);

//And then get the KML root and do your thing with it.
$kml = $kmlParser->getKml();
```

Some samples:
- The built-in [`Processor`](https://github.com/alexboia/KML-for-PHP/blob/main/src/KmlParser/Processor.php)
- The [current set of tests for the parser class](https://github.com/alexboia/KML-for-PHP/blob/main/tests/LibKmlParserTests.php)

### Using the processor

The [processor class](https://github.com/alexboia/KML-for-PHP/blob/main/src/KmlParser/Processor.php) provides a simple and expedient way of traversing a KML document, while allowing a certain degree of customization. Its usage is not mandatory.

Limitations:

- Either root KML folder or root KML document is considered, not both (first it checks for a root folder and, if not found for a root document);
- A KML container is searched, in this order, for: folders, documents and placemarks;
- Neither folder, nor document metadata is stored;
- For a placemark, only the name and description metadata items are stored and reported;
- Order in which various document parts are processed cannot be altered;
- Visibility, as specified by the `visibility` feature attribute, is not accounted for.

In order to use the processor, you need to provide a mandatory delegate (a class implementing [`KamelPhp\KmlParser\Processor\Delegate`](https://github.com/alexboia/KML-for-PHP/blob/main/src/KmlParser/Processor/Delegate.php)) which you can use to:

- control what gets reported back to you (`Delegate::shouldXYZ()` methods, e.g. return `false` from `Delegate::shouldProcessPointGeometry()` if you do not what to have KML points sent back to you.);
- process KML primitives as they are found and reported back to you (e.g. implement `Delegate::processPoint()` to process KML points);
- react when processing begins (`Delegate::begin()`) and ends (`Delegate::end()`);
- react when an error occurs (`Delegate::error()`).

As it may already be obvious, the way it works sort of breaks the tree structure, but that's perfectly acceptable in my use case - obtain relevant geometries for simple map drawing.

It's up to yo what the delegate does, either it stores the artefacts somewhere or it builds some representation in memory and provides a way to access it at the end. See [here an example implementation](https://github.com/alexboia/WP-Trip-Summary/blob/master/lib/route/track/documentParser/kml/LibKmlProcessorDelegate.php).

```PHP
use KamelPhp\KmlParser\Parser;

$delegate = new MyDelegate();
$processor = new Processor($delegate);
$processor->processKmlString($sourceString);
```