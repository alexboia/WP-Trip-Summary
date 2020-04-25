<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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

class Abp01_Lookup {
	const DIFFICULTY_LEVEL = 'difficultyLevel';

	const PATH_SURFACE_TYPE = 'pathSurfaceType';

	const BIKE_TYPE = 'bikeType';

	const RAILROAD_LINE_TYPE = 'railroadLineType';

	const RAILROAD_OPERATOR = 'railroadOperator';

	const RAILROAD_LINE_STATUS = 'railroadLineStatus';

	const RECOMMEND_SEASONS = 'recommendSeasons';

	const RAILROAD_ELECTRIFICATION = 'railroadElectrificationStatus';

	const DEFAULT_LANGUAGE = '_default';

	/**
	 * Internal cache for the lookup data
	 */
	private $_cache = null;

	/**
	 * Reference to the environment object
	 */
	private $_env = null;

	/**
	 * Current languate setting
	 */
	private $_lang = null;

	/**
	 * Constructor. Initializez the current instance with respect to the given languate setting.
	 * If no language setting is provided, it is picked up from the current environment
	 * @param string $lang The desired languate setting. Optional. Defaults to null
	 */
	public function __construct($lang = null) {
		$this->_env = Abp01_Env::getInstance();
		if (empty($lang)) {
			$this->_lang = $this->_env->getLang();
		} else {
			$this->_lang = $lang;
		}
	}

	/**
	 * Resets the internal lookup item cache to null
	 * @return void
	 */
	private function _invalidateCache() {
		$this->_cache = null;
	}

	/**
	 * Checks whether the given type is supported as a lookup item type or not
	 * @param string $type The lookup item type
	 * @return boolean True if it's supported, false otherwise
	 */
	public static function isTypeSupported($type) {
		return in_array($type, self::getSupportedCategories());
	}

	/**
	 * Checks whether the given language is supported for a lookup item translation.
	 * @param string $lang The language code to check for
	 * @return boolean True if it's supported, false otherwise
	 */
	public static function isLanguageSupported($lang) {
		return array_key_exists($lang, self::getSupportedLanguages());
	}

	/**
	 * Checks whether the given language code is the default lookup item translation language code.
	 * The default language code is merely a convention, as the labels for this language code are not stored in the translation table.
	 * This language code describes the default label of a lookup item.
	 * @param string $lang The language code to check for
	 * @return boolean True if it is the default language code, false otherwise
	 */
	public static function isDefaultLanguage($lang) {
		return $lang == self::DEFAULT_LANGUAGE;
	}

