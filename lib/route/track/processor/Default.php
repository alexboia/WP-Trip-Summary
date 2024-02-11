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

class Abp01_Route_Track_Processor_Default implements Abp01_Route_Track_Processor {
	const GEOJSON_FILE_EXTENSION = 'geojson';

	const GPX_FILE_EXTENSION = 'gpx';

	const KML_FILE_EXTENSION = 'kml';

	const DEFAULT_FILE_EXTENSION = 'dat';
	
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	/**
	 * @var Abp01_Route_Track_DocumentParser_Factory
	 */
	private $_documentParserFactory;

	private $_trackFileMapTypesToExtensionsMapping = array();

	public function __construct(Abp01_Route_Track_DocumentParser_Factory $documentParserFactory, Abp01_Env $env) {
		$this->_documentParserFactory = $documentParserFactory;
		$this->_env = $env;

		$this->_registerExtensionForMimeTypes(self::GPX_FILE_EXTENSION, 
			Abp01_KnownMimeTypes::getGpxDocumentMimeTypes());
		$this->_registerExtensionForMimeTypes(self::GEOJSON_FILE_EXTENSION, 
			Abp01_KnownMimeTypes::getGeoJsonDocumentMimeTypes());
		$this->_registerExtensionForMimeTypes(self::KML_FILE_EXTENSION, 
			Abp01_KnownMimeTypes::getKmlDocumentMimeTypes());
	}

	private function _registerExtensionForMimeTypes($extension, array $mimeTypes) {
		foreach ($mimeTypes as $mimeType) {
			$this->_trackFileMapTypesToExtensionsMapping[$mimeType] = $extension;
		}
	}

	public function processInitialTrackSourceFile($postId, $trackFilePath, $trackFileMimeType) {
		if (empty($postId)) {
			throw new InvalidArgumentException('Post id may not be empty');
		}

		if (empty($trackFilePath)) {
			throw new InvalidArgumentException('Track file path may not be empty');
		}

		if (empty($trackFileMimeType)) {
			throw new InvalidArgumentException('Track file mime type may not be empty');
		}

		$originalTrackDocument = $this->_getOriginalTrackDocument($postId);
		if (!$originalTrackDocument) {
			$originalTrackDocument = $this->_processTrackSourceFile($trackFilePath, 
				$trackFileMimeType);
			$this->_storeOriginalTrackDocument($postId, 
				$originalTrackDocument);
		}

		$trackFileName = basename($trackFilePath);
		$trackFileMimeType = $this->_standardizeTrackFileMimeType($trackFileMimeType);

		$track = new Abp01_Route_Track($postId, 
			$trackFileName, 
			$trackFileMimeType,
			$originalTrackDocument->getBounds(), 
			$originalTrackDocument->getMinAlt(),
			$originalTrackDocument->getMaxAlt());

		return $track;
	}

	/**
	 * @return Abp01_Route_Track_Document|null
	 */
	private function _getOriginalTrackDocument($postId) {
		$originalTrackDocumentFilePath = $this->_constructOriginalTrackDocumentFilePath($postId);
		if (is_readable($originalTrackDocumentFilePath)) {
			$documentContents = file_get_contents($originalTrackDocumentFilePath);
			return Abp01_Route_Track_Document::fromSerializedDocument($documentContents);
		} else {
			return null;
		}
	}

	/**
	 * @return Abp01_Route_Track_Document
	 * @throws InvalidArgumentException 
	 */
	private function _processTrackSourceFile($trackFilePath, $trackFileMimeType) {
		$trackFileContents = file_get_contents($trackFilePath);
		$documentParser = $this->_getSourceTrackFileDocumentParser($trackFileMimeType);
		return $documentParser->parse($trackFileContents);
	}

	private function _storeOriginalTrackDocument($postId, Abp01_Route_Track_Document $originalDocument) {
		$originalTrackDocumentFilePath = $this->_constructOriginalTrackDocumentFilePath($postId);
		$documentContents = $originalDocument->serializeDocument();

		file_put_contents($originalTrackDocumentFilePath, 
			$documentContents, 
			LOCK_EX);
	}

	private function _standardizeTrackFileMimeType($trackFileMimeType) {
		$documentParser = $this->_getSourceTrackFileDocumentParser($trackFileMimeType);
		return $documentParser->getDefaultMimeType();
	}

	private function _constructOriginalTrackDocumentFilePath($postId) {
		$trackProfileFileName = $this->_constructOriginalTrackDocumentFileName($postId);
		return $this->_constructTrackCacheFilePath($trackProfileFileName);
	}

	private function _constructOriginalTrackDocumentFileName($postId) {
		return sprintf('track-original-%d.cache', $postId);
	}

	/**
	 * @return Abp01_Route_Track_DocumentParser
	 */
	private function _getSourceTrackFileDocumentParser($trackFileMimeType) {
		return $this->_documentParserFactory->resolveDocumentParser($trackFileMimeType);
	}

