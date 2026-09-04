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

declare(strict_types = 1);

if (!defined('ABP01_LOADED')) {
	exit ;
}

class Abp01_MaintenanceTool_DetectMissingTracks implements Abp01_MaintenanceTool {
	private Abp01_Route_Manager $_routeManager;

	private Abp01_Route_Track_FileNameProvider $_trackFileNameProvider;

	private Abp01_PostInfoProvider $_postInfoProvider;
	
	public function __construct(Abp01_Route_Manager $routeManager, 
			Abp01_Route_Track_FileNameProvider $trackFileNameProvider) {
		$this->_routeManager = $routeManager;
		$this->_trackFileNameProvider = $trackFileNameProvider;		
		$this->_postInfoProvider = new Abp01_PostInfoProvider();
	}

	public function execute(array $parameters = array()): Abp01_MaintenanceTool_Result { 
		$shouldFixDb = isset($parameters['fixDb'])
			? $parameters['fixDb'] === true
			: false;

		$postIdsWithRouteTracks = $this->_getPostIdsWithRouteTracks();
		$postsWithMissingTrackFiles = $this->_testPostsForTrackFiles($postIdsWithRouteTracks);
		if (!empty($postsWithMissingTrackFiles)) {
			if ($shouldFixDb) {
				$this->_fixDbForMissingTrackFiles($postsWithMissingTrackFiles);
			}
		}

		return new Abp01_MaintenanceTool_Result(true, array(
			'posts' => $this->_getPostsInfo($postsWithMissingTrackFiles)
		));
	}

	private function _getPostIdsWithRouteTracks(): array {
		$postIds = $this->_routeManager->getAllPostsWithRouteTracks();
		return array_map('intval', $postIds);
	}

	private function _testPostsForTrackFiles(array $postIds): array {
		$postsWithMissingTrackFiles = array();
		foreach ($postIds as $postId) {
			$trackFilePath = $this->_constructTrackFilePath($postId);
			if (!is_readable($trackFilePath)) {
				$postsWithMissingTrackFiles[] = $postId;
			}
		}
		return $postsWithMissingTrackFiles;
	}

	private function _constructTrackFilePath(int $postId): string {
		$track = $this->_getTrack($postId);
		return $this->_trackFileNameProvider->constructTrackFilePath($track);
	}

	private function _getTrack(int $postId): Abp01_Route_Track {
		return $this->_routeManager
			->getRouteTrack($postId);
	}

	private function _fixDbForMissingTrackFiles(array $postIdsWithRouteTracks): void {
		foreach ($postIdsWithRouteTracks as $postId) {
			$this->_routeManager->deleteRouteTrack($postId);
		}
	}

	private function _getPostsInfo(array $postIds): array {
		return $this->_postInfoProvider
			->getInfoForPostIds($postIds);
	}

	public function getName(): string { 
		return __('Detect missing track files', 'abp01-trip-summary')
			?? 'Detect missing track files';
	}

	public function getId(): string {
		return 'detect-missing-track-files';
	}
}