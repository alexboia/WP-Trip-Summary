<?php
class GpxDocumentFakerDataProvider extends \Faker\Provider\Base {
    private $_acceptableError = 0.1;

    public function __construct($generator, $acceptableError = 0.1) {
        parent::__construct($generator);
        $this->_acceptableError = 0.1;
    }

    public function gpx($opts = array()) {
        $defaults = $this->_getDefaults();

        if (isset($opts['metadata']) && $opts['metadata'] === false) {
            $opts['metadata'] = array(
                'name' => false,
                'desc' => false,
                'keywords' => false,
                'author' => false,
                'copyright' => false,
                'link' => false,
                'time' => false,
                'bounds' => false
            );
        }

        foreach ($defaults as $key => $value) {
            if (isset($opts[$key])) {
                if (is_array($defaults[$key])) {
                    $opts[$key] = array_merge($defaults[$key], $opts[$key]);
                }
            } else {
                $opts[$key] = $value;
            }
        }

        $documentInfo = $this->_generateDocument($opts);
        $renderedDocument = $this->_renderDocument($documentInfo, $opts);

        $retVal = array(
            'content' => &$renderedDocument,
            'data' => null
        );

        if ($opts['addData']) {
            $retVal['data'] = &$documentInfo;
        }

        return $retVal;
    }

    public function randomizedGpx($override = array()) {
        $opts = array_merge($this->_getRandomizedDefaults(), $override);
        return $this->gpx($opts);
    }

    private function _renderDocument(&$documentInfo, $options) {
        $content = '';
        $content .= 
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<gpx version="1.1" creator="' . __CLASS__ . '" ' .
                'xmlns="http://www.topografix.com/GPX/1/1" ' .
                'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
                'xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">';

        $content .= $this->_renderMetadata($documentInfo['metadata'], $options['precision']);
        $content .= $this->_renderDocumentContent($documentInfo['content'], $options['precision']);

        $content .=  '</gpx>';

        $contentNoPretty = null;
        if ($options['prettify']) {
            if (isset($options['addNoPretty']) && $options['addNoPretty'])  {
                $contentNoPretty = $content;
            }

            $doc = new DomDocument('1.0');
            $doc->loadXML($content);
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;

            $content = $doc->saveXML();
            $doc = null;
        }

        return array(
            'text' => &$content,
            'textNoPretty' => &$contentNoPretty
        );
    }

    private function _renderMetadata(&$metadataInfo, $precision) {
        $content = '';
        $content .= '<metadata>';

        if (!empty($metadataInfo['name'])) {
            $content .= $this->_renderStringElement('name', $metadataInfo['name']);
        }
        if (!empty($metadataInfo['desc'])) {
            $content .= $this->_renderStringElement('desc', $metadataInfo['desc']);
        }

        if (!empty($metadataInfo['author'])) {
            $content .= (
                '<author>' . 
                    $this->_renderStringElement('name', $metadataInfo['author']['name']) . 
                    $this->_renderEmailElement('email', $metadataInfo['author']['email']) . 
                    $this->_renderLinkElement('link', $metadataInfo['author']['link']) . 
                '</author>'
            );
        }

        if (!empty($metadataInfo['copyright'])) {
            $content .= $this->_renderCopyrightElement('copyright', $metadataInfo['copyright']);
        }

        if (!empty($metadataInfo['link'])) {
            $content .= $this->_renderLinkElement('link', $metadataInfo['link']);
        }

        if (!empty($metadataInfo['time'])) {
            $content .= $this->_renderStringElement('time', $metadataInfo['time']);
        }

        if (!empty($metadataInfo['keywords'])) {
            $content .= $this->_renderStringElement('keywords', $metadataInfo['keywords']);
        }

        $content .= '</metadata>';
        return $content;
    }

    private function _renderDocumentContent(&$contentInfo, $precision) {
        $content = '';

        $content .= $this->_renderWaypoints($contentInfo['waypoints'], $precision);
        foreach ($contentInfo['tracks'] as $trackInfo) {
            $content .= $this->_renderTrack($trackInfo, $precision);
        }

        return $content;
    }

