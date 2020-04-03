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

class Abp01_Installer {
	/**
	 * @var Integer Error code returned when an incompatible PHP version is detected upon installation
	 */
    const INCOMPATIBLE_PHP_VERSION = 1;

	/**
	 * @var Integer Error code returned when an incompatible WordPress version is detected upon installation
	 */
    const INCOMPATIBLE_WP_VERSION = 2;

	/**
	 * @var Integer Error code returned when LIBXML is not found
	 */
    const SUPPORT_LIBXML_NOT_FOUND = 3;

	/**
	 * @var Integer Error code returned when MySQL Spatial extension is not found
	 */
    const SUPPORT_MYSQL_SPATIAL_NOT_FOUND = 4;

	/**
	 * @var Integer Error code returned when MySqli extension is not found
	 */
    const SUPPORT_MYSQLI_NOT_FOUND = 5;

    /**
     * @var Integer Error code returned when the installation capabilities cannot be detected
     */
    const COULD_NOT_DETECT_INSTALLATION_CAPABILITIES = 255;

	/**
	 * @var String WP options key for current plug-in version
	 */
    const OPT_VERSION = 'abp01.option.version';

	/**
	 * @var Abp01_Env The current instance of the plug-in environment
	 */
    private $_env;

	/**
	 * @var Object The last occured error
	 */
    private $_lastError = null;

	/**
	 * @var Boolean Whether or not to install lookup data
	 */
    private $_installLookupData;
    
    private $_cachedDefinitions = null;

	/**
	 * Creates a new installer instance
	 * @param Boolean $installLookupData Whether or not to install lookup data
	 */
    public function __construct($installLookupData = true) {
        $this->_env = Abp01_Env::getInstance();
		$this->_installLookupData = $installLookupData;
    }

	/**
	 * Retrieves the current plug-in version (not the currently installed one, 
	 *	but the one in the package currently being run)
	 * @return String The current plug-in version
	 */
    private function _getVersion() {
        return $this->_env->getVersion();
    }

	/**
	 * Checks whether or not an update is needed
	 * @param String $version The version to be installed
	 * @param String $installedVersion The version to be installed
	 */
    private function _isUpdatedNeeded($version, $installedVersion) {
        return $version != $installedVersion;
    }

	/**
	 * Retrieve the currently installed version (which may be different 
	 *	than the one in the package currently being run)
	 * @return String The version
	 */
    private function _getInstalledVersion() {
        $version = null;
        if (function_exists('get_option')) {
            $version = get_option(self::OPT_VERSION, null);
        }
        return $version;
    }

	/**
	 * Carry out the update operation
	 * @param String $version The version to be installed
	 * @param String $installedVersion The version currently being installed
	 * @return Boolean Whether the operation succeeded or not
	 */
    private function _update($version, $installedVersion) {
		$this->_reset();
		$result = true;

        if (empty($installedVersion)) {
            //If no installed version is set, this is the very first version, 
            //  so we need to run the update to 0.2b, to 0.2.1, then to 0.2.2
            //NOTE: 0.2.3 did not have any specific update procedure
            $result = $this->_updateTo02Beta() 
                && $this->_updateTo021() 
                && $this->_updateTo022();
        } else {
            //...otherwise, we need to see 
            //  which installed version this is
            switch ($installedVersion) {
                case '0.2b':
                    //If the installed version is 0.2b, 
                    //  then run the update to 0.2.1, then to 0.2.2
                    $result = $this->_updateTo021() 
                        && $this->_updateTo022();
                break;
                case '0.2.1':
                    //If the installed version is 0.2.1, 
                    //  then run the update to 0.2.2
                    $result = $this->_updateTo022();
                break;
            }
        }

        //Finally, run the update to 0.2.4, 
        //  if the pervious updates (if there were any), 
        //  were successful
        if ($result) {
            $result = $this->_updateTo024();
        }

		if ($result) {
			update_option(self::OPT_VERSION, $version);
		}
        return $result;
    }

