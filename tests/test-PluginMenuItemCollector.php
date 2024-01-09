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

class PluginMenuItemCollectorTests extends WP_UnitTestCase {
	public function test_canCollectMenuItems_parentsOnly_oneAtATime_validMenuItems() {
		$collector = new Abp01_PluginMenuItemCollector();
		$parentMenuItems = $this->_getSampleParentMenuItems();

		foreach ($parentMenuItems as $menuItem) {
			$collector->collectMenuItems(array($menuItem));
		}

		$this->_assertCollectedMenuItemsMatchParents($parentMenuItems, 
			$collector->getCollectedMenuItems());
	}

	private function _assertCollectedMenuItemsMatchParents(array $sampleParentMenuItems, array $collectedMenuItems) {
		$this->assertEquals(count($collectedMenuItems), 
			count($sampleParentMenuItems));
		
		foreach ($sampleParentMenuItems as $parentMenuItem) {
			$this->assertArrayHasKey($parentMenuItem['slug'], $collectedMenuItems);
			$collectedMenuItem = $collectedMenuItems[$parentMenuItem['slug']];
			
			$this->assertNotEmpty($collectedMenuItem);
			$this->assertEmpty($collectedMenuItem['children']);

			$this->_assertMenuItemsMatch($parentMenuItem, 
				$collectedMenuItem);
		}
	}

	private function _assertMenuItemsMatch(array $expected, array $actual) {
		$commonKeys = array(
			'slug',
			'pageTitle',
			'menuTitle',
			'capability',
			'callback'
		);

		$parentMenuItemKeys = array(
			'reRegisterAsChildWithMenuTitle',
			'iconUrl',
			'position'
		);

		$childMenuItemKeys = array(
			'parent'
		);

		foreach ($commonKeys as $commonKey) {
			$this->assertArrayHasKey($commonKey, $actual);
			$this->assertEquals($expected[$commonKey], $actual[$commonKey]);
		}

		foreach ($parentMenuItemKeys as $parentMenuItemKey) {
			if (isset($expected[$parentMenuItemKey])) {
				$this->assertArrayHasKey($parentMenuItemKey, $actual);
				$this->assertEquals($expected[$parentMenuItemKey], $actual[$parentMenuItemKey]);
			} else {
				$this->assertArrayNotHasKey($parentMenuItemKey, $actual);
			}
		}

		foreach ($childMenuItemKeys as $childMenuItemKey) {
			if (isset($expected[$childMenuItemKey])) {
				$this->assertArrayHasKey($childMenuItemKey, $actual);
				$this->assertEquals($expected[$childMenuItemKey], $actual[$childMenuItemKey]);
			} else {
				$this->assertArrayNotHasKey($childMenuItemKey, $actual);
			}
		}

		if (isset($expected['children'])) {
			$this->assertArrayHasKey('children', $actual);
			$this->assertEquals(count($expected['children']), count($actual['children']));
			foreach ($expected['children'] as $expectedChildMenuItem) {
				$this->_assertChildMenuItemsListHasExpectedChild($expectedChildMenuItem, $actual['children']);
			}
		}
	}

	private function _assertChildMenuItemsListHasExpectedChild(array $expectedChildMenuItem, array $actualChildMenuItemList) {
		$foundActualChildMenuItem = null;
		foreach ($actualChildMenuItemList as $actualChildMenuItem) {
			if ($actualChildMenuItem['slug'] == $expectedChildMenuItem['slug']) {
				$foundActualChildMenuItem = $actualChildMenuItem;
				break;
			}
		}

		if ($foundActualChildMenuItem) {
			$this->_assertMenuItemsMatch($expectedChildMenuItem, $foundActualChildMenuItem);
		} else {
			$this->fail('Expected menu item <' . $expectedChildMenuItem['slug'] . '> not found.');
		}
	}

	public function test_canCollectMenuItems_parentsOnly_allAtOnce_validMenuItems() {
		$collector = new Abp01_PluginMenuItemCollector();
		$parentMenuItems = $this->_getSampleParentMenuItems();

		$collector->collectMenuItems($parentMenuItems);

		$this->_assertCollectedMenuItemsMatchParents($parentMenuItems, 
			$collector->getCollectedMenuItems());
	}

	public function test_canCollectMenuItems_parentsBeforeChildren_validMenuItems() {
		$collector = new Abp01_PluginMenuItemCollector();
		$parentMenuItems = $this->_getSampleParentMenuItems();
		$childMenuItems = $this->_getSampleChildMenuItems();

		$collector->collectMenuItems($parentMenuItems);
		$collector->collectMenuItems($childMenuItems);

		$this->_assertCollectMenuMatchesExpectedStructure($this->_getExpectedMenuStructure(), 
			$collector->getCollectedMenuItems());
	}

	private function _assertCollectMenuMatchesExpectedStructure(array $expectedMenuItems, array $collectedMenuItems) {
		$this->assertEquals(count($expectedMenuItems), 
			count($collectedMenuItems));

		foreach ($expectedMenuItems as $slug => $expectedMenuItem) {
			$this->assertArrayHasKey($slug, $collectedMenuItems);
			$collectedMenuItem = $collectedMenuItems[$slug];
			$this->assertNotEmpty($collectedMenuItem);

			$this->_assertMenuItemsMatch($expectedMenuItem, 
				$collectedMenuItem);
		}
	}

