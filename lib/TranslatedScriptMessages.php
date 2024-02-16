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
	exit ;
}

class Abp01_TranslatedScriptMessages {
	public static function getCommonScriptTranslations() {
		return array(
			'lblConfirmTitle' => esc_html__('Confirm', 'abp01-trip-summary'),
			'lblConfirmQuestion' => esc_html__('Are you sure you want to proceed?', 'abp01-trip-summary'),
			'btnYes' => esc_html__('Yes', 'abp01-trip-summary'),
			'btnNo' => esc_html__('No', 'abp01-trip-summary')
		);
	}

	public static function getAdminSystemLogsManagementTranslations() {
		return array(
			'msgConfirmLogFileRemoval' => esc_html__('Are you sure you want to remove this log file?', 'abp01-trip-summary'),
			'msgLogFileRemovalSuccess' => esc_html__('The log file has been successfully deleted.', 'abp01-trip-summary'),
			'errCouldNotRemoveLogFile' => esc_html__('The log file could not be deleted', 'abp01-trip-summary'),
			'errCouldNotLoadLogFile' => esc_html__('The log file could not be loaded', 'abp01-trip-summary'),
			'errCouldNotFindLogFile' => esc_html__('The log file could not be found', 'abp01-trip-summary')
		);
	}

	public static function getAdminTripSummaryAdminLogEntriesTranslations() {
		return array(
			'msgSaveWorking' => esc_html__('Saving trip summary log entry. Please wait...', 'abp01-trip-summary'),
			'msgDeleteLogEntryWorking' => esc_html__('Deleting trip summary log entry. Please wait...', 'abp01-trip-summary'),
			'msgDeleteAllLogEntriesWorking' => esc_html__('Deleting all trip summary log entries for this post. Please wait...', 'abp01-trip-summary'),
			'msgLoadAdminLogEntryByIdForEditing' => esc_html__('Loading log entry. Please wait...', 'abp01-trip-summary'),
			
			'msgConfirmLogEntryRemoval' => esc_html__('Remove selected log entry? This action cannot be undone!', 'abp01-trip-summary'),
			'msgConfirmLogAllEntriesRemoval' => esc_html__('Remove all log entries for this post? This action cannot be undone!', 'abp01-trip-summary'),

			'msgLogEntryDeleted' => esc_html__('The trip summary log entry has been successfully deleted', 'abp01-trip-summary'),
			'msgLogAllEntriesDeleted' => esc_html__('All the trip summary log entries for this post have been successfully deleted', 'abp01-trip-summary'),
			
			'errCouldNotSaveLogEntry' => esc_html__('The trip summary log entry could not be saved', 'abp01-trip-summary'),
			'errCouldNotDeleteLogEntry' => esc_html__('The trip summary log entry could not be deleted', 'abp01-trip-summary'),
			'errCouldNotDeleteAllLogEntries' => esc_html__('The trip summary log entries for this post could not be deleted', 'abp01-trip-summary'),
			'errCouldNotLoadAdminLogEntryDataById' => esc_html__('The log entry could not be loaded.', 'abp01-trip-summary'),

			'lblLogEntryAddFormTitle' => esc_html__('Add trip summary log entry', 'abp01-trip-summary'),
			'lblLogEntryEditFormTitle' => esc_html__('Edit trip summary log entry', 'abp01-trip-summary'),

			'lblTripSummaryLogPresent' => esc_html__('Trip summary log is present for this post', 'abp01-trip-summary'),
			'lblTripSummaryLogNotPresent' => esc_html__('Trip summary log is not present for this post', 'abp01-trip-summary')
		);
	}

