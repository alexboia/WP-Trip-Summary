<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

trait IncludesTestDataHelpers {
	use GenericTestHelpers;

	protected function _generateRandomIncludesItems() {
		$faker = $this->_getFaker();
		
		$items = array();
		$itemCount = $faker->numberBetween(10, 100);
		
		for ($iItem = 0; $iItem < $itemCount; $iItem ++) {
			$items[] = array(
				'path' => $faker->url,
				'is-leaflet-plugin' => $faker->boolean(),
				'needs-wrap' => $faker->boolean()
			);
		}

		return $items;
	}

	protected function _generateRandomLeafletPluginIncludesItems() {
		$randomItems = $this->_generateRandomIncludesItems();
		$countRandomItems = count($randomItems);

		for ($iItem = 0; $iItem < $countRandomItems; $iItem ++) {
			$randomItems[$iItem] = array_merge($randomItems[$iItem], array(
				'is-leaflet-plugin' => true,
				'needs-wrap' => true
			));
		}

		return $randomItems;
	}

	protected function _generateRandomNonLeafletPluginIncludesItems() {
		$randomItems = $this->_generateRandomIncludesItems();
		$countRandomItems = count($randomItems);

		for ($iItem = 0; $iItem < $countRandomItems; $iItem ++) {
			$randomItems[$iItem] = array_merge($randomItems[$iItem], array(
				'is-leaflet-plugin' => false,
				'needs-wrap' => false
			));
		}

		return $randomItems;
	}

	protected function _generateTestDependencyHandlesWithCallbackSelectionFromList(array $sourceDepsHandlesIds) {
		$faker = $this->_getFaker();
		$countDepsHandles = count($sourceDepsHandlesIds);
		$countSelectDepHandles = intdiv($countDepsHandles, 2);
		$depsHandles = array();
		
		$selectDepsHandlesIds = $faker->randomElements($sourceDepsHandlesIds, $countSelectDepHandles);

		for ($iDepHandle = 0; $iDepHandle < $countDepsHandles; $iDepHandle++) {
			$depHandleId = $sourceDepsHandlesIds[$iDepHandle];
			$depsHandles[] = array(
				'handle' => $depHandleId,
				'if' => function(Abp01_Env $env, Abp01_Settings $settings) use($depHandleId, $selectDepsHandlesIds) {
					return in_array($depHandleId, $selectDepsHandlesIds);
				}
			);
		}

		return array(
			'expectedHandlesIds' => $selectDepsHandlesIds,
			'handles' => $depsHandles
		);
	}

	protected function _generateTestDependencyHandlesWithAllTrueCallbacks() {
		return $this->_generateTestDependencyHandlesWithFixedBooleanResult(true);
	}

	protected function _generateTestDependencyHandlesWithAllFalseCallbacks() {
		return $this->_generateTestDependencyHandlesWithFixedBooleanResult(false);
	}

	private function _generateTestDependencyHandlesWithFixedBooleanResult($result) {
		$depsHandles = $this->_generateTestDependencyHandlesWithoutCallbackConditions();
		$countDepsHandles = count($depsHandles);
		for ($iDepHandle = 0; $iDepHandle < $countDepsHandles; $iDepHandle++) {
			$depsHandles[$iDepHandle]['if'] = function(Abp01_Env $env, Abp01_Settings $settings) use ($result) {
				return $result;
			};
		}
		return $depsHandles;
	}

	protected function _generateTestDependencyHandlesWithoutCallbackConditions() {
		$depHandles = array();
		$depsHandlesIds = $this->_generateRandomDepsHandlesIds();
		
		foreach ($depsHandlesIds as $depHandleId) {
			$depHandles[] = array(
				'handle' => $depHandleId,
				'if' => null
			);
		}

		return $depHandles;
	}

	protected function _generateRandomDepsHandlesIds() {
		$depHandlesIds = array();
		$faker = $this->_getFaker();
		
		$countDepsHandles = $faker->numberBetween(1, 100);
		for ($iDepHandle = 0; $iDepHandle < $countDepsHandles; $iDepHandle ++) {
			$depHandles[] = $faker->uuid;
		}

		return $depHandlesIds;
	}
}