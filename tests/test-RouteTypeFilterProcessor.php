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

class RouteTypeFilterProcessorTests extends WP_UnitTestCase {
	private $_oldGlobals = array();

	private $_registeredPostTypes = array();

	private $_routePostIds = array();

	protected function setUp(): void {
		parent::setUp();
		foreach (array('_GET', 'pagenow', 'current_screen', 'typenow', 'taxnow', 'wp_query', 'wp_the_query') as $name) {
			$this->_oldGlobals[$name] = array(array_key_exists($name, $GLOBALS), $GLOBALS[$name] ?? null);
		}

		// WP_UnitTestCase restores the original hooks after each test.
		remove_all_filters('posts_join');
		remove_all_filters('posts_clauses');
		$filter = new Abp01_Display_PostListing_FilterDropdown('abp01_route_type', null,
			new Abp01_Display_PostListing_RouteTypeFilterDataSource(),
			new Abp01_Display_PostListing_RouteTypeFilterProcessor());
		$filter->process();

		$_GET = array();
		$GLOBALS['pagenow'] = 'edit.php';
		set_current_screen('edit-post');
	}

	protected function tearDown(): void {
		global $wpdb;
		// Plugin tables can use MyISAM, so transaction rollback alone is insufficient.
		foreach ($this->_routePostIds as $postId) {
			$wpdb->delete(abp01_get_env()->getRouteDetailsTableName(), array('post_ID' => $postId));
		}
		foreach ($this->_registeredPostTypes as $postType) {
			unregister_post_type($postType);
		}
		foreach ($this->_oldGlobals as $name => $state) {
			if ($state[0]) {
				$GLOBALS[$name] = $state[1];
			} else {
				unset($GLOBALS[$name]);
			}
		}
		parent::tearDown();
	}

	private function _createRoutePost(array $args = array(), $routeType = 'bike'): int {
		global $wpdb;
		$postId = self::factory()->post->create(array_merge(array(
			'post_title' => 'Mountain route',
			'post_content' => '<p>A day outdoors.</p>',
			'post_status' => 'publish',
			'post_date' => '2020-06-15 12:00:00'
		), $args));
		if ($routeType !== null) {
			$this->assertSame(1, $wpdb->insert(abp01_get_env()->getRouteDetailsTableName(), array(
				'post_ID' => $postId,
				'route_type' => $routeType,
				'route_data_serialized' => '{}'
			)));
			$this->_routePostIds[] = $postId;
		}
		return $postId;
	}