	public function test_canCollectMenuItems_childrenBeforeParents_validMenuItems() {
		$collector = new Abp01_PluginMenuItemCollector();
		$parentMenuItems = $this->_getSampleParentMenuItems();
		$childMenuItems = $this->_getSampleChildMenuItems();

		$collector->collectMenuItems($childMenuItems);
		$collector->collectMenuItems($parentMenuItems);

		$this->_assertCollectMenuMatchesExpectedStructure($this->_getExpectedMenuStructure(), 
			$collector->getCollectedMenuItems());
	}

	public function test_canCollectMenuItems_invalidMenuItems() {
		$collector = new Abp01_PluginMenuItemCollector();
		foreach ($this->_getSampleInvalidMenuItems() as $invalidMenuItem) {
			$raisedException = null;
			try {
				$collector->collectMenuItems(array($invalidMenuItem));
			} catch (Abp01_Exception $exc) {
				$raisedException = $exc;
			}
			
			$this->assertInstanceOf(Abp01_Exception::class, $raisedException);
			$this->assertEquals(0, count($collector->getCollectedMenuItems()));
		}
	}

	private function _getSampleInvalidMenuItems() {
		return array(
			array(
				'slug' => null,
				'parent' => 'parent1',
				'pageTitle' => 'Child page 1',
				'menuTitle' => 'Child menu page 1',
				'capability' => 'childcap1',
				'callback' => 'abp01_noop'
			),
			array(
				'slug' => 'child1',
				'parent' => 'parent1',
				'pageTitle' => null,
				'menuTitle' => 'Child menu page 1',
				'capability' => 'childcap1',
				'callback' => 'abp01_noop'
			),
			array(
				'slug' => 'child1',
				'parent' => 'parent1',
				'pageTitle' => 'Child page 1',
				'menuTitle' => null,
				'capability' => 'childcap1',
				'callback' => 'abp01_noop'
			),
			array(
				'slug' => 'child1',
				'parent' => 'parent1',
				'pageTitle' => 'Child page 1',
				'menuTitle' => 'Child menu page 1',
				'capability' => null,
				'callback' => 'abp01_noop'
			),
			array(
				'slug' => 'child1',
				'parent' => 'parent1',
				'pageTitle' => 'Child page 1',
				'menuTitle' => 'Child menu page 1',
				'capability' => 'childcap1',
				'callback' => 'abp01_bogus_callback1234'
			),
			array(
				'slug' => 'child1',
				'parent' => 'parent1',
				'pageTitle' => 'Child page 1',
				'menuTitle' => 'Child menu page 1',
				'capability' => 'childcap1',
				'callback' => null
			),
			array(
				'slug' => 'child1',
				'parent' => 'parent1',
				'pageTitle' => 'Child page 1',
				'menuTitle' => 'Child menu page 1',
				'capability' => 'childcap1',
				'callback' => 'abp01_noop',
				'reRegisterAsChildWithMenuTitle' => 'Should not be here'
			)
		);
	}

	private function _getExpectedMenuStructure() {
		$expectedMenu = array();
		$parentMenuItems = $this->_getSampleParentMenuItems();
		$childMenuItems = $this->_getSampleChildMenuItems();

		foreach ($parentMenuItems as $parentMenuItem) {
			$expectedMenu[$parentMenuItem['slug']] = array_merge($parentMenuItem, array(
				'children' => array()
			));
		}

		foreach ($childMenuItems as $childMenuItem) {
			$expectedMenu[$childMenuItem['parent']]['children'][] = $childMenuItem;
		}

		return $expectedMenu;
	}

	private function _getSampleParentMenuItems() {
		return array(
			array(
				'slug' => 'parent1',
				'pageTitle' => 'Parent page 1',
				'menuTitle' => 'Parent menu page 1',
				'capability' => 'parentcap1',
				'callback' => 'abp01_noop',
				'iconUrl' => 'iconparent1',
				'position' => 10,
				'reRegisterAsChildWithMenuTitle' => 'Parent page 1'
			),
			array(
				'slug' => 'parent2',
				'pageTitle' => 'Parent page 2',
				'menuTitle' => 'Parent menu page 2',
				'capability' => 'parentcap2',
				'callback' => 'abp01_noop',
				'iconUrl' => 'iconparent2',
				'position' => 11
			)
		);
	}

	private function _getSampleChildMenuItems() {
		return array(
			array(
				'slug' => 'child1',
				'parent' => 'parent1',
				'pageTitle' => 'Child page 1',
				'menuTitle' => 'Child menu page 1',
				'capability' => 'childcap1',
				'callback' => 'abp01_noop'
			),

			array(
				'slug' => 'child2',
				'parent' => 'parent1',
				'pageTitle' => 'Child page 2',
				'menuTitle' => 'Child menu page 2',
				'capability' => 'childcap2',
				'callback' => 'abp01_noop'
			),

			array(
				'slug' => 'child3',
				'parent' => 'parent2',
				'pageTitle' => 'Child page 3',
				'menuTitle' => 'Child menu page 3',
				'capability' => 'childcap3',
				'callback' => 'abp01_noop'
			)
		);
	}
	
	public function noop() {
		return;
	}
}