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

class Abp01_Io_DirectoryAssetInstaller {
	public static function installAssetsForDirectory(string $targetDir, array $assetsDesc): bool {
		if (!is_dir($targetDir)) {
			return false;
		}

		if (empty($assetsDesc)) {
			return true;
		}

		foreach ($assetsDesc as $asset) {
			if (empty($asset)) {
				continue;
			}

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
				if (!is_dir($assetPath)) {
					@mkdir($assetPath);
				}
				$result = is_dir($assetPath);
			}

			if (!$result) {
				return false;
			}
		}

		return true;
	}
}