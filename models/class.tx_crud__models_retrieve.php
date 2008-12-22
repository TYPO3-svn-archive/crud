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
require_once(t3lib_extMgm::extPath('crud') . 'models/class.tx_crud__models_common.php');
class tx_crud__models_retrieve extends tx_crud__models_common{
	
	function setMode() {
		$this->mode = "PROCESS";
		//if($this->controller->parameters->get("retrieve")) $this->panelRecord=$this->controller->parameters->get("retrieve");
	}
	
	public function getData($type="preview") {
		if (!is_array($this->processData)) {
			$this->processQuery();
		}
		
		$daten = $this->processData;
		
		if (is_array($this->processData)) {
			foreach ($daten as $num=>$data) {
				if (is_array($data)) {
					foreach ($data as $key=>$result) {
						if (is_array($this->html[$key]['options.'])) {
							$values = explode(",",$result);
							if (count($values) <= 1) {
								$values = array();
								$values[$result] = $result;
							}
							$array = array();
							if (is_array($values)) {
								foreach($values as $k=>$v) {
									if ($this->html[$key]['config.']['MM']) {
										$array = $this->getDataMM($this->panelRecord,$key);
									} else {
										$array[$v] = $this->html[$key]['options.'][$v];
									}
								}
							}
							if ($this->html[$key]['config.']['MM']) {
								$mmValues = array();
								if (is_array($array)) {
									foreach ($array as $uid=>$mm) {
										$mmValues[] = $this->html[$key]['options.'][$uid];
									}
								}
								$daten[$num][$key] = implode(",",$mmValues);
							} else {
								$daten[$num][$key] = implode(",",$array);
							}
						}
					}
				}
			}
		}
		
		$this->processData = $daten;
		return $daten;
	}
	
	function _nextStep(){}
	function setSubmit() {
		$this->submit = true;
		$this->type = "PROCESS";
	}
	
	function checkNode() {
		$where = 'uid=' . $this->panelRecord;
		$table = strtolower($this->panelTable);
		$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid",$table,$where);
		if (!$query) {
			$this->mode=='NOT_EXIST';
		}
	}

	function _getValue($item_key)  {
		return $this->processData[$item_key];
	}

	function processQuery() {
		$this->retrieveQuery();
	}

	function retrieveQuery() {
		$this->preQuery();
		if (!$this->getStorageNodes()) {
			// TODO: Localization
			die("keine PanelRecord beim Retrieve gesetzt");
		}
		if (!is_array($this->processData)) {
			$where = 'uid=' . $this->panelRecord;
			if(strlen($this->bigTCA['ctrl']['languageField'])>=3) {
			$where.=" AND ".$this->bigTCA['ctrl']['languageField']."=".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
			}
			$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid," . $this->fields,strtolower($this->panelTable),$where);
			$result=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
			$uid=$result['uid'];
			unset($result['uid']);
			
			$this->processData[$uid] = $result;
			$config = $this->controller->configurations->getArrayCopy();
			if ($config['enable.']['logging'] == 1) {
				//echo "schreibe log";
				tx_crud__log::write($config['storage.']['action'], $this->panelRecord, $config['storage.']['nameSpace'],$config['logging.']);
			}
		}
		$this->postQuery();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/models/class.tx_crud_models_retrieve.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/models/class.tx_crud_models_retrieve.php']);
}
?>