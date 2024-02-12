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
class Abp01_PluginModules_AdminTripSummaryEditorPluginModule extends Abp01_PluginModules_PluginModule {
	const TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME = 'abp01_nonce';

	const CLASSIC_EDITOR_PLUGIN_SLUG = 'classic-editor/classic-editor.php';

	const LAUNCHER_METABOX_REGISTRATION_HOOK_PRIORITY = 10;

	const LAUNCHER_METABOX_POSITION = 'side';

	const LAUNCHER_METABOX_PRIORITY = 'high';
	
	/**
	 * @var Abp01_Route_Manager
	 */
	private $_routeManager;

	/**
	 * @var Abp01_Route_Track_Processor
	 */
	private $_routeTrackProcessor;

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
	 * @var Abp01_Transfer_Uploader_FileValidatorProvider
	 */
	private $_fileValidatorProvider;

	/**
	 * @var Abp01_TripSummaryShortcodeBlockType
	 */
	private $_tripSummaryShortCodeBlockType;

	/**
	 * @var Abp01_Logger
	 */
	private $_logger;

	public function __construct(Abp01_Route_Manager $routeManager,
		Abp01_Route_Track_Processor $routeTrackProcessor,
		Abp01_Transfer_Uploader_FileValidatorProvider $fileValidatorProvider,
		Abp01_NonceProvider_ReadTrackData $readTrackDataNonceProvider, 
		Abp01_Viewer_DataSource_Cache $viewerDataSourceCache,
		Abp01_View $view,
		Abp01_UrlHelper $urlHelper,
		Abp01_Logger $logger,
		Abp01_Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_routeManager = $routeManager;
		$this->_routeTrackProcessor = $routeTrackProcessor;
		$this->_fileValidatorProvider = $fileValidatorProvider;
		$this->_readTrackDataNonceProvider = $readTrackDataNonceProvider;
		$this->_urlHelper = $urlHelper;
		$this->_viewerDataSourceCache = $viewerDataSourceCache;
		$this->_view = $view;
		$this->_tripSummaryShortCodeBlockType = new Abp01_TripSummaryShortcodeBlockType();
		$this->_logger = $logger;

		$this->_initAjaxActions();
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createEditCurrentPostTripSummaryAuthCallback();
		$currentResourceProvider = new Abp01_AdminAjaxAction_CurrentResourceProvider_CurrentPostId();

		$this->_saveRouteInfoAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_EDIT, array($this, 'saveRouteInfo'))
				->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_removeRouteInfoAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_CLEAR_INFO, array($this, 'removeRouteInfo'))
				->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_uploadRouteTrackAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_UPLOAD_TRACK, array($this, 'uploadRouteTrack'))
				->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();

		$this->_removeRouteTrackAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_CLEAR_TRACK, array($this, 'removeRouteTrack'))
				->useDefaultNonceProvider(self::TRIP_SUMMARY_EDITOR_NONCE_URL_PARAM_NAME)
				->useCurrentResourceProvider($currentResourceProvider)
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();
	}

	public function load() {
		$this->_ensureStorageDirectory();
		$this->_registerAjaxActions();
		$this->_registerCustomBlockTypesEditors();
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

	private function _registerCustomBlockTypesEditors() {
		add_action('init', array($this, 'onPluginInitSetupCustomBlockTypesEditors'));
	}

	public function onPluginInitSetupCustomBlockTypesEditors() {
		if ($this->_shouldEnqueueWebPageAssets(false) && !$this->_isClassicEditorActive()) {
			$this->_registerCustomBlockTypes();
		}
	}

	private function _shouldEnqueueWebPageAssets($strictPostTypeCheck) {
		$isEditingPost = $strictPostTypeCheck
			? $this->_env->isEditingWpPost(Abp01_AvailabilityHelper::getTripSummaryAvailableForPostTypes()) 
			: $this->_env->isEditingWpPost();

		return $isEditingPost
			&& $this->_canEditCurrentPostTripSummary();
	}

	private function _isClassicEditorActive() {
		return $this->_env->isPluginActive(self::CLASSIC_EDITOR_PLUGIN_SLUG);
	}

	private function _registerCustomBlockTypes() {
		if ($this->_canRegisterWpBlockTypes()) {
			$this->_registerTripSummaryShortCodeBlock();
		}
	}

	private function _canRegisterWpBlockTypes() {
		return function_exists('register_block_type');
	}

	private function _registerTripSummaryShortCodeBlock() {
		$this->_tripSummaryShortCodeBlockType
			->registerWithEditorScripts();
	}

	private function _registerWebPageAssets() {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'));
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'));
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets(true)) {
			Abp01_Includes::includeStyleAdminMain();
		}
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets(true)) {
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
			self::LAUNCHER_METABOX_REGISTRATION_HOOK_PRIORITY, 
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
				self::LAUNCHER_METABOX_POSITION, 
				self::LAUNCHER_METABOX_PRIORITY, 
				array(
					'postType' => $postType,
					'post' => $post
				)
			);
		}
	}

	private function _shouldRegisterAdminEditorLauncherMetaboxes($postType, $post) {
		return Abp01_AvailabilityHelper::isEditorAvailableForPostType($postType) 
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

		$data->difficultyLevels = $lookup->getDifficultyLevelOptions();
		$data->difficultyLevelsAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::DIFFICULTY_LEVEL);

		$data->pathSurfaceTypes = $lookup->getPathSurfaceTypeOptions();
		$data->pathSurfaceTypesAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::PATH_SURFACE_TYPE);

		$data->recommendedSeasons = $lookup->getRecommendedSeasonsOptions();
		$data->recommendedSeasonsAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::RECOMMEND_SEASONS);

		$data->bikeTypes = $lookup->getBikeTypeOptions();
		$data->bikeTypesAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::BIKE_TYPE);

		$data->railroadOperators = $lookup->getRailroadOperatorOptions();
		$data->railroadOperatorsAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::RAILROAD_OPERATOR);

		$data->railroadLineStatuses = $lookup->getRailroadLineStatusOptions();
		$data->railroadLineStatusesAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::RAILROAD_LINE_STATUS);

		$data->railroadLineTypes = $lookup->getRailroadLineTypeOptions();
		$data->railroadLineTypesAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::RAILROAD_LINE_TYPE);

		$data->railroadElectrification = $lookup->getRailroadElectrificationOptions();
		$data->railroadElectrificationAdminUrl = $this->_constructAdminLookupUrl(Abp01_Lookup::RAILROAD_ELECTRIFICATION);

		//current context information
		$data->postId = intval($post->ID);
		$data->hasRouteTrack = $this->_routeManager->hasRouteTrack($post->ID);
		$data->hasRouteInfo = $this->_routeManager->hasRouteInfo($post->ID);
		$data->trackDownloadUrl = $this->_urlHelper->constructGpxTrackDownloadUrl($post->ID);

		$data->editInfoNonce = $this->_saveRouteInfoAjaxAction->generateNonce();
		$data->ajaxEditInfoAction = ABP01_ACTION_EDIT;

		$data->uploadTrackNonce = $this->_uploadRouteTrackAjaxAction->generateNonce();
		$data->ajaxUploadTrackAction = ABP01_ACTION_UPLOAD_TRACK;

		$data->clearTrackNonce = $this->_removeRouteTrackAjaxAction->generateNonce();
		$data->ajaxClearTrackAction = ABP01_ACTION_CLEAR_TRACK;

		$data->clearInfoNonce = $this->_removeRouteInfoAjaxAction->generateNonce();
		$data->ajaxClearInfoAction = ABP01_ACTION_CLEAR_INFO;

		$data->getTrackNonce = $this->_readTrackDataNonceProvider->generateNonce($data->postId);
		$data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;	

		$data->ajaxUrl = $this->_getAjaxBaseUrl();
		$data->imgBaseUrl = $this->_getPluginMediaImgBaseUrl();

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

	private function _constructAdminLookupUrl($lookupType) {
		return $this->_urlHelper->constructAdminLookupUrl($lookupType);
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
		$context = array(
			'postId' => $postId
		);

		$this->_logger->debug('Saving route info for post...', $context);

		if (!Abp01_Route_Info::isTypeSupported($type)) {
			$this->_logger->warning('Route type <' . $type . '> not supported.', $context);
			die;
		}

		$response = abp01_get_ajax_response();
		$info = Abp01_Route_Info::fromType($type);
		$info->populateFromRawInput($_POST);

		try {
			if ($this->_routeManager->saveRouteInfo($postId, $info, get_current_user_id())) {
				$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);
				$this->_logger->debug('Route info successfully saved for post.', $context);
				$response->success = true;
			} else {
				$this->_logger->warning('Route info could not be saved for post.', $context);
			}
		} catch (Exception $exc) {
			$this->_logger->exception($exc->getMessage(), $exc, $context);
		}

		if (!$response->success) {
			$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
		}

		return $response;
	}

	public function removeRouteInfo() {
		$postId = $this->_getCurrentPostId();
		$response = abp01_get_ajax_response();
		$context = array(
			'postId' => $postId
		);

		$this->_logger->debug('Removing route info for post...', $context);

		try {
			if ($this->_routeManager->deleteRouteInfo($postId)) {
				$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);
				$this->_logger->debug('Route info successfully removed for post.', $context);
				$response->success = true;
			} else {
				$this->_logger->warning('Route info could not be removed for post.', $context);
			}
		} catch (Exception $exc) {
			$this->_logger->exception($exc->getMessage(), $exc, $context);
		}
		
		if (!$response->success) {
			$response->message = esc_html__('The data could not be saved due to a possible database error', 'abp01-trip-summary');
		}

		return $response;
	}

	public function uploadRouteTrack() {
		$postId = $this->_getCurrentPostId();
		$uploader = $this->_createUploader($postId);
		$context = array(
			'postId' => $postId
		);

		$this->_logger->debug('Uploading route track for post...', $context);

		$response = new stdClass();
		$response->status = $uploader->receive();
		$response->ready = $uploader->isReady();

		//if the upload has completed, then process the newly 
		//	uploaded file and save the track information
		if ($response->ready) {
			$response->status = $this->_processUploadedFile($postId, 
				$uploader->getDestinationPath(), 
				$uploader->getDetectedType());
		}

		$this->_logger->debug('Done uploading route track for post.', 
			array_merge($context, array(
				'ready' => $response->ready,
				'status' => $response->status
			)));

		return $response;
	}

	private function _createUploader($destination) {
		$config = $this->_constructUploaderConfig($destination);
		$uploader = new Abp01_Transfer_Uploader($this->_logger, $config);
		return $uploader;
	}

	private function _constructUploaderConfig($postId) {
		$fileNameProvider = $this->_constructFileNameProvider($postId);

		if (ABP01_TRACK_UPLOAD_CHUNK_SIZE > 0) {
			$currentChunk = $this->_readCurrentChunkFromRequest();
			$chunkCount = $this->_readChunkCountFromRequest();
		} else {
			$currentChunk = $chunkCount = 0;
		}

		$config = new Abp01_Transfer_Uploader_Config(ABP01_TRACK_UPLOAD_KEY, 
			$fileNameProvider,
			$this->_fileValidatorProvider);

		$config->setChunking(ABP01_TRACK_UPLOAD_CHUNK_SIZE, $currentChunk, $chunkCount)
			->setMaxFileSize(ABP01_TRACK_UPLOAD_MAX_FILE_SIZE);

		return $config;
	}

	private function _constructFileNameProvider($postId) {
		return new Abp01_Transfer_Uploader_FileNameProvider_Track($this->_routeTrackProcessor, 
			$postId);
	}

	private function _readCurrentChunkFromRequest() {
		return isset($_REQUEST['chunk']) 
			? intval($_REQUEST['chunk']) 
			: 0;
	}

	private function _readChunkCountFromRequest() {
		return isset($_REQUEST['chunks']) 
			? intval($_REQUEST['chunks']) 
			: 0;
	}

	private function _processUploadedFile($postId, $uploadedTrackFilePath, $uploadedTrackFileMimeType) {
		$status = Abp01_Transfer_Uploader::UPLOAD_OK;

		try {
			$track = $this->_routeTrackProcessor->processInitialTrackSourceFile($postId, 
				$uploadedTrackFilePath,
				$uploadedTrackFileMimeType);

			$currentUserId = get_current_user_id();
			if ($this->_routeManager->saveRouteTrack($track, $currentUserId)) {
				$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);						
			} else {
				$status = Abp01_Transfer_Uploader::UPLOAD_INTERNAL_ERROR;
			}
		} catch (Abp01_Route_Track_DocumentParser_Exception $exc) {
			$status = Abp01_Transfer_Uploader::UPLOAD_DESTINATION_FILE_CORRUPT;
			$this->_logger->exception($exc->getMessage(), $exc, array(
				'postId' => $postId
			));
		}

		return $status;
	}

	public function removeRouteTrack() {
		$postId = $this->_getCurrentPostId();
		$response = abp01_get_ajax_response();
		$context = array(
			'postId' => $postId
		);

		$this->_logger->debug('Removing route track for post...', $context);

		try {
			if ($this->_routeManager->deleteRouteTrack($postId)) {
				$this->_routeTrackProcessor->deleteTrackFiles($postId);
				$this->_viewerDataSourceCache->clearCachedPostTripSummaryViewerData($postId);
				$this->_logger->debug('Route track successfully removed for post.', $context);
				$response->success = true;
			} else {
				$this->_logger->warning('Route track could not be removed for post.', $context);
			}
		} catch (Exception $exc) {
			$this->_logger->exception($exc->getMessage(), $exc, $context);
		}

		if (!$response->success) {
			$response->message = esc_html__('The data could not be updated due to a possible database error', 'abp01-trip-summary');
		}

		return $response;
	}
}