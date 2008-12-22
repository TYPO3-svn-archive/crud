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
require_once(t3lib_extMgm::extPath('crud') . 'models/class.tx_crud__models_retrieve.php');
class tx_crud__models_browse extends tx_crud__models_retrieve{
	
	var $start = 0;
	var $limit = 5;
	var $data;
	var $count = 0;
	var $page = 0;
	var $panelAction = "BROWSE";
	
	public function getData($type="preview") {
		$start = microtime(true);
		//echo "<br>QUERY time:".round($stop-$start,3);
		if (!is_array($this->processData)) {
			$this->processQuery();
		}
//		/t3lib_div::debug($this->html[$key]);
		$daten = $this->processData;
		if (is_array($this->processXXData)) {
			foreach ($this->processData as $num=>$data) {
				if (is_array($data)) {
					foreach ($data as $key=>$result) {
						//t3lib_div::debug($this->html[$key]);
						if (is_array($this->html[$key]['options.'])) {
						
							$values = explode(",",$result);
							if (count($values) <= 1) {
								$values = array();
								$values[$result] = $result;
							}
							$array = array();
							if (is_array($values)) {
								foreach ($values as $k=>$v) {
									if ($this->html[$key]['config.']['MM']) {
										$array = $this->getDataMM($num,$key);
									} else {
										$array[$v]=$this->html[$key]['options.'][$v];
									}
								}
							}
							//echo "ok";
							if ($this->html[$key]['config.']['MM']) {
								$mmValues = array();
								if (is_array($array)) {
									foreach ($array as $uid=>$mm) {
										$mmValues[] = $this->html[$key]['options.'][$uid];
									}
								}
								$daten[$num][$key] = implode(",",$mmValues);
							} else {
								$daten[$num][$key]=implode(",",$array);
							}
						}
					}
				}
			}
		}
		//t3lib_div::debug($daten);
		return $daten;
	}

	public function processQuery() {
		//echo"processQuery()";
		$this->processData = array();
		$this->browseQuery();
	}

	
	protected function browseQuery() {
		$start = microtime(true);
		$this->preQuery();
		$typoscript = $this->controller->configurations->getArrayCopy();
		$config = $typoscript;
		$this->limit = $config['view.']['limit'];
		$pars = $this->controller->parameters->getArrayCopy();
		if (!empty($pars['limit'])) {
			$this->limit = $pars['limit'];
		}
		if ($this->page >= 1) {
			$this->start = $this->limit * $this->page;
		}
		if(!empty($pars['upper'])){ 
			$sort=" ORDER BY ".$pars['upper']." ASC";
		} elseif(!empty($pars['lower'])) { 
			$sort=" ORDER BY ".$pars['lower']." DESC";
		} else {
			$sort = "";
		}
		$where = $this->getFilterWhere();
		if (!$pars['search']) {
			$count = tx_crud__cache::get($this->getCacheHash()."-counter");
		}
		if ($count < 1) {
			$countQuery = $GLOBALS['TYPO3_DB']->exec_SELECTquery("count(uid)",$this->getStorageNameSpace(),$where);
			if ($countQuery) {
				$countResult = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($countQuery);
				$count = $countResult['count(uid)'];
				if (!$pars['search']) {
					if($config['enable.']['caching'])tx_crud__cache::write($this->getCacheHash()."-counter",$count);
				}
			}
		}
		$this->size = $count;
		//debug($count,"count");;
		if ($typoscript['additionalWhere']) {
			$where.=" AND ".$typoscript['additionalWhere'];
		}
		
		$sql="select uid,pid,".$this->getStorageFields()." from ". $this->getStorageNameSpace(). " where ".$where.$sort." LIMIT ".$this->start.",". ($this->limit); 
		//echo $sql; 
		$query = $GLOBALS['TYPO3_DB']->sql_query($sql); 
		if ($query) {
			for ($i = 0; $i < $GLOBALS['TYPO3_DB']->sql_affected_rows($query); $i++) {
				//debug($i,$this->size);
				if ($i < $this->size) {
					$GLOBALS['TYPO3_DB']->sql_data_seek($query,$i); 
					$res = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
					if ($res['hidden'] == 0 && $res['deleted'] == 0) {
						$this->processData[$res['uid']] = $res;
					}
				}
			}
		} else {
			$this->mode = "QUERY_ERROR";
		}

		if (!$query) {
			$this->mode = "QUERY_ERROR";
		}
		$this->postQuery();
		$config['view.']['count'] = $this->size;
		$config['view.']['limit'] = $this->limit;
		$config['view.']['page'] = $this->page;
		$config['view.']['start'] = $this->start;
		$this->controller->configurations = new tx_lib_object($config); 
		
	}
	
