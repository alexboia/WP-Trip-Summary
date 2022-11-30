<?php
class Abp01_SettingsValuesDataProvider {
	public function getAvailablePredefinedTileLayers() {
		$allowedPredefinedTileLayersInfos = array();
		$predefinedTileLayers = Abp01_Settings_PredefinedTileLayer::getPredefinedTileLayers();
		
		foreach ($predefinedTileLayers as $id => $predefinedLayer) {
			$allowedPredefinedTileLayersInfos[$id] = $predefinedLayer->asPlainObject();
		}

		return $allowedPredefinedTileLayersInfos;
	} 

	public function getAvailableUnitSystems() {
		return Abp01_UnitSystem::getAvailableUnitSystems();
	}

	public function getAvailableViewerTabs() {
		return Abp01_Viewer::getAvailableTabs();
	}

	public function getAvailableItemLayouts() {
		return Abp01_Viewer::getAvailableItemLayouts();
	}
}