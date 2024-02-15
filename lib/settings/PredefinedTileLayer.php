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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit;
}

class Abp01_Settings_PredefinedTileLayer {
	const FILTER_HOOK_GET_DEFAULT_TILE_LAYER_ID = 'abp01_default_tile_layer_id';

	const FILTER_HOOK_GET_PREDEFINED_TILE_LAYERS = 'abp01_predefined_tile_layers';

	const TL_OPEN_STREET_MAP = 'open-street-map';

	const TL_TF_OPENCYCLEMAP = 'tf-open-cycle-map';

	const TL_TF_TRANSPORT = 'tf-transport';

	const TL_TF_LANDSCAPE = 'tf-landscape';

	const TL_TF_OUTDOORS = 'tf-outdoors';

	const TL_TF_TRANSPORT_DARK = 'tf-transport-dark';

	const TL_TF_SPINAL_MAP = 'tf-spinal-map';

	const TL_TF_PIONEER = 'tf-pioneer';

	const TL_TF_MOBILE_ATLAS = 'tf-mobile-atlas';

	const TL_TF_NEIGHBOORHOOD = 'tf-neighbourhood';

	const TL_TF_ATLAS = 'tf-atlas';

	private $_id;

	private $_label;

	private $_url;

	private $_attributionTxt;

	private $_attributionUrl;

	private $_infoUrl = null;

	private $_apiKeyRequired;

	private static $_predefinedTileLayers = null;

	public function __construct($id, $label, $url, $attributionTxt, $attributionUrl, $infoUrl = null) {
		if (empty($id)) {
			throw new InvalidArgumentException('Tile layer id may not be empty.');
		}

		if (empty($label)) {
			throw new InvalidArgumentException('Tile layer label may not be empty.');
		}
		
		if (empty($url)) {
			throw new InvalidArgumentException('Tile layer url may not be empty.');
		}

		$this->_id = $id;
		$this->_label = $label;
		$this->_url = $url;
		$this->_attributionTxt = $attributionTxt;
		$this->_attributionUrl = $attributionUrl;
		$this->_infoUrl = $infoUrl;
		$this->_apiKeyRequired = $this->_tileLayerUrlHasApiKeyPlaceholder($url);
	}

	private function _tileLayerUrlHasApiKeyPlaceholder($url) {
		return strpos($url, '{apiKey}') !== false;
	}

	public static function isPredefinedTileLayerSupported($id) {
		return !empty(self::getPredefinedTileLayer($id));
	}

	/**
	 * @return Abp01_Settings_PredefinedTileLayer|null 
	 * @throws Abp01_Exception 
	 */
	public static function getPredefinedTileLayer($id) {
		if (empty($id)) {
			return null;
		}

		$predefinedTileLayers = self::getPredefinedTileLayers();
		return isset($predefinedTileLayers[$id])
			? $predefinedTileLayers[$id]
			: null;
	}