	function _nextStep(){}
	
	
	function getFilterWhere() {
		$pars = $this->controller->parameters->getArrayCopy();
		$typoscript = $this->controller->configurations->getArrayCopy();
		$config = $typoscript;
		$search = "pid=" . $this->panelRecord . " AND hidden=0 AND deleted=0";
		unset($pars['page']);
		unset($pars['lower']);
		unset($pars['upper']);
		unset($pars['limit']);
		if (!is_array($pars['search']) && strlen($pars['search']) >= 1) {
			$words[] = $pars['search'];
			$fields = explode(",","uid,pid," . $this->getStorageFields());
			foreach ($fields as $key=>$val) {
				if ($this->html[$val]['search'] == 1) {
					$searchFields[$val] = $val;
				}
			}
			$i = 0;
			if (is_array($searchFields)) {
				foreach ($searchFields as $key=>$val) {
					foreach ($words as $k=>$v) {
						if ($i == 0) {
							$textsearch .= " AND (" . $val . " like '%$v%'";
						} else {
							$textsearch .= " OR " . $val . " like '%$v%'";
						}
					}
					$i++;
				}
			}
		}
		if (strlen($textsearch) >= 3) {
			$search .= $textsearch . ")";
		}
		$eval = array();
		if (is_array($pars['search'])) {
			foreach ($pars['search'] as $key=>$val) {
				$filter = $pars['search'][$key];
				if (strlen($val['min'])>=1 && is_array($filter) && isset($filter['min']) && $filter['min'] >= 0) {
					if (!$date) {
						$search .= " AND " . $key . " >= " . $val['min'];
					} else {
						$search .= " AND " . $key . " <= " . $val['min'];
					}
				}
				if (strlen($val['max'])>=1 && is_array($filter) && isset($filter['max']) && $filter['max'] >= 0) {
					if (!$date) {
						$search .= " AND " . $key . " <= " . $val['max'];
					} else {
						$search .= " AND " . $key . " >= " . $val['max'];
					}
				}
				if (is_array($filter) && isset($filter['integer']) && $filter['leng'] > 0) {
					$search .= " AND " . $key . " like '" . $val . "%'";
				}
				if (is_array($filter) &&  $filter['is'] > 1) {

					if(is_array($filter['is'])) {
						foreach($filter['is'] as $k=>$v) if(strlen($v)>=1) $search .= " AND FIND_IN_SET(".$v.",".$key.")";
					}
					else $search .= " AND FIND_IN_SET(".$filter['is'].",".$key.")";
				}
				if (!is_array($filter) && !empty($val)) {
					if($val=="on") $val=1;
    				elseif($val=="off") $val=0; 
					if (is_array($eval) && in_array('int',$eval) || in_array('integer',$eval)) {
						$search .= " AND " . $key . " = $val";
					} elseif (is_numeric($val)) {
						$search .= " AND " . $key . " = '$val'";
					} else {
						$search .= " AND " . $key . " like '%$val%'";
					}
				}
			}
		}
		if(strlen($this->bigTCA['ctrl']['languageField'])>=3) {
			$search.=" AND ".$this->bigTCA['ctrl']['languageField']."=".$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		}
		return $search;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud_browser/models/class.tx_crudbrowser_models_browse.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud_browser/models/class.tx_crudbrowser_models_browse.php']);
}
?>