<?php
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
	private function _isTypeSupported($type) {
		return in_array($type, array(
			self::DIFFICULTY_LEVEL,
			self::PATH_SURFACE_TYPE,
			self::BIKE_TYPE,
			self::RAILROAD_LINE_TYPE,
			self::RAILROAD_OPERATOR,
			self::RAILROAD_LINE_STATUS,
			self::RECOMMEND_SEASONS,
			self::RAILROAD_ELECTRIFICATION
		));
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
            SELECT l.ID, l.lookup_category,
                COALESCE(lg.lookup_label, l.lookup_label) AS lookup_label
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
                $this->_cache[$category][$row['ID']] = $row['lookup_label'];
            }
        }
    }

    private function _getLookupOptions($type) {
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
	 * Creates an option object of type stdClass with the following properties: id, type and label
	 * @param integer $id Populates the id property
	 * @param string $label Populates the label property
	 * @param string $type Populates the type property
	 * @return stdClass The resulting option object
	 */
    private function _createOption($id, $label, $type) {
        $option = new stdClass();
        $option->id = $id;
        $option->label = $label;
        $option->type = $type;
        return $option;
    }

	/**
	 * Checks whether the given lookup item is in use or not.
	 * A lookup item is in use if it's associated with a post.
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
	 * @param string $lang The translation that should be deleted
	 * @return boolean True on success, false otherwise
	 */
	public function deleteLookupItemTranslation($lookupId, $lang) {
		if (empty($lookupId) || $lookupId <= 0) {
			throw new InvalidArgumentException();
		}
		if (empty($lang)) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$lookupLangTableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		$db->where('ID', $lookupId);
		$db->where('lookup_lang', $lang);
		$result = $db->delete($lookupLangTableName);

		$this->_invalidateCache();
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
		if (empty($defaultLabel)) {
			throw new InvalidArgumentException();
		}
		if (!$this->_isTypeSupported($type)) {
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
	 * @param string $lang The language for which to add the translation
	 * @param string $label The translation label
	 * @return boolean True if it succeeds, false otherwise
	 */
	public function addLookupItemTranslation($id, $lang, $label) {
		if (empty($id) || $id < 0) {
			throw new InvalidArgumentException();
		}
		if (empty($lang)) {
			throw new InvalidArgumentException();
		}
		if (empty($label)) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$lookupTranslationTableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		$db->insert($lookupTranslationTableName, array(
			'ID' => $id,
			'lookup_lang' => $lang,
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
	 * @param string $lang The language for which to modify the translation
	 * @param string $label The new translation label
	 * @return boolean True on success, false on failure
	 */
	public function modifyLookupItemTranslation($id, $lang, $label) {
		if (empty($id) || $id < 0) {
			throw new InvalidArgumentException();
		}
		if (empty($lang)) {
			throw new InvalidArgumentException();
		}
		if (empty($label)) {
			throw new InvalidArgumentException();
		}

		$db = $this->_env->getDb();
		$lookupTranslationTableName = $this->_env->getLookupLangTableName();

		if (!$db) {
			return false;
		}

		$db->where('ID', $id);
		$db->where('lookup_lang', $lang);
		$result = $db->update($lookupTranslationTableName, array(
			'lookup_label' => $label
		));

		$this->_invalidateCache();
		return $result;
	}

	/**
	 * Get the available difficulty level options. 
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getDifficultyLevelOptions() {
        return $this->_getLookupOptions(self::DIFFICULTY_LEVEL);
    }

	/**
	 * Get the available path surface type options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getPathSurfaceTypeOptions() {
        return $this->_getLookupOptions(self::PATH_SURFACE_TYPE);
    }

	/**
	 * Get the available bike type options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getBikeTypeOptions() {
        return $this->_getLookupOptions(self::BIKE_TYPE);
    }

	/**
	 * Get the available recommended seasons options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getRecommendedSeasonsOptions() {
        return $this->_getLookupOptions(self::RECOMMEND_SEASONS);
    }

	/**
	 * Get the available railroad line type options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getRailroadLineTypeOptions() {
        return $this->_getLookupOptions(self::RAILROAD_LINE_TYPE);
    }

	/**
	 * Get the available railroad operator options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getRailroadOperatorOptions() {
        return $this->_getLookupOptions(self::RAILROAD_OPERATOR);
    }

	/**
	 * Get the available railroad line status options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getRailroadLineStatusOptions() {
        return $this->_getLookupOptions(self::RAILROAD_LINE_STATUS);
    }

	/**
	 * Get the available railroad electrification status options
	 * Each array element is an object with the following properties: id, type and label.
	 * @return array The available options
	 */
    public function getRailroadElectrificationOptions() {
        return $this->_getLookupOptions(self::RAILROAD_ELECTRIFICATION);
    }

	/**
	 * Looks up the given lookup item, given the item type/category and the item id
	 * @param string $type The item type
	 * @param integer $id The item id
	 * @return stdClass The entire lookup item as an object with the following properties: id, label and type
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

    public function lookupDifficultyLevel($id) {
        return $this->lookup(self::DIFFICULTY_LEVEL, $id);
    }

    public function lookupPathSurfaceType($id) {
        return $this->lookup(self::PATH_SURFACE_TYPE, $id);
    }

    public function lookupBikeType($id) {
        return $this->lookup(self::BIKE_TYPE, $id);
    }

    public function lookupRailroadLineType($id) {
        return $this->lookup(self::RAILROAD_LINE_TYPE, $id);
    }

    public function lookupRailroadOperator($id) {
        return $this->lookup(self::RAILROAD_OPERATOR, $id);
    }

    public function lookupRailroadLineStatus($id) {
        return $this->lookup(self::RAILROAD_LINE_STATUS, $id);
    }

    public function lookupRailroadElectrification($id) {
        return $this->lookup(self::RAILROAD_ELECTRIFICATION, $id);
    }

    public function lookupRecommendedSeason($id) {
        return $this->lookup(self::RECOMMEND_SEASONS, $id);
    }
}