    private function _renderWaypoints(&$waypointInfo, $precision) {
        $content = '';

        foreach ($waypointInfo['waypoints'] as $wptInfo) {
            $content .= $this->_renderPoint('wpt', $wptInfo, $precision);
        }

        return $content;
    }

    private function _renderTrack(&$trackInfo, $precision) {
        $content = '';
        $content .= '<trk>';

        if (!empty($trackInfo['name'])) {
            $content .= $this->_renderStringElement('name', $trackInfo['name']);
        }

        foreach ($trackInfo['segments'] as $segmentInfo) {
            $content .= $this->_renderSegment($segmentInfo, $precision);
        }

        $content .= '</trk>';
        return $content;
    }

    private function _renderSegment(&$segmentInfo, $precision) {
        $content = '';
        $content .= '<trkseg>';

        foreach ($segmentInfo['points'] as $pointInfo) {
            $content .= $this->_renderPoint('trkpt', $pointInfo, $precision);
        }

        $content .= '</trkseg>';
        return $content;
    }

    private function _renderPoint($tag, &$pointInfo, $precision) {
        $content = '';
        $content .= ('<' . $tag . ' lat="' . number_format($pointInfo['lat'], $precision, '.', '') . '" lon="' . number_format($pointInfo['lon'], $precision, '.', '') . '">');
        if (!empty($pointInfo['ele'])) {
            $content .= ('<ele>' . number_format($pointInfo['ele'], $precision, '.', '')  . '</ele>');
        }
        if (!empty($pointInfo['name'])) {
            $content .= $this->_renderStringElement('name', $pointInfo['name']);
        }
        if (!empty($pointInfo['desc'])) {
            $content .= $this->_renderStringElement('desc', $pointInfo['desc']);
        }

        $content .= '</' . $tag . '>';
        return $content;
    }

    private function _renderEmailElement($tag, $email) {
        $parts = explode('@', $email, 2);
        return '<' . $tag . ' id="' . $parts[0] . '" domain="' . $parts[1] . '" />';
    }

    private function _renderLinkElement($tag, $linkInfo) {
        return (
            '<' . $tag . '>' . 
                $this->_renderStringElement('text', $linkInfo['text']) . 
                $this->_renderStringElement('type', $linkInfo['type']) . 
            '</' . $tag . '>'
        );
    }

    private function _renderStringElement($tag, $str) {
        return ('<' . $tag . '><![CDATA[' . $str . ']]></' . $tag . '>');
    }

    private function _renderCopyrightElement($tag, $copyright) {
        return (
            '<' . $tag .' author="' . $copyright['author'] . '">' .
                '<year>' . $copyright['year'] . '</year>' .
                '<license>' . $copyright['license'] . '</license>' .
            '</' . $tag . '>'
        );
    }

    private function _generateDocument($options) {
        $from = $this->_generateStartLatLng($options['area'], 
            $options['span'], 
            $options['elevation']['generate'] 
                ? $options['elevation']['base'] 
                : 0,
            $options['precision']);

        $metaArgs = $options['metadata'];
        $metadata = $this->_generateMetaData($metaArgs);

        $documentArgs = array(
            'from' => $from,
            'spanLat' => $options['span'],
            'spanLng' => $options['span'],
            'spanAlt' => $options['elevation']['generate'] 
                ? $options['elevation']['span'] 
                : 0,
            'countTracks' => $options['tracks']['count'],
            'withTrackName' => $options['tracks']['name'],

            'countSegments' => $options['segments']['count'],

            'countPoints' => $options['points']['count'],
            'withPointName' => $options['points']['name'],
            'withPointDesc' => $options['points']['desc'],

            'countWaypointsRatio' => $options['waypoints'] 
                ? 0.1 
                : 0,

            'precision' => $options['precision']
        );

        $content = $this->_generateDocumentContent($documentArgs);

        return array(
            'metadata' => $metadata,
            'content' => $content
        );
    }

