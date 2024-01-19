<?php
interface Abp01_Route_Log_Manager {
	/**
	 * @param int $postId 
	 * @return Abp01_Route_Log
	 */
	function getAdminLog($postId);

	/**
	 * @param int $postId 
	 * @return Abp01_Route_Log
	 */
	function getPublicLog($postId);

	/**
	 * @param Abp01_Route_Log_Entry $logEntry 
	 * @return bool
	 */
	function saveLogEntry(Abp01_Route_Log_Entry $logEntry);

	/**
	 * @param int $postId 
	 * @return bool
	 */
	function deleteLog($postId);

	/**
	 * @param Abp01_Route_Log_Entry $logEntry 
	 * @param int $postId
	 * @param int $logEntryId
	 * @return bool
	 */
	function deleteLogEntry($postId, $logEntryId);

	/**
	 * @return void
	 */
	function clearAllLogEntries();

	/**
	 * @param int $postId 
	 * @param int $logEntryId 
	 * @return Abp01_Route_Log_Entry
	 */
	function getLogEntryById($postId, $logEntryId);

	/**
	 * @param int $postId 
	 * @return string
	 */
	function getLastUsedVehicle($postId);
}