	/**
	 * @return Abp01_Route_Track_AltitudeProfile
	 * @throws InvalidArgumentException 
	 */
	public function getOrCreateDisplayableAltitudeProfile(Abp01_Route_Track $track, $targetSystem, $stepPoints = 10) {
		if ($stepPoints <= 0) {
			throw new InvalidArgumentException('Number of points to step over must be greater than 0');
		}

		$trackProfileDocument = $this->_getTrackProfileDocument($track->getPostId());

		$targetSystemInstance = !($targetSystem instanceof Abp01_UnitSystem) 
			? $this->_createUnitSystemInstanceOrThrow($targetSystem) 
			: $targetSystem;

		if (!$this->_isTrackProfileUseable($trackProfileDocument, $targetSystemInstance, $stepPoints)) {
			$trackDocument = $this->getOrCreateDisplayableTrackDocument($track);

			$trackProfileDocument = $trackDocument->computeAltitudeProfile($targetSystemInstance, 
				$stepPoints);
			
			$this->_storeTrackProfileDocument($track->getPostId(), 
				$trackProfileDocument);
		}

		return $trackProfileDocument;
	}

	private function _isTrackProfileUseable($profileDocument, $targetSystemInstance, $stepPoints) {
		return ($profileDocument instanceof Abp01_Route_Track_AltitudeProfile) 
			&& $profileDocument->hasBeenGeneratedFor($targetSystemInstance, $stepPoints);
	}

	private function _getTrackProfileDocument($postId) {
		$path = $this->_constructTrackProfileDocumentCacheFilePath($postId);
		if (empty($path) || !is_readable($path)) {
			return null;
		}

		$contents = file_get_contents($path);
		return Abp01_Route_Track_AltitudeProfile::fromSerializedDocument($contents);
	}

	private function _createUnitSystemInstanceOrThrow($unitSystem) {
        if (!Abp01_UnitSystem::isSupported($unitSystem)) {
            throw new InvalidArgumentException('Unsupported unit system: "' . $unitSystem . '"');
        }

        return Abp01_UnitSystem::create($unitSystem);
    }

	private function _storeTrackProfileDocument($postId, Abp01_Route_Track_AltitudeProfile $trackProfileDocument) {
		//Ensure the storage directory structure exists
		abp01_ensure_storage_directory();

		//Compute the path at which to store the cached file 
		//	and store the serialized track data
		$path = $this->_constructTrackProfileDocumentCacheFilePath($postId);
		if (!empty($path)) {
			file_put_contents($path, $trackProfileDocument->serializeDocument(), LOCK_EX);
		}
	}

	/**
	 * @return Abp01_Route_Track_Document|null
	 * @throws InvalidArgumentException 
	 */
	public function getOrCreateDisplayableTrackDocument(Abp01_Route_Track $track) {
		$simplifiedTrackDocument = $this->_getSimplifiedTrackDocument($track->getPostId());

		if (!($simplifiedTrackDocument instanceof Abp01_Route_Track_Document)) {
			$originalTrackDocument = $this->_getOriginalTrackDocument($track->getPostId());
			if (empty($originalTrackDocument)) {
				$trackFileMimeType = $track->getFileMimeType();
				$trackFilePath = $this->_constructTrackFilePath($track->getFileName());
				if (is_readable($trackFilePath)) {
					$originalTrackDocument = $this->_processTrackSourceFile($trackFilePath, 
						$trackFileMimeType);
					$this->_storeOriginalTrackDocument($track->getPostId(), 
						$originalTrackDocument);
				}
			}

			if (!empty($originalTrackDocument)) {
				$simplifiedTrackDocument = $originalTrackDocument
					->simplify(0.01);
				$this->_storeSimplifiedTrackDocument($track->getPostId(), 
					$simplifiedTrackDocument);
			}
		}

		return $simplifiedTrackDocument;
	}

	private function _getSimplifiedTrackDocument($postId) {
		$filePath = $this->_constructTrackDocumentCacheFilePath($postId);
		if (empty($filePath) || !is_readable($filePath)) {
			return null;
		}

		//First, try to fetch in-memory cached item
		$simplifiedTrackDocument = wp_cache_get($postId, 'abp01_cached_track_documents');
		if (empty($simplifiedTrackDocument)) {
			//If in-memory cached item does not exist, 
			//	deserialize it from disk cache
			//	and store it in-memory
			$contents = file_get_contents($filePath);
			$simplifiedTrackDocument = Abp01_Route_Track_Document::fromSerializedDocument($contents);
			wp_cache_set($postId, $simplifiedTrackDocument, 'abp01_cached_track_documents');
		}

		return $simplifiedTrackDocument;
	}