    private function _generateMetaData($args) {
        return array(
            'name' => $args['name'] 
                ? $this->generator->numerify('GPX Document ####') 
                : null,
            'desc' => $args['desc'] 
                ? $this->generator->sentence($this->generator->numberBetween(3, 10)) 
                : null,
            'keywords' => $args['keywords'] 
                ? join(',', $this->generator->words($this->generator->numberBetween(1, 10))) 
                : null,
            'author' => $args['author']
                ? array(
                    'name' => $this->generator->name,
                    'email' => $this->generator->email,
                    'link' => array(
                        'text' => $this->generator->url,
                        'type' => 'text/html'
                    )
                )
                : null,
            'copyright' => $args['copyright']
                ? array(
                    'author' => $this->generator->company,
                    'year' => $this->generator->numberBetween(1990, 2100),
                    'license' => $this->generator->url
                )
                : null,
            'link' => $args['link']
                ? array(
                    'text' => $this->generator->url,
                    'type' => 'text/html'
                )
                : null,
            'time' => $args['time']
                ? $this->generator->dateTime->format('Ymd\THis\Z')
                : null,
            'bounds' => null
        );
    }

    private function _generateDocumentContent($args) {
        $tracks = array();
        $from = $args['from']; 
        $withTrackName = $args['withTrackName'];
        $withPointName = $args['withPointName'];
        $withPointDesc = $args['withPointDesc'];
        $spanLat = $args['spanLat']; 
        $spanLng = $args['spanLng']; 
        $spanAlt = $args['spanAlt']; 
        $countTracks = $args['countTracks']; 
        $countPoints = $args['countPoints'];
        $countSegments = $args['countSegments'];
        $countWaypointsRatio = $args['countWaypointsRatio'];
        $precision = $args['precision'];

        $deltaGenerationArgs = $this->_getDeltaLatLngAltGenerationArgs($spanLat, 
            $spanLng, 
            $spanAlt, 
            $countTracks, 
            $precision);

        $trackSpanLat = $deltaGenerationArgs['unscaledDeltaLat'];
        $trackSpanLng = $deltaGenerationArgs['unscaledDeltaLng'];
        $trackSpanAlt = $deltaGenerationArgs['unscaledDeltaAlt'];

        $currentTo = $from;

        for ($i = 0; $i < $countTracks; $i ++) {
            $newDelta = $this->_generateDeltaLatLngAlt($deltaGenerationArgs);
            $newTrackArgs = array(
                'from' => $currentTo,
                'withTrackName' => $withTrackName,
                'withPointName' => $withPointName,
                'withPointDesc' => $withPointDesc,
                'spanLat' => round($trackSpanLat + $newDelta['newDeltaLat'], $precision),
                'spanLng' => round($trackSpanLng + $newDelta['newDeltaLng'], $precision),
                'spanAlt' => $trackSpanAlt !== 0 
                    ? round($trackSpanAlt + $newDelta['newDeltaAlt'], $precision) 
                    : 0,
                'precision' => $precision,
                'countPoints' => $countPoints,
                'countSegments' => $countSegments
            );

            $newTrack = $this->_generateTrack($newTrackArgs);
            $currentTo = $newTrack['to'];
            $tracks[] = $newTrack;
        }

        $waypoints = $this->_pickRandomWaypoints(array(
            'tracks' => $tracks,
            'countWaypointsRatio' => $countWaypointsRatio,
            'withPointName' => $withPointName,
            'withPointDesc' => $withPointDesc
        ));

        return array(
            'waypoints' => $waypoints,
            'tracks' => $tracks,
            'from' => $from,
            'to' => $currentTo
        );
    }

    private function _pickRandomWaypoints($args) {
        $waypoints = array();
        $tracks = $args['tracks'];
        $countWaypointsRatio = $args['countWaypointsRatio'];
        $withPointName = $args['withPointName'];
        $withPointDesc = $args['withPointDesc'];

        $nameGenerator = $withPointName 
            ? function() { return $this->generator->numerify('Waypoint ######'); }
            : function() { return null; };

        $descGenerator = $withPointDesc
            ? function() { return $this->generator->sentence(3); }
            : function() { return null; };

        foreach ($tracks as $track) {
            foreach ($track['segments'] as $segment) {
                $countPoints = count($segment['points']);
                $intervalSize = floor($countPoints * $countWaypointsRatio);

                $interval = array(0, $intervalSize);
                while ($interval[0] < $interval[1] &&  $interval[1] <= $countPoints - 1) {
                    $selectedIndex = $this->generator->numberBetween($interval[0], $interval[1]);

                    $waypoint = $segment['points'][$selectedIndex];
                    $waypoints[] = array_merge($waypoint, array(
                        'name' => $nameGenerator(),
                        'desc' => $descGenerator()
                    ));

                    $interval[0] = min($interval[1] + 1, $countPoints - 1);
                    $interval[1] = min($interval[1] + $intervalSize, $countPoints - 1);
                }
            }
        }

        return array(
            'from' => !empty($waypoints) 
                ? $waypoints[0] 
                : null,
            'to' => !empty($waypoints) 
                ? $waypoints[count($waypoints) - 1] 
                : null,
            'waypoints' => $waypoints
        );
    }

