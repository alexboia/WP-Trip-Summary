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

use WpTripSummary\Env;

if (!defined('ABP01_LOADED')) {
    exit;
}

/**
 * @package WP-Trip-Summary
 */
class Abp01_PluginModules_JsonLdFrontendDataPluginModule extends Abp01_PluginModules_PluginModule {
	const PLACE_ID_FRAGMENT = '#wpts-place';

	private Abp01_Settings $_settings;

	private Abp01_Viewer_DataSource $_viewerDataSource;

	private Abp01_View $_view;
	
	public function __construct(Abp01_Settings $settings, 
		Abp01_Viewer_DataSource $viewerDataSource, 
		Abp01_View $view, 
		Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_settings = $settings;
		$this->_viewerDataSource = $viewerDataSource;
		$this->_view = $view;
	}

	public function load(): void {
		// The enabled check is deferred until wp_head,
		//	so that themes can also hook into its filter
		add_action('wp_head', array($this, 'includeJsonLdFrontendData'));

		// Integration with the wp-alexboia-net theme schema.org output:
		//	links its BlogPosting node to the Place node output by this module
		add_filter('wp_abnet_schema_org_properties_blog_posting',
			array($this, 'linkBlogPostingToPlace'),
			10,
			2);
	}

	public function linkBlogPostingToPlace(mixed $properties, WP_Post $post): mixed {
		if (is_array($properties) && $this->_shouldIncludeJsonLdFrontendData($post->ID)) {
			$properties['contentLocation'] = array(
				'@id' => $this->_getPlaceId($post->ID)
			);
		}

		return $properties;
	}

	private function _shouldIncludeJsonLdFrontendData(int $postId): bool {
		return $postId > 0
			&& $this->_jsonLdFrontendDataEnabled($postId)
			&& $this->_hasTrackData($this->_getTripSummaryViewerData($postId));
	}

	private function _getPlaceId(int $postId): string {
		return get_permalink($postId) . self::PLACE_ID_FRAGMENT;
	}

	private function _jsonLdFrontendDataEnabled(int $postId): bool {
		/**
		 * Whether or not to output JSON-LD frontend data for the given post.
		 * Defaults to the value of the corresponding plug-in setting.
		 * Result is converted such that only boolean true enables the output.
		 *
		 * @since 0.3.3
		 * @category Front-end Viewer
		 * @unstable Susceptible to breaking changes due to PSR-4 migration
		 *
		 * @param bool $enabled Whether or not the JSON-LD frontend data output is enabled
		 * @param int $postId The post identifier
		 */
		return apply_filters('abp01_jsonld_frontend_data_enabled',
			$this->_settings->getEnableJsonLdFrontenData(),
			$postId) === true;
	}

	public function includeJsonLdFrontendData(): void {
		if ($this->_isPostDetailsPage()) {
			$postId = $this->_getCurrentPostId();
			if ($postId > 0 && $this->_jsonLdFrontendDataEnabled($postId)) {
				$this->_includeJsonLdFrontendData($postId);
			}
		}
	}

	private function _isPostDetailsPage(): bool {
		return is_single() || is_page();
	}

	private function _includeJsonLdFrontendData(int $postId): void {
		$viewerData = $this->_getTripSummaryViewerData($postId);
		if ($this->_hasTrackData($viewerData)) {
			/** @var WP_Post $post */
			$post = get_post($postId);
			$bounds = $viewerData->track->summary->bounds;
			
			$data = new stdClass();
			$data->id = $this->_getPlaceId($postId);
			$data->name = $post->post_title;
			$data->southWest = $bounds->southWest;
			$data->northEast = $bounds->northEast;

			echo $this->_renderJsonLdFrontendData($data);
		}
	}

	private function _getTripSummaryViewerData(int $postId): stdClass {
		return  $this->_viewerDataSource->getTripSummaryViewerData($postId);
	}

	private function _hasTrackData(stdClass $viewerData): bool {
		return !empty($viewerData) 
			&& !empty($viewerData->track) 
			&& $viewerData->track->exists;
	}

	private function _renderJsonLdFrontendData(stdClass $data): string|bool {
		return $this->_view->renderJsonLdFrontendData($data);
	}
}