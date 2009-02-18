<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Frank Thelemann
 *  Contact: f.thelemann@yellowmed.com
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Depends on: liv/div
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
include_once(t3lib_extMgm::extPath('crud') . 'models/class.tx_crud__models_common.php');
class tx_crud__models_retrieve extends tx_crud__models_common{

	// -------------------------------------------------------------------------------------
	// database create queries
	// -------------------------------------------------------------------------------------
	
	/**
	 * overwrite of the query call in common
	 * 
	 * @return  void
	 */	
	public function processQuery() {
		if(!is_array($this->processData))$this->retrieveQuery();
	}
	
	/**
	 * makes the retrieve query
	 * 
	 * @return  void
	 */	
	private function retrieveQuery() {
		$this->preQuery();
		if (!$this->getStorageNodes()) {
			die("keine PanelRecord beim Retrieve gesetzt");
		}
		if (!is_array($this->processData)) {
			$where = 'uid=' . $this->panelRecord;
		if(strlen($this->bigTCA['languageField'])>=3 ) {
			if(strlen($GLOBALS['TSFE']->config['config']['sys_language_uid']>=1))$where.=" AND ".$this->bigTCA['languageField']."=".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
			else $where.=" AND ".$this->bigTCA['languageField']."=0";
		}
			$table=$this->panelTable;
			$orgTCA = $this->bigTCA;
			if ($orgTCA['columns']['fe_group']) {
				$where .= " AND (NOT $table.fe_group";
				if ($GLOBALS['TSFE']->fe_user->user['usergroup']) {
					$fegroups = explode(",",$GLOBALS['TSFE']->fe_user->user['usergroup']);
					foreach ($fegroups as $groupid) {
						$where .= " OR $table.fe_group IN ($groupid)";
					}
				}
				$where.=")";
			}
			if($query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid," . $this->getStorageFields(),strtolower($this->panelTable),$where) AND $result=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($query)){
				$uid=$result['uid'];
				unset($result['uid']);
				$this->processData[$uid] = $result;
				$config = $this->controller->configurations->getArrayCopy();
				if ($config['enable.']['logging'] == 1) {
					tx_crud__log::write($config['storage.']['action'], $this->panelRecord, $config['storage.']['nameSpace'],$config['logging.']);
				}
				if ($config['enable.']['histories'] == 1) {
					$this->histories=tx_crud__histories::read($this->panelTable,$this->panelRecord);
				}
			}
			else {
				$where = 'uid=' . $this->panelRecord;
				if($query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid," . $this->getStorageFields(),strtolower($this->panelTable),$where)) $this->mode="NO_RIGHTS";
				else $this->mode="QUERY_ERROR";
			}
		}
		$this->postQuery();
	}

	// -------------------------------------------------------------------------------------
	// SETTER
	// -------------------------------------------------------------------------------------
	
	/**
	 * dummy for overwrite the common setupValues bcause in a retrieve will need no setup the values
	 * 
	 * @return void
	 */	
	public function setupValues() {
	
	}
	
	public function _getValue($item_key) {
		return $this->processData[$this->panelRecord][$item_key];
	}
	
	/**
	 * set the mode for the retrieve
	 * 
	 * @return void
	 */
	public function setMode() {
		$this->mode = "PROCESS";
	}
	
	/**
	 * set the submit state for the retrieve
	 * 
	 * @return void
	 */
	public function setSubmit() {
		$this->submit = true;
		$this->type = "PROCESS";
	}
	
	/**
	 * overwrite the commmon nextStep because in a retriebe we need no redirect
	 * 
	 * @return void
	 */
	function _nextStep(){}
	
	
	// -------------------------------------------------------------------------------------
	// GETTER
	// -------------------------------------------------------------------------------------
	
	/**
	 * returns the data for the retrieve include mm values
	 * 
	 * @return void
	 */
	public function getData($type="preview") {
		if (!is_array($this->processData)) {
			$this->processQuery();
		}
		$daten = $this->processData;
		if (is_array($this->processData)) foreach ($this->processData as $num=>$array) {
			foreach ($array as $key=>$result) {
				if ($this->html[$key]['config.']['MM']) {
					$array = $this->getDataMM($num,$key);
					$mmValues = array();
					if (is_array($array)) {
						foreach ($array as $uid=>$mm) {
							$mmValues[] = $uid;
						}
					}
					$daten[$num][$key] = implode(",",$mmValues);
				}
			}
			
		}
		return $daten;
	}
	
}
?>