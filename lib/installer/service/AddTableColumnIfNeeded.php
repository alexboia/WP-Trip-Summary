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

class Abp01_Installer_Service_AddTableColumnIfNeeded {
	/**
	 * @var Abp01_Env
	 */
	private $_env;

	/**
	 * @var \Exception|\WP_Error|null
	 */
	private $_lastError;

	public function __construct(Abp01_Env $env) {
		$this->_env = $env;
	}

	public function execute($tableName, 
		$columnName, 
		array $properties = array()) { 
		$this->_lastError = null;
		
		$columnType = !empty($properties['columnType'])
			? $properties['columnType']
			: 'VARCHAR(255)';

		$notNull = !empty($properties['notNull'])
			? $properties['notNull'] === true
			: false;

		$defaultValue = isset($properties['defaultValue'])
			? $properties['defaultValue']
			: false;

		$afterColumn = !empty($properties['afterColumn'])
			? $properties['afterColumn']
			: false;

		return $this->_addColumnToTable($tableName, 
			$columnName, 
			$columnType, 
			$notNull, 
			$defaultValue, 
			$afterColumn);
	}

	private function _addColumnToTable($tableName, 
		$columnName, 
		$columnType, 
		$notNull, 
		$defaultValue, 
		$afterColumn) {
		$result = false;

		try {
			if (!$this->_columnExists($tableName, $columnName)) {
				$addColumnSql = $this->_buildAddColumnSql($tableName, 
					$columnName, 
					$columnType, 
					$notNull, 
					$defaultValue, 
					$afterColumn);

				$db = $this->_env->getDb();
				$db->rawQuery($addColumnSql);

				$result = $this->_columnExists($tableName, 
					$columnName);
			}
		} catch (Exception $exc) {
			$this->_lastError = $exc;
			$result = false;
		}

		return $result;
	}

	private function _columnExists($tableName, $columnName) {
		$metaDb = $this->_env
			->getMetaDb();

		$metaDb->where('TABLE_NAME', $tableName)
			->where('COLUMN_NAME', $columnName);

		$columnCountRow = $metaDb->getOne('COLUMNS', 
			'COUNT(*) as COLUMN_COUNT');

		return $columnCountRow['COLUMN_COUNT'] 
			> 0;
	}

	private function _buildAddColumnSql($tableName, 
		$columnName, 
		$columnType, 
		$notNull, 
		$defaultValue, 
		$afterColumn) {
		$addColumnSql = 
			"ALTER TABLE `" . $tableName . 
			"` COLUMN `" . $columnName . 
			"` " . $columnType; 
		
		if ($notNull) {
			$addColumnSql . " NOT NULL";
		}

		if ($defaultValue !== false) {
			$defaultValueSql = ($defaultValue !== null 
				? $defaultValue 
				: 'NULL');

			$addColumnSql . " DEFAULT '" . $defaultValueSql . "'";
		}

		if (!empty($afterColumn)) {
			$addColumnSql .= " AFTER `" . $afterColumn . "`";
		}

		return $addColumnSql;
	}

	public function getLastError() { 
		return $this->_lastError;
	}
}