	private function _storeSimplifiedTrackDocument($postId, Abp01_Route_Track_Document $trackDocument) {
		//Ensure the storage directory structure exists
		abp01_ensure_storage_directory();

		//Compute the path at which to store the cached file 
		//	and store the serialized track data
		$path = $this->_constructTrackDocumentCacheFilePath($postId);
		if (!empty($path)) {
			file_put_contents($path, $trackDocument->serializeDocument(), LOCK_EX);
		}

		//Ensure in-memory copy is removed
		wp_cache_delete($postId, 'abp01_cached_track_documents');
	}

	public function deleteTrackFiles($postId) {
		//delete track file
		$trackFilePathPattern = $this->_constructGlobTrackFilePath($postId);
		$this->_deleteFilesByGlobPattern($trackFilePathPattern);

		//delete cached track file
		$cacheFilePattern = $this->_constructGlobTrackCacheFilePath($postId);
		$this->_deleteFilesByGlobPattern($cacheFilePattern);
	}

	private function _constructGlobTrackFilePath($postId) {
		$globTrackFileName = $this->_constructGlobTrackFileName($postId);
		return $this->_constructTrackFilePath($globTrackFileName);
	}

	private function _constructGlobTrackFileName($postId) {
		return $this->_constructTrackFileName($postId, '*');
	}

	private function _constructTrackFileName($postId, $extension) {
		return sprintf('track-%d.%s', 
			$postId, 
			$extension);
	}

	private function _constructTrackFilePath($trackFileName) {
		$tracksStorageDir = $this->_getTracksStorageDir();
		return wp_normalize_path($tracksStorageDir . '/' . $trackFileName);
	}

	private function _getTracksStorageDir() {
		return $this->_env->getTracksStorageDir();
	}

	private function _constructGlobTrackCacheFilePath($postId) {
		$globTrackCacheFileName = $this->_constructGlobTrackCacheFileName($postId);
		return $this->_constructTrackCacheFilePath($globTrackCacheFileName);
	}

	private function _constructTrackCacheFilePath($fileName) {
		$cacheStorageDir = $this->_getCacheStorageDir();
		return wp_normalize_path($cacheStorageDir . '/' . $fileName);
	}

	private function _getCacheStorageDir() {
		return $this->_env->getCacheStorageDir();
	}

	private function _constructGlobTrackCacheFileName($postId) {
		return sprintf('track*-%d.cache', $postId);
	}

	private function _deleteFilesByGlobPattern($globPathPattern) {
		abp01_delete_files_by_glob_pattern($globPathPattern);
	}

	private function _constructTrackDocumentCacheFilePath($postId) {
		$trackDocumentCacheFileName = $this->_constructTrackDocumentCacheFileName($postId);
		return $this->_constructTrackCacheFilePath($trackDocumentCacheFileName);
	}

	private function _constructTrackDocumentCacheFileName($postId) {
		return sprintf('track-%d.cache', $postId);
	}

	private function _constructTrackProfileDocumentCacheFilePath($postId) {
		$trackProfileFileName = $this->_constructTrackProfileCacheFileName($postId);
		return $this->_constructTrackCacheFilePath($trackProfileFileName);
	}

	private function _constructTrackProfileCacheFileName($postId) {
		return sprintf('track-profile-%d.cache', $postId);
	}

	public function constructTrackFilePathForPostId($postId, $trackFileMimeType) {
		if (empty($postId)) {
			throw new InvalidArgumentException('Post id may not be empty');
		}

		if (empty($trackFileMimeType)) {
			throw new InvalidArgumentException('Track file mime type may not be empty');
		}

		$extension = $this->_resolveFileExtensionForMimeType($trackFileMimeType);
		if (empty($extension)) {
			throw new InvalidArgumentException('Track file mime type <' . $trackFileMimeType . '> is not supported');
		}

		return $this->_constructTrackFilePathForPostId($postId, $extension);
	}

	private function _resolveFileExtensionForMimeType($trackFileMimeType) {
		return isset($this->_trackFileMapTypesToExtensionsMapping[$trackFileMimeType])
			? $this->_trackFileMapTypesToExtensionsMapping[$trackFileMimeType]
			: self::DEFAULT_FILE_EXTENSION;
	}

	private function _constructTrackFilePathForPostId($postId, $extension) {
		$trackFileName = $this->_constructTrackFileName($postId, 
			$extension);

		return $this->_constructTrackFilePath($trackFileName);
	}

	public function constructTempTrackFilePathForPostId($postId, $trackFileMimeType) {
		if (empty($postId)) {
			throw new InvalidArgumentException('Post id may not be empty');
		}

		if (empty($trackFileMimeType)) {
			throw new InvalidArgumentException('Track file mime type may not be empty');
		}
	
		return $this->_constructTrackFilePathForPostId($postId, self::DEFAULT_FILE_EXTENSION);
	}

	public function constructTrackFilePath(Abp01_Route_Track $track) {
		return $this->_constructTrackFilePath($track->getFileName());
	}
}