    private function _generateTrack($args) {
        $segments = array();
        $from = $args['from']; 
        $withTrackName = $args['withTrackName'];
        $withPointName = $args['withPointName'];
        $withPointDesc = $args['withPointDesc'];
        $spanLat = $args['spanLat']; 
        $spanLng = $args['spanLng']; 
        $spanAlt = $args['spanAlt']; 
        $countSegments = $args['countSegments']; 
        $countPoints = $args['countPoints'];
        $precision = $args['precision'];

        $deltaGenerationArgs = $this->_getDeltaLatLngAltGenerationArgs($spanLat, 
            $spanLng, 
            $spanAlt, 
            $countSegments, 
            $precision);

        $segmentSpanLat = $deltaGenerationArgs['unscaledDeltaLat'];
        $segmentSpanLng = $deltaGenerationArgs['unscaledDeltaLng'];
        $segmentSpanAlt = $deltaGenerationArgs['unscaledDeltaAlt'];

        $currentTo = $from;
        $name = $withTrackName 
            ? $this->generator->numerify('GPX Track ####')
            : 0;

        for ($i = 0; $i < $countSegments; $i ++) {
            $newDelta = $this->_generateDeltaLatLngAlt($deltaGenerationArgs);
            $newSegmentArgs = array(
                'from' => $currentTo,
                'withPointName' => $withPointName,
                'withPointDesc' => $withPointDesc,
                'spanLat' => round($segmentSpanLat + $newDelta['newDeltaLat'], $precision),
                'spanLng' => round($segmentSpanLng + $newDelta['newDeltaLng'], $precision),
                'spanAlt' => $segmentSpanAlt !== 0 
                    ? round($segmentSpanAlt + $newDelta['newDeltaAlt'], $precision) 
                    : 0,
                'precision' => $precision,
                'countPoints' => $countPoints
            );

            $newSegment = $this->_generateSegment($newSegmentArgs);
            $currentTo = $newSegment['to'];
            $segments[] = $newSegment;
        }

        return array(
            'name' => $name,
            'segments' => $segments,
            'from' => $from,
            'to' => $currentTo
        );
    }

    private function _generateSegment($args) {
        $points = array();
        $from = $args['from']; 
        $withPointName = $args['withPointName'];
        $withPointDesc = $args['withPointDesc'];
        $spanLat = $args['spanLat']; 
        $spanLng = $args['spanLng']; 
        $spanAlt = $args['spanAlt']; 
        $countPoints = $args['countPoints']; 
        $precision = $args['precision'];

        $deltaGenerationArgs = $this->_getDeltaLatLngAltGenerationArgs($spanLat, 
            $spanLng, 
            $spanAlt, 
            $countPoints, 
            $precision);

        $pointSpanLat = $deltaGenerationArgs['unscaledDeltaLat'];
        $pointSpanLng = $deltaGenerationArgs['unscaledDeltaLng'];
        $pointSpanAlt = $deltaGenerationArgs['unscaledDeltaAlt'];

        $nameGenerator = $withPointName 
            ? function() { return $this->generator->numerify('Point ######'); }
            : function() { return null; };

        $descGenerator = $withPointDesc
            ? function() { return $this->generator->sentence(3); }
            : function() { return null; };

        $currentTo = array_merge($from, array(
            'name' => $nameGenerator(),
            'desc' => $descGenerator()
        ));

        $points[] = $currentTo;
        
        for ($i = 0; $i < $countPoints - 1; $i ++) {
            $newDelta = $this->_generateDeltaLatLngAlt($deltaGenerationArgs, true);
            $currentTo = array(
                'lat' => round($currentTo['lat'] + $pointSpanLat + $newDelta['newDeltaLat'], $precision),
                'lon' => round($currentTo['lon'] + $pointSpanLng + $newDelta['newDeltaLng'], $precision),
                'ele' => $pointSpanAlt !== 0 && $newDelta['newDeltaAlt'] !== 0 
                    ? round($currentTo['ele'] + $pointSpanAlt + $newDelta['newDeltaAlt'], $precision)
                    : 0,
                'name' => $nameGenerator(),
                'desc' => $descGenerator()
            );

            $points[] = $currentTo;
        }

        return array(
            'points' => $points,
            'from' => $from,
            'to' => $currentTo
        );
    }

