<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

use WpTripSummary\Exception;

if (!defined('ABP01_LOADED')) {
	exit;
}

class Abp01_Settings_PredefinedTileLayer {
	const FILTER_HOOK_GET_DEFAULT_TILE_LAYER_ID = 'abp01_default_tile_layer_id';

	const FILTER_HOOK_GET_PREDEFINED_TILE_LAYERS = 'abp01_predefined_tile_layers';

	public const string TL_OPEN_STREET_MAP = 'open-street-map';

	public const string TL_TF_OPENCYCLEMAP = 'tf-open-cycle-map';

	public const string TL_TF_TRANSPORT = 'tf-transport';

	public const string TL_TF_LANDSCAPE = 'tf-landscape';

	public const string TL_TF_OUTDOORS = 'tf-outdoors';

	public const string TL_TF_TRANSPORT_DARK = 'tf-transport-dark';

	public const string TL_TF_SPINAL_MAP = 'tf-spinal-map';

	public const string TL_TF_PIONEER = 'tf-pioneer';

	public const string TL_TF_MOBILE_ATLAS = 'tf-mobile-atlas';

	public const string TL_TF_NEIGHBOORHOOD = 'tf-neighbourhood';

	public const string TL_TF_ATLAS = 'tf-atlas';

	private ?string $_id;

	private ?string $_label;

	private ?string $_url;

	private ?string $_attributionTxt;

	private ?string $_attributionUrl;

	private ?string $_infoUrl = null;

	private bool $_apiKeyRequired;

	/**
	 * @var null|Abp01_Settings_PredefinedTileLayer[]
	 */
	private static ?array $_predefinedTileLayers = null;

	public function __construct(?string $id, 
		?string $label, 
		?string $url, 
		?string $attributionTxt, 
		?string $attributionUrl, 
		?string $infoUrl = null) {

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

	private function _tileLayerUrlHasApiKeyPlaceholder(?string $url): bool {
		return !empty($url) && strpos($url, '{apiKey}') !== false;
	}

	public static function isPredefinedTileLayerSupported(?string $id): bool {
		return !empty(self::getPredefinedTileLayer($id));
	}

	public static function getPredefinedTileLayer(?string $id): ?Abp01_Settings_PredefinedTileLayer {
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

			$predefinedTileLayers = self::_filterAvailablePredefinedTileLayers($predefinedTileLayers);
			self::$_predefinedTileLayers = $predefinedTileLayers;
		}

		return self::$_predefinedTileLayers;
	}

	/**
	 * @param Abp01_Settings_PredefinedTileLayer[] $predefinedTileLayers 
	 * @return Abp01_Settings_PredefinedTileLayer[]
	 */
	private static function _filterAvailablePredefinedTileLayers(array $predefinedTileLayers): array {
		/**
		 * Filters the list of available pre-defined tile layers offered 
		 * 	as options when configuring the tile layer used for the viewer.
		 * The return value must be a non-empty array 
		 * 	of Abp01_Settings_PredefinedTileLayer instances.
		 * 
		 * Invalid elements are fitlered out.
		 * 
		 * If the resulting value is a empty array, 
		 * 	the default list is used instead.
		 * 
		 * @since 0.3.2
		 * @category Settings - Trip Summary Map
		 * @unstable Susceptible to breaking changes due to PSR-4 migration
		 * 
		 * @param Abp01_Settings_PredefinedTileLayer[] $predefinedTileLayer The list of pre-defined tile layers.
		 */
		$finalTileLayers = apply_filters(self::FILTER_HOOK_GET_PREDEFINED_TILE_LAYERS, 
			$predefinedTileLayers);

		if (!is_array($finalTileLayers)) {
			$finalTileLayers = array();
		}

		$finalTileLayers = array_filter($finalTileLayers, 
			fn(mixed $tileLayer): bool => 
				is_object($tileLayer) && $tileLayer instanceof self);

		if (empty($finalTileLayers)) {
			$finalTileLayers = $predefinedTileLayers;
		}

		return $finalTileLayers;
	}

	public static function clearPredefinedTileLayersCache() {
		self::$_predefinedTileLayers = null;
	}

	public static function getDefaultTileLayer(): ?Abp01_Settings_PredefinedTileLayer {
		$allTileLayerIds = array_keys(self::getPredefinedTileLayers());
		$defaultTileLayeId = self::_getDefaultTileLayerId($allTileLayerIds);

		if (!self::isPredefinedTileLayerSupported($defaultTileLayeId)) {
			throw new Exception(
				'Unsupported pre-defined tile layer id <' . $defaultTileLayeId . '> used for default tile layer'
			);
		}

		return self::getPredefinedTileLayer($defaultTileLayeId);
	}

	private static function _getDefaultTileLayerId(array $allTileLayerIds): string {
		/**
		 * Filters the identifier of the pre-defined tile layer selected by default.
		 *
		 * The initial value is "open-street-map". Callbacks also receive the identifiers
		 * available after the pre-defined tile layer list has been filtered. The result
		 * must be a non-empty string identifying one of those layers. An empty or
		 * non-string result falls back to "open-street-map"; an unknown non-empty string
		 * causes default tile layer resolution to fail.
		 *
		 * @since 0.2.7
		 * @category Settings - Trip Summary Map
		 *
		 * @param string $defaultTileLayerId The default pre-defined tile layer identifier.
		 * @param string[] $allTileLayerIds The available pre-defined tile layer identifiers.
		 */
		$defaultTileLayeId = apply_filters(self::FILTER_HOOK_GET_DEFAULT_TILE_LAYER_ID, 
			self::TL_OPEN_STREET_MAP, 
			$allTileLayerIds);

		if (empty($defaultTileLayeId) || !is_string($defaultTileLayeId)) {
			$defaultTileLayeId = self::TL_OPEN_STREET_MAP;
		}

		return $defaultTileLayeId;
	}

	public function getTileLayerObject(): stdClass {
		$tileLayer = new stdClass();
		$tileLayer->url = $this->_url;
		$tileLayer->attributionTxt = $this->_attributionTxt;
		$tileLayer->attributionUrl = $this->_attributionUrl;
		$tileLayer->apiKey = null;
		return $tileLayer;
	}

	public function asPlainObject(): stdClass {
		$predefinedTileLayerInfo = new stdClass();
		$predefinedTileLayerInfo->id = $this->getId();
		$predefinedTileLayerInfo->label = $this->getLabel();
		$predefinedTileLayerInfo->infoUrl = $this->getInfoUrl();
		$predefinedTileLayerInfo->apiKeyRequired = $this->isApiKeyRequired();
		$predefinedTileLayerInfo->tileLayerObject = $this->getTileLayerObject();
		return $predefinedTileLayerInfo;
	}

	public function getId(): ?string {
		return $this->_id;
	}

	public function getLabel(): ?string {
		return $this->_label;
	}

	public function getUrl(): ?string {
		return $this->_url;
	}

	public function getAttributionText(): ?string {
		return $this->_attributionTxt;
	}

	public function getAttributionUrl(): ?string {
		return $this->_attributionUrl;
	}

	public function getInfoUrl(): ?string {
		return $this->_infoUrl;
	}

	public function isApiKeyRequired(): bool {
		return $this->_apiKeyRequired;
	}
}
