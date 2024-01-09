<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class GpxDocumentFakerDataProvider extends \Faker\Provider\Base {
	public function __construct($generator) {
		parent::__construct($generator);
	}

	public function gpx($opts = array()) {
		$documentData = $this->generator->gpsDocumentData($opts);
		return $this->_generateGpx($documentData, 
			$opts);
	}

	private function _generateGpx(array $documentData, array $gpxOpts) {
		$defaults = $this->_getDefaults();
		$renderOpts = $this->_extractRenderOpts($gpxOpts, $defaults);
		$renderedDocument = $this->_renderDocument($documentData, $renderOpts);

		$retVal = array(
			'content' => &$renderedDocument,
			'data' => null
		);

		if ($renderOpts['addData']) {
			$retVal['data'] = &$documentData;
		}

		return $retVal;
	}

	private function _extractRenderOpts($gpxOpts, $defaults) {
		$mergedOpts = array();
		foreach ($defaults as $key => $defaultValue) {
			if (isset($gpxOpts[$key])) {
				$mergedOpts[$key] = $gpxOpts[$key];
			} else {
				$mergedOpts[$key] = $defaultValue;
			}
		}
		return $mergedOpts;
	}

	public function randomizedGpx($overrideOpts = array()) {
		$opts = array_merge($this->_getRandomizedDefaults(), $overrideOpts);
		$documentData = $this->generator->randomizedGpsDocumentData($opts);
		return $this->_generateGpx($documentData, 
			$overrideOpts);
	}

	private function _renderDocument(&$documentData, $renderOpts) {
		$content = '';
		$content .= 
			'<?xml version="1.0" encoding="UTF-8"?>' .
			'<gpx version="1.1" creator="' . __CLASS__ . '" ' .
				'xmlns="http://www.topografix.com/GPX/1/1" ' .
				'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
				'xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">';

		$content .= $this->_renderMetadata($documentData['metadata']);
		$content .= $this->_renderDocumentContent($documentData['content'], $renderOpts['precision']);

		$content .=  '</gpx>';

		$contentNoPretty = null;
		if ($renderOpts['prettify']) {
			if (isset($renderOpts['addNoPretty']) && $renderOpts['addNoPretty'])  {
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

	private function _renderMetadata(&$metadataInfo) {
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

	private function _getRandomizedDefaults() {
		$precision = $this->generator->numberBetween(3, 6);
		return array(
			'precision'=> $precision,
			'prettify' => true,
            'addNoPretty' => false,
            'addData' => true
		);
	}

	private function _getDefaults() {
		return array(
			//precision of the calculations: 
            //  how many decimals
            'precision'=> 4,

			//whether to format the XML in a clean an readable way 
            //  (indendented and with new lines)
            'prettify' => true,

            //if prettify, then set this to true to also include 
            //  in the return result the not-prettified XML text
            'addNoPretty' => false,

            //whether to include the data source used to generate the XML document
            //  in the return result or not
            'addData' => true
		);
	}
}