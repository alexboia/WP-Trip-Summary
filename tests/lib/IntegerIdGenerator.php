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

class IntegerIdGenerator {
    use GenericTestHelpers;

    /**
     * @var array
     */
    private $_generatedIds = array();

    /**
     * @var array
     */
    private $_excludedIds = array();

    private $_range;

    public function __construct($range = 1000, array $initiallyExcludedIds = array()) {
        $this->_range = $range;
        $this->_addExcludedIds($initiallyExcludedIds);
    }

    public function setExcludedIds(array $exludeIds) {
        $this->_excludedIds = array();
        $this->_addExcludedIds($exludeIds);
    }

    private function _addExcludedIds(array $excludeIds) {
        $this->_excludedIds = array_merge($this->_excludedIds, $excludeIds);
        $this->_excludedIds = array_unique($this->_excludedIds);
    }

    public function generateId(array $excludeIds = array()) {
        $this->_addExcludedIds($excludeIds);

        $nextId = $this->_generateNextId();
        $this->_registerGeneratedId($nextId);

        return $nextId;
    }

    private function _generateNextId() {
        $max = $this->_getMaxExcludedId();
        return $this->_getFaker()->numberBetween($max + 1, $max + $this->_range + 1);
    }

    private function _registerGeneratedId($nextId) {
        $this->_generatedIds[] = $nextId;
        $this->_excludedIds[] = $nextId;
    }

    private function _getMaxExcludedId() {
        return !empty($this->_excludedIds) 
            ? max($this->_excludedIds) 
            : 0;
    }

    public function _getGeneratedIds() {
        return $this->_generatedIds;
    }

    public function reset() {
        $this->_generatedIds = array();
        $this->_excludedIds = array();
    }
}