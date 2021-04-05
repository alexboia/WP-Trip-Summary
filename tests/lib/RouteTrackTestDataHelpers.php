<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

trait RouteTrackTestDataHelpers {
    use GenericTestHelpers;

    public function _generateRandomRouteTracks() {
        $postIds = array();
        $routeTracks = array();
        $faker = self::_getFaker();

        $deltaLat = $faker->numberBetween(2, 10);
        $deltaLng = $faker->numberBetween(2, 10);

        for ($i = 0; $i < 10; $i++) {
            $postId = $this->_generatePostId($postIds);
            $postIds[] = $postId;

            $routeTracks[] = array(
                $this->_generateRandomRouteTrack($postId, $deltaLat, $deltaLng)
            );
        }

        return $routeTracks;
    }

    /**
     * @return Abp01_Route_Track_Document
     */
    protected function _readDocumentFromCachedFile($postId) {
        $filePath = $this->_getCachedTrackDocumentFilePath($postId);
        return is_readable($filePath) 
            ? Abp01_Route_Track_Document::fromSerializedDocument(file_get_contents($filePath)) 
            : null;
    }

    protected function _generateRandomRouteTrack($postId, $fileNameExtension = null) {
        $faker = self::_getFaker();
        
        if (func_get_args() == 3) {
            $deltaLat = func_get_arg(1);
            $deltaLng = func_get_arg(2);
        } else {
            $deltaLat = $faker->numberBetween(2, 10);
            $deltaLng = $faker->numberBetween(2, 10);
        }

        $minLat = $faker->randomFloat(5, -85, 85 - $deltaLat * 2);
        $minLng = $faker->randomFloat(5, -180, 180 - $deltaLng * 2);

        $maxLat = $minLat + $deltaLat;
        $maxLng = $minLng + $deltaLng;
        
        if ($fileNameExtension == null) {
            $fileNameExtension = 'gpx';
        }

        $fileName = $this->_getTrackDocumentFileName($postId, 
            $fileNameExtension);

        $minAltitude = $faker->randomFloat(3, 0, 4000);
        $maxAltitude = $minAltitude + $faker->randomFloat(3, 0, 4000);

        $bbox = new Abp01_Route_Track_Bbox($minLat, 
            $minLng, 
            $maxLat, 
            $maxLng);

        return new Abp01_Route_Track($postId, 
            $fileName, 
            $faker->mimeType,
            $bbox, 
            $minAltitude, 
            $maxAltitude);
    }

    protected function _generateRandomRouteTrackWithMimeType($postId, $mimeType, $fileNameExtension) {
        $sourceTrack = $this->_generateRandomRouteTrack($postId, $fileNameExtension);
        $track = new Abp01_Route_Track($postId, 
            $sourceTrack->getFileName(), 
            $mimeType, 
            $sourceTrack->getBounds(), 
            $sourceTrack->getMinimumAltitude(), 
            $sourceTrack->getMaximumAltitude());
        return $track;
    }

    protected function _storeTrackDocument($postId, $documentContent, $extension) {
        $path = $this->_getTrackDocumentFilePath($postId, $extension);
        file_put_contents($path, $documentContent);
    }

    protected function _prepareAndStoreCachedTrackDocument($postId, $documentContent, Abp01_Route_Track_DocumentParser $parser) {
        $path = $this->_getCachedOriginalTrackDocumentFilePath($postId);
        $trackDocument = $parser->parse($documentContent);
        file_put_contents($path, $trackDocument->serializeDocument());
    }

    protected function _getTrackDocumentFilePath($postId, $extension) {
        return wp_normalize_path($this->_getEnv()->getTracksStorageDir() . '/' 
            . $this->_getTrackDocumentFileName($postId, $extension));
    }

    private function _getTrackDocumentFileName($postId, $extension) {
        return sprintf('track-%s.%s', 
            $postId, 
            $extension);
    }
    
    private function _getCachedOriginalTrackDocumentFileName($postId) {
        return sprintf('track-original-%d.cache', $postId);
    }

    protected function _getCachedOriginalTrackDocumentFilePath($postId) {
        return wp_normalize_path($this->_getEnv()->getCacheStorageDir() . '/' 
            . $this->_getCachedOriginalTrackDocumentFileName($postId));
    }

    protected function _getCachedTrackDocumentFileName($postId) {
        return sprintf('track-%d.cache', $postId);
    }

    protected function _getCachedTrackDocumentFilePath($postId) {
        return wp_normalize_path($this->_getEnv()->getCacheStorageDir() . '/' 
            . $this->_getCachedTrackDocumentFileName($postId));
    }

    abstract protected function _generatePostId($excludeAdditionalIds = null);
}