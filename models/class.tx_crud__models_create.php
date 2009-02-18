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
 * Depends on: lib/div
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */
require_once(t3lib_extMgm::extPath('crud') . 'models/class.tx_crud__models_common.php');
class tx_crud__models_create extends tx_crud__models_common{

	// -------------------------------------------------------------------------------------
	// database create queries
	// -------------------------------------------------------------------------------------
	
	/**
	 * overwrite of the query call in common
	 * 
	 * @return  void
	 */	
	public function processQuery() {		
		$this->createQuery();
	}

	/**
	 * makes the create query
	 * 
	 * @return  void
	 */	
	protected function createQuery() {
		foreach ($this->html as $key=>$val) {
			if (is_array($val['process'])) {
				$this->processData[$key] = implode(",",$val['process']);
			} elseif ($val['processMM']) {
				$this->processMM[$key] = $val;
				$this->processData[$key] = sizeof($val['processMM']);
			} else {
				$this->processData[$key] = $val['process'];
			}
		}
		$this->preQuery();
		if (is_array($this->controller->configurations['storage.']['defaultQuery.'][$this->panelTable."."])) {
			$insert = $this->controller->configurations['storage.']['defaultQuery.'][$this->panelTable."."];
		}
		foreach ($this->processData as $key=>$val) {
			if (strlen($val) >= 1) {
				$insert[$key] = $val;
			}
		}
		unset($insert['captcha']);
		$insert["pid"] = $this->panelRecord;
		$insert["tstamp"] = time();
		if(strlen($this->bigTCA['ctrl']['languageField'])>=3) {
			$insert[$this->bigTCA['ctrl']['languageField']]=$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		}
		if ($this->mode == 'PROCESS') {
			$query = $GLOBALS['TYPO3_DB']->exec_INSERTquery(strtolower($this->panelTable),$insert);
		}
		if (!$query) {
			$this->mode="QUERY_ERROR";
		} else {
			$config = $this->controller->configurations->getArrayCopy();
			$this->lastQueryID = $GLOBALS['TYPO3_DB']->sql_insert_id();
			if ($config['enable.']['logging'] == 1) {
				tx_crud__log::write($config['storage.']['action'], $this->lastQueryID, $config['storage.']['nameSpace'],$config['logging.']);
			}
			if (is_array($this->processMM)) {
				$uid_local = $this->lastQueryID;
				foreach ($this->processMM as $key=>$val) {
					$table = $this->html[$key]['config.']['MM'];
					if (is_array($val['processMM'])) {
						$i = 1;
						foreach ($val['processMM'] as $k=>$v) {
							$what = explode("__",$v);
							if (count($what) > 1) {
								$tableName = $what[0];
								$v = $what[1];
							}
							$insertMM = array();
							$insertMM['uid_local'] = $uid_local;
							$insertMM['uid_foreign'] = $v;
							$insertMM['tablenames'] = $tableName;
							$insertMM['sorting'] = $i; 
							if (!$GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertMM)) {
								$this->mode="QUERY_ERROR";
							}
							$i++;
						}
					}
				}
			}
			$this->postQuery();
			$this->processData = false;
			$this->processMM = false;
		}
	}

	// -------------------------------------------------------------------------------------
	// create data helpers
	// -------------------------------------------------------------------------------------
	
	/**
	 * sets the values for a create form to the setup
	 * 
	 * @return  void
	 */	
	function setupValues() {
		$orgMode=$this->mode;
		$pars = $this->controller->parameters->getArrayCopy();
		if(is_array($this->html))foreach ($this->html as $item_key=>$entry) {
			$item_value = $this->_getValue($item_key);	
			if(($entry['config.']['type'] == "input" || $entry['config.']['type'] == "text" )) {
				$eval = $this->_processEval($item_key);
				if (is_array($eval)) {
					foreach ($eval as $key=>$val) {
						$entry[$key] = $val;
					}
				}
				$eval_exploded = explode(",",$entry['config.']['eval']);
				if (in_array('datetime',$eval_exploded) || in_array('date',$eval_exploded)) {
					if (!$entry['config.']['splitter']) {
						$entry['config.']['splitter'] = ".";//TODO: set default date splitter/format in TS
					}
					if (!$entry['config.']['format']) {
						$entry['config.']['format']="dd.mm.yyyy"; //TODO: Ist das sinnvoll?
					}
					$split = $entry['config.']['splitter'];
					if (strlen($entry['config.']['format']) >= 2) {
						$format = explode($split,$entry['config.']['format']);
					}
					if (!$entry['config.']['output']) {
						if (in_array('datetime',$eval_exploded)) $entry['config.']['output']="H:i d.m.Y";
						if (in_array('date',$eval_exploded)) $entry['config.']['output']="d.m.Y";
					}
				}
				if (in_array('datetime',$eval_exploded)) {
					$item_value = $this->_getValue($item_key);
					if($entry['process'] !=0) {
						$date_exploded=explode(" ",date($entry['config.']['output'],$entry['process']));
						$entry['value']['time'] = $date_exploded[0]; 
						$entry['value']['date'] = $date_exploded[1];
						$entry['preview'] = date($entry['config.']['output'],$entry['process']);
					}
				} 
				elseif (in_array('date',$eval_exploded) && is_numeric($this->_getValue($item_key)))  {
					$item_value = $this->_getValue($item_key);
					if ($entry['process'] != 0) {;
						$item_value=date($entry['config.']['output'],$entry['process']);
						$entry['value'] = $item_value;
						$entry['preview'] = $item_value;
					}
				} 
				elseif (strlen($item_value) >= 1) {
					$entry['value'] = $item_value;
					$entry['process'] = $item_value;
					$entry['preview'] = $item_value;
				}
				$this->html[$item_key] = $entry;
			} 
			elseif ($entry['config.']['type'] == "select") {
				$eval = $this->_processEval($item_key);
				if (is_array($eval)) {
					foreach ($eval as $key=>$val) {
						$entry[$key] = $val;
					}
				}
				if ($entry['config.']['maxitems'] > 1) {
					$multiple = 1;
					$entry["element"] = "multiselectRow";
				} else {
					$entry["element"] = "selectRow";
				}
				$item_value = $this->_getValue($item_key);
				
				if (is_array($item_value)) {
					$values = $item_value;
					$item_value = array();
					foreach ($values as $k=>$v) {
						$item_value[$v] = $v;
					}
				}
				if (is_array($entry['options.'])) {
					foreach ($entry['options.'] as $uid=>$val) {
						if (isset($multiple) && is_array($item_value) && in_array($uid,$item_value)) {
							if ($entry['config.']['MM']) {
								$entry["processMM"] = $item_value;
							} else {
								$entry["process"] = $item_value;
							}
							$entry["value"] = $item_value;
						} elseif ($uid == $item_value) {
							if ($entry['config.']['MM']) {
								$html["processMM"] = $item_value;
							} else {
								$entry["process"] = $item_value;
							}
							$entry["value"] = $item_value;
						} else {
							if ($entry['config.']['MM']) {
								$entry["processMM"] = $item_value;
							} else {
								$entry["process"] = $item_value;
							}
							$entry["value"] = $item_value;
						}
					}
				}
				if (isset($this->cType[$item_key]))$entry['reload'] = 1;
				if (is_array($entry['value'])) {
					$preview = array();
					foreach ($entry['value'] as $key=>$val) {
						$preview[] = $entry['options.'][$key];
					}
					$entry['preview'] = implode(",",$preview);
				} elseif (strlen($entry['value']) >= 1) {
					$entry['preview'] = $entry['options.'][$entry['value']];
				}
				$this->html[$item_key] = $entry;
			} 
			elseif ($entry['config.']['type'] == "radio") {
				$eval = $this->_processEval($item_key);
				if (is_array($eval)) {
					foreach ($eval as $key=>$val) {
						$entry[$key] = $val;
					}
				}
				$item_value = $this->_getValue($item_key);
				if (strlen($items[$item_value]) >= 1) {
					$entry['process'] = $item_value;
					$entry['value'] = $item_value;
					$entry['preview'] = $items[$item_value];
				}
				$this->html[$item_key] = $entry;
			} elseif ($entry['config.']['type'] == "check") {
				$eval = $this->_processEval($item_key);
				if (is_array($eval)) {
					foreach($eval as $key=>$val) $entry[$key] = $val;
				}
				$items = $entry['attributes.']['options.'];
				$item_value = $this->_getValue($item_key);
				if (is_array($entry['config.']['items'])) {
					$items = $entry['config.']['items'];
					$item_process = 0;
					$y = 1;
					for ($i = 0; $i < sizeof($items); $i++) {
						$options[$i] = $items[$i][0];
						if (is_array($item_value) && in_array($i,$item_value)) {
							$item_process += $y;
							$value[$y] = $items[$i][0];
						}
						$y = $y * 2;
					}
					$entry["process"] = $item_process;
					if (strtoupper($this->panelAction) == "UPDATE" && !$pars[$item_key] && is_numeric($this->_getValue($item_key))) {
						$entry["process"] = $this->_getValue($item_key);
						$db = $entry['process'];
						$y = 1;
						for ($i = 1; $i <= count($entry['config.']['items']); $i++) {
							$data[$y] = $y;
							$y = $y * 2;
						}
						$data = array_reverse($data);
						$alle = 0;
						foreach ($data as $key=>$val) {
							$alle += $val;
						}
						//wenn alle boxen an
						if ($alle == $db) {
							foreach ($data as $key=>$val) {
								$entry['value'][] = $key; //all ok
							}
						} else {
							$next = true;
							$begin = true;
							$sum = $db;
							$counter = count($data) - 1;
							$size = count($data);
							foreach ($data as $key=>$val) {
								if ($begin) {
									$try = $db;
								} else {
									if ($next) {
										$try = $db;
									} else {
										$try = $zsum;
									}
								}
								$zsum = $try-$val;
								if ($zsum >= 0) {
									$values[$counter] = $val;//wert ok!
									$next = false;
								}
								else {
									$next = true;
									$values[$counter] = false;
								}
								$begin = false;
								$counter--;
							}
						}
						if (is_array($values)) {
							$values = array_reverse($values);
							foreach ($values as $key=>$val) {
								if ($val) {
									$entry['value'][] = $key;
								}
							}
						}
					} else {
						$entry["value"] = $this->controller->parameters->get($item_key);
					}
					if (is_array($entry['value'])) {
						foreach ($entry['value'] as $key=>$val) {
							$preview[] = $options[$key];
						}
						$entry['preview'] = implode(",",$preview);
					} else {
						$entry['preview']="";
					}
					$entry["name"] = $item_key;
					$entry["key"] = $item_key;
					$entry["element"] = "multicheckbox";
					$entry["attributes."]["options."] = $options;
					$entry["attributes."]["name"] = $this->prefixId . "[" . $item_key . "][]";
				} else {
					if (!empty($item_value)) {
						$entry["process"] = 1;
						$entry["value"] = 1;
						$entry["preview"] = "On";
						$entry['attributes.']["value"] = 1;
					} else {
						$entry["process"] = "0";
						$entry['value'] = 0;
						$entry["preview"] = "Off";
						$entry['attributes.']["value"] = 1;
					}
					$entry["key"] = $item_key;
					$entry["element"] = "checkboxRow";
				}
				$this->html[$item_key] = $entry;
			} 
			elseif($entry['config.']['type'] == 'group' && $entry['config.']['internal_type']!="file") {
				$eval = $this->_processEval($item_key);
				if (is_array($eval)) {
					foreach ($eval as $key=>$val) {
						$entry[$key] = $val;
					}
				}
				$item_value = $this->_getValue($item_key);
				$new_value=array();
				if (is_array($item_value) && strlen($item_value[0])>=1) {
					foreach ($item_value as $key=>$val) {
						$new_value[$val] = $val;
					}
					$item_value = $new_value;
				}
				
				if ($entry['config.']['maxitems'] > 1) {
					$multiple = 1;
				}
				$options = $entry['options.'];
				$sorting = $entry['sorting.'];	
				$processValues="";
				if(is_array($item_value)) foreach($item_value as $key=>$value) {
					if(isset($multiple)) {
						if(isset($options[$value])) {
							$processValues[$key]=$value;
							$processPreviews[]=$options[$value];
						}	
					}
					else {
						if(isset($options[$value])) {
							$processValues[$key]=$value;	
							$processPreviews[]=$options[$value];
						}
					}
				}
				if(is_array($processValues)) {
					if ($entry['config.']['MM']) {
						$entry["processMM"] = $processValues;
						$entry["value"] = $processValues;
						$entry["preview"] = implode(",",$processPreviews);
					} else {
						$entry["process"] = $processValues;
						$entry["value"] = $processValues;
						$entry["preview"] = implode(",",$processPreviews);
					}
				}
				$this->html[$item_key] = $entry;
			} 
			elseif($entry['config.']['type'] == 'group' && $entry['config.']['internal_type'] == "file") {
				$eval = $this->_processEval($item_key);
				if (is_array($eval)) {
					foreach ($eval as $key=>$val) {
						$entry[$key] = $val;
					}
				}
				$item_value="";
				$value=$this->_getValue($item_key);
				if(!is_array($item_value) && strlen($item_value)>=3) {
					$item_value[0]=$value;
				}
				else $item_value=$value;
				if (is_array($eval)) {
					foreach ($eval as $key=>$val) $entry[$key] = $val;
				}
				$entry["element"] = "multiFileRow";
				if (is_array($entry['value'])) {
					$entry['preview'] = implode(",",$entry['value']);
				}
				$this->html[$item_key] = $entry;
			}
			$this->html[$item_key] = $entry;
		}
		$this->mode=$orgMode;
	}
	
	/**
	 * returns the form data
	 * 
	 * @return  void
	 */	
	function getData() {
		$pars = $this->controller->parameters->getArrayCopy();
		return $pars;
	}
	
	/**
	 * returns the value from form field
	 * 
	 * @return  void
	 */	
	function _getValue($item_key) {
		$pars = $this->controller->parameters->getArrayCopy();
		if(!is_array($pars[$item_key]) && strlen($pars[$item_key])>=1) return $pars[$item_key];
		if(is_array($pars[$item_key]) && strlen($pars[$item_key][0])>=1) return $pars[$item_key];
	}
}
?>