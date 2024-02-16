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
class Abp01_PluginModules_SystemLogsManagementPluginModule extends Abp01_PluginModules_PluginModule {
	/**
	 * @var Abp01_View
	 */
	private $_view;

	/**
	 * @var Abp01_Logger_Manager
	 */
	private $_logManager;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_getLogFileContentsAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_downloadLogFileAjaxAction;

	/**
	 * @var Abp01_AdminAjaxAction
	 */
	private $_deleteLogFileAjaxAction;

	public function __construct(Abp01_Logger_Manager $logManager,
		Abp01_View $view,
		Abp01_Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_logManager = $logManager;
		$this->_view = $view;

		$this->_initAjaxActions();
	}

	private function _initAjaxActions(): void {
		$authCallback = $this->_createManagePluginSettingsAuthCallback();

		$this->_getLogFileContentsAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_GET_LOG_FILE_CONTENTS, array($this, 'getLogFileContents'))
				->useDefaultNonceProvider('abp01_nonce_get_log_file_contents')
				->authorizeByCallback($authCallback)
				->onlyForHttpGet();

		$this->_downloadLogFileAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_DOWNLOAD_LOG_FILE, array($this, 'downloadLogFile'))
				->useDefaultNonceProvider('abp01_nonce_download_log_file')
				->authorizeByCallback($authCallback)
				->onlyForHttpGet();

		$this->_deleteLogFileAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_DELETE_LOG_FILE, array($this, 'deleteLogFile'))
				->useDefaultNonceProvider('abp01_nonce_delete_log_file')
				->authorizeByCallback($authCallback)
				->onlyForHttpPost();
	}

    public function load(): void { 
		$this->_registerAjaxActions();
		$this->_registerWebPageAssets();
	}

	private function _registerAjaxActions(): void {
		$this->_getLogFileContentsAjaxAction
			->register();
		$this->_downloadLogFileAjaxAction
			->register();
		$this->_deleteLogFileAjaxAction
			->register();
	}

	private function _registerWebPageAssets(): void {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'));
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'));
	}

	public function onAdminEnqueueStyles(): void {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeStyleAdminSystemLogs();
		}
	}

	public function onAdminEnqueueScripts(): void {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeScriptAdminSystemLogs($this->_getAdminSystemLogsManagementTranslations());
		}
	}

	private function _getAdminSystemLogsManagementTranslations() {
		return Abp01_TranslatedScriptMessages::getAdminSystemLogsManagementTranslations();
	}

	private function _shouldEnqueueWebPageAssets(): bool {
		return $this->_isViewingSystemLogsPage();
	}

	private function _isViewingSystemLogsPage(): bool {
		return $this->_env->isAdminPage('abp01-system-logs');
	}
	
	public function getMenuItems(): array {
		return array(
			array(
				'slug' => 'abp01-system-logs',
				'parent' => ABP01_MAIN_MENU_SLUG,
				'pageTitle' => esc_html__('System logs', 'abp01-trip-summary'),
				'menuTitle' => esc_html__('System logs', 'abp01-trip-summary'),
				'capability' => Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY,
				'callback' => array($this, 'displayLogsManagementPage')
			)
		);
	}

	public function displayLogsManagementPage(): void {
		if (!$this->_currentUserCanManagePluginSettings()) {
			die;
		}

		$debugLogFiles = array();
		$errorLogFiles = array();

		$logFiles = $this->_logManager
			->getLogFiles();
		
		foreach ($logFiles as $lf) {
			if ($lf->isDebugLogFile()) {
				$debugLogFiles[] = $lf->asPlainObject();
			} else if ($lf->isErrorLogFile()) {
				$errorLogFiles[] = $lf->asPlainObject();
			}
		}

		$data = new stdClass();

		$data->ajaxUrl = $this->_getAjaxBaseUrl();
		$data->ajaxGetLogFileAction = $this->_getLogFileContentsAjaxAction
			->getActionCode();
		$data->getLogFileNonce = $this->_getLogFileContentsAjaxAction
			->generateNonce();

		$data->ajaxDownloadLogFileAction = $this->_downloadLogFileAjaxAction
			->getActionCode();
		$data->downloadLogFileNonce = $this->_downloadLogFileAjaxAction
			->generateNonce();

		$data->ajaxDeleteLogFileAction = $this->_deleteLogFileAjaxAction
			->getActionCode();
		$data->deleteLogFileNonce = $this->_deleteLogFileAjaxAction
			->generateNonce();

		$data->isDebugLoggingEnabled = $this->_logManager->isErrorLoggingEnabled();
		$data->isErrorLoggingEnabled = $this->_logManager->isErrorLoggingEnabled();

		$data->hasErrorLogFiles = !empty($errorLogFiles);
		$data->errorLogFiles = $errorLogFiles;
		
		$data->hasDebugLogFiles = !empty($debugLogFiles);
		$data->debugLogFiles = $debugLogFiles;

		echo $this->_view->renderAdminSystemLogsPage($data);
	}

	public function getLogFileContents(): stdClass {
		$fileId = $this->_getFileIdFromHttpGet();
		if (empty($fileId)) {
			die;
		}

		$foundFile = $this->_logManager
			->getLogFileById($fileId);

		$response = abp01_get_ajax_response(array(
			'found' => false,
			'trimmed' => false,
			'contents' => null
		));

		$response->found = $foundFile != null;
		$response->trimmed = false;

		if ($foundFile != null) {
			$response->trimmed = $foundFile->getFileSize() > $this->_getMaxDisplayableLogSize();
			$response->contents = !$response->trimmed
				? $foundFile->contents()
				: $foundFile->tail($this->_getLogFileTailLineCount());
		} else {
			$response->contents = null;
		}

		$response->success = true;
		return $response;
	}

	private function _getFileIdFromHttpGet() {
		return Abp01_InputFiltering::getFilteredGETValue('abp01_fileId');
	}

	private function _getMaxDisplayableLogSize(): int {
		$lineCount = $defaultLineCount = $this->_getLogFileTailLineCount() * 500;

		$lineCount = intval(apply_filters('abp01_max_displayable_log_file_size', 
			$lineCount));

		if ($lineCount <= 0) {
			$lineCount = $defaultLineCount;
		}

		return $lineCount;
	}

	private function _getLogFileTailLineCount(): int {
		$tailLineCount = $defaultTailLineCount = 500;
		if (defined('ABP01_LOG_FILE_DISPLAY_TAIL_LINE_COUNT')) {
			$tailLineCount = intval(constant('ABP01_LOG_FILE_DISPLAY_TAIL_LINE_COUNT'));
		}

		$tailLineCount = intval(apply_filters('abp01_log_file_display_tail_line_count', 
			$tailLineCount));

		if ($tailLineCount <= 0) {
			$tailLineCount = $defaultTailLineCount;
		}

		return $tailLineCount;
	}

	public function downloadLogFile(): void {
		$fileId = $this->_getFileIdFromHttpGet();
		if (empty($fileId)) {
			die;
		}

		$foundFile = $this->_logManager
			->getLogFileById($fileId);

		$fileDownloader = $this->_createDefaultFileDownloaderInstance();
		$fileDownloader->sendFileWithMimeType($foundFile->getFilePath(), 'text/plain');

		die;
	}

	private function _createDefaultFileDownloaderInstance() {
		return new Abp01_Transfer_FileDownloaderWithScriptTermination(
			new Abp01_Transfer_SimpleFileDownloader()
		);
	}

	public function deleteLogFile(): stdClass {
		$fileId = $this->_getFileIdFromHttpGet();
		if (empty($fileId)) {
			die;
		}

		$wasDeleted = $this->_logManager
			->deleteLogFileById($fileId);

		$response = abp01_get_ajax_response();
		$response->success = $wasDeleted;

		if (!$response->success) {
			$response->message = __('The requested log file could not be deleted.', 'abp01-trip-summary');
		}

		return $response;
	}
}