	/**
	 * Update the version 0.2 Beta
	 * @return Boolean Whether the operation succeeded or not
	 */
	private function _updateTo02Beta() {
		try {
			if ($this->_createTable($this->_getRouteDetailsLookupTableDefinition())) {
				return $this->_syncExistingLookupAssociations();
			} else {
				return false;
			}
		} catch (Exception $exc) {
			$this->_lastError = $exc;
		}
		return false;
    }
    
    private function _syncExistingLookupAssociations() {
		$db = $this->_env->getDb();
		if (!$db) {
			return false;
		}
		
		$tableName = $this->_getRouteDetailsLookupTableName();
		$detailsTableName = $this->_getRouteDetailsTableName();

		//remove all existing entries
		if ($db->rawQuery('TRUNCATE TABLE `' . $tableName . '`', null, false) === false) {
			return false;
		}

		//extract the current values
		$data = $db->rawQuery('SELECT post_ID, route_data_serialized FROM `' . $detailsTableName . '`');
		if (!is_array($data)) {
			return false;
		}

		foreach ($data as $row) {
			$postId = intval($row['post_ID']);
			if (empty($row['route_data_serialized'])) {
				continue;
			}

			$routeDetails = json_decode($row['route_data_serialized'], false);
			if (!empty($routeDetails->bikeRecommendedSeasons)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->bikeRecommendedSeasons);
			}
			if (!empty($routeDetails->bikePathSurfaceType)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->bikePathSurfaceType);
			}
			if (!empty($routeDetails->bikeBikeType)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->bikeBikeType);
			}
			if (!empty($routeDetails->bikeDifficultyLevel)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->bikeDifficultyLevel);
			}
			if (!empty($routeDetails->hikingDifficultyLevel)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->hikingDifficultyLevel);
			}
			if (!empty($routeDetails->hikingRecommendedSeasons)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->hikingRecommendedSeasons);
			}
			if (!empty($routeDetails->hikingSurfaceType)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->hikingSurfaceType);
			}
			if (!empty($routeDetails->trainRideOperator)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->trainRideOperator);
			}
			if (!empty($routeDetails->trainRideLineStatus)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->trainRideLineStatus);
			}
			if (!empty($routeDetails->trainRideElectrificationStatus)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->trainRideElectrificationStatus);
			}
			if (!empty($routeDetails->trainRideLineType)) {
				$this->_addLookupAssociation($db, $tableName, $postId, $routeDetails->trainRideLineType);
			}
		}

		return true;
	}

	private function _addLookupAssociation($db, $tableName, $postId, $lookupId) {
		if (is_array($lookupId)) {
			foreach ($lookupId as $id) {
				$this->_addLookupAssociation($db, $tableName, $postId, $id);
			}
		} else {
			$db->insert($tableName, array(
				'lookup_ID' => $lookupId,
				'post_ID' => $postId
			));
		}
	}

    private function _updateTo021() {
        $result = true;

        //1. Ensure storage directories
        if ($this->_ensureStorageDirectories()) {
            //2. Copy files if needed
            if (!$this->_moveTrackDataFiles()) {
                $result = false;
            }

            //3. Update track file paths in db.
            //  Since the plug-in update can be performed either via manual upload 
            //  or through WP's interface, the files can be moved 
            //  either by the plug-in or by the user.
            // Thus, fixing the routes path in the database is actually 
            //  independent of the process of moving the data files.
            if (!$this->_fixRoutePathsInDb()) {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    private function _ensureStorageDirectories() {
        $result = true;
        $rootStorageDir = $this->_env->getRootStorageDir();
        
        if (!is_dir($rootStorageDir)) {
            @mkdir($rootStorageDir);
        }

        if (is_dir($rootStorageDir)) {
            $tracksStorageDir = $this->_env->getTracksStorageDir();
            if (!is_dir($tracksStorageDir)) {
                @mkdir($tracksStorageDir);
            }

            if (is_dir($tracksStorageDir)) {
                $cacheStorageDir = $this->_env->getCacheStorageDir();
                if (!is_dir($cacheStorageDir)) {
                    @mkdir($cacheStorageDir);
                }

                $result = is_dir($cacheStorageDir);
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    private function _moveTrackDataFiles() {
        $result = true;
        $legacyTracksStorageDir = wp_normalize_path(sprintf('%s/storage', 
            $this->_env->getDataDir()));
        $legacyCacheStorageDir = wp_normalize_path(sprintf('%s/cache', 
            $this->_env->getDataDir()));

        //1. Move GPX files
        if (is_dir($legacyTracksStorageDir)) {
            $result = $this->_cleanLegacyStorageDirectory($legacyTracksStorageDir, 
                $this->_env->getTracksStorageDir(), 
                'gpx');
        }

        //2. Move cache files
        if (is_dir($legacyCacheStorageDir)) {
            $result = $this->_cleanLegacyStorageDirectory($legacyCacheStorageDir, 
                $this->_env->getCacheStorageDir(), 
                'cache');
        }

        return $result;
    }

    private function _cleanLegacyStorageDirectory($legacyDirectoryPath, $newDirectoryPath, $searchExtension) {
        $failedCount = 0;
        $moveFiles = glob($legacyDirectoryPath . DIRECTORY_SEPARATOR . '*.' . $searchExtension);

        //Move all files that match the given extension
        if ($moveFiles !== false && !empty($moveFiles)) {
            foreach ($moveFiles as $sourceFilePath) {
                $destinationFilePath = wp_normalize_path(sprintf('%s/%s', 
                    $newDirectoryPath,
                    basename($sourceFilePath)));
                    
                if (!@rename($sourceFilePath, $destinationFilePath)) {
                    $failedCount++;
                }
            }
        }

        //If no failures were registered whilst moving the files, 
        //  remove the legacy directory
        if ($failedCount == 0) {
            return $this->_removeDirectoryAndContents($legacyDirectoryPath);
        } else {
            return false;
        }
    }

    private function _removeStorageDirectory() {
        $rootStorageDir = $this->_env->getRootStorageDir();
        $tracksStorageDir = $this->_env->getTracksStorageDir();
        $cacheStorageDir = $this->_env->getCacheStorageDir();

        if ($this->_removeDirectoryAndContents($tracksStorageDir) && $this->_removeDirectoryAndContents($cacheStorageDir)) {
            return $this->_removeDirectoryAndContents($rootStorageDir);
        } else {
            return false;
        }
    }

    private function _removeDirectoryAndContents($directoryPath) {
        if (!is_dir($directoryPath)) {
            return true;
        }

        $failedCount = 0;
        $entries = @scandir($directoryPath, SCANDIR_SORT_ASCENDING);

        //Remove the files
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if ($entry != '.' && $entry != '..') {
                    $toRemoveFilePath = wp_normalize_path(sprintf('%s/%s', 
                        $directoryPath, 
                        $entry));

                    if (!@unlink($toRemoveFilePath)) {
                        $failedCount++;
                    }
                }
            }
        }

        //And if no file removal failed,
        //  remove the directory
        if ($failedCount == 0) {
            return @rmdir($directoryPath);
        } else {
            return false;
        }
    }

    private function _fixRoutePathsInDb() {
        $db = $this->_env->getDb();
        $routesTable = $this->_getRouteTrackTableName();

        //The route_track_file will only contain the file name
        //  so discard the everything before the last "/"
        $db->update($routesTable, array(
            'route_track_file' => $db->func("SUBSTRING_INDEX(route_track_file, '/', -1)")
        ));

        return empty(trim($db->getLastError()));
    }

    private function _updateTo022() {
        return $this->_ensureStorageDirectories() 
            && $this->_installStorageDirsSecurityAssets()
            && $this->_createCapabilities();
    }

    private function _updateTo024() {
        $this->_addLookupCategoryIndexToLookupTable();
        $this->_installDataTranslationsForLanguage('fr_FR');
        return true;
    }

    private function _installStorageDirsSecurityAssets() {
        $rootStorageDir = $this->_env->getRootStorageDir();
        $tracksStorageDir = $this->_env->getTracksStorageDir();
        $cacheStorageDir = $this->_env->getCacheStorageDir();

        $rootAssets = array(
            array(
                'name' => 'index.php',
                'contents' => $this->_getGuardIndexPhpFileContents(3),
                'type' => 'file'
            )
        );

        $tracksAssets = array(
            array(
                'name' => 'index.php',
                'contents' => $this->_getGuardIndexPhpFileContents(4),
                'type' => 'file'
            ),
            array(
                'name' => '.htaccess',
                'contents' => $this->_getTrackAssetsGuardHtaccessFileContents(),
                'type' => 'file'
            )
        );

        $this->_installAssetsForDirectory($rootStorageDir, 
            $rootAssets);
        $this->_installAssetsForDirectory($tracksStorageDir, 
            $tracksAssets);
        $this->_installAssetsForDirectory($cacheStorageDir, 
            $tracksAssets);

        return true;
    }

    private function _installAssetsForDirectory($targetDir, $assetsDesc) {
        if (!is_dir($targetDir)) {
            return false;
        }

        foreach ($assetsDesc as $asset) {
            $result = false;
            $assetPath = wp_normalize_path(sprintf('%s/%s', 
                $targetDir, 
                $asset['name']));

            if ($asset['type'] == 'file') {
                $assetHandle = @fopen($assetPath, 'w+');
                if ($assetHandle) {
                    fwrite($assetHandle, $asset['contents']);
                    fclose($assetHandle);
                    $result = true;
                }
            } else if ($asset['type'] == 'directory') {
                @mkdir($assetPath);
                $result = is_dir($assetPath);
            }

            if (!$result) {
                return false;
            }
        }
        return true;
    }

    private function _getTrackAssetsGuardHtaccessFileContents() {
        return join("\n", array(
            '<FilesMatch "\.cache">',
                "\t" . 'order allow,deny',
                "\t" . 'deny from all',
            '</FilesMatch>',
            '<FilesMatch "\.gpx">',
                "\t" . 'order allow,deny',
                "\t" . 'deny from all',
            '</FilesMatch>'
        ));
    }

    private function _getGuardIndexPhpFileContents($redirectCount) {
        return '<?php header("Location: ' . str_repeat('../', $redirectCount) . 'index.php"); exit;';
    }

    public function updateIfNeeded() {
        $version = $this->_getVersion();
        $installedVersion = $this->_getInstalledVersion();
        if (!$this->_isUpdatedNeeded($version, $installedVersion)) {
            return true;
        }
        return $this->_update($version, $installedVersion);
    }

    public function canBeInstalled() {
        $this->_reset();

        try {
            if (!$this->_isCompatPhpVersion()) {
                return self::INCOMPATIBLE_PHP_VERSION;
            }
            if (!$this->_isCompatWpVersion()) {
                return self::INCOMPATIBLE_WP_VERSION;
            }
            if (!$this->_hasMysqli()) {
                return self::SUPPORT_MYSQLI_NOT_FOUND;
            }
            if (!$this->_hasLibxml()) {
                return self::SUPPORT_LIBXML_NOT_FOUND;
            }
            if (!$this->_hasMysqlSpatialSupport() ||
                !$this->_hasRequiredMysqlSpatialFunctions()
            ) {
                return self::SUPPORT_MYSQL_SPATIAL_NOT_FOUND;
            }
        } catch (Exception $e) {
            $this->_lastError = $e;
        }

        return empty($this->_lastError) ? 0 : self::COULD_NOT_DETECT_INSTALLATION_CAPABILITIES;
    }

    public function activate() {
        $this->_reset();
        try {
            if (!$this->_installStorageDirectory()) {
                //Ensure no partial directory and file structure remains
                $this->_removeStorageDirectory();
                return false;
            }

            if (!$this->_installSchema()) {
                //Ensure no partial directory and file structure remains
                $this->_removeStorageDirectory();
                return false;
            }

            if (!$this->_installData()) {
                //Ensure no partial directory and file structure remains
                $this->_removeStorageDirectory();
                //Remove schema as well
                $this->_uninstallSchema();
                return false;
            } else {
                if ($this->_createCapabilities()) {
                    update_option(self::OPT_VERSION, $this->_getVersion());
                    return true;
                } else {
                    return false;
                }
            }
        } catch (Exception $e) {
            $this->_lastError = $e;
        }
        return false;
    }

    public function deactivate() {
        $this->_reset();
        try {
            return $this->_removeCapabilities();
        } catch (Exception $e) {
            $this->_lastError = $e;
        }
        return false;
    }

    public function uninstall() {
        $this->_reset();
        try {
            return $this->deactivate()
                && $this->_uninstallSchema()
                && $this->_uninstallSettings()
                && $this->_removeStorageDirectory()
                && $this->_uninstallVersion();
        } catch (Exception $e) {
            $this->_lastError = $e;
        }
        return false;
    }

    public function ensureStorageDirectories() {
        if ($this->_ensureStorageDirectories()) {
            $this->_installStorageDirsSecurityAssets();
        }
    }

    public function getRequiredPhpVersion() {
        return $this->_env->getRequiredPhpVersion();
    }

    public function getRequiredWpVersion() {
        return $this->_env->getRequiredWpVersion();
    }

    public function getLastError() {
        return $this->_lastError;
    }

    private function _installStorageDirectory() {
        $result = false;

        if ($this->_ensureStorageDirectories()) {
            $result = $this->_installStorageDirsSecurityAssets();
        }

        return $result;
    }

    private function _readLookupDefinitions() {
        if ($this->_cachedDefinitions === null) {
            $definitions = array();
            $filePath = $this->_getLookupDefsFile();
            $categories = array(
                Abp01_Lookup::BIKE_TYPE,
                Abp01_Lookup::DIFFICULTY_LEVEL,
                Abp01_Lookup::PATH_SURFACE_TYPE,
                Abp01_Lookup::RAILROAD_ELECTRIFICATION,
                Abp01_Lookup::RAILROAD_LINE_STATUS,
                Abp01_Lookup::RAILROAD_OPERATOR,
                Abp01_Lookup::RAILROAD_LINE_TYPE,
                Abp01_Lookup::RECOMMEND_SEASONS
            );

            if (!is_readable($filePath)) {
                return null;
            }

            $prevUseErrors = libxml_use_internal_errors(true);
            $xml = simplexml_load_file($filePath, 'SimpleXMLElement');

            if ($xml) {
                foreach ($categories as $c) {
                    $definitions[$c] = $this->_parseDefinitions($xml, $c);
                }
            } else {
                $this->_lastError = libxml_get_last_error();
                libxml_clear_errors();
            }

            libxml_use_internal_errors($prevUseErrors);
            $this->_cachedDefinitions = $definitions;
        }
        return $definitions;
    }

    private function _parseDefinitions($xml, $category) {
        $lookup = array();
        $node = $xml->{$category};
        if (empty($node) || empty($node->lookup)) {
            return array();
        }
        foreach ($node->lookup as $lookupNode) {
            if (empty($lookupNode['default'])) {
                continue;
            }
            $lookup[] = array(
                'default' => (string)$lookupNode['default'],
                'translations' => $this->_readLookupTranslations($lookupNode)
            );
        }

        return $lookup;
    }

    private function _readLookupTranslations($xml) {
        $translations = array();
        if (empty($xml->lang)) {
            return array();
        }
        foreach ($xml->lang as $langNode) {
            if (empty($langNode['code'])) {
                continue;
            }
            $tx = (string)$langNode;
            if (!empty($tx)) {
                $translations[(string)$langNode['code']] = $tx;
            }
        }
        return $translations;
    }

    private function _getLookupDefsFile() {
        $env = $this->_env;
        $dataDir = $env->getDataDir();

        if ($env->isDebugMode()) {
            $dirName = 'dev/setup';
            $testDir = sprintf('%s/%s', $dataDir, $dirName);
            if (!is_dir($testDir)) {
                $dirName = 'setup';
            }
        } else {
            $dirName = 'setup';
        }

        $filePath = sprintf('%s/%s/lookup-definitions.xml', $dataDir, $dirName);
        return $filePath;
    }

    private function _isCompatPhpVersion() {
        $current = $this->_env->getPhpVersion();
        $required = $this->_env->getRequiredPhpVersion();
        return version_compare($current, $required, '>=');
    }

    private function _isCompatWpVersion() {
        $current = $this->_env->getWpVersion();
        $required = $this->_env->getRequiredWpVersion();
        return version_compare($current, $required, '>=');
    }

    private function _hasLibxml() {
        return function_exists('simplexml_load_string') &&
            function_exists('simplexml_load_file');
    }

    private function _hasMysqli() {
        return extension_loaded('mysqli') &&
            class_exists('mysqli_driver') &&
            class_exists('mysqli');
    }

    private function _hasMysqlSpatialSupport() {
        $result = false;
        $db = $this->_env->getDb();

        if (!$db) {
            return false;
        }

        try {
            $haveGeometry = $db->rawQuery("SHOW VARIABLES WHERE Variable_name = 'have_geometry'");
        } catch (Exception $exc) {
            $this->_lastError = $exc;
            $haveGeometry = null;
        }

        if (!empty($haveGeometry) && is_array($haveGeometry)) {
            $haveGeometry = $haveGeometry[0];
            $result = !empty($haveGeometry['Value']) &&
                strcasecmp($haveGeometry['Value'], 'YES') === 0;
        }

        return $result;
    }

    private function _hasRequiredMysqlSpatialFunctions() {
        $result = false;
        $db = $this->_env->getDb();
        $expected = 'POLYGON((1 2,3 2,3 4,1 4,1 2))';

        if (!$db) {
            return false;
        }

        try {
            $spatialTest = $db->rawQuery('SELECT ST_AsText(ST_Envelope(LINESTRING(
                ST_GeomFromText(ST_AsText(POINT(1, 2)), 3857),
                ST_GeomFromText(ST_AsText(POINT(3, 4)), 3857)
            ))) AS SPATIAL_TEST');
        } catch (Exception $exc) {
            $this->_lastError = $exc;
            $spatialTest = null;
        }

        if (!empty($spatialTest) && is_array($spatialTest)) {
            $result = strcasecmp($spatialTest[0]['SPATIAL_TEST'], $expected) === 0;
        }

        return $result;
    }

    private function _createCapabilities() {
        Abp01_Auth::getInstance()->installCapabilities();
        return true;
    }

    private function _removeCapabilities() {
        Abp01_Auth::getInstance()->removeCapabilities();
        return true;
    }

    private function _uninstallVersion() {
        delete_option(self::OPT_VERSION);
        return true;
    }

    private function _uninstallSettings() {
        Abp01_Settings::getInstance()->purgeAllSettings();
        return true;
    }

    private function _installData() {
		if (!$this->_installLookupData) {
			return true;
		}
		
        $db = $this->_env->getDb();
        $table = $this->_getLookupTableName();
        $langTable = $this->_getLookupLangTableName();
        $definitions = $this->_readLookupDefinitions();
        $ok = true;

        if (!$db || !is_array($definitions)) {
            return false;
        }

        //make sure table is empty
        $stats = $db->getOne($table, 'COUNT(*) AS cnt');
        if ($stats && is_array($stats) && $stats['cnt'] > 0) {
            return true;
        }

        //save lookup data
        foreach ($definitions as $category => $data) {
            if (empty($data)) {
                continue;
            }
            foreach ($data as $lookup) {
                $id = $db->insert($table, array(
                    'lookup_category' => $category,
                    'lookup_label' => $lookup['default']
                ));

                $ok = $ok && $id !== false;
                if (!$ok) {
                    break 2;
                }

                foreach ($lookup['translations'] as $lang => $label) {
                    $ok = $ok && $db->insert($langTable, array(
                        'ID' => $id,
                        'lookup_lang' => $lang,
                        'lookup_label' => $label
                    )) !== false;
                    if (!$ok) {
                        break 3;
                    }
                }
            }
        }

        return $ok;
    }

    private function _installDataTranslationsForLanguage($langCode) {
        $db = $this->_env->getDb();
        $table = $this->_getLookupTableName();
        $langTable = $this->_getLookupLangTableName();
        $definitions = $this->_readLookupDefinitions();

        foreach ($definitions as $category => $data) {
            if (empty($data)) {
                continue;
            }

            foreach ($data as $lookup) {
                $defaultLabel = $lookup['default'];

                $db->where('LOWER(lookup_label)', strtolower($defaultLabel));
                $db->where('lookup_category', $category);
                $id = intval($db->getValue($table, 'ID'));

                if (!is_nan($id) && $id > 0) {
                    $db->where('ID', $id);
                    $db->where('lookup_lang', $langCode);
                    $test = $db->getOne($langTable, 'COUNT(*) as cnt');

                    if ($test && is_array($test) && $test['cnt'] == 0) {
                        $db->insert($langTable, array(
                            'ID' => $id,
                            'lookup_lang' => $langCode,
                            'lookup_label' => $lookup['translations'][$langCode]
                        ));
                    }
                }
            }
        }

        return true;
    }

    private function _installSchema() {
        $ok = true;
        $tables = array(
            $this->_getLookupTableDefinition(),
            $this->_getLookupLangTableDefinition(),
            $this->_getRouteDetailsTableDefinition(),
            $this->_getRouteTrackTableDefinition(),
			$this->_getRouteDetailsLookupTableDefinition()
        );

        foreach ($tables as $table) {
            $ok = $ok && $this->_createTable($table);
        }

        if (!$ok) {
            $this->_uninstallSchema();
        }

        return $ok;
    }

    private function _uninstallSchema() {
        return $this->_uninstallRouteDetailsTable() !== false &&
            $this->_uninstallRouteTrackTable() !== false &&
            $this->_uninstallLookupTable() !== false &&
            $this->_uninstallLookupLangTable() !== false &&
			$this->_uninstallRouteDetailsLookupTable() !== false;
    }

    private function _createTable($tableDef) {
        $db = $this->_env->getDb();
        if (!$db) {
            return false;
        }

        $charset = $this->_getDefaultCharset();
        $collate = $this->_getCollate();

        if (!empty($charset)) {
            $charset = "DEFAULT CHARACTER SET = '" . $charset . "'";
            $tableDef .= ' ' . $charset . ' ';
        }
        if (!empty($collate)) {
            $collate = "COLLATE = '" . $collate . "'";
            $tableDef .= ' ' . $collate . ' ';
        }

        $tableDef .= ' ';
        $tableDef .= 'ENGINE=MyISAM';

        $db->rawQuery($tableDef, null, false);
        $lastError = trim($db->getLastError());

        return empty($lastError);
    }

    private function _addLookupCategoryIndexToLookupTable() {
        $result = false;

        try {
            $db = $this->_env->getDb();
            $db->rawQuery('ALTER TABLE `' . $this->_getLookupTableName() .  '` ADD INDEX `lookup_category` (`lookup_category`)');
            $result = empty(trim($db->getLastError()));
        } catch (Exception $exc) {
            $result = false;
        }

        return $result;
    }

	/**
	 * Fetch the definition of the table which stores track data.
	 * It needs to be within a function because the table name is dynamically computed, 
	 *	accounting for the WP prefix
	 * @return String The DDL SQL query
	 */
    private function _getRouteTrackTableDefinition() {
        return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteTrackTableName() . "` (
            `post_ID` BIGINT(20) UNSIGNED NOT NULL,
            `route_track_file` LONGTEXT NOT NULL,
            `route_min_coord` POINT NOT NULL,
            `route_max_coord` POINT NOT NULL,
            `route_bbox` POLYGON NOT NULL,
            `route_min_alt` FLOAT NULL DEFAULT '0',
            `route_max_alt` FLOAT NULL DEFAULT '0',
            `route_track_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `route_track_modified_at` TIMESTAMP NULL DEFAULT NULL,
            `route_track_modified_by` BIGINT(20) NULL DEFAULT NULL,
                PRIMARY KEY (`post_ID`),
                SPATIAL INDEX `idx_route_track_bbox` (`route_bbox`)
        )";
    }

	/**
	 * Fetch the definition of the route details table.
	 * It needs to be within a function because the table name is dynamically computed, 
	 *	accounting for the WP prefix
	 * @return String The DDL SQL query
	 */
    private function _getRouteDetailsTableDefinition() {
        return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteDetailsTableName() . "` (
            `post_ID` BIGINT(10) UNSIGNED NOT NULL,
            `route_type` VARCHAR(150) NOT NULL,
            `route_data_serialized` LONGTEXT NOT NULL,
            `route_data_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `route_data_last_modified_at` TIMESTAMP NULL DEFAULT NULL,
            `route_data_last_modified_by` BIGINT(20) NULL DEFAULT NULL,
                PRIMARY KEY (`post_ID`)
        )";
    }

	/**
	 * Fetch the definition of the table which stores per-language lookup data definitions.
	 * It needs to be within a function because the table name is dynamically computed, 
	 *	accounting for the WP prefix
	 * @return String The DDL SQL query
	 */
    private function _getLookupLangTableDefinition() {
        return "CREATE TABLE IF NOT EXISTS `" . $this->_getLookupLangTableName() . "` (
            `ID` INT(10) UNSIGNED NOT NULL,
            `lookup_lang` VARCHAR(10) NOT NULL,
            `lookup_label` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`ID`, `lookup_lang`)
        )";
    }

	/**
	 * Fetch the definition of the table which stores the base lookup data definitions.
	 * It needs to be within a function because the table name is dynamically computed, 
	 *	accounting for the WP prefix
	 * @return String The DDL SQL query
	 */
    private function _getLookupTableDefinition() {
        return "CREATE TABLE IF NOT EXISTS `" . $this->_getLookupTableName() . "` (
            `ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `lookup_category` VARCHAR(150) NOT NULL,
            `lookup_label` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`ID`)
        )";
    }

	/**
	 * Fetch the definition of the table which stores the correspondence between posts and lookup data.
	 * It needs to be within a function because the table name is dynamically computed, 
	 *	accounting for the WP prefix
	 * @return String The DDL SQL query
	 */
	private function _getRouteDetailsLookupTableDefinition() {
		return "CREATE TABLE IF NOT EXISTS `" . $this->_getRouteDetailsLookupTableName() . "` (
			`post_ID` BIGINT(10) UNSIGNED NOT NULL,
			`lookup_ID` INT(10) UNSIGNED NOT NULL,
				PRIMARY KEY (`post_ID`, `lookup_ID`)
		)";
	}

	/**
	 * Remove the table which stores track data.
	 */
    private function _uninstallRouteTrackTable() {
        $db = $this->_env->getDb();
        return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getRouteTrackTableName() . '`', null, false) : false;
    }

	/**
	 * Remove the table which stores track details.
	 */
    private function _uninstallRouteDetailsTable() {
        $db = $this->_env->getDb();
        return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getRouteDetailsTableName() . '`', null, false) : false;
    }

	/**
	 * Remove the table which stores the base lookup data definitions.
	 */
    private function _uninstallLookupTable() {
        $db = $this->_env->getDb();
        return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getLookupTableName() . '`', null, false) : false;
    }

	/**
	 * Remove the table which stores per-language lookup data definitions.
	 */
    private function _uninstallLookupLangTable() {
        $db = $this->_env->getDb();
        return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getLookupLangTableName() . '`', null, false) : false;
    }

	/**
	 * Remove the table which stores the correspondence between posts and lookup data.
	 */
	private function _uninstallRouteDetailsLookupTable() {
		$db = $this->_env->getDb();
		return $db != null ? $db->rawQuery('DROP TABLE IF EXISTS `' . $this->_getRouteDetailsLookupTableName() . '`', null, false) : false;
	}

    private function _reset() {
        $this->_lastError = null;
    }

    private function _getDefaultCharset() {
        return $this->_env->getDbCharset();
    }

    private function _getCollate() {
        return $this->_env->getDbCollate();
    }

    private function _getRouteTrackTableName() {
        return $this->_env->getRouteTrackTableName();
    }

    private function _getRouteDetailsTableName() {
        return $this->_env->getRouteDetailsTableName();
    }

    private function _getLookupLangTableName() {
        return $this->_env->getLookupLangTableName();
    }

    private function _getLookupTableName() {
        return $this->_env->getLookupTableName();
    }

	private function _getRouteDetailsLookupTableName() {
		return $this->_env->getRouteDetailsLookupTableName();
	}
}