	private function _queryPosts(array $args = array(), bool $mainQuery = true): WP_Query {
		$query = new WP_Query();
		if ($mainQuery) {
			$GLOBALS['wp_the_query'] = $query;
			$GLOBALS['wp_query'] = $query;
		}
		$query->query(array_merge(array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'cache_results' => false
		), $args));
		return $query;
	}

	public function test_mainListing_preservesExistingConditions(): void {
		$author = self::factory()->user->create();
		$otherAuthor = self::factory()->user->create();
		$expected = $this->_createRoutePost(array('post_author' => $author));
		foreach (array(
			array('post_type' => 'page'),
			array('post_status' => 'draft'),
			array('post_date' => '2019-06-15 12:00:00'),
			array('post_author' => $otherAuthor),
			array('post_title' => 'Coastal route')
		) as $overrides) {
			$this->_createRoutePost(array_merge(array('post_author' => $author), $overrides));
		}
		$this->_createRoutePost(array('post_author' => $author), 'hiking');
		$this->_createRoutePost(array('post_author' => $author), null);
		$_GET['abp01_route_type'] = 'bike';

		$query = $this->_queryPosts(array('year' => 2020, 'author' => $author, 's' => 'Mountain'));
		$this->assertSame(array($expected), $query->posts);
	}

	public static function getSupportedListings(): array {
		return array(
			'posts and biking' => array('post', 'bike'),
			'pages and hiking' => array('page', 'hiking'),
			'custom posts and train rides' => array('wpts_trip', 'trainRide')
		);
	}

	/** @dataProvider getSupportedListings */
	public function test_mainListing_filtersSupportedPostTypes(string $postType, string $routeType): void {
		if ($postType === 'wpts_trip') {
			register_post_type($postType, array('public' => true));
			$this->_registeredPostTypes[] = $postType;
			add_filter('abp01_trip_summary_available_for_post_types', function($postTypes) use ($postType) {
				$postTypes[] = $postType;
				return $postTypes;
			});
		}
		set_current_screen('edit-' . $postType);
		$_GET['post_type'] = $postType;
		$expected = $this->_createRoutePost(array('post_type' => $postType), $routeType);
		$this->_createRoutePost(array('post_type' => $postType), null);
		$_GET['abp01_route_type'] = $routeType;
		$this->assertSame(array($expected), $this->_queryPosts(array('post_type' => $postType))->posts);
	}

	public static function getExcludedQueries(): array {
		return array(
			'secondary post query' => array('edit-post', 'edit.php', 'post', false, false),
			'frontend main query' => array('front', 'index.php', 'post', true, false),
			'other admin screen' => array('dashboard', 'index.php', 'post', true, false),
			'unsupported post type' => array('edit-wpts_other', 'edit.php', 'wpts_other', true, false),
			'disabled post type' => array('edit-post', 'edit.php', 'post', true, true)
		);
	}

	/** @dataProvider getExcludedQueries */
	public function test_doesNotFilter_unrelatedQueries(string $screen, string $page, string $postType,
		bool $mainQuery, bool $disabled): void {
		if ($postType === 'wpts_other') {
			register_post_type($postType, array('public' => true));
			$this->_registeredPostTypes[] = $postType;
		}
		if ($disabled) {
			add_filter('abp01_trip_summary_available_for_post_types', '__return_empty_array');
		}
		set_current_screen($screen);
		$GLOBALS['pagenow'] = $page;
		$_GET['post_type'] = $postType;
		$this->_createRoutePost(array('post_type' => $postType), 'hiking');
		$before = $this->_queryPosts(array('post_type' => $postType), $mainQuery);
		$_GET['abp01_route_type'] = 'bike';
		$after = $this->_queryPosts(array('post_type' => $postType), $mainQuery);

		$this->assertNotEmpty($before->posts);
		$this->assertSame($before->request, $after->request);
		$this->assertSame($before->posts, $after->posts);
	}

	public function test_bikingFilter_doesNotReplaceGlobalStylesWithRoutePost(): void {
		$stylesId = self::factory()->post->create(array(
			'post_type' => 'wp_global_styles',
			'post_status' => 'publish',
			'post_date' => '2019-06-15 12:00:00',
			'post_content' => '{"version":3,"isGlobalStylesUserThemeJSON":true}'
		));
		wp_set_object_terms($stylesId, wp_get_theme()->get_stylesheet(), 'wp_theme');
		$this->_createRoutePost();
		$_GET['abp01_route_type'] = 'bike';
		$this->_queryPosts();

		$styles = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles(wp_get_theme(), false);
		$this->assertSame($stylesId, $styles['ID']);
		$this->assertSame('wp_global_styles', $styles['post_type']);
		$this->assertTrue(json_decode($styles['post_content'], true)['isGlobalStylesUserThemeJSON']);
	}

	public function test_mainListing_ignoresEmptyAndInvalidRouteTypes(): void {
		$this->_createRoutePost();
		$this->_createRoutePost(array(), 'hiking');
		$this->_createRoutePost(array(), null);
		$before = $this->_queryPosts();
		foreach (array('', 'unknown-route-type') as $routeType) {
			$_GET['abp01_route_type'] = $routeType;
			$after = $this->_queryPosts();
			$this->assertCount(3, $after->posts);
			$this->assertSame($before->request, $after->request);
			$this->assertSame($before->posts, $after->posts);
		}
	}
}
