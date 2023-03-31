<?php
/**
 * Copyright (c) 2014-2023 Alexandru Boia
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

trait GenericTestHelpers {
	private static $_faker = null;

	protected function _randomFileName() {
		$faker = self::_getFaker();
		$extension = $faker->fileExtension;
		$filenameWithoutExtension = $faker->uuid;

		return sprintf('%s.%s', 
			$filenameWithoutExtension, 
			$extension);
	}

	protected function _getEnqueuedStyleUrl($handle) {
		global $wp_styles;
		return $wp_styles->registered[$handle]->src;
	}

	protected function _ensureDirExists($dir) {
		if (!is_dir($dir)) {
			@mkdir($dir);
		}
	}

	protected function _recursiveCopyDirectory($source, $destination) {
		$entries = @scandir($source);
		if ($entries === false) {
			return;
		}

		if (!is_dir($destination)) {
			@mkdir($destination, 0777);
		}

		foreach ($entries as $entry) {
			if ($entry != '.' && $entry != '..') {
				$sourceEntry = $source . '/' . $entry;
				$destinationEntry = $destination . '/' . $entry;

				if (is_dir($sourceEntry)) {
					if (!is_dir($destinationEntry)) {
						@mkdir($destinationEntry, 0777);
					}

					$this->_recursiveCopyDirectory($sourceEntry, $destinationEntry);
				} else {
					copy($sourceEntry, $destinationEntry);
				}
			}
		}
	}

	protected function _removeDirectoryRecursively($target) {
		$entries = @scandir($target, SCANDIR_SORT_ASCENDING);
		if ($entries === false) {
			return;
		}

		foreach ($entries as $entry) {
			if ($entry != '.' && $entry != '..') {
				$toRemove = $target . '/' . $entry;
				if (is_dir($toRemove)) {
					$this->_removeDirectoryRecursively($toRemove);
				} else {
					@unlink($toRemove);
				}
			}
		}

		@rmdir($target);
	}

	protected function _generateNonEmptyAscii() {
		$faker = $this->_getFaker();
		$ascii = $faker->randomAscii;
		while (empty($ascii)) {
			$ascii = $faker->randomAscii;
		}
		return $ascii;
	}

	/**
	 * @return \Faker\Generator
	 */
	protected static function _getFaker() {
		if (self::$_faker == null) {
			self::$_faker = Faker\Factory::create();
			self::$_faker->addProvider(new GpsDocumentFakerDataProvider(self::$_faker, 0.1));
			self::$_faker->addProvider(new GpxDocumentFakerDataProvider(self::$_faker));
			self::$_faker->addProvider(new GeoJsonDocumentFakerDataProvider(self::$_faker));
		}

		return self::$_faker;
	}

	protected function _assertFileNotEmpty($filePath) {
		return filesize($filePath) > 0;
	}

	protected function _removeAllFiles($targetDir, $globPattern) {
		$files = glob($targetDir . '/' . $globPattern);
		if (is_array($files)) {
			foreach ($files as $file) {
				@unlink($file);
			}
		}
	}

	protected function _createWpPosts($postIds) {
		$db = $this->_getDb();
		$postsTableName = $this->_getEnv()->getWpPostsTableName();

		foreach ($postIds as $postId) {
			$db->insert($postsTableName, $this->_generateWpPostData($postId));
		}
	}

	protected function _generateWpPostData($postId, $output = ARRAY_A) {
		$faker = self::_getFaker();
		$data = array(
			'ID' => $postId,
			'post_title' => $faker->words(3, true),
			'post_content' => $faker->words(10, true),
			'guid' => $faker->uuid
		);

		if ($output == OBJECT) {
			$data = (object)$data;
		}

		return $data;
	}

	protected function _extensionCorrectlyLoaded($extension, 
		array $checkFunctionsDefined = array(), 
		array $checkClasesDefined = array()) {

		$result = extension_loaded($extension);
		if ($result) {
			$allFunctionsDefined = true;
			foreach ($checkFunctionsDefined as $f) {
				$allFunctionsDefined = $this->_isFunctionDefined($f);
				if (!$allFunctionsDefined) {
					break;
				}
			}

			$result = $result 
				&& $allFunctionsDefined;

			if ($result) {
				$allClassesDefined = true;
				foreach ($checkClasesDefined as $c) {
					$allClassesDefined = $this->_isClassDefinedAlt($c);
					if (!$allClassesDefined) {
						break;
					}
				}

				$result = $result 
					&& $allClassesDefined;
			}
		}

		return $result;
	}

	protected function _isFunctionDefined($functionName) {
		$defined = get_defined_functions();
		return (isset($defined['internal']) && in_array($functionName, $defined['internal'], true)) 
			|| (isset($defined['user']) && in_array($functionName, $defined['user'], true));
	}

	protected function _isClassDefinedAlt($className) {
		return in_array($className, get_declared_classes(), true);
	}

	protected function _countObjectVars($obj) {
		return count(get_object_vars($obj));
	}

	public static function emptyValuesProvider() {
		return array(array(
			''
		), array(
			null
		));
	}

	protected function _getRouteManager() {
		return abp01_get_route_manager();
	}

	protected function _getEnv() {
		return abp01_get_env();
	}

	protected function _getDb() {
		return $this->_getEnv()->getDb();
	}

	protected function _getInstaller() {
		return new Abp01_Installer();
	}
}