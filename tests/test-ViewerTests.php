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

class ViewerTests extends WP_UnitTestCase {
    use GenericTestHelpers;

    private const ADDITIONAL_TABS_FILTER_NAME = 'abp01_additional_frontend_viewer_tabs';

    protected function tearDown(): void {
        parent::tearDown();
        Mockery::close();
    }

    public function test_canGetAvailableTabs() {
        $availableTabs = Abp01_Viewer::getAvailableTabsInfo();

        $this->assertNotEmpty($availableTabs);
        $this->assertEquals(3, count($availableTabs));
        $this->assertArrayHasKey(Abp01_Viewer::TAB_INFO, $availableTabs);
        $this->assertArrayHasKey(Abp01_Viewer::TAB_MAP, $availableTabs);
    }

    public function test_canCheckIfTabIsSupportted_validTabName() {
        foreach (Abp01_Viewer::getAvailableTabsInfo() as $tab => $label) {
            $this->assertTrue(Abp01_Viewer::isTabSupported($tab));
        }
    }

    public function test_tryCheckIfTabIsSupportted_invalidTabName() {
        $faker = $this->_getFaker();
        $validTabs = array_keys(Abp01_Viewer::getAvailableTabsInfo());

        for ($i = 0; $i < 10; $i ++) {
            $invalidTab = $faker->randomAscii;
            while (in_array($invalidTab, $validTabs)) {
                $invalidTab = $faker->randomAscii;
            }

            $this->assertFalse(Abp01_Viewer::isTabSupported($invalidTab));
        }
    }

    public function test_getAvailableTabsInfo_mergesOnlyValidAdditionalTabs_withoutOverridingDefaults() {
        $availableTabsBeforeFilter = Abp01_Viewer::getAvailableTabsInfo();
        $customTabCode = 'abp01-tab-test-additional';
        $invalidTabCode = 'abp01-tab-test-invalid';
        $customTabLabel = 'Test additional tab';

        $filter = static function($additionalTabs, $postId) use (
            $customTabCode,
            $invalidTabCode,
            $customTabLabel
        ) {
            return array(
                Abp01_Viewer::TAB_INFO => array(
                    'label' => 'Attempted override'
                ),
                $customTabCode => array(
                    'label' => $customTabLabel
                ),
                $invalidTabCode => array(),
                '' => array(
                    'label' => 'Tab without a code'
                )
            );
        };

        add_filter(
            self::ADDITIONAL_TABS_FILTER_NAME, 
            $filter, 
            PHP_INT_MAX, 
            2
        );
        
        try {
            $availableTabs = Abp01_Viewer::getAvailableTabsInfo();

            $this->assertSame(
                $availableTabsBeforeFilter[Abp01_Viewer::TAB_INFO],
                $availableTabs[Abp01_Viewer::TAB_INFO]
            );

            $this->assertSame(
                $availableTabsBeforeFilter[Abp01_Viewer::TAB_MAP],
                $availableTabs[Abp01_Viewer::TAB_MAP]
            );

            $this->assertSame($customTabLabel, $availableTabs[$customTabCode]);
            $this->assertArrayNotHasKey($invalidTabCode, $availableTabs);
            $this->assertArrayNotHasKey('', $availableTabs);
        } finally {
            remove_filter(
                self::ADDITIONAL_TABS_FILTER_NAME, 
                $filter, 
                PHP_INT_MAX
            );
        }
    }

    public function test_render_reusesCachedContent_forSamePostId() {
        $data = $this->_createRenderableData(123);
        
        $teaserHtml = '<div>Test teaser</div>';
        $viewerHtml = '<div>Test viewer</div>';

        $view = $this->_createViewMock($data, $teaserHtml, $viewerHtml);
        $viewer = new Abp01_Viewer($view);

        $firstResult = $viewer->render($data);
        $secondResult = $viewer->render($data);

        $this->assertSame(array(
            'teaserHtml' => $teaserHtml,
            'viewerHtml' => $viewerHtml
        ), $firstResult);

        $this->assertSame($firstResult, 
            $secondResult);
    }

    public function test_renderAndAttachToContent_prependsTeaserAndAppendsViewer_whenNoShortcodeExists() {
        $data = $this->_createRenderableData(456);
        
        $teaserHtml = '<div>Test teaser</div>';
        $viewerHtml = '<div>Test viewer</div>';
        $postContent = '<p>Test post content</p>';
        
        $view = $this->_createViewMock($data, $teaserHtml, $viewerHtml);
        $viewer = new Abp01_Viewer($view);

        $result = $viewer->renderAndAttachToContent($data, 
            $postContent);

        $this->assertSame($teaserHtml . $postContent . $viewerHtml, 
            $result);
    }

    private function _createRenderableData(int $postId): stdClass {
        $data = new stdClass();
        $data->postId = $postId;
        $data->info = (object) array(
            'exists' => true
        );
        $data->track = (object) array(
            'exists' => false
        );

        return $data;
    }

    /**
     * @return Abp01_View|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private function _createViewMock(stdClass $data, string $teaserHtml, string $viewerHtml) {
        $view = Mockery::mock(Abp01_View::class);
        $view->shouldReceive('renderFrontendTeaser')
            ->once()
            ->with($data)
            ->andReturn($teaserHtml);
        $view->shouldReceive('renderFrontendViewer')
            ->once()
            ->with($data)
            ->andReturn($viewerHtml);

        return $view;
    }
}
