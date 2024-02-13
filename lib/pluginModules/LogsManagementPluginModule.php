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
class Abp01_PluginModules_LogsManagementPluginModule extends Abp01_PluginModules_PluginModule {
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

	public function __construct(Abp01_Logger_Manager $logManager,
		Abp01_View $view,
		Abp01_Env $env, 
		Abp01_Auth $auth) {
		parent::__construct($env, $auth);

		$this->_logManager = $logManager;
		$this->_view = $view;

		$this->_initAjaxActions();
	}

	private function _initAjaxActions() {
		$authCallback = $this->_createManagePluginSettingsAuthCallback();

		$this->_getLogFileContentsAjaxAction = 
			Abp01_AdminAjaxAction::create(ABP01_ACTION_GET_LOG_FILE_CONTENTS, array($this, 'getLogFileContents'))
				->useDefaultNonceProvider('abp01_nonce_get_log_file_contents')
				->authorizeByCallback($authCallback)
				->onlyForHttpGet();
	}

    public function load() { 
		$this->_registerAjaxActions();
		$this->_registerWebPageAssets();
	}

	private function _registerAjaxActions() {
		$this->_getLogFileContentsAjaxAction
			->register();
	}

	private function _registerWebPageAssets() {
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueStyles'));
		add_action('admin_enqueue_scripts', 
			array($this, 'onAdminEnqueueScripts'));
	}

	public function onAdminEnqueueStyles() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeStyleAdminSystemLogs();
		}
	}

	public function onAdminEnqueueScripts() {
		if ($this->_shouldEnqueueWebPageAssets()) {
			Abp01_Includes::includeScriptAdminSystemLogs(array());
		}
	}

	private function _shouldEnqueueWebPageAssets() {
		return $this->_isViewingSystemLogsPage();
	}

	private function _isViewingSystemLogsPage() {
		return $this->_env->isAdminPage('abp01-system-logs');
	}
	
	public function getMenuItems() {
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

	public function displayLogsManagementPage() {
		if (!$this->_currentUserCanManagePluginSettings()) {
			die;
		}

		$debugLogFiles = array();
		$errorLogFiles = array();

		$logFiles = $this->_logManager->getLogFiles();
		
		foreach ($logFiles as $lf) {
			if ($lf->isDebugLogFile()) {
				$debugLogFiles[] = $lf->asPlainObject();
			} else if ($lf->isErrorLogFile()) {
				$errorLogFiles[] = $lf->asPlainObject();
			}
		}

		$data = new stdClass();

		$data->ajaxUrl = $this->_getAjaxBaseUrl();
		$data->ajaxGetLogFileAction = ABP01_ACTION_GET_LOG_FILE_CONTENTS;
		$data->getLogFileNonce = $this->_getLogFileContentsAjaxAction
			->generateNonce();

		$data->isDebugLoggingEnabled = $this->_logManager->isErrorLoggingEnabled();
		$data->isErrorLoggingEnabled = $this->_logManager->isErrorLoggingEnabled();

		$data->hasErrorLogFiles = !empty($errorLogFiles);
		$data->errorLogFiles = $errorLogFiles;
		
		$data->hasDebugLogFiles = !empty($debugLogFiles);
		$data->debugLogFiles = $debugLogFiles;

		echo $this->_view->renderAdminSystemLogsPage($data);
	}

	public function getLogFileContents() {
		$fileId = Abp01_InputFiltering::getFilteredGETValue('abp01_fileId');
		if (empty($fileId)) {
			die;
		}

		$logFiles = $this->_logManager->getLogFiles();

		/**
		 * @var Abp01_Logger_FileInfo $foundFile
		 */
		$foundFile = null;
		foreach ($logFiles as $logFile) {
			if ($logFile->matchesId($fileId)) {
				$foundFile = $logFile;
				break;
			}
		}

		$response = abp01_get_ajax_response(array(
			'found' => false,
			'trimmed' => false,
			'contents' => null
		));

		$response->found = $foundFile != null;
		$response->trimmed = false;

		if ($logFile != null) {
			$response->trimmed = $logFile->getFileSize() > 500000;
			$response->contents = !$response->trimmed
				? $logFile->contents()
				: $logFile->tail(200);
		} else {
			$response->contents = null;
		}

		$response->success = true;
		return $response;
	}
}