    private function _generateDeltaLatLngAlt($args, $allowNegative = false) {
        extract($args);
        $newDeltaLat = round($this->generator->numberBetween($minDeltaLat, $maxDeltaLat) / $factor, 
            $precision);

        $newDeltaLng = round($this->generator->numberBetween($minDeltaLng, $maxDeltaLng) / $factor, 
            $precision);

        $newDeltaAlt = $minDeltaAlt !== 0  && $maxDeltaAlt !== 0
            ? round($this->generator->numberBetween($minDeltaAlt, $maxDeltaAlt) / $factor, 
                $precision)
            : 0;

        $signumLat = $allowNegative && $this->generator->boolean() ? -1 : 1;
        $signumLng = $allowNegative && $this->generator->boolean() ? -1 : 1;
        $signumAlt = $allowNegative && $this->generator->boolean() ? -1 : 1;

        return array(
            'newDeltaLat' => $signumLat * $newDeltaLat,
            'newDeltaLng' => $signumLng * $newDeltaLng,
            'newDeltaAlt' => $signumAlt * $newDeltaAlt
        );
    }

    private function _getDeltaLatLngAltGenerationArgs($spanLat, $spanLng, $spanAlt, $count, $precision) {
        $factor = pow(10, $precision);
        $min = round(1 / $factor, $precision);

        $unscaledDeltaLat = max($min, round(($spanLat / $count), $precision));
        $unscaledDeltaLng = max($min, round(($spanLng / $count), $precision));
        $unscaledDeltaAlt = $spanAlt !== 0 
            ? max($min, round(($spanAlt / $count), $precision))
            : 0;

        $maxDeltaLat = round($unscaledDeltaLat * $factor);
        $maxDeltaLng = round($unscaledDeltaLng * $factor);
        $maxDeltaAlt = $unscaledDeltaAlt !== 0 
            ? round($unscaledDeltaAlt * $factor)
            : 0;

        return array(
            'factor' => $factor,
            'minDeltaValue' => $min,
            'precision' => $precision,
            
            'maxDeltaLat' => $maxDeltaLat,
            'minDeltaLat' => round($maxDeltaLat * (1 - $this->_acceptableError), $precision),
            'unscaledDeltaLat' => $unscaledDeltaLat,
            
            'maxDeltaLng' => $maxDeltaLng,
            'minDeltaLng' => round($maxDeltaLng * (1 - $this->_acceptableError), $precision),
            'unscaledDeltaLng' => $unscaledDeltaLng,
            
            'maxDeltaAlt' => $maxDeltaAlt,
            'minDeltaAlt' => $maxDeltaAlt !== 0
                ? round($maxDeltaAlt * (1 - $this->_acceptableError), $precision)
                : 0,
            'unscaledDeltaAlt' => $unscaledDeltaAlt
        );
    }

    private function _generateStartLatLng($area, $span, $alt, $precision) {
        $areaBounds = $this->_getAreaBounds($area, $span);
        return array(
            'lat' => $this->generator->randomFloat($precision, 
                $areaBounds['minLat'], 
                $areaBounds['maxLat']),
            'lon' => $this->generator->randomFloat($precision, 
                $areaBounds['minLng'], 
                $areaBounds['maxLng']),
            'ele' => $alt,
            'name' => null,
            'desc' => null
        );
    }