	/**
	 * Loads all the lookup data if needed, with respect to the current language setting
	 * First the internal cache is checked and if the cache is not set, 
	 *	then the database is hit in order to retrieve the data
	 * @return void
	 */
	private function _loadDataIfNeeded() {
		$env = $this->_env;
		$db = $env->getDb();        
		$table = $env->getLookupTableName();
		$langTable = $env->getLookupLangTableName();
		$lang = $this->_lang;

		//already an array, so simply return
		if (is_array($this->_cache)) {
			return;
		}

		$rows = $db->rawQuery('
			SELECT l.ID, l.lookup_category, l.lookup_label AS default_lookup_label,
				lg.lookup_label AS translated_lookup_label
			FROM `' . $table . '` l
			LEFT JOIN `' . $langTable . '` lg
				ON lg.ID = l.ID AND  lg.lookup_lang = ?
			ORDER BY lg.lookup_label ASC,
				l.lookup_label ASC', array($lang), false);

		$this->_cache = array();
		if (is_array($rows)) {
			foreach ($rows as $row) {
				$category = $row['lookup_category'];
				if (!isset($this->_cache[$category])) {
					$this->_cache[$category] = array();
				}
				$this->_cache[$category][$row['ID']] = array(
					'defaultLabel' => $row['default_lookup_label'],
					'translatedLabel' => $row['translated_lookup_label']
				);
			}
		}
	}

	/**
	 * Gets a list of all the lookup items for the given lookup item type/category.
	 * Each element of the arrary is an object with the following properties:
	 * - id = the id of the item;
	 * - type = the type of the item;
	 * - defaultLabel = the default label;
	 * - hasTranslation = whether the item has a translation in the context of the current language or not
	 * - label = the translated item label.
	 * @param string $type The type of the items that should be returned
	 * @return array The list of lookup items
	 */
	public function getLookupOptions($type) {
		$this->_loadDataIfNeeded();
		$options = array();
		if (isset($this->_cache[$type])) {
			foreach ($this->_cache[$type] as $id => $label) {
				$options[] = $this->_createOption($id, $label, $type);
			}
		}
		return $options;
	}

	/**
	 * Creates an option object of type stdClass with the following properties: id, type, defaultLabel and label
	 * @param integer $id Populates the id property
	 * @param mixed $label Populates the two label properties
	 * @param string $type Populates the type property
	 * @return stdClass The resulting option object
	 */
	private function _createOption($id, $label, $type) {
		if (is_string($label)) {
			$label = array(
				'defaultLabel' => $label,
				'translatedLabel' => $label
			);
		}

		$option = new stdClass();
		$option->id = $id;		
		$option->type = $type;
		$option->defaultLabel = $label['defaultLabel'];
		$option->hasTranslation = !empty($label['translatedLabel']);
		$option->label = $option->hasTranslation
			? $label['translatedLabel'] 
			: $label['defaultLabel'];

		return $option;
	}

	/**
	 * Gets a list of all supported lookup item categories/types.
	 * The list is a simple indexed array, that has the category/type codes as values
	 * @return array The list of supported lookup item categories/types
	 */
	public static function getSupportedCategories() {
		return array(
			self::DIFFICULTY_LEVEL,
			self::PATH_SURFACE_TYPE,
			self::BIKE_TYPE,
			self::RAILROAD_LINE_TYPE,
			self::RAILROAD_OPERATOR,
			self::RAILROAD_LINE_STATUS,
			self::RECOMMEND_SEASONS,
			self::RAILROAD_ELECTRIFICATION
		);
	}

	/**
	 * Gets a list of all supported languages.
	 * The list is an array that has the language code as a key and a label as a value.
	 * The label is comprised of both the english name and the native name of the language.
	 * 
	 * @return array The list of supported languages
	 */
	public static function getSupportedLanguages() {
		if (!function_exists('wp_get_available_translations')) {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		}

		$translations = array(
			'_default' => __('Default', 'abp01-trip-summary'),
			'en_US' => 'English (United States)'
		);
		
		$systemTranslations = wp_get_available_translations();

		foreach ($systemTranslations as $tx) {
			$translations[$tx['language']] = sprintf('%s (%s)', $tx['english_name'], $tx['native_name']);
		}

		return $translations;
	}

	/**
	 * Checks whether the given lookup item is in use or not.
	 * A lookup item is in use if it's associated with a post.
	 * 
	 * @param integer $lookupId The id of the lookup item to check for
	 * @return boolean True if it is, false otherwise
	 */
	public function isLookupInUse($lookupId) {
		//empty lookup, return
		if (empty($lookupId) || $lookupId <= 0) {
			throw new InvalidArgumentException();
		}

		//obtain and check database handle state
		$db = $this->_env->getDb();
		if (!$db) {
			return true;
		}

		$lookupId = intval($lookupId);
		$tableName = $this->_env->getRouteDetailsLookupTableName();

		//query the association table to check if it's assigned to a post
		$result = $db->rawQuery('SELECT COUNT(post_ID) AS post_count_check FROM `' . $tableName . '` WHERE lookup_ID = ?',  
			array($lookupId), 
			false);

		return is_array($result) && isset($result[0]) && intval($result[0]['post_count_check']) > 0;
	}

	/**
	 * Deletes the lookup item described by the given ID. 
	 * It deletes the item itself as well as all the available translations in the lookup translation table.
	 * If the item has been previoulsy cached, the cached data will also be deleted
	 * @param integer $lookupId The identifier of the lookup item to be deleted
	 * @return boolean True if the item is found and deleted, false otherwise
	 */
	public function deleteLookup($lookupId) {
		if (empty($lookupId) || $lookupId <= 0) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$lookupTableName = $this->_env->getLookupTableName();
		$lookupLangTableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		//delete the main item first
		$db->where('ID', $lookupId);
		if ($db->delete($lookupTableName)) {
			//delete all the available translations
			$db->where('ID', $lookupId);
			$result = $db->delete($lookupLangTableName) === false;
			//remove any cached data related to that item
			$this->_invalidateCache();
		} else {
			$result = false;
		}

		if (!$result) {
			$lastError = trim($db->getLastError());
			$result = empty($lastError);
		}

		return $result;
	}

	/**
	 * Deletes the look-up item translation, described by the given ID and the given language
	 * @param integer $lookupId The identifier of the lookup item for which the translation should be deleted
	 * @return boolean True on success, false otherwise
	 */
	public function deleteLookupItemTranslation($lookupId) {
		if (empty($lookupId) || $lookupId <= 0) {
			throw new InvalidArgumentException();
		}

		//nothing to do for the default language
		if (self::isDefaultLanguage($this->_lang)) {
			return true;
		}

		$db = $this->_env->getDb();
		$lookupLangTableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		$db->where('ID', $lookupId);
		$db->where('lookup_lang', $this->_lang);
		$result = $db->delete($lookupLangTableName);

		$this->_invalidateCache();
		if (!$result) {
			$lastError = trim($db->getLastError());
			$result = empty($lastError);
		}
		return $result;
	}

	/**
	 * Creates a new lookup item, with the given type and default label.
	 * Translations are created separately, after the item is created.
	 * @param string $type The type of the lookup item
	 * @param string $defaultLabel The default label of the lookup item
	 * @return stdClass The descriptor for the newly created item or null if some problem occurs
	 */
	public function createLookupItem($type, $defaultLabel) {
		if (!self::isTypeSupported($type)) {
			throw new InvalidArgumentException();
		}

		if (empty($defaultLabel)) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$lookupTableName = $this->_env->getLookupTableName();

		if (!$db) {
			return null;
		}

		$id = $db->insert($lookupTableName, array(			
			'lookup_label' => $defaultLabel,
			'lookup_category' => $type
		));

		if ($id !== false) {
			$this->_invalidateCache();
			return $this->_createOption($id, $defaultLabel, $type);
		}

		return null;
	}

	/**
	 * Add a translation for the given lookup item. The item must first exist before adding a translation.
	 * @param integer $id The identifier of the lookup item for which to add a translation
	 * @param string $label The translation label
	 * @return boolean True if it succeeds, false otherwise
	 */
	public function addLookupItemTranslation($id, $label) {
		if (empty($id) || $id < 0) {
			throw new InvalidArgumentException();
		}
		if (empty($label)) {
			throw new InvalidArgumentException();
		}

		//nothing to do for the default language
		if (self::isDefaultLanguage($this->_lang)) {
			return true;
		}

		$db = $this->_env->getDb();
		$lookupTranslationTableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		$db->insert($lookupTranslationTableName, array(
			'ID' => $id,
			'lookup_lang' => $this->_lang,
			'lookup_label' => $label
		));	

		$this->_invalidateCache();
		$lastError = trim($db->getLastError());
		return empty($lastError);
	}

	/**
	 * Updates the default label for the given lookup item.
	 * The lookup item type cannot be changed.
	 * @param integer $id The identifier of the lookup item that needs to be updated
	 * @param string $label The new default label
	 * @return boolean True on success, false on failure
	 */
	public function modifyLookupItem($id, $label) {
		if (empty($id) || $id < 0) {
			throw new InvalidArgumentException();
		}
		if (empty($label)) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$lookupTableName = $this->_env->getLookupTableName();

		if (!$db) {
			return false;
		}

		$db->where('ID', $id);
		$result = $db->update($lookupTableName, array(
			'lookup_label' => $label
		));

		$this->_invalidateCache();
		return $result;
	}

	/**
	 * Modifies the translation for the given lookup item and the given language
	 * @param integer $id The lookup item for which to modify the translation
	 * @param string $label The new translation label
	 * @return boolean True on success, false on failure
	 */
	public function modifyLookupItemTranslation($id, $label) {
		if (empty($id) || $id < 0) {
			throw new InvalidArgumentException();
		}
		if (empty($label)) {
			throw new InvalidArgumentException();
		}

		//nothing to do for the default language
		if (self::isDefaultLanguage($this->_lang)) {
			return true;
		}

		$db = $this->_env->getDb();
		$lookupTranslationTableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		$db->where('ID', $id);
		$db->where('lookup_lang', $this->_lang);
		$result = $db->update($lookupTranslationTableName, array(
			'lookup_label' => $label
		));

		$this->_invalidateCache();
		return $result;
	}

	/**
	 * Checkes whether there is an available translation for the given item, in the context of the current language.
	 * Will directly return false if the current language is the default language.
	 * @param integer $id The item id for which the existence of the translation is to be checked
	 * @return boolean True if a translation exists, false otherwise
	 */
	public function hasLookupItemTranslation($id) {
		if (empty($id) || $id < 0) {
			throw new InvalidArgumentException();
		}

		//if the current language is the default one - return false - there is no translation
		if (self::isDefaultLanguage($this->_lang)) {
			return false;
		}

		$db = $this->_env->getDb();
		$tableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		$result = $db->rawQuery('SELECT COUNT(ID) AS count_check FROM `' . $tableName . '` WHERE ID = ? AND lookup_lang = ?',  
			array($id, $this->_lang), 
			false);

		return is_array($result) && isset($result[0]) && intval($result[0]['count_check']) > 0;
	}

	/**
	 * Get the available difficulty level options. 
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getDifficultyLevelOptions() {
		return $this->getLookupOptions(self::DIFFICULTY_LEVEL);
	}

	/**
	 * Get the available path surface type options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getPathSurfaceTypeOptions() {
		return $this->getLookupOptions(self::PATH_SURFACE_TYPE);
	}

	/**
	 * Get the available bike type options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getBikeTypeOptions() {
		return $this->getLookupOptions(self::BIKE_TYPE);
	}

	/**
	 * Get the available recommended seasons options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getRecommendedSeasonsOptions() {
		return $this->getLookupOptions(self::RECOMMEND_SEASONS);
	}

	/**
	 * Get the available railroad line type options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getRailroadLineTypeOptions() {
		return $this->getLookupOptions(self::RAILROAD_LINE_TYPE);
	}

	/**
	 * Get the available railroad operator options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getRailroadOperatorOptions() {
		return $this->getLookupOptions(self::RAILROAD_OPERATOR);
	}

	/**
	 * Get the available railroad line status options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getRailroadLineStatusOptions() {
		return $this->getLookupOptions(self::RAILROAD_LINE_STATUS);
	}

	/**
	 * Get the available railroad electrification status options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
	public function getRailroadElectrificationOptions() {
		return $this->getLookupOptions(self::RAILROAD_ELECTRIFICATION);
	}

	/**
	 * Looks up the given lookup item, given the item type/category and the item id
	 * @param string $type The item type
	 * @param integer $id The item id
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookup($type, $id) {
		$this->_loadDataIfNeeded();
		if (isset($this->_cache[$type][$id])) {
			$result = $this->_createOption(intval($id), $this->_cache[$type][$id], $type);
		} else {
			$result = null;
		}
		return $result;
	}

	/**
	 * Lookup the difficulty level item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupDifficultyLevel($id) {
		return $this->lookup(self::DIFFICULTY_LEVEL, $id);
	}

	/**
	 * Lookup the path surface type item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupPathSurfaceType($id) {
		return $this->lookup(self::PATH_SURFACE_TYPE, $id);
	}

	/**
	 * Lookup the bike type item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupBikeType($id) {
		return $this->lookup(self::BIKE_TYPE, $id);
	}

	/**
	 * Lookup the railroad line type item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupRailroadLineType($id) {
		return $this->lookup(self::RAILROAD_LINE_TYPE, $id);
	}

	/**
	 * Lookup the railroad operator item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupRailroadOperator($id) {
		return $this->lookup(self::RAILROAD_OPERATOR, $id);
	}

	/**
	 * Lookup the railroad line status item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupRailroadLineStatus($id) {
		return $this->lookup(self::RAILROAD_LINE_STATUS, $id);
	}

	/**
	 * Lookup the railroad electrification status item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupRailroadElectrification($id) {
		return $this->lookup(self::RAILROAD_ELECTRIFICATION, $id);
	}

	/**
	 * Lookup the recommended season item that corresponds to the given id.
	 * @param integer $id The id for which the item must be retrieved
	 * @return stdClass The item, or null if not found. Item structure: id, hasTranslation, defaultLabel, label and type
	 */
	public function lookupRecommendedSeason($id) {
		return $this->lookup(self::RECOMMEND_SEASONS, $id);
	}
}