<?php
interface Abp01_Route_Log_Manager {
	/**
	 * @param int $postId 
	 * @return Abp01_Route_Log
	 */
	function getLog($postId);

	/**
	 * @param Abp01_Route_Log $log 
	 * @param int $currentUserId
	 * @return bool
	 */
	function saveLog(Abp01_Route_Log $log, $currentUserId);

	/**
	 * @param int $postId 
	 * @return bool
	 */
	function deleteLog($postId);

	/**
	 * @return void
	 */
	function clearAllLogs();
}