	public static function getAdminTripSummaryEditorScriptTranslations() {
		return array(
			'btnClearInfo' => esc_html__('Clear info', 'abp01-trip-summary'), 
			'btnClearTrack' => esc_html__('Clear track', 'abp01-trip-summary'), 
			'lblPluploadFileTypeSelector' => esc_html__('GPX files', 'abp01-trip-summary'), 
			'lblGeneratingPreview' => esc_html__('Generating preview. Please wait...', 'abp01-trip-summary'), 
			'lblTrackUploadingWait' => esc_html__('Uploading track', 'abp01-trip-summary'), 
			'lblTrackUploaded' => esc_html__('The track has been uploaded and saved successfully', 'abp01-trip-summary'), 
			'lblTypeBiking' => esc_html__('Biking', 'abp01-trip-summary'), 
			'lblTypeHiking' => esc_html__('Hiking', 'abp01-trip-summary'), 
			'lblTypeTrainRide' => esc_html__('Train ride', 'abp01-trip-summary'), 
			'lblClearingTrackWait' => esc_html__('Clearing track. Please wait...', 'abp01-trip-summary'), 
			'lblTrackClearOk' => esc_html__('The track has been successfully cleared', 'abp01-trip-summary'), 
			'lblTrackClearFail' => esc_html__('The data could not be updated', 'abp01-trip-summary'), 
			'lblTrackClearFailNetwork' => esc_html__('The data could not be updated due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
			'lblSavingDataWait' => esc_html__('Saving data. Please wait...', 'abp01-trip-summary'), 
			'lblDataSaveOk' => esc_html__('The data has been saved', 'abp01-trip-summary'), 
			'lblDataSaveFail' => esc_html__('The data could not be saved', 'abp01-trip-summary'), 
			'lblDataSaveFailNetwork' => esc_html__('The data could not be saved due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
			'lblClearingInfoWait' => esc_html__('Clearing trip info. Please wait...', 'abp01-trip-summary'), 
			'lblClearInfoOk' => esc_html__('The trip info has been cleared', 'abp01-trip-summary'), 
			'lblClearInfoFail' => esc_html__('The trip info could not be cleared', 'abp01-trip-summary'), 
			'lblClearInfoFailNetwork' => esc_html__('The trip info could not be cleared due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
			'errPluploadTooLarge' => esc_html__('The selected file is too large. Maximum allowed size is 10MB', 'abp01-trip-summary'), 
			'errPluploadFileType' => esc_html__('The selected file type is not valid. Only GPX, GeoJSON and KML files are allowed', 'abp01-trip-summary'), 
			'errPluploadIoError' => esc_html__('The file could not be read', 'abp01-trip-summary'), 
			'errPluploadSecurityError' => esc_html__('The file could not be read', 'abp01-trip-summary'), 
			'errPluploadInitError' => esc_html__('The uploader could not be initialized', 'abp01-trip-summary'), 
			'errPluploadHttp' =>  esc_html__('The file could not be uploaded', 'abp01-trip-summary'), 
			'errServerUploadFileType' =>  esc_html__('The selected file type is not valid. Only GPX, GeoJSON and KML files are allowed', 'abp01-trip-summary'), 
			'errServerUploadTooLarge' =>  esc_html__('The selected file is too large. Maximum allowed size is 10MB', 'abp01-trip-summary'), 
			'errServerUploadNoFile' =>  esc_html__('No file was uploaded', 'abp01-trip-summary'), 
			'errServerUploadInternal' =>  esc_html__('The file could not be uploaded due to a possible internal server issue', 'abp01-trip-summary'), 
			'errServerUploadStoreFailed' => esc_html__('The file could not be stored on the server (#2). This usually indicates an internal server issue.', 'abp01-trip-summary'),
			'errServerUploadStoreInitiationFailed' => esc_html__('The file could not be stored on the server (#1). This usually indicates an internal server issue.', 'abp01-trip-summary'),
			'errServerUploadInvalidUploadParams' => esc_html__('The upload request contains some invalid parameters. This might indicate an error within the plug-in itself or an attempt to forge the request.', 'abp01-trip-summary'),
			'errServerUploadDestinationFileNotFound' => esc_html__('The destination were the track file was uploaded cannot be found. This usually indicates an internal server issue.', 'abp01-trip-summary'),
			'errServerUploadDestinationFileCorrupt' => esc_html__('The destination were the track file was uploaded has been found, but is corrupt. This usually indicates a problem with the file itself or, less likely, an internal server issue.', 'abp01-trip-summary'),
			'errServerUploadFail' =>  esc_html__('The file could not be uploaded', 'abp01-trip-summary'),
			'errServerCustomValidationFail' => esc_html__('The uploaded file was not a valid GPX / GeoJSON / KML file', 'abp01-trip-summary'), 
			'selectBoxPlaceholder' => esc_html__('Choose options', 'abp01-trip-summary'),
			'selectBoxCaptionFormat' => esc_html__('{0} selected', 'abp01-trip-summary'),
			'selectBoxSelectAllText' => esc_html__('Select all', 'abp01-trip-summary'),
			'lblStatusTextTripSummaryInfoPresent' => esc_html__('Trip summary information is present for this post', 'abp01-trip-summary'),
			'lblStatusTextTripSummaryInfoNotPresent' => esc_html__('Trip summary information is not present for this post', 'abp01-trip-summary'),
			'lblStatusTextTripSummaryTrackPresent' => esc_html__('Trip summary track is present for this post', 'abp01-trip-summary'),
			'lblStatusTextTripSummaryTrackNotPresent' => esc_html__('Trip summary track is not present for this post', 'abp01-trip-summary'),
			'lblWarnRemoveTripSummaryInfo' => esc_html__('Are you sure you want to remove the trip summary information? This action cannot be undone!', 'abp01-trip-summary'),
			'lblWarnRemoveTripSummaryTrack' => esc_html__('Are you sure you want to remove the trip summary track? This action cannot be undone!', 'abp01-trip-summary')
		);
	}

	public static function getAdminSettingsScriptTranslations() {
		return array(
			'errSaveFailNetwork' => esc_html__('The settings could not be saved due to a possible network error or an internal server issue', 'abp01-trip-summary'), 
			'errSaveFailGeneric' => esc_html__('The settings could not be saved due to a possible internal server issue', 'abp01-trip-summary'), 
			'msgSaveOk' => esc_html__('Settings successfully saved', 'abp01-trip-summary'), 
			'msgSaveWorking' => esc_html__('Saving settings. Please wait...', 'abp01-trip-summary')
		);
	}

	public static function getAdminLookupScriptTranslations() {
		return array(
			'msgWorking' => esc_html__('Working. Please wait...', 'abp01-trip-summary'),
			'msgSaveOk' => esc_html__('Item successfully saved', 'abp01-trip-summary'),
			'addItemTitle' => esc_html__('Add new item', 'abp01-trip-summary'),
			'editItemTitle' => esc_html__('Modify item', 'abp01-trip-summary'),
			'errFailNetwork' => esc_html__('The item could not be saved due to a possible network error or an internal server issue', 'abp01-trip-summary'),
			'errFailGeneric' => esc_html__('The item could not be saved due to a possible internal server issue', 'abp01-trip-summary'),
			'ttlConfirmDelete' => esc_html__('Confirm item removal', 'abp01-trip-summary'),
			'errDeleteFailedNetwork' => esc_html__('The item could not be deleted due to a possible network error or an internal server issue', 'abp01-trip-summary'),
			'errDeleteFailedGeneric' => esc_html__('The item could not be deleted due to a possible internal server issue', 'abp01-trip-summary'),
			'msgDeleteOk' => esc_html__('The item has been successfully deleted', 'abp01-trip-summary'),
			'errListingFailNetwork' => esc_html__('The lookup items could not be loaded due to a possible network error or an internal server issue', 'abp01-trip-summary'),
			'errListingFailGeneric' => esc_html__('The lookup items could not be loaded', 'abp01-trip-summary')
		);
	}

	public static function getAdminMaintenanceScriptTranslations() {
		return array(
			'msgWorking' => esc_html__('Working. Please wait...', 'abp01-trip-summary'),
			'msgConfirmExecute' => esc_html__('Are you sure you want to execute the selected tool?', 'abp01-trip-summary'),
			'msgExecutedOk' => esc_html__('The selected maintenance tool successfully executed.', 'abp01-trip-summary'),
			'msgExecutedFailGeneric' => esc_html__('The selected maintenance tool could not be executed.', 'abp01-trip-summary'),
			'msgExecutedFailNetwork' => esc_html__('The selected maintenance tool could not be executed due to a possible network issue. Please check your internet connection and try again.', 'abp01-trip-summary')
		);
	}

	public static function getFrontendViewerScriptTranslations() {
		return array(
			'lblMinAltitude' => esc_html__('Minimum altitude:', 'abp01-trip-summary'),
			'lblMaxAltitude' => esc_html__('Maximum altitude:', 'abp01-trip-summary'),
			'lblAltitude' => esc_html__('Altitude:', 'abp01-trip-summary'),
			'lblDistance' => esc_html__('Distance:', 'abp01-trip-summary'),
			'lblItemValuesShow' => esc_html__('(show)', 'abp01-trip-summary'),
			'lblItemValuesHide' => esc_html__('(hide)', 'abp01-trip-summary')
		);
	}

	public static function getAdminHelpScriptTranslations() {
		return array(
			'msgWorking' => esc_html__('Loading help contents. Please wait...', 'abp01-trip-summary'),
			'errLoadHelpContentsFailNetwork' => esc_html__('The help contents could not be loaded due to a possible network error or an internal server issue.', 'abp01-trip-summary'),
			'errLoadHelpContentsGeneric' => esc_html__('The help contents could not be loaded.', 'abp01-trip-summary')
		);
	}

	public static function getAdminListingAuditLogScriptTranslations() {
		return array(
			'msgWorking' => esc_html__('Loading audit log. Please wait...', 'abp01-trip-summary'),
			'errFailedToLoadAuditLog' => esc_html__('The audit log could not be loaded!', 'abp01-trip-summary')
		);
	}
}