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

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_PluginModules_AdminTripSummaryEditorPluginModule extends Abp01_PluginModules_PluginModule {
	const TRIP_SUMMARY_EDITOR_NONCE_ACTION = 'abp01_nonce_trip_summary_editor';
	
	const TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME = 'abp01_nonce';
	
	/**
	 * @var Abp01_Route_Manager
	 */
	private $_routeManager;

	/**
	 * @var Abp01_NonceProvider_DownloadTrackData
	 */
	private $_downloadTrackDataNonceProvider;

	/**
	 * @var Abp01_NonceProvider_ReadTrackData
	 */
	private $_readTrackDataNonceProvider;

	/**
	 * @var Abp01_UrlHelper
	 */
	private $_urlHelper;

	/**
	 * @var Abp01_Viewer_DataSource_Cache
	 */
	private $_viewerDataSourceCache;

	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_saveRouteInfoAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_removeRouteInfoAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_uploadRouteTrackAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_removeRouteTrackAjaxAction;

	/**
	 * @var Abp01_NonceProvider
	 */
	private $_editorNonceProvider;
	
	public function __construct(Abp01_Route_Manager $routeManager,
		Abp01_NonceProvider_ReadTrackData $readTrackDataNonceProvider, 
		Abp01_NonceProvider_DownloadTrackData $downloadTrackDataNonceProvider, 
		Abp01_Viewer_DataSource_Cache $viewerDataSourceCache,
		Abp01_View $view,
		Abp01_UrlHelper $urlHelper,
		Abp01_Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_routeManager = $routeManager;
		$this->_readTrackDataNonceProvider = $readTrackDataNonceProvider;
		$this->_downloadTrackDataNonceProvider = $downloadTrackDataNonceProvider;
		$this->_urlHelper = $urlHelper;
		$this->_viewerDataSourceCache = $viewerDataSourceCache;
		$this->_view = $view;

		$this->_editorNonceProvider = new Abp01_NonceProvider_Default(self::TRIP_SUMMARY_EDITOR_NONCE_ACTION, 
			self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME);

		$this->_initAjaxActions();
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createEditCurrentPostTripSummaryAuthCallback();
		$currentResourceProvider = new Abp01_AdminAjaxAction_CurrentResourceProvider_CurrentPostId();

		$this->_saveRouteInfoAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_EDIT, array($this, 'saveRouteInfo'))
				//->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->useNonceProvider($this->_editorNonceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_removeRouteInfoAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_CLEAR_INFO, array($this, 'removeRouteInfo'))
				//->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->useNonceProvider($this->_editorNonceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_uploadRouteTrackAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_UPLOAD_TRACK, array($this, 'uploadRouteTrack'))
				//->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->useNonceProvider($this->_editorNonceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_removeRouteTrackAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_CLEAR_TRACK, array($this, 'removeRouteTrack'))
				//->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->useNonceProvider($this->_editorNonceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();
	}

	public function load() {
		$this->_ensureStorageDirectory();
		$this->_registerAjaxActions();
		$this->_registerBlockEditorComponents();
		$this->_registerWebPageAssets();
		$this->_registerEditorControls();
	}

	private function _ensureStorageDirectory() {
		abp01_ensure_storage_directory();
	}

	private function _registerAjaxActions() {
		$this->_saveRouteInfoAjaxAction
			->register();
		$this->_removeRouteInfoAjaxAction
			->register();
		$this->_uploadRouteTrackAjaxAction
			->register();
		$this->_removeRouteTrackAjaxAction
			->register();
	}

	private function _registerBlockEditorComponents() {
		add_action('init', array($this, 'onPluginInitSetupBlockEditorBlocks'));
	}

	public function onPluginInitSetupBlockEditorBlocks() {
		if (!$this->_isClassicEditorActive()) {
			$this->_registerBlockTypes();
		}
	}

	private function _registerBlockTypes() {
		if ($this->_canRegisterWpBlockTypes()) {
			$this->_registerTripSummaryShortCodeBlock();
		}
	}

	private function _canRegisterWpBlockTypes() {
		return function_exists('register_block_type');
	}

	private function _registerTripSummaryShortCodeBlock() {
		Abp01_Includes::includeScriptBlockEditorViewerShortCodeBlock();
		register_block_type('abp01/block-editor-shortcode', array(
			'editor_script' => 'abp01-viewer-short-code-block',
			//we need server side rendering to account for 
			//	potential changes of the configured shortcode tag name
			'render_callback' => array($this, 'renderTripSummaryShortcodeBlock')
		));
	}

	public function renderTripSummaryShortCodeBlock($attributes, $content) {
		static $rendered = false;
		if ($rendered === false || !doing_filter('the_content')) {
			$rendered = true;
			return '<div class="abp01-viewer-shortcode-block">' . ('[' . ABP01_VIEWER_SHORTCODE . ']') . '</div>';
		} else {
			return '';
		}
	}

	private function _registerWebPageAssets() {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'));
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'));
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeStyleAdminMain();
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		return $this->_env->isEditingWpPost('post', 'page') 
			&& $this->_canEditCurrentPostTripSummary();
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeScriptAdminEditorMain(true, $this->_getAdminTripSummaryEditorScriptTranslations());
		}
	}

	private function _getAdminTripSummaryEditorScriptTranslations() {
		return Abp01_TranslatedScriptMessages::getAdminTripSummaryEditorScriptTranslations();
	}

	private function _registerEditorControls() {
		add_filter('mce_buttons', 
			array($this, 'registerClassicEditorButtons'));
		add_filter('mce_external_plugins', 
			array($this, 'registerClassicEditorPlugins'));
		add_filter('tiny_mce_before_init', 
			array($this, 'registerClassicEditorSettings'));
		add_action('add_meta_boxes', array($this, 'registerAdminEditorLauncherMetaboxes'), 
			10, 
			2);
	}

	public function registerClassicEditorButtons($buttons) {
		if ($this->_isClassicEditorActive()) {
			$buttons = array_merge($buttons, array(
				'abp01_insert_viewer_shortcode'
			));
		}

		return $buttons;
	}

	private function _isClassicEditorActive() {
		return $this->_env->isPluginActive('classic-editor/classic-editor.php');
	}

	public function registerClassicEditorPlugins($plugins) {
		if ($this->_isClassicEditorActive()) {
			$plugins['abp01_viewer_shortcode'] = Abp01_Includes::getClassicEditorViewerShortcodePluginUrl();
		}
		return $plugins;
	}

	public function registerClassicEditorSettings($settings) {
		if ($this->_isClassicEditorActive()) {
			$settings['abp01_viewer_short_code_name'] = ABP01_VIEWER_SHORTCODE;
		}
		return $settings;
	}

	public function registerAdminEditorLauncherMetaboxes($postType, $post) {
		if ($this->_shouldRegisterAdminEditorLauncherMetaboxes($postType, $post)) {
			add_meta_box('abp01-enhanced-editor-launcher-metabox', 
				__('Trip summary', 'abp01-trip-summary'),
				array($this, 'addAdminEditor'), 
				$postType, 
				'side', 
				'high', 
				array(
					'postType' => $postType,
					'post' => $post
				));
		}
	}

	private function _shouldRegisterAdminEditorLauncherMetaboxes($postType, $post) {
		return in_array($postType, array('post', 'page')) 
			&& $this->_cantEditPostTripSummary($post);
	}

	public function addAdminEditor($post, $args) {
		if ($this->_cantEditPostTripSummary($post)) {
			$this->_addAdminEditorForm($post, $args);
			$this->_addAdminEditorLauncher($post, $args);
		}
	}

	private function _addAdminEditorForm($post, $args) {
		$data = new stdClass();
		$lookup = $this->_createLookupForCurrentLang();

		//get the lookup data
		$data->difficultyLevels = $lookup->getDifficultyLevelOptions();
		$data->difficultyLevelsAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::DIFFICULTY_LEVEL);

		$data->pathSurfaceTypes = $lookup->getPathSurfaceTypeOptions();
		$data->pathSurfaceTypesAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::PATH_SURFACE_TYPE);

		$data->recommendedSeasons = $lookup->getRecommendedSeasonsOptions();
		$data->recommendedSeasonsAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::RECOMMEND_SEASONS);

		$data->bikeTypes = $lookup->getBikeTypeOptions();
		$data->bikeTypesAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::BIKE_TYPE);

		$data->railroadOperators = $lookup->getRailroadOperatorOptions();
		$data->railroadOperatorsAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::RAILROAD_OPERATOR);

		$data->railroadLineStatuses = $lookup->getRailroadLineStatusOptions();
		$data->railroadLineStatusesAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::RAILROAD_LINE_STATUS);

		$data->railroadLineTypes = $lookup->getRailroadLineTypeOptions();
		$data->railroadLineTypesAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::RAILROAD_LINE_TYPE);

		$data->railroadElectrification = $lookup->getRailroadElectrificationOptions();
		$data->railroadElectrificationAdminUrl = $this->_urlHelper->constructAdminLookupUrl(Abp01_Lookup::RAILROAD_ELECTRIFICATION);

		//current context information
		$data->postId = intval($post->ID);
		$data->hasRouteTrack = $this->_routeManager->hasRouteTrack($post->ID);
		$data->hasRouteInfo = $this->_routeManager->hasRouteInfo($post->ID);
		$data->trackDownloadUrl = $this->_urlHelper->constructGpxTrackDownloadUrl($post->ID);

		$data->ajaxEditInfoAction = ABP01_ACTION_EDIT;
		$data->ajaxUploadTrackAction = ABP01_ACTION_UPLOAD_TRACK;
		$data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;	
		$data->ajaxClearTrackAction = ABP01_ACTION_CLEAR_TRACK;
		$data->ajaxClearInfoAction = ABP01_ACTION_CLEAR_INFO;
		$data->downloadTrackAction = ABP01_ACTION_DOWNLOAD_TRACK;

		//TODO: should be one nonce per action
		$data->nonce = $this->_editorNonceProvider->generateNonce($data->postId);
		//TODO: might be better to replace it with directly generated URL from _urlHelper
		$data->nonceGet = $this->_readTrackDataNonceProvider->generateNonce($data->postId);	
		//TODO: this might be redundant with $data->trackDownloadUrl
		$data->nonceDownload = $this->_downloadTrackDataNonceProvider->generateNonce($data->postId);

		$data->ajaxUrl = $this->_getAjaxBaseUrl();
		$data->imgBaseUrl = $this->_getPluginMediaImgBaseUrl();
		//TODO: these should be removed
		$data->flashUploaderUrl = includes_url('js/plupload/plupload.flash.swf');
		$data->xapUploaderUrl = includes_url('js/plupload/plupload.silverlight.xap');

		$data->uploadMaxFileSize = ABP01_TRACK_UPLOAD_MAX_FILE_SIZE;
		$data->uploadChunkSize = ABP01_TRACK_UPLOAD_CHUNK_SIZE;
		$data->uploadKey = ABP01_TRACK_UPLOAD_KEY;

		//the already existing values
		$info = $this->_routeManager->getRouteInfo($data->postId);
		if ($info instanceof Abp01_Route_Info) {
			$data->tourInfo = $info->getData();
			$data->tourType = $info->getType();
		} else {
			$data->tourType = null;
			$data->tourInfo = null;
		}

		echo $this->_view->renderAdminTripSummaryEditor($data);
	}

	private function _createLookupForCurrentLang() {
        return new Abp01_Lookup();
    }

	private function _addAdminEditorLauncher($post, $args) {
		$postId = intval($post->ID);

		$data = new stdClass();
		$data->postId = $postId;
		$data->hasRouteTrack = $this->_routeManager->hasRouteTrack($postId);
		$data->hasRouteInfo = $this->_routeManager->hasRouteInfo($postId);
		$data->trackDownloadUrl = $this->_urlHelper->constructGpxTrackDownloadUrl($postId);

		echo $this->_view->renderAdminTripSummaryEditorLauncherMetabox($data);
	}

	public function saveRouteInfo() {
		$postId = $this->_getCurrentPostId();
		$type = Abp01_InputFiltering::getPOSTValueOrDie('type');

		if (!Abp01_Route_Info::isTypeSupported($type)) {
			die;
		}

		$info = new Abp01_Route_Info($type);
		$response = abp01_get_ajax_response();

		foreach ($info->getValidFieldNames() as $field) {
			if (isset($_POST[$field])) {
				//Value is filtered on assignment.
				// @see Abp01_Route_Info::__set
				// @see Abp01_Route_Info::_filterFieldValue
				$info->$field = $_POST[$field];
			}
		}

		if ($this->_routeManager->saveRouteInfo($postId, $info, get_current_user_id())) {
			$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);
			$response->success = true;
		} else {
			$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
		}

		return $response;
	}

	public function removeRouteInfo() {
		$postId = $this->_getCurrentPostId();
		$response = abp01_get_ajax_response();

		if ($this->_routeManager->deleteRouteInfo($postId)) {
			$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);
			$response->success = true;
		} else {
			$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
		}

		return $response;
	}

	public function uploadRouteTrack() {
		$postId = $this->_getCurrentPostId();
		$currentUserId = get_current_user_id();
		$destination = $this->_routeManager->getTrackFilePath($postId);
		if (empty($destination)) {
			die;
		}

		//detect chunking
		if (ABP01_TRACK_UPLOAD_CHUNK_SIZE > 0) {
			$chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
			$chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;
		} else {
			$chunk = $chunks = 0;
		}

		//create and configure the uploader
		$uploader = new Abp01_Uploader(ABP01_TRACK_UPLOAD_KEY, $destination, array(
			'chunk' => $chunk, 
			'chunks' => $chunks, 
			'chunkSize' => ABP01_TRACK_UPLOAD_CHUNK_SIZE, 
			'maxFileSize' => ABP01_TRACK_UPLOAD_MAX_FILE_SIZE, 
			'allowedFileTypes' => array(
				'application/gpx', 
				'application/x-gpx+xml', 
				'application/xml-gpx', 
				'application/xml', 
				'text/xml',
				'application/octet-stream'
		)));
		$uploader->setCustomValidator(array(new Abp01_Validate_GpxDocument(), 'validate'));

		$response = new stdClass();
		$response->status = $uploader->receive();
		$response->ready = $uploader->isReady();

		//if the upload has completed, then process the newly 
		//	uploaded file and save the track information
		if ($response->ready) {
			$route = file_get_contents($destination);
			if (!empty($route)) {
				$parser = new Abp01_Route_Track_GpxDocumentParser();
				$route = $parser->parse($route);
				if ($route && !$parser->hasErrors()) {
					$destination = basename($destination);
					$track = new Abp01_Route_Track($postId, 
						$destination, 
						$route->getBounds(), 
						$route->minAlt, 
						$route->maxAlt);
					
					if (!$this->_routeManager->saveRouteTrack($track, $currentUserId)) {
						$response->status = Abp01_Uploader::UPLOAD_INTERNAL_ERROR;
					} else {
						$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);
					}
				} else {
					$response->status = Abp01_Uploader::UPLOAD_DESTINATION_FILE_CORRUPT;
				}
			} else {
				$response->status = Abp01_Uploader::UPLOAD_DESTINATION_FILE_NOT_FOUND;
			}
		}

		return $response;
	}

	public function removeRouteTrack() {
		$postId = $this->_getCurrentPostId();
		$response = abp01_get_ajax_response();

		if ($this->_routeManager->deleteRouteTrack($postId)) {
			$this->_routeManager->deleteTrackFiles($postId);
			$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);
			$response->success = true;
		} else {
			$response->message = esc_html__('The data could not be updated due to a possible database error', 'abp01-trip-summary');
		}

		return $response;
	}
}