<?php
/**
 * See these resources for some good points about parsing:
 * - http://stackoverflow.com/questions/24500347/gpx-file-parse-with-php-get-extensions-element-values
 * - http://stackoverflow.com/questions/8319556/gpx-parsing-patterns-and-standards
 *
 * See these resources for some info about the GPX format
 * - http://www.rigacci.org/wiki/doku.php/tecnica/gps_cartografia_gis/gpx
 * - http://www.topografix.com/gpx/1/1/#type_wptType
 * - http://wiki.openstreetmap.org/wiki/GPX
 * */

class Abp01_Route_Track_GpxDocumentParser implements Abp01_Route_Track_DocumentParser {
    private $_parseErrors = array();

    function __construct() {
        if (!self::isSupported()) {
            throw new Exception('The GPX parser requirements are not met');
        }
    }

    public static function isSupported() {
        return function_exists('simplexml_load_string') &&
            function_exists('simplexml_load_file');
    }

    public function parse($sourceString) {
        if ($sourceString === null || empty($sourceString)) {
            throw new InvalidArgumentException('Empty GPX string');
        }

        $document = null;
        $this->_parseErrors = array();
        $prevUseErrors = libxml_use_internal_errors(true);

        $gpx = simplexml_load_string($sourceString, 'SimpleXMLElement');
        if (!$gpx) {
            $this->_setLastErrors(libxml_get_errors());
            libxml_clear_errors();
        } else {
            $document = $this->_parseGpx($gpx);
        }

        libxml_use_internal_errors($prevUseErrors);
        return $document instanceof Abp01_Route_Track_Document ? $document : null;
    }

    private function _parseGpx($gpx) {
        $document = null;
        $meta = $this->_readMetaData($gpx);
        $document = new Abp01_Route_Track_Document($meta);

        $this->_readWayPoints($document, $gpx);
        $this->_readTracks($document, $gpx);

        return $document;
    }

    private function _readMetaData($gpx) {
        $node = $gpx->metadata;
        if (!empty($node)) {
            $meta = new stdClass();
            $meta->name = !empty($node->name) ? (string)$node->name : null;
            $meta->desc = !empty($node->desc) ? (string)$node->desc : null;
            $meta->keywords = !empty($node->keywords) ? (string)$node->keywords : null;
            return $meta;
        } else {
            return null;
        }
    }

    private function _readTracks(Abp01_Route_Track_Document $document, $gpx) {
        if (empty($gpx->trk)) {
            return;
        }
        foreach ($gpx->trk as $trkNode) {
            $trk = $this->_readTrack($trkNode);
            if ($trk instanceof Abp01_Route_Track_Part) {
                $document->addTrackPart($trk);
            }
        }
    }

    private function _readTrack($trkNode) {
        $track = new Abp01_Route_Track_Part();
        $name = !empty($trkNode->name) ? (string)$trkNode->name : null;

        if ($name) {
            $track->name = $name;
        }

        if (!empty($trkNode->trkseg)) {
            foreach ($trkNode->trkseg as $trgSegNode) {
                $trkSeg = $this->_readTrackSegment($trgSegNode);
                if ($trkSeg) {
                    $track->addLine($trkSeg);
                }
            }
        }

        return $track;
    }

    private function _readTrackSegment($trkSegNode) {
        $segment = new Abp01_Route_Track_Line();
        if (!empty($trkSegNode->trkpt)) {
            foreach ($trkSegNode->trkpt as $trkptNode) {
                $trkpt = $this->_readWayPoint($trkptNode);
                if ($trkpt) {
                    $segment->addPoint($trkpt);
                }
            }
        }
        return $segment;
    }

    private function _readWayPoints(Abp01_Route_Track_Document $doc, $gpx) {
        if (empty($gpx->wpt)) {
            return;
        }
        foreach ($gpx->wpt as $wptNode) {
            $wpt = $this->_readWayPoint($wptNode);
            if ($wpt instanceof Abp01_Route_Track_Point) {
                $doc->addWayPoint($wpt);
            }
        }
    }

    private function _readWayPoint($wptNode) {
        if (empty($wptNode['lat']) || empty($wptNode['lon'])) {
            return null;
        }

        $lat = floatval((string)$wptNode['lat']);
        $lon = floatval((string)$wptNode['lon']);
        $alt = !empty($wptNode->ele) ? floatval((string)$wptNode->ele) : 0;
        $coordinate = new Abp01_Route_Track_Coordinate($lat, $lon, $alt);
        $wpt = new Abp01_Route_Track_Point($coordinate);

        if (!empty($wptNode->name)) {
            $wpt->name = (string)$wptNode->name;
        }
        if (!empty($wptNode->desc)) {
            $wpt->description = (string)$wptNode->desc;
        }

        return $wpt;
    }

    private function _setLastErrors($errors) {
        $this->_parseErrors = $errors;
    }

    public function hasErrors() {
        return count($this->_parseErrors) > 0;
    }

    public function getLastErrors() {
        return $this->_parseErrors;
    }
}