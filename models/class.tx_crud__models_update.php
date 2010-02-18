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
require_once(t3lib_extMgm::extPath('crud') . 'models/class.tx_crud__models_create.php');
class tx_crud__models_update extends tx_crud__models_create{

	// -------------------------------------------------------------------------------------
	// database create queries
	// -------------------------------------------------------------------------------------

	/**
	 * overwrite of the query call in common
	 *
	 * @return  void
	 */
	function processQuery() {
		$this->updateQuery();
	}

	/**
	 * makes the update query
	 *
	 * @return  void
	 */
	private function updateQuery() {
		//t3lib_div::debug($this->html['image']);
		foreach ($this->html as $key=>$val) {
			if (is_array($val['process'])) {
				$this->processData[$key] = implode(",",$val['process']);
			} elseif (is_array($val['processMM'])) {
				$this->processMM[$key] = $val;
				$this->processData[$key] = sizeof($val['processMM']);
			} else {
				$val['process'] = str_replace("###ACTION###",strtoupper($this->panelAction),$val['process']);
				$val['process'] = str_replace("###RECORD###",strtoupper($this->panelRecord),$val['process']);
				$val['process'] = str_replace("###TABLE###",strtoupper($this->panelTable),$val['process']);
				$val['process'] = str_replace('<div style="display:none">',"",$val['process']);
				$val['process'] = str_replace('<div style="display: none">',"",$val['process']);
				$val['process'] = str_replace('</div>',"",$val['process']);
				$this->processData[$key] = $val['process'];
			}
		}
		if (is_array($this->controller->configurations['storage.']['defaultQuery.'][$this->panelTable."."])) {
			foreach($this->controller->configurations['storage.']['defaultQuery.'][$this->panelTable."."] as $field=>$value) {
				$this->processData[$field]=$value;
			}

		}
		$this->preQuery();
		$update = $this->processData;
		$update['tstamp'] = time();
		$where = 'uid=' . $this->panelRecord;
		$table = strtolower($this->panelTable);
		$config=$this->controller->configurations->getArrayCopy();
		if ($this->mode == 'PROCESS') {
			if($config['enable.']['histories']) {
				$query4History = $GLOBALS['TYPO3_DB']->sql_query('SELECT '.$this->getStorageFields().' FROM ' . $table . ' WHERE ' . $where);
				$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query4History);
				foreach($result AS $itemkey=>$value) {
					if(isset($this->html[$itemkey]['config.']['MM'])) {
						$mmData=$this->getDataMM($this->panelRecord,$itemkey);
						if(is_array($mmData))$result[$itemkey]=implode(",",array_keys($mmData));
					}
				}
			}
			//t3lib_div::debug($_GET,"get");
			//t3lib_div::Debug($update,$where);
			//t3lib_div::debug($_POST,"psot");
			//die();
			$query = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$update);
			$this->lastQueryID=$this->panelRecord;
		}
		if (!$query) {
			$this->mode='QUERY_ERROR';
		}
		else {
			$TCE = tx_div::makeInstance('t3lib_TCEmain');
			$TCE->admin = 1;
			$TCE->clear_cacheCmd('pages');
			$TCE->clear_cacheCmd($GLOBALS['TSFE']->id);
			$config = $this->controller->configurations->getArrayCopy();
			if ($config['enable.']['logging'] == 1) {
				tx_crud__log::write($config['storage.']['action'], $this->panelRecord, $config['storage.']['nameSpace'],$config['logging.']);
			}
			if($config['enable.']['histories']) {
				tx_crud__histories::write($this->panelTable, $this->panelRecord, $result);
			}
			if(!isset(tx_crud__lock::$status)) {
				tx_crud__lock::init($this->panelTable,$this->panelRecord,$config['locks.']);
			}
			tx_crud__lock::unlock($this->panelTable,$this->panelRecord);
			if (is_array($this->processMM)) {
				foreach($this->processMM as $key=>$val) {
					$uid_local = $this->panelRecord;
					$where = "uid_local=" . $uid_local;
					$table = $val['config.']['MM'];
					$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
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
								die("Fehler beim crud update query in processMM");
							}
							$i *= 16;
						}
					}
				}
			}
			$this->postQuery();
		}
	}

	/**
	 * if no post value from a form isset it returns the db value otherwise the post param
	 *
	 * @param
	 * @return  void
	 */
	function _getValue($item_key)  {

		$TCA = $this->items[$item_key];
		$pars = $this->controller->parameters->getArrayCopy();
		$pars = $this->controller->parameters->getArrayCopy();
		// t3lib_div::debug($pars);
		//echo "hole val zu ".$item_key."--";
		//if($item_key=="image") return "";
		$config=$this->controller->configurations->getArrayCopy();
		if(is_array($config["storage."] ['virtual.'] [strtolower ( $this->panelTable ) . "."][$item_key."."])) {
			if(isset($pars[$item_key]))return $pars[$item_key];
			else return false;
		}
		if(!isset(tx_crud__lock::$status)) {
			tx_crud__lock::init($this->panelTable,$this->panelRecord,$config['locks.']);
			if(tx_crud__lock::$status=="LOCKED") $this->mode="LOCKED";
		}
		if(!is_array($pars[$item_key]) && strlen($pars[$item_key])>=1) return $pars[$item_key];
		elseif(is_array($pars[$item_key])) return $pars[$item_key];
		else {
			if ($TCA['config']['MM']) {
				$table = $TCA["config"]['MM'];
				$where = "uid_local=" . $this->panelRecord;
				//echo $where." -".$table;
				$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*",$table,$where);
				if($query) {
					for ($y = 0; $y < $GLOBALS['TYPO3_DB']->sql_num_rows($query); $y++) {
						$GLOBALS['TYPO3_DB']->sql_data_seek($query,$y);
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
						if ($TCA['config']["allowed"]) {
							$values[$row['tablenames']."__".$row['uid_foreign']] = $row['tablenames'] . "__" . $row['uid_foreign'];
						} else {
							$values[] = $TCA['config']['foreign_table']."__".$row['uid_foreign'];
						}
					}
				}
				$this->updateValues[$item_key]=$values;
				return $values;
			}
			elseif(!$this->submit) {

				$fields = $this->getStorageFields();
				$where = 'uid=' . $this->panelRecord;
				if (strlen($fields)>1 && $query = $GLOBALS['TYPO3_DB']->exec_SELECTquery($item_key,strtolower($this->panelTable),$where)) {
					if($query) $result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
				}
				if ($result && $TCA['config']['maxitems'] > 1) {
					if(strlen($result[$item_key])>=1)$result[$item_key] = explode(",",$result[$item_key]);
				}
				$this->updateValues[$item_key]=$result[$item_key];
				if (strlen($result[$item_key])>=1) {
					return $result[$item_key];
				}
				else {
					return false;
				}
			}
		}
	}
}
?>