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

/**
 * @package WP-Trip-Summary
 */
class Abp01_Route_Track {
	private $_postId;

	private $_bounds;

	private $_fileName;

	private $_fileMimeType;

	public $maxAlt;

	public $minAlt;

	public function __construct($postId, $fileName, $fileMimeType, Abp01_Route_Track_Bbox $bounds, $minAlt = 0, $maxAlt = 0) {
		if (empty($postId)) {
			throw new InvalidArgumentException('Post ID cannot be empty');
		}

		if (empty($fileName)) {
			throw new InvalidArgumentException('File cannot be empty');
		}

		if (empty($fileMimeType)) {
			throw new InvalidArgumentException('File mime type cannot be empty');
		}

		if ($bounds == null) {
			throw new InvalidArgumentException('The bounds cannot be empty');
		}

		$this->_postId = $postId;
		$this->_fileName = $fileName;
		$this->_fileMimeType = $fileMimeType;
		$this->_bounds = $bounds;

		$this->minAlt = $minAlt;
		$this->maxAlt = $maxAlt;
	}

	public function equals(Abp01_Route_Track $other) {
		return $other->_postId == $this->_postId
			&& $other->_bounds->equals($this->_bounds)
			&& $other->_fileName === $this->_fileName
			&& $other->_fileMimeType === $this->_fileMimeType
			&& abs($other->minAlt - $this->minAlt) < 0.1
			&& abs($other->maxAlt - $this->maxAlt) < 0.1;
	}

	/**
	 * @return Abp01_Route_Track_Info 
	 * @throws InvalidArgumentException 
	 */
	public function constructDisplayableInfo($targetSystem) {
		$minAlt = new Abp01_UnitSystem_Value_Height($this->getMinimumAltitude());
		$maxAlt = new Abp01_UnitSystem_Value_Height($this->getMaximumAltitude());

		$minAlt = $minAlt->convertTo($targetSystem);
		$maxAlt = $maxAlt->convertTo($targetSystem);

		return new Abp01_Route_Track_Info($minAlt, 
			$maxAlt);
	}

	public function getMinimumAltitude() {
		return $this->minAlt;
	}

	public function getMaximumAltitude() {
		return $this->maxAlt;
	}

	public function getBounds() {
		return $this->_bounds;
	}

	public function getFileName() {
		return $this->_fileName;
	}

	public function getFileMimeType() {
		return $this->_fileMimeType;
	}

	public function getPostId() {
		return $this->_postId;
	}

	public function toPlainObject() {
		$track = new stdClass();
		$track->minAlt = $this->minAlt;
		$track->maxAlt = $this->maxAlt;
		$track->bounds = $this->_bounds->toPlainObject();
		$track->postId = $this->_postId;
		return $track;
	}
}