	/**
	 * @return Abp01_Settings_PredefinedTileLayer[]
	 * @throws Abp01_Exception 
	 */
	public static function getPredefinedTileLayers() {
		if (self::$_predefinedTileLayers === null) {
			$predefinedTileLayers = array(
				//See OSM usage policies here: https://operations.osmfoundation.org/policies/tiles/
				//See OSM tile servers here: https://wiki.openstreetmap.org/wiki/Tile_servers
				self::TL_OPEN_STREET_MAP => new self(self::TL_OPEN_STREET_MAP, 
					__('Basic Open Street Map Style', 'abp01-trip-summary'),
					'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', 
					'OpenStreetMap & Contributors', 
					'https://www.openstreetmap.org/copyright',
					'https://www.openstreetmap.org/about'),

				//See all thunderforest maps here: https://www.thunderforest.com/maps/
				//See thunderforest map tiles API: https://www.thunderforest.com/docs/map-tiles-api/
				self::TL_TF_ATLAS => new self(self::TL_TF_ATLAS, 
					__('Thunderforest Atlas Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/atlas/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/',
					'https://www.thunderforest.com/maps/atlas/'),
				self::TL_TF_LANDSCAPE => new self(self::TL_TF_LANDSCAPE, 
					__('Thunderforest Landscape Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/', 
					'https://www.thunderforest.com/maps/landscape/'),
				self::TL_TF_MOBILE_ATLAS => new self(self::TL_TF_MOBILE_ATLAS, 
					__('Thunderforest Mobile Atlas Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/mobile-atlas/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/', 
					'https://www.thunderforest.com/maps/mobile-atlas/'),
				self::TL_TF_NEIGHBOORHOOD => new self(self::TL_TF_NEIGHBOORHOOD, 
					__('Thunderforest Neighbourhood Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/neighbourhood/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/', 
					'https://www.thunderforest.com/maps/neighbourhood/'),
				self::TL_TF_OPENCYCLEMAP => new self(self::TL_TF_OPENCYCLEMAP, 
					__('Thunderforest Open Cycle Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/',
					'https://www.thunderforest.com/maps/opencyclemap/'),
				self::TL_TF_OUTDOORS => new self(self::TL_TF_OUTDOORS, 
					__('Thunderforest Outdoors Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/',
					'https://www.thunderforest.com/maps/outdoors/'),
				self::TL_TF_PIONEER => new self(self::TL_TF_PIONEER, 
					__('Thunderforest Pioneer Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/pioneer/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/', 
					'https://www.thunderforest.com/maps/pioneer/'),
				self::TL_TF_SPINAL_MAP => new self(self::TL_TF_SPINAL_MAP, 
					__('Thunderforest Spinal Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/spinal-map/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/', 
					'https://www.thunderforest.com/maps/spinal-map/'),
				self::TL_TF_TRANSPORT => new self(self::TL_TF_TRANSPORT, 
					__('Thunderforest Transport Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/transport/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/', 
					'https://www.thunderforest.com/maps/transport/'),
				self::TL_TF_TRANSPORT_DARK => new self(self::TL_TF_TRANSPORT_DARK, 
					__('Thunderforest Transport Dark Map', 'abp01-trip-summary'),
					'https://{s}.tile.thunderforest.com/transport-dark/{z}/{x}/{y}.png?apikey={apiKey}', 
					'Maps © Thunderforest, Data © OpenStreetMap contributors', 
					'https://www.thunderforest.com/terms/',
					'https://www.thunderforest.com/maps/transport-dark/'),
			);

			$predefinedTileLayers = apply_filters(self::FILTER_HOOK_GET_PREDEFINED_TILE_LAYERS, 
				$predefinedTileLayers);

			self::_validatePredefinedTileLayers($predefinedTileLayers);
			self::$_predefinedTileLayers = $predefinedTileLayers;
		}

		return self::$_predefinedTileLayers;
	}

	public static function clearPredefinedTileLayersCache() {
		self::$_predefinedTileLayers = null;
	}

	private static function _validatePredefinedTileLayers(array $predefinedTileLayers) {
		foreach ($predefinedTileLayers as $layer) {
			if (!($layer instanceof Abp01_Settings_PredefinedTileLayer)) {
				throw new Abp01_Exception('A predefined tile layer must be an instance of <' . __CLASS__ . '> class');
			}
		}
	}

	/**
	 * @return Abp01_Settings_PredefinedTileLayer|null 
	 * @throws Abp01_Exception 
	 */
	public static function getDefaultTileLayer() {
		$allTileLayerIds = array_keys(self::getPredefinedTileLayers());

		$defaultTileLayeId = apply_filters(self::FILTER_HOOK_GET_DEFAULT_TILE_LAYER_ID, 
			self::TL_OPEN_STREET_MAP, 
			$allTileLayerIds);

		if (!self::isPredefinedTileLayerSupported($defaultTileLayeId)) {
			throw new Abp01_Exception('Unsupported pre-defined tile layer id <' . $defaultTileLayeId . '> used for default tile layer');
		}

		return self::getPredefinedTileLayer($defaultTileLayeId);
	}

	public function getTileLayerObject() {
		$tileLayer = new stdClass();
		$tileLayer->url = $this->_url;
		$tileLayer->attributionTxt = $this->_attributionTxt;
		$tileLayer->attributionUrl = $this->_attributionUrl;
		$tileLayer->apiKey = null;
		return $tileLayer;
	}

	public function asPlainObject() {
		$predefinedTileLayerInfo = new stdClass();
		$predefinedTileLayerInfo->id = $this->getId();
		$predefinedTileLayerInfo->label = $this->getLabel();
		$predefinedTileLayerInfo->infoUrl = $this->getInfoUrl();
		$predefinedTileLayerInfo->apiKeyRequired = $this->isApiKeyRequired();
		$predefinedTileLayerInfo->tileLayerObject = $this->getTileLayerObject();
		return $predefinedTileLayerInfo;
	}

	public function getId() {
		return $this->_id;
	}

	public function getLabel() {
		return $this->_label;
	}

	public function getUrl() {
		return $this->_url;
	}

	public function getAttributionText() {
		return $this->_attributionTxt;
	}

	public function getAttributionUrl() {
		return $this->_attributionUrl;
	}

	public function getInfoUrl() {
		return $this->_infoUrl;
	}

	public function isApiKeyRequired() {
		return $this->_apiKeyRequired;
	}
}