    private function _getAreaBounds($area, $span = 0) {
        $allBounds = $this->_getDefinedBounds();
        $areaBounds = $allBounds[$area];

        $span = $span * $this->_acceptableError;

        if (is_numeric($span)) {
            $areaBounds = array(
                'minLat' => $areaBounds['minLat'] + $span, 
                'maxLat' => $areaBounds['maxLat'] - $span,
                'minLng' => $areaBounds['minLng'] + $span, 
                'maxLng' => $areaBounds['maxLng'] - $span
            );
        }

        return $areaBounds;
    }

    private function _getRandomizedDefaults() {
        $supportedAreas = $this->_getSupportedAreas();
        $precision = $this->generator->numberBetween(3, 6);
        $generateElevation = $this->generator->boolean();

        return array(
            'span' => $this->generator->randomFloat($precision, 0.2, 1.5),
            'precision'=> $precision,
            'prettify' => true,
            'addNoPretty' => false,
            'addData' => true,
            
            'tracks' => array(
                'count' => $this->generator->numberBetween(1, 10),
                'name' => $this->generator->boolean(),
            ),

            'segments' => array(
                'count' => $this->generator->numberBetween(1, 10)
            ),

            'points' => array(
                'count' => $this->generator->numberBetween(500, 10000),
                'name' => $this->generator->boolean(),
                'desc' => $this->generator->boolean()
            ),

            'metadata' => array(
                'name' => $this->generator->boolean(),
                'desc' => $this->generator->boolean(),
                'keywords' => $this->generator->boolean(),
                'author' => $this->generator->boolean(),
                'copyright' => $this->generator->boolean(),
                'link' => $this->generator->boolean(),
                'time' => $this->generator->boolean(),
                'bounds' => $this->generator->boolean()
            ),
            'waypoints' => $this->generator->boolean(),
            
            'elevation' => array(
                'generate' => $generateElevation,
                'base' => $generateElevation
                    ? $this->generator->numberBetween(50, 1500)
                    : 0,
                'span' => $generateElevation 
                    ? $this->generator->numberBetween(50, 5000) 
                    : 0
            ),

            'area' => $this->generator->randomElement($supportedAreas)
        );
    }

    private function _getDefaults() {
        return array(
            'span' => 1,
            'precision'=> 4,
            'prettify' => true,

            'addNoPretty' => false,
            'addData' => true,
            
            'tracks' => array(
                'count' => 1,
                'name' => true,
            ),

            'segments' => array(
                'count' => 3
            ),

            'points' => array(
                'count' => 100,
                'name' => true,
                'desc' => true
            ),

            'metadata' => array(
                'name' => true,
                'desc' => true,
                'keywords' => true,
                'author' => true,
                'copyright' => true,
                'link' => true,
                'time' => true,
                'bounds' => true
            ),
            'waypoints' => true,
            
            'elevation' => array(
                'generate' => true,
                'base' => 150,
                'span' => 1500
            ),

            'area' => 'north-east'
        );
    }

    private function _getDefinedBounds() {
        return array(
            'north-east' => array(
                'minLat' => 0, 
                'maxLat' => 85,
                'minLng' => 0, 
                'maxLng' => 180
            ),
            'north-west' => array(
                'minLat' => 0, 
                'maxLat' => 85,
                'minLng' => -180, 
                'maxLng' => 0
            ),
            'south-east' => array(
                'minLat' => -85, 
                'maxLat' => 0,
                'minLng' => 0, 
                'maxLng' => 180
            ),
            'south-west' => array(
                'minLat' => -85, 
                'maxLat' => 0,
                'minLng' => -180, 
                'maxLng' => 0
            )
        );
    }

    private function _getSupportedLicenseIds() {
        return array(
            "0BSD",
            "AAL",
            "ADSL",
            "AFL-1.1",
            "AFL-1.2",
            "AFL-2.0",
            "AFL-2.1",
            "AFL-3.0",
            "AGPL-1.0-only",
            "AGPL-1.0-or-later",
            "AGPL-3.0-only",
            "AGPL-3.0-or-later",
            "AMDPLPA",
            "AML"
        );
    }

    private function _getSupportedAreas() {
        return array_keys($this->_getDefinedBounds());
    }
}