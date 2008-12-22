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
	
 		


//tx_div::load('tx_lib_object');
require_once(t3lib_extMgm::extPath('crud') . 'library/class.tx_crud__acl.php');
require_once(t3lib_extMgm::extPath('crud') . 'library/class.tx_crud__log.php');
class tx_crud__models_common extends tx_lib_object{
	
	var $errors; 
	private $rights = false;
	var $html;
	var $cache = false;
	var $items;
	var $fields;
	var $backValues;
	var $lasterror = false;
	var $submit = false;
	var $processData = false;
	var $processMM = false;
	var $lastQueryID = false;  
	var $panelTable;
	var $types = 0;
	protected $cType;
	protected $modify;
	
	function __construct(&$controller) {
		$this->controller = $controller;
		$pars = $controller->parameters->getArrayCopy();
		$filter = $pars['filter'];
		if (is_array($filter)) {
			$this->filter = $filter;
		}
		if ($pars['page'] >= 1) {
			$this->page = $pars['page'];
		}
		$config = $this->controller->configurations->getArrayCopy();
		$storage = $config['storage.'];;
		$this->controller->parameters = new tx_lib_object($pars); 
		$this->setStorageWorkSpace($storage['workSpace']);
		$this->setStorageNameSpace($storage['nameSpace']);
		$this->setStorageNodes($storage['nodes']);
	    $this->setStorageAction($storage['action']);
		$this->setStorageFields($storage['fields']);
		if ($config['enable.']['rights'] == 0) {
			$this->setStorageAnonym();
		}
		else {
			$rights=new tx_crud__acl($storage['nameSpace'],$storage['fields'],$storage['action']);
			$this->rights=$rights->getOptions();
			//t3lib_div::debug($this->rights);
		}
		if ($config['enable.']['logging'] == 1) {
			$this->logs = tx_crud__log::read($storage['action'], $storage['nodes'], $storage['nameSpace'],$config['logging.']);
		}
		$this->load();
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////SETTER//////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function setStorageWorkSpace($workspace=0) {
		///$this->panelTable="init";
	}
	
	public function setStorageNameSpace($namespace) {
		$this->panelTable = $namespace;
	}
	
	public function setStorageNodes($nodes) {
		$this->panelRecord = $nodes;
	}
	
	public function setStorageAction($action) {
		$this->panelAction= $action;
	}
	
	public function setStorageFields($fields) {
		$this->fields = $fields;
	}
	
	public function setStorageAnonym($bool=1) {
		$fields = explode(",",$this->fields);
		if (!isset($this->panelAction) || !isset($this->panelTable)) {
			//TODO: Localization
			die ("call setStorageAnonym required a setting up panelTable and panelAction");
		}
		foreach ($fields as $key) {
			$this->rights[$this->panelTable][$this->panelAction][$key] = $key;
		}
	}
	
	public function setStorageAcls($acls) {
		$this->rights = $acls;
	}
	
   	public function setStorageFunction($call,&$obj) {
		$this->functions[$call] = &$obj; 
	}
	
	public function setError($key,$error) {
		$this->errors[$key] = $error;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////GETTER///////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getError($key=false) {
		if ($key) {
			return $this->errors[$key];
		} else {
			return $this->errors;
		}
	}
	
	public function getStorageFields() {
		if (is_array($this->rights)) {
			//t3lib_div::debug($this->rights);
			$fields = implode(",",$this->rights[strtolower($this->getStorageNameSpace())][strtolower($this->getStorageAction())]);
		} else {
			$fields = $this->fields;
		}
		
		$fields = explode(",",$fields);
		$config = $this->controller->configurations->getArrayCopy();
		if (is_array($config['storage.']['virtual.'][$this->panelTable."."])) {
			///debug($fields);
			foreach ($fields as $val) {
				$new_fields[$val] = $val;
			}
			$fields = $new_fields;
			foreach ($config['storage.']['virtual.'][$this->panelTable."."] as $key=>$val) {
				$key = str_replace(".","",$key);
				if ($fields[$key]) {
					unset($fields[$key]);
				}
				$key .= "_again";
				if ($fields[$key]) {
					unset($fields[$key]);
				}
			}
		}
		if (is_array($fields)) {
			return implode(",",$fields);
		}
	}

	public function getStorageErrors() {
		return $this->errors;
	}

	public function getStorageMode() {
	}
	
	public function getStorageNodeValue($field) {
	}
	
	public function getStorageNodeProperty($field) {
		
	}
	
	public function getStorageNodes() {
		return $this->panelRecord;
	}
	
	public function getStorageSetup() {
		
	}
	
	public function getStorageChilds() {
		
	}
	
	public function getStorageParent() {
		
	}
	
	public function getStorageAcls() {}
		
	public function getStorageAction() {
		return $this->panelAction;
	}
	
	public function getStorageNameSpace() {
 		return $this->panelTable;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////POST/PRE-PROCESS DUMMIES FOR YOUR OWN CODE/////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	protected function preQuery() { 
		if ($this->functions['preQuery']) {
			$this->functions['preQuery']->preQuery();
		}
	}
	
	protected function postQuery() {
		if ($this->functions['postQuery']) {
			$this->functions['postQuery']->postQuery();
		}
	}

	protected function preSetMode() {
		if ($this->functions['preSetMode']) {
			$this->functions['preSetMode']->preSetMode();
		}
	}
	
	protected function postSetMode() {
		if ($this->functions['postSetMode']) {
			$this->functions['postSetMode']->postSetMode();
		}
	}
	
	protected function preSetSubmit() { 
		if ($this->functions['preSetSubmit']) {
			$this->functions['preSetSubmit']->preSetSubmit();
		}
	}
	
	protected function postSetSubmit() {
		if ($this->functions['postSetSubmit']) {
			$this->functions['postSetSubmit']->postSetSubmit();
		}
	}

	protected function preNextStep() {
		if ($this->functions['preNextStep']) {
			$this->functions['preNextStep']->preNextStep();
		}
	}
	 
	protected function postNextStep() {
		if ($this->functions['postNextStep']) {
			$this->functions['postNextStep']->postNextStep();
		}
    }

	protected function preProcessEval($item_key) {
		if ($this->functions['preProcessEval']) {
			$this->functions['preProcessEval']->preProcessEval();
		}
	}
	
	protected function postProcessEval($item_key) {
		if ($this->functions['postProcessEval']) {
			$this->functions['postProcessEval']->postProcessEval();
		}
	}

	protected function preProcessInput($item_key){
		if ($this->functions['preProcessInput']) {
			$this->functions['preProcessInput']->preProcessInput($item_key);
		}
	}
	
	protected function postProcessInput($item_key){
		if ($this->functions['postProcessInput']) {
			$this->functions['postProcessInput']->postProcessInput($item_key);
		}
	}
	
	final public function load() { 
		//echo "LOADER";
		$start = microtime(true);
		$this->prefixId = $this->getDesignator();
		$table = strtolower($this->panelTable);
		$action = $this->panelAction;
		$rights = $this->rights;
	 	$allowedFields = explode(",",$this->getStorageFields());
		foreach ($allowedFields as $key=>$val) {
			$allowedParameters[$val] = $val;
		}
		$pars = $this->controller->parameters->getArrayCopy();
		foreach ($pars as $key=>$val) {
			if (!$allowedParameters[$key]) {
				$deniedParameters[$key] = $key;
			}
		}
		if (is_array($deniedParameters)) {
			foreach($deniedParameters as $key=>$val) {
				unset($pars[$key]);
				$unsetParameters = true; //TODO: Access violation! Needs a Log?
			}
		}
		$conf = $this->controller->configurations->getArrayCopy();
		if (is_array($conf['storage.'][$table.'.'])) {
			$this->modify = $conf['storage.'][$table.'.'];
		}
		$config = $this->controller->configurations->getArrayCopy();
		$back_id = $GLOBALS['TSFE']->id;
		$this->_checkBackValues();
		if($fields = @implode(',',$rights[strtolower($table)][strtolower($action)])) {
//			/echo "has rights";
   			$cache = tx_crud__cache::get($this->getCacheHash()."-TCA");
			if (is_array($cache)) {
				$items = $cache['TCA']; 
				$this->TCA = $items;
				$this->bigTCA=$cache['ORIGINAL_TCA'];
				$this->divider = $cache['DIVIDER'];
				if($cache['CTYPE'])$this->cType = $cache['CTYPE'];
				if($cache['TYPES']) $this->types = $cache['TYPES'];
				if($cache['TITLE']) $this->recordTitle = $cache['TITLE'];
			} else {
				$cache['TITLE'] = $this->TCA['ctrl']['title'];
			}
			if (!is_array($items)) {
				$items = $this->_processTCA($table,$fields);
				
			}
			$this->table = $table;
			$this->items = $items;
			$this->setSubmit();
			if($config['enable.']['caching'])$this->html = tx_crud__cache::get($this->getCacheHash()."-HTML");
			if (!is_array($this->html)) {
				$this->_processAll();
				if($config['enable.']['caching'])tx_crud__cache::write($this->getCacheHash()."-HTML",$this->html);
			}
			if (is_array($this->html) && $this->mode != "PROCESS") {
				foreach ($this->html as $key=>$val) {
					$this->html[$key]['process'] = $this->deleteMarker($key,$val['process']) ;
					$this->html[$key]['preview'] = $this->deleteMarker($key,$val['preview']) ;
					$this->html[$key]['value'] = $this->deleteMarker($key,$val['value']) ;
				}
			}

			$data = $this->getData();
			$this->setupValues();
			$this->setMode();
			
			$config['view.']['mode'] = $this->mode;
			$config['view.']['errors'] = $this->errors;
			$config['view.']['setup'] = $this->html;
			$config['view.']['title'] = $this->recordTitle;
			$config['view.']['backValues'] = $this->backValues;
			if (is_array($data)) {
				$config['view.']['data'] = $data;
			}
			if (is_array($this->logs)) {
				$config['view.']['logs'] = $this->logs;
			}
			//t3lib_div::debug($this->html,"Setup Browse");
			$this->controller->configurations = new tx_lib_object($config);
			if ($this->submit && !$this->errors && $this->mode = "EDIT") {
				$this->mode = "PROCESS";
			}
			if($this->hasUpload || $_POST[$this->getDesignator()]['icon'] || is_array($_POST[$this->getDesignator()]['remove'])) $this->mode="EDIT";
			if ($this->mode == 'PROCESS' ) {
				$this->processQuery();
				$this->_nextStep();
			}
		} else {
			$this->mode="NO_RIGHTS";
			$config = $this->controller->configurations->getArrayCopy();
			$config['view.']['mode'] = $this->mode;
			$config['view.']['setup'] = $this->html;
			$config['view.']['title'] = $this->recordTitle;
			$config['view.']['backValues'] = $this->backValues;
			$this->controller->configurations = new tx_lib_object($config);

		}
		$stop = microtime(true);
		//echo "<br />CRUD Load() time:".round($stop-$start,3);
	}
	

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////PRIVATE//////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function setTable($panelTable) {
		$this->panelTable = $panelTable;
	}

	function setRecord($panelRecord) {
		$this->panelRecord = $panelRecord;
	}

	function setSubmit() {
		$this->preSetSubmit();
		if ($this->controller->parameters->get("form") == $this->getActionID()) {
			if ($this->controller->parameters->get("process") == "preview") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($this->controller->parameters->get("process") == "create") {
				$this->submit = false;
				$this->mode = "EDIT";
			} elseif ($this->controller->parameters->get("process") == "update") {
				if($this->controller->parameters->get("icon") == "1") {
					$this->submit = false;
					$this->mode = "EDIT";
				}
				else {
					$this->submit = true;
					$this->mode = "EDIT";
				}
			} elseif ($this->controller->parameters->get("process") == "delete") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($this->controller->parameters->get("process") == "cancel") {
				$this->submit = false;
				$this->mode = "ICON";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		} else {
			if ($this->controller->parameters->get("form") && !$this->controller->parameters->get("cancel")) {
				$this->submit = false;
				$this->mode = "HIDE";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		}
		if (is_array($this->cType) && $this->mode=="PROCESS") foreach($this->cType as $name=>$val){
			if ($this->controller->parameters->get($name) && !$this->controller->parameters->get("submit")) {
				$this->mode = "EDIT";
			}
		}
	
		$this->postSetSubmit(); 
	}
	
	function setupValues() {
		
	}
	
	function setMode() {
		$this->preSetMode(); 
		if ($this->mode == "PROCESS" && is_array($this->errors) || $this->controller->parameters->get("remove")) {
			$this->mode = "EDIT";
		}
		$this->checkNode();
		$this->postSetMode();
	}
	
	function setInternalFields($key,$value) {
		$this->internalFields[$key] = $value; 
	}

	function setPrefixId($prefixId) {
		$this->prefixId = $prefixId;
	}

	function setVirtualFields($key,$TCA) {
		$this->html[$key] = $TCA; 
	}
	
	function getBackValues() {
		if (is_array($this->backValues)) {
			return $this->backValues;
		} else {
			return false;
		}
	}

	function getTCA() {
		$array = false;
		if (is_array($this->html)) {
			$array = array();
			foreach ($this->html as $key => $val) {
				foreach ($val as $row=>$value) {
					$form[$row] = $value;
				}
			}
			return $form;
		} else {
			return false;
		}
	}


	function getActionID() {
		$setup = $this->controller->configurations->getArrayCopy();
		return md5($setup['setup.']['secret'].$setup['storage.']['nameSpace'].$setup['storage.']['nodes'].$setup['storage.']['fields']);
	}
   
	function _processTCA($table,$fields) {
		//echo $table.$fields;
		if (!$TCA) {
			//t3lib_div::debug($GLOBALS['TCA']);
			$start = microtime(true);
			$setup = $this->controller->configurations->getArrayCopy();
			$extList = explode(",",$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);
			$fields = explode(',',$fields);
			foreach ($fields as $f) {
				$nFields[$f] = $f;
			}
			$fields = $nFields;
			foreach ($fields as $val) {
				if (substr($val,0,3) == "tx_") {
					$parts = explode("_",$val);
					if ($parts[1]) {
						$extraTCA[$parts[1]] = $parts[1];
					}
				}
			}
			if (is_array($extraTCA)) {
				foreach ($extList as $listKey) {
					$listName = str_replace("_","",$listKey);
					if ($extraTCA[$listName]) {
						$loadExtraTCA[] = $listKey;
					}
				}
			}
			if ($table == "pages") {
				require_once(t3lib_extMgm::extPath('crud') . 'library/pages_tca.php');
			}
			if (is_array($loadExtraTCA)) {
	 			tx_div::loadTcaAdditions($loadExtraTCA);  
			}
			t3lib_div::loadTCA($table);
			$this->TCA = $GLOBALS["TCA"][$table];
			//t3lib_div::debug($this->TCA);
			$this->bigTCA = $GLOBALS["TCA"][$table];
			$this->recordTitle = $this->TCA['ctrl']['title'];
			if ($GLOBALS["TCA"][$table]['ctrl']['type']) {
				//t3lib_div::debug($GLOBALS["TCA"][$table]);
				$control = $GLOBALS["TCA"][$table]['ctrl']['type'];
				$cType[$control] = $GLOBALS["TCA"][$table]['columns'][$control]['config']['default'];
				if ($this->controller->parameters->get($control)) {
					$cType[$control] = $this->controller->parameters->get($control);
				}
				$this->cType = $cType;
				$this->types = $cType[$control];
				$cFields = explode(",",$GLOBALS['TCA'][$table]['types'][$this->types]['showitem']);
				foreach ($cFields as $key) {
					$name = explode(";",$key);
					$name[0] = str_replace(" ","",$name[0]);
					if ($fields[$name[0]]) {
						$newFields[$name[0]] = $name[0];
					}
					if (!empty($GLOBALS['TCA'][$table]['palettes'][$name[2]]['showitem']) || $name[0] == '--palette--') {
						$extraFields = explode(",",$GLOBALS['TCA'][$table]['palettes'][$name[2]]['showitem']);
						if (is_array($extraFields)) {
							foreach ($extraFields as $e=>$extra) {
								$extra = str_replace(" ","",$extra);
								if ($fields[$extra]) {
									$newFields[$extra] = $extra;
								}
							}
						}
					}
				}
			}
			if (is_array($newFields)) {
				foreach ($fields as $key=>$val) {
					if (!$newFields[$key]) {
						if ($key != "starttime" && $key != "endtime" && $key != "fe_group") {
							unset($fields[$key]);
						}
					}
				}
			}
			$TCA = $GLOBALS["TCA"][$table]['columns'];
			if ($setup["storage."]['virtual.'][$this->panelTable."."]) {
				foreach ($setup["storage."]['virtual.'][$this->panelTable."."] as $key=>$val) {
					if (!is_array($val)) {
						//$TCA[str_replace(".","",$key)]=str_replace(".","",$val);
					} else {
						foreach ($val as $key2=>$val2) {
							if (!is_array($val2)) {
								if ($key2 != "label") {
									$TCA[str_replace(".","",$key)][str_replace(".","",$key2)] = str_replace(".","",$val2);
								} else {
									$TCA[str_replace(".","",$key)][$key2] = $val2;
								}
							} else {
								foreach ($val2 as $key3=>$val3) {
									if (!is_array($val3)) {
										$TCA[str_replace(".","",$key)][str_replace(".","",$key2)][str_replace(".","",$key3)] = str_replace(".","",$val3);
									}
								}
							}
						}
					}
				}
			}
			$TCA['deleted'] = array('delete'=>1);
			$rights = $this->rights;
			foreach ($fields as $key=>$val) {
				if (!empty($rights[strtolower($table)][strtolower($this->panelAction)][$val])) {
					$newTCA[$val] = $TCA[$val];
				}
			}
			if (strtoupper($this->panelAction) != 'DELETE') {
				unset($newTCA['deleted']);
			}
			if (is_array($GLOBALS["TCA"][$table]['columns']['hidden']) && $rights[strtolower($table)][strtoupper($this->panelAction)]['hidden'] ) {
				$newTCA['hidden']['config']['type'] = "hidden";
			}
			if (!is_array($cType)) {
				$this->types = 0;
			} else {
				$this->types=$cType[$control];
			}
			$setup = $this->controller->configurations->getArrayCopy();
			if (is_array($setup["storage."]["modifications."][$this->panelTable."."]['divider.']['tabs.'])) {
				$divider = $setup["storage."]["modifications."][$this->panelTable."."]['divider.']['tabs.'];
				$orgDivider = $divider;
				//print_r($divider);
				foreach ($divider as $key=>$val) {
					$name = str_replace(".","",$key);
					foreach ($val as $breaker=>$values) {
						$fFields = explode(",",$values);
						$sectionFields = false;
						foreach ($fFields as $fName) {
							if ($newTCA[$fName]) {
								$sectionFields[$fName] = $fName;
								$sectionTCA[$fName] = $newTCA[$fName];
							}
						}
						if (is_array($sectionFields)) {
							$this->divider[$name][$breaker]=$sectionFields;
						}
					}
				}
				$newTCA = $sectionTCA;
			} else {
				//TCA standard divider tabs
				$tabs = explode("--div--",$GLOBALS['TCA'][$table]['types'][$this->types]['showitem']);
				if (sizeof($tabs >= 1)) {
					$i = 0;
					foreach ($tabs as $key=>$val) {
						$values = explode(",",$val);
						foreach ($values as $k=>$v) {
							$v = str_replace(" ","",$v);
							$v = explode(";",$v);
							$v = $v[0];
							if (is_array($newTCA[$v])) {
								if ($i == 0) {
									$title="General"; 
								} elseif (strlen($values[0]) > 2) {
									$title=$values[0];
								} else {
									$title = "More " . $i; // Localization
								}
								$title = str_replace(";","",$title);
								$this->divider[$title]["emptySection"][$v] = $v;
							}
						}
						$i++;
					}
				}
			}
		}

		$stop = microtime(true);
		$this->cType = $cType;
		$this->types = $cType[$control];
		$cache['TCA'] = $newTCA;
		$cache['ORIGINAL_TCA'] = $this->bigTCA;
		$cache['DIVIDER'] = $this->divider;
		$cache['TYPES'] = $this->type;
		$cache['CTYPE'] = $this->cType;
		$cache['TITLE'] = $this->recordTitle;
		tx_crud__cache::write($this->getCacheHash()."-TCA",$cache);
		return $newTCA;
	}

	function _processAll() {
		$items = $this->items;
		//print_r($items);
		$pars = $this->parameters;
		if ($pars['submit']) {
			unset($pars['submit']);
		}
		if ($pars['cancel']) {
			unset($pars['cancel']);
		}
		if ($pars['preview']) {
			unset($pars['preview']);
		}
		if ($pars['action']) {
			unset($pars['action']);
		}
		if ($pars['table']) {
			unset($pars['table']);
		}
		if ($pars['record']) {
			unset($pars['record']);
		}
		if (is_array($items)) {
			foreach ($items as $key=>$val) {
				if ($val['config']['type'] == 'input') {
					$this->_processInput($key);
				} elseif ($val['config']['type'] == 'hidden') {
					$this->_processHidden($key);
				} elseif ($val['config']['type'] == 'text' ) {
					$this->_processText($key);
				} elseif ($val['config']['type'] == 'select') {
					$this->_processSelect($key);
				} elseif ($val['config']['type'] == 'check') {
					$this->_processCheck($key);
				} elseif ($val['config']['type'] == 'radio') {
					$this->_processRadio($key);
				} elseif ($val['config']['type'] == 'group' && $val['config']['internal_type'] == "file") {
					$this->_processFiles($key);
				} elseif ($val['config']['type'] == 'group' && $val['config']['internal_type'] != "file") {
					$this->_processGroup($key);
				} elseif ($key == 'deleted') {
					$this->_processDeleted($key);
				}
			}
		}
		if ($this->submit && is_array($pars)) {
			foreach ($pars as $key=>$val) $values .= $val;
		}
		if (strlen($values) < 1) {
			$this->lasterror = "empty";
		}
		//t3lib_div::debug($this->html,'html');
		if ($this->lasterror) {
			return false;
		} else {
			return true;
		}
	}

	function _processText($item_key) {
		$pars = $this->parameters;
		$conf = $this->configurations;
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		if (!isset($TCA['config']['type'])) {
			$TCA['config'] = $GLOBALS["TCA"][$this->table]["columns"][$item_key]['config'];
		}
		$item = $this->items[$item_key];
		$html = $this->_processEval($item_key);
		$html["config."] = $TCA['config'];
		$html["search"] = 1;
		if (is_array($this->divider)) {
			$html['divider'] = $this->_processDivider($item_key);
		}
		$html["key"] = $item_key;
		if (strstr($TCA['defaultExtras'],"richtext") || is_array($TCA['config']['wizards']['RTE'])) {
			$html["element"] = "rteRow";
		} else {
			$html["element"] = "textareaRow";
		}
		$html["attributes."] = $this->_processAttributes($TCA['config']);
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
		return $html;
	}

	function _processFiles($item_key) {
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		if (!isset($TCA['config']['type'])) {
			$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		}
		$item = $this->_processModifications($this->items[$item_key],$item_key);;
		$html = $this->_processEval($item_key);
		$html["key"] = $item_key;
		$html["attributes."] = $this->_processAttributes($TCA['config']); 
		$html["attributes."]['name'] = $this->prefixId . "[" . $item_key . "]";
		$html['config.'] = $TCA['config'];
		if (!empty($html['help'])) {
			$html["attributes."]['title'] = $html['help'];
		}
		if (is_array($this->divider)) {
			$html['divider'] = $this->_processDivider($item_key);
		}
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
	}

	function _processInput($item_key) {
		$this->preProcessInput($item_key);
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		if (!isset($TCA['config']['type'])) {
			$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		}
		$item = $TCA;
		if (!$item_value && $TCA['config']['eval'] == 'datetime') {
			if ($TCA['config']['default'] > 1) {
				$item_value = $TCA['config']['default'];
			}
		}
		$html["label"] = $TCA["label"];
		$html["help"] = $TCA["help"];
		$eval = explode(",",$TCA['config']['eval']);
		$html["key"] = $item_key;
		$this->_processDivider($item_key);
		if (in_array("password",$eval)) {
			$html["element"] = "passwordRow";
		} elseif (in_array("datetime",$eval)) {
			$html["element"] = "dateTimeRow";
		} else {
			$html["search"] = 1;
			$html["element"] = "inputRow"; 
		}
		$html["config."] = $TCA['config'];
		$html["attributes."] = $this->_processAttributes($TCA['config']); 
		if (!empty($html['help'])) {
			$html["attributes."]['title']=$html['help'];
		}
		if (in_array("twice",$eval) && $this->mode="EDIT") {
			$second_value = $this->_getValue($item_key."_again");
			$html2["label"] = str_replace($item_key,$item_key."_again",$html['label']);
        	$html2["help"] = $html["help"];
			$html2["key"] = $item_key."_again";
			$html2["element"] = "inputRow";
			$html2["attributes."]= $this->_processAttributes($TCA['config']); 
		}
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
		if(is_array($html2)) {
			$this->html[$item_key."_again"] = $html2;
			$this->html[$item_key."_again"]['divider'] = $this->html[$item_key]['divider'];
			$this->html[$item_key."_again"]['section'] = $this->html[$item_key]['section'];
			if (in_array("password",$eval)) {
				$this->html[$item_key."_again"]['element'] = 'passwordRow';
			}
			if (in_array("required",$eval)) {
				$this->html[$item_key."_again"]['required'] = '1';
			}
			if ($this->errors[$item_key."_again"]) {
				$this->html[$item_key."_again"]['error'] = $this->errors[$item_key."_again"];
			}
		}
		$this->postProcessInput($item_key);
		return $html;
	}
	
	function _processSelect($item_key) {
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		$item = $TCA;
		$html = array();
		$html["label"] = $TCA["label"];
		$html["help"] = $TCA["help"];
		if ($TCA['config']['maxitems'] > 1) {
			$multiple = 1;
			$html["element"] = "multiselectRow";
		} else {
			$html["element"] = "selectRow";
		}
		if (is_array($item_value)) {
			$values = $item_value;
			$item_value = array();
			foreach ($values as $k=>$v) {
				$item_value[$v] = $v;
			}
		}
		if (!isset($TCA['config']['type'])) {
			$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		}
		if ($TCA['config']['foreign_table']) {
			$options = $this->_processForeignTable($item_key);
		} elseif ($TCA['config']['itemsProcFunc']) {
			$procOptions = $this->_processItemsProcFunc($item_key);
			if (is_array($procOptions)) {
				foreach ($procOptions as $key=>$val) {
					$options[$val[1]] = $val[0];
				}
			}
		} else {
			$items = $TCA['config']['items'];
			for ($i = 0; $i < sizeof($items); $i++) {
				$options[$items[$i][1]] = $this->_getLL($items[$i][0]);
			}
		}
		$html["key"] = $item_key;
		$html["attributes."] = $this->_processAttributes($TCA['config']);
		if (isset($this->cType[$item_key])) {
			$html['reload'] = 1;
		}
		$setup = $this->controller->configurations->getArrayCopy();
		if ($setup["storage."][strtolower($this->panelTable)."."][$item_key."."]['unset']) {
			$what = explode(",",$setup[$this->controller->action."."]["storage."][strtolower($this->panelTable)."."][$item_key."."]['unset']);
			foreach ($what as $key) {
				if ($options[$key]) {
					unset($options[$key]);
				}
			}
		}
		$html["options."] = $options;
		$html["config."] = $TCA["config"];
		if (is_array($this->divider)) {
			$html['divider'] = $this->_processDivider($item_key);
		}
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
		return $html;
	}

	function _processCheck($item_key)  { 
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		$pars = $this->controller->parameters->get($item_key) ;
		if (!isset($TCA['config']['type'])) {
			$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		}
		$item = $this->_processModifications($this->items[$item_key],$item_key);
		$item_value = $this->_getValue($item_key);
		$html = $this->_processEval($item_key);
		$html["config."] = $TCA["config"];
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
		return $html;
	}

	function _processRadio($item_key)  {
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		if (!isset($TCA['config']['type'])) {
			$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		}
		$item = $this->_processModifications($this->items[$item_key],$item_key);
		$html["label"] = $TCA["label"];
		$html["help"] = $TCA["help"];
		$html["config."] = $TCA['config'];
		$items = $TCA['config']['items'];
		for ($i = 0; $i < sizeof($items); $i++) {
			$options[$items[$i][1]] = $items[$i][0];
		}
		$html["attributes."]['name'] = $this->prefixId . "[" . $item_key . "]";  
		$html["attributes."]["options."] = $options;
		if (is_array($this->divider)) {
			$html['divider'] = $this->_processDivider($item_key);
		}
		$html["key"] = $item_key;
		$html["element"] = "radio";
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
		return $html;
	}
	
	function _processGroup($item_key) {
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);;
		$item = $TCA;
		$html = $this->_processEval($item_key);
		$html["element"] = "multiselectRow";
		if ($TCA['config']['maxitems'] > 1) {
			$multiple = 1;
			$html["element"] = "multiselectRow";
		} else {
			$html["element"] = "selectRow";
		}
		if (is_array($item_value)) {
			$values = $item_value;
			$item_value = array();
			foreach ($values as $k=>$v) {
				$item_value[$v] = $v;
			}
		}
		$allowed = $this->_processAllowed($item_key);	
		$options = $allowed['data'];
		$sorting = $allowed['sorting'];	
		$process_value = explode("__",$item_value);
		$html["key"] = $item_key;
		$html["attributes."] = $this->_processAttributes($TCA['config']);
		$setup = $this->controller->configurations->getArrayCopy();
		if ($setup["storage."][strtolower($this->panelTable)."."][$item_key."."]['unset']) {
			$what = explode(",",$setup["storage."][strtolower($this->panelTable)."."][$item_key."."]['unset']);
			foreach ($what as $key) {
				if ($options[$key]) {
					unset($options[$key]);
				}
			}
		}
		
		$html["options."] = $options;
		$html["sorting."] = $sorting;
		$html["config."] = $TCA["config"];
		if (is_array($this->divider)) {
			$html['divider'] = $this->_processDivider($item_key);
		}
		//$html=$this->_postModifications($html,$item_key);
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
		return $html;
	}
	
	function _processGroupOptions($options)  { //  TODO : process group elements with a record browser
		return $options;
	}

	function _processDeleted($item_key)  {
		$conf = $this->configurations;
		$pars = $this->parameters;
		$html["hidden"] .=  '<input type="hidden" value="' . $uid . '" name="' . $this->prefixId . "[" . $item_key . "]" . '" />' . "\n\t";
		$html["preview"] .= $val;
		$html["label"] = 'Delete';
		$html["field"] = 'hm';
		$html["name"] = $item_key;
		$html["config"] = $TCA['config'];
		$html["help"] = 'Really delete?'; //TODO: Localization
		$html["value"] = 1;
		$this->html[$item_key] = $html;
		$this->_processDivider($item_key);
		return $html;
	}

	function _processForeignTable($item_key) {
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		//t3lib_div::debug($TCA,$item_key);
		if (!isset($TCA['config']['type'])) {
			$TCA = $GLOBALS["TCA"][$this->table]["columns"][$item_key];
		}
		$table = $TCA['config']['foreign_table'];
		//t3lib_div::loadTCA($table);
		$orgTCA = $GLOBALS["TCA"][$table];
		if (isset($GLOBALS["TCA"][$table]['ctrl']['delete'])) {
			$where = $table . "." . $GLOBALS["TCA"][$table]['ctrl']['delete'] . "=0 ";
		}
		if ($GLOBALS["TCA"][$table]['colums']['hidden']) {
			$where = ' AND hidden=0 ';
		}
		if (isset($TCA['config']['foreign_table_where'])) {
			if (strpos($TCA['config']['foreign_table_where'],"###CURRENT_PID###")) {
				$where .= str_replace("###CURRENT_PID###",$GLOBALS['TSFE']->id,$TCA['config']['foreign_table_where']);
			} elseif (strpos($TCA['config']['foreign_table_where'],"###STORAGE_PID###")) {
				$where .= str_replace("###STORAGE_PID###",$GLOBALS['TSFE']->rootLine[0]['storage_pid'],$TCA['config']['foreign_table_where']);
			} elseif (strpos($TCA['config']['foreign_table_where'],"###SITEROOT###")) {
				$where .= str_replace("###SITEROOT###",0,$TCA['config']['foreign_table_where']);
			} else {
				//$where .= $TCA['config']['foreign_table_where'];
			}
		}
		if (strlen($TCA['config']['field']) > 3) {
			$field = $TCA['config']['field'];
		} else {
			$field = $GLOBALS["TCA"][$table]['ctrl']['label'];
		}
		$what = $table . ".uid" . ',' . $table . "." . $field;
		if ($orgTCA['columns']['fe_group']) {
			$where .= " AND NOT $table.fe_group";
			if ($GLOBALS['TSFE']->fe_user->user['usergroup']) {
				$fegroups = explode(",",$GLOBALS['TSFE']->fe_user->user['usergroup']);
				foreach ($fegroups as $groupid) {
					$where .= " OR $table.fe_group IN ($groupid)";
				}
			}
		}
		if ($orgTCA['columns']['subgroup']) {
			if ($GLOBALS['TSFE']->fe_user->user['usergroup']) {
				$fegroups = explode(",",$GLOBALS['TSFE']->fe_user->user['usergroup']);
				$plus = "AND";
				foreach ($fegroups as $groupid) {
					$where .= " $plus $table.uid=$groupid OR $table.subgroup IN($groupid) ";
					$plus = "OR";
				}
			} else {
				$where .= " AND $table.uid=0";
			}
		}
		//TODO: was wenn ts im cache und andere fe-group
		
		//echo "select " . $what . " from " . $table . " " . $where;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($what,$table,$where);
		//echo $query;
		$size = $GLOBALS['TYPO3_DB']->sql_affected_rows($res);
		//echo $size;
		for ($i = 0; $i < $size; $i++) {
			$GLOBALS['TYPO3_DB']->sql_data_seek($res,$i);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$data[$row['uid']] = $row[$field];
		}
		//t3lib_div::debug($data);
		return $data;
	}

	function _processAllowed($item_key,$key='uid') {
		//echo "allows";
		$setup = $this->controller->configurations->getArrayCopy();
		$config = $setup["storage."][$this->panelTable."."][$item_key."."];
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);;
		if (!isset($TCA['config']['type'])) {
			$TCA = $GLOBALS["TCA"][$this->panelTable]["columns"][$item_key];
		}
		$allowed = $TCA['config']['allowed'];
		$tables = explode(",",$allowed);
		foreach ($tables as $table) {
			if (strlen($field = $TCA['config']['field']) > 3) {
				$field = $TCA['config']['field'];
			} else {
				$field = 'title';
			}
			$what = 'uid,pid,' . $field;
			if ($key != 'uid' && $key != $field) {
				$what .= ',' . $key;
			}
			t3lib_div::loadTCA($table);
			$orgTCA = $GLOBALS["TCA"][$table];
			if ($GLOBALS["TCA"][$table]['ctrl']['delete']) {
				$where = $GLOBALS["TCA"][$table]['ctrl']['delete'].'=0';
			}
			if ($GLOBALS["TCA"][$table]['colums']['hidden']) {
				$where .= ' AND hidden=0';
			}
			if ($config[$table."."]['denyPids']) {
				$denyPids = explode(",",$config[$table."."]['denyPids']);
			} else {
				$denyPids = array();
			}
			if ($config[$table."."]['denyUids']) {
				$denyUids = explode(",",$config[$table."."]['denyUids']);
				foreach ($denyUids as $denyUid) {
					$where .= " AND uid !=" . $denyUid;
				}
			}
			if ($GLOBALS["TCA"][$table]['ctrl']['sorting']) {
				$sorting = $GLOBALS["TCA"][$table]['ctrl']['sorting'];
			} elseif (strtolower($table) == "pages") {
				$sorting="sorting";
			} elseif ($GLOBALS["TCA"][$table]['ctrl']['default_sortby']) {
				$sorting = explode(" ",$GLOBALS["TCA"][$table]['ctrl']['default_sortby']);
				$sorting = $sorting[2];
			} else {
				$sorting="pid";
			}
			if ($orgTCA['columns']['fe_group']) {
				$where .= " AND NOT $table.fe_group";
				if ($GLOBALS['TSFE']->fe_user->user['usergroup']) {
					$fegroups = explode(",",$GLOBALS['TSFE']->fe_user->user['usergroup']);
					foreach ($fegroups as $groupid) {
						$where .= " OR $table.fe_group IN ($groupid)";
					}
				}
			}
			if ($orgTCA['columns']['subgroup']) {
				if ($GLOBALS['TSFE']->fe_user->user['usergroup']) {
					$fegroups = explode(",",$GLOBALS['TSFE']->fe_user->user['usergroup']);
					$plus = "AND";
					foreach ($fegroups as $groupid) {
						$where .= " $plus $table.uid=$groupid OR subgroup IN($groupid) ";
						$plus = "OR";
					}
				}
			}
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($what,$table,$where,"",$sorting);
			$size = $GLOBALS['TYPO3_DB']->sql_affected_rows($res);
			$pageTree = array();
			for ($i = 0; $i < $size; $i++) {
				$GLOBALS['TYPO3_DB']->sql_data_seek($res,$i);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				if($TCA['config']['MM']) $data[$table."__".$row[$key]] = $row[$field]."(".$table.":".$row['uid'].")";
				elseif(sizeof($tables)>1)$data[$table."_".$row[$key]] = $row[$field]."(".$table.":".$row['uid'].")";
				else $data[$row[$key]] = $row[$field]."(".$table.":".$row['uid'].")";
				$newData[$table][$row["pid"]][$row["uid"]]=$row;
			}
		}
		foreach ($tables as $table) {
			$rootPid = $config[$table.'.']['rootPid'];
			if (empty($rootPid)) {
				$rootPid="0";
			}
			$start = $newData[$table][$rootPid];
			if (!is_array($start)) {
				$start = $newData[$table];
			}
			if (is_array($start)) {
				foreach ($start as $pid=>$entry) {
					if (is_array($newData[$table][$pid]) && !in_array($pid,$denyPids)) {
						//second level";
						foreach ($newData[$table][$pid] as $pid2=>$entry2) {
							if ($pid2 != $pid && is_array($newData[$table][$pid2]) && !in_array($pid2,$denyPids)) {
								foreach ($newData[$table][$pid2] as $pid3=>$entry3) {
									//third level";
									if ($pid != $pid2 && is_array($newData[$table][$pid3]) && !in_array($pid3,$denyPids)) {
										foreach ($newData[$table][$pid3] as $pid4=>$entry4) {
											//forth level
											if($pid3 != $pid2 && is_array($newData[$table][$pid4]) && !in_array($pid4,$denyPids)) {
												foreach ($newData[$table][$pid4] as $pid5=>$entry5) {
													if ($pid5 != $pid4 && $pid5 != $pid3) {
														$pageTree[$table][$pid][$pid2][$pid3][$pid4][$pid5] = $pid5;
													}
												}
											} elseif ($pid4!=$pid3 && $pid4!=$pid2) {
												$pageTree[$table][$pid][$pid2][$pid3][$pid4] = $pid4;
											}
										}
									} elseif($pid3 != $pid2 && $pid3 != $pid) {
										$pageTree[$table][$pid][$pid2][$pid3] = $pid3;
									}
								}
							} elseif ($pid2 != $pid) {
								$pageTree[$table][$pid][$pid2] = $pid2;
							}
						}
					} elseif (is_numeric($newData[$table][$pid]) && !in_array($pid,$denyPids)) {
						$pageTree[$table][$pid] = $pid;
					}
				}
			}
		}
		
		return array("data"=>$data,"sorting"=>$pageTree);
	}

	function _processItemsProcFunc($item_key) {
		$params = $this->_processModifications($this->items[$item_key],$item_key);
		$className = explode("->",$params['config']['itemsProcFunc']);
		$extList = explode(",",$GLOBALS['TSFE']->TYPO3_CONF_VARS['EXT']['extList']);
		$extKey = explode("tx_",$className[0]);
		$extKey = explode("_",$extKey[1]);
		$extKey = $extKey[0];
		foreach ($extList as $key=>$ext) {
			$name = str_replace("_","",$ext);
			if ($name == $extKey) {
				$extKey = $ext;
				break;
			}
		}
		require_once(t3lib_extMgm::extPath($extKey) . 'class.'.$className[0].'.php');
		require_once("t3lib/class.t3lib_befunc.php");
		$class = new $className[0];
		$items = $class->$className[1](&$params,&$this);
		return $params['items'];
	}

	function sL($key) {
		return $key;
	}
	
	function _processEval($item_key) {
		$this->preProcessEval($item_key);
		$TCA = $this->_processModifications($this->items[$item_key],$item_key);
		$string = explode(":",$TCA['label']);
		$evalTCA = @explode(",",$TCA["config"]["eval"]);
		$item = $TCA;
		$html = array();
		$config=$this->controller->configurations->getArrayCopy();
		$html["label"] = $TCA["label"];
		$html["help"] = $TCA["help"];
		$item_value = $this->_getValue($item_key);
		if (!empty($item_value) && !is_array($item_value) && $TCA['config']['internal_type'] == "file") {
			$file_value = explode(",",$item_value);
			$item_value = array(); 
			foreach ($file_value as $key=>$val) {
				$item_value[$key] = $TCA['config']['uploadfolder'] . "/" . $file_value[$key];
			}
		}
		if (is_array($evalTCA)) {
			foreach ($evalTCA as $k=>$v) {
				if (!empty($v)) {
					$eval[$v] = true;
				}
			}
		}
		$min = 1;
		$max = 1024;
		if ($TCA['config']['min'] > 1) {
			$min = $TCA['config']['min'];
		}
		if ($TCA['config']['max'] > 1) {
			$max = $TCA['config']['max'];
		}
		if ($this->controller->parameters->get($item_key) && $TCA['config']['wizards']['link']) {
			$url = explode("http://",$item_value);
			if (!isset($url[1])) {
				$item_value="http://".$item_value;
			}
			if (!@file_get_contents($item_value)) {
				$this->errors[$item_key]="error_link";
			}
		}
		if ($eval['date'] && $this->submit) {
			if (!$TCA['config']['splitter']) {
				$TCA['config']['splitter'] = ".";//TODO: set default date splitter/format in TS
			}
			if (!$TCA['config']['format']) {
				$TCA['config']['format']="dd.mm.yyyy"; //TODO: Ist das sinnvoll?
			}
			$split = $TCA['config']['splitter'];
			if (strlen($TCA['config']['format']) >= 2) {
				$format = explode($split,$TCA['config']['format']);
			}
			if ($this->controller->parameters->get($item_key)) {
				$time = explode($split,$this->controller->parameters->get($item_key));
				for ($i = 0; $i < count($format); $i++) {
					$date[$format[$i]]=$time[$i];
				}
				if ($item_value && sizeof($time) == 3 && is_numeric($date['dd']) && $date['dd'] >= 1 && $date['dd'] <= 31 && is_numeric($date['mm']) && $date['mm'] >= 1 && $date['mm'] <= 12 && is_numeric($date['yyyy']) && $date['yyyy'] >= 1000 && $date['yyyy'] <= 4000) {
					$postStamp = mktime(0,0,0,$date['mm'],$date['dd'],$date['yyyy']);
					if (!empty($TCA['config']['range']['lower']) && $postStamp <= ($TCA['config']['range']['lower'])) {
						$this->errors[$item_key] = "error_date_lower"; 
					}
					if (!empty($TCA['config']['range']['upper']) && $postStamp >= $TCA['config']['range']['upper']) {
						$this->errors[$item_key] = "error_date_upper"; 
					}
				} else {
					$this->errors[$item_key] = "error_date"; 
				}
			} 
			if ($postStamp) {
				$html['process'] = $postStamp;
			}
			else $html['process']=$item_value;
			
		}
		elseif($eval['date'] && !$this->submit && !empty($item_value)) $html['process']=$item_value;
		//echo $this->submit."sub";
		if ($eval['datetime'] && $this->submit) {
			$pars=$this->controller->parameters->getArrayCopy();
			$item_value=$pars[$item_key];
			if (!$TCA['config']['splitter']) {
				$TCA['config']['splitter'] = ".";//TODO: set default date splitter/format in TS
			}
			if (!$TCA['config']['format']) {
				$TCA['config']['format'] = "dd.mm.yyyy";
			}
			$split = $TCA['config']['splitter'];
			if (strlen($item_value['time']) > 1) {
				$datetime = explode(":",$item_value['time']);
				if (strlen($datetime[0]) <= 0 || strlen($datetime[1]) <= 0 || $datetime[0] < 0 || $datetime[0] > 23 || $datetime[1] < 0 || $datetime[1] > 60) {
					$this->errors[$item_key] = "error_datetime";
					$datetime[0] = 0;
					$datetime[1] = 0;
				}
			}
			//echo "submit ok";
			
			//t3lib_div::debug($pars);
			if (strlen($TCA['config']['format']) >= 2) {
				$format = explode($split,$TCA['config']['format']);
			}
			if (strlen($item_value['date']) > 1) {
				$time = explode($split,$item_value['date']);
				for ($i = 0; $i < count($format); $i++) {
					$date[$format[$i]]=$time[$i];
				}
				if ($item_value['date'] && sizeof($time) == 3 && is_numeric($date['dd']) && $date['dd'] >= 1 && $date['dd'] <= 31 && is_numeric($date['mm']) && $date['mm'] >= 1 && $date['mm'] <= 12 && is_numeric($date['yyyy']) && $date['yyyy'] >= 1000 && $date['yyyy'] <= 4000) {
					$postStamp = mktime($datetime[0],$datetime[1],0,$date['mm'],$date['dd'],$date['yyyy']);
					if (!empty($TCA['config']['range']['lower']) && $postStamp <= ($TCA['config']['range']['lower'])) {
						$this->errors[$item_key] = "error_date_lower"; 
					}
					if (!empty($TCA['config']['range']['upper']) && $postStamp >= $TCA['config']['range']['upper']) {
						$this->errors[$item_key] = "error_date_upper"; 
					}
				} else {
					$this->errors[$item_key]=  "error_datetime"; 
				}
			}
			if ($postStamp) {
				$html['process'] = $postStamp;
			}
			//echo $item_value;
		}
		elseif($eval['datetime'] && !$this->submit && !empty($item_value)){
			//echo "hmmm";
			$html['process']=$item_value;
		}
		$item_value = $this->_getValue($item_key);
		if (is_array($item_value) && $TCA['config']['internal_type'] == "file") {
			
			if(strtoupper($this->panelAction)=="UPDATE") foreach($item_value as $k=>$v) {
				//echo $v;
				$v_exploded=explode("/",$v);
				if(!$v_exploded[1]) $item_value[$k]=$TCA['config']['uploadfolder']."/".$v;
			}
			foreach($item_value as $key=>$file) {
				$remove = $this->controller->parameters->get('remove');
				if (is_array($remove) && strlen($remove[$item_key])>=1 && $remove[$item_key] == $key) {  
					
					if (!unlink($item_value[$key])) { //FIXME: delete erst beim processQuery! bug:wenn ein upload geloescht wird muss db aktualseirt werden,wenn kein submit
						$this->errors[$item_key] = "error_unlink"; 
					}
				
				} else {
					if (!@file_get_contents($item_value[$key])) {
						$error="error_link";
					}
					else {
						$html['value'][$key] = $item_value[$key];
						$valData = explode("/",$item_value[$key]);
						$html['process'][$key] = $valData[count($valData) - 1];
						$html['preview'][$key] = $item_value[$key];
					}
				}
			}
			//t3lib_div::debug($html);
		}
	//	t3lib_div::debug($_FILES);
		$pars=$this->controller->parameters->getArrayCopy();
	//	t3lib_div::debug($pars);
		if(is_array($_FILES[$this->prefixId]['name'][$item_key])) {
			foreach($_FILES[$this->prefixId]['name'][$item_key] as $key=>$file) {
				if ($this->submit && $TCA['config']['internal_type'] == "file" && is_uploaded_file($_FILES[$this->prefixId]['tmp_name'][$item_key][$key])) {
					$allowed = explode(",",$TCA['config']['allowed']);
					$allowed_types = "(" . implode("|",$allowed) . ")";
					if (strlen($allowed[0]) <= 0 || preg_match("/\." . $allowed_types . "$/i", $_FILES[$this->prefixId]["name"][$item_key][$key])) { 
						if ($_FILES[$this->prefixId]["size"][$item_key][$key] <= ($TCA['config']['max_size'] * 1024)) {
							$path = $TCA['config']['uploadfolder'] . "/" . $_FILES[$this->prefixId]["name"][$item_key][$key];
							$fName = $_FILES[$this->prefixId]["name"][$item_key][$key];
							if (is_readable($path)) {
								$fileName = explode(".",$_FILES[$this->prefixId]["name"][$item_key][$key]);
								$micro = explode(" ",microtime());
								$microtime = substr($micro[0],4,4); 
								$fileName[0] = $fileName[0] . "_" . $microtime; 
								$newName = $fileName[0] . "." . $fileName[count($fileName)-1];
								$path = $TCA['config']['uploadfolder'] . "/" . $newName; 
								$fName = $newName;
							}

							if (move_uploaded_file($_FILES[$this->prefixId]["tmp_name"][$item_key][$key], $path)) {
								$html['value'][$key] = $path;
								$html['process'][$key] = $fName;
								$item_value[$key] = $fName;
								$this->mode = "EDIT";
								$this->hasUpload=true;
							} else {
								$this->errors[$item_key] = "error_filecopy";
							}
						} else {
							$this->errors[$item_key] = "error_filesize";
						}
					} else {
						$this->errors[$item_key] = "error_filetype";
					}
				} else {
					//if($TCA['config']['minitems']>=1 && $this->submit && count($item_value)<$TCA['config']['minitems']) 
						//$this->errors[$item_key] = "error_fileitems";
				}
			}
		}

		$error = false;
		if ($eval['required']  || $this->modify[$item_key.'.']['required']) {
			$html['required'] = 1;
			if ($this->submit && strlen($item_value) < 1) {
				$this->errors[$item_key] = "error_required";
			}
		}
		if (!$this->errors[$item_key] && $this->submit && isset($TCA['config']['minitems']) && sizeof($item_value) < $TCA['config']['minitems']) {
			$this->errors[$item_key] = "error_minitems"; 
		}
		if (!$this->errors[$item_key] && $this->submit && isset($TCA['config']['maxitems']) && sizeof($item_value) > $TCA['config']['maxitems']) {
			$this->errors[$item_key] = "error_maxitems"; 
		}
		if ($TCA['config']['type'] == "group" && $this->submit) {
			if (!$this->errors[$item_key] && isset($TCA['config']['minitems']) && sizeof($item_value) < $TCA['config']['minitems']) {
				$this->errors[$item_key] = "error_minitems"; 
			}
			if(!$this->errors[$item_key] && isset($TCA['config']['maxitems']) && sizeof($item_value) > $TCA['config']['maxitems']) {
				$this->errors[$item_key]= "error_maxitems"; 
			}
		}
		if (strlen($item_value) >= 1 ) {
			if (strlen($item_value) < $min) {
				$this->errors[$item_key] = "error_min"; 
			}
			if (strlen($item_value) > $max ) {
				$this->errors[$item_key] = "error_max"; 
			}
		}
		
		if ($eval['email'] && strlen($item_value) >= 1  && !t3lib_div::validEmail($item_value)) {
			$this->errors[$item_key] = "error_email"; 
		}
		if ($eval['unique'] && strlen($item_value) >= $min && !$this->_checkUnique($item_key,$TCA)) {
			$this->errors[$item_key] = "error_unique"; 
		}
		if ($eval['captcha'] && strlen($item_value) >=1 && $this->submit) {
			if (t3lib_extMgm::isLoaded('captcha')){
				session_start();
				$captchaStr = $_SESSION['tx_captcha_string'];
				$_SESSION['tx_captcha_string'] = '';
			} else {
				$this->errors[$item_key] = "error_captcha_ext"; 
			}
			if ($captchaStr!=$item_value) $this->errors[$item_key] = "error_captcha";
		}
		if (!empty($item_value) && $eval['int'] && $this->submit && !is_numeric($item_value)) { 
			$this->errors[$item_key] = "error_integer";  
		} 
		if ($eval['twice'] && strlen($item_value) >= $min && strlen($second_value) >= $min) {
			if ($item_value != $second_value) {
				$this->errors[$item_key] = "error_twice"; 
				$this->errors[$item_key."_again"] = "error_twice"; 
			}
		}
		if ($eval['twice'] && $this->mode="EDIT" && $this->submit) {
			$second_value = $this->_getValue($item_key."_again");
			if ($eval['required']) {
				if (strlen($second_value) < 1) {
					$this->errors[$item_key."_again"]  = "error_required"; 
				}
			}
			if (strlen($second_value) >= 1 && strlen($second_value) < $min || strlen($item_value) > $max ) {
				$this->errors[$item_key."_again"]  = "error_leng"; 
			}
			if ($eval['email'] && strlen($second_value) >= 1  && !t3lib_div::validEmail($second_value))	{
				$this->errors[$item_key."_again"] = "error_email"; 
			}
			if ($eval['unique'] && strlen($item_value) >= $min && !$this->_checkUnique($item_key,$TCA)) {
				$this->errors[$item_key."_again"]  = "error_unique"; 
			}
			if ($eval['int'] && $this->submit && !is_numeric($second_value)) {
				$this->errors[$item_key."_again"]  = "error_integer"; 
			}
			if ($eval['twice'] && strlen($item_value) >= $min && strlen($second_value) >= $min) {
				if ($item_value != $second_value) {
					$this->errors[$item_key."_again"]  = "error_twice"; 
					$this->errors[$item_key]  = "error_twice"; 
				}
			}
		}
		if ($eval['required'] && $this->submit && !$item_value) {
			$this->errors[$item_key]= "error_required"; 
		}
		if ($this->errors[$item_key]) {
			$this->lasterror = $this->errors[$item_key];
			$html["error"] =  $this->errors[$item_key];
		}
		$this->postProcessEval($item_key); 
		return $html;
	}

	function _processAttributes($config) { 
		$array = array();
		//debug($config);
		if (isset($config['size'])) {
			$array['size'] = $config['size'];
		}
		if (isset($config['max'])) {
			$array['maxlength'] = $config['max'];
		}
		if (isset($config['max_size'])) {
			$array['maxlength'] = $config['max_size'];
		}
		if (isset($config['cols'])) {
			$array['cols'] = $config['cols'];
		}
		if (isset($config['rows'])) {
			$array['rows'] = $config['rows'];
		}
		return $array;
	}

	function _processDivider($item_key) {
		if (is_array($this->divider)) {
			foreach ($this->divider as $key=>$val) {
				foreach ($val as $key2=>$val2) {
					if ($val2[$item_key]) {
						$this->html[$item_key]['divider'] = $key;
						$this->html[$item_key]['section'] = $key2;
						$ok = true;
					}
				}
			}
		}
		if (!$ok) {
			$this->html[$item_key]['divider'] = "General";  
			$this->html[$item_key]['section'] = "emptySection"; 
		}
	}

	function _processHidden($item_key) { 
		$TCA = $this->TCA["columns"]['hidden'];
		$item = $this->items[$item_key];
		$item_value = $this->_getValue($item_key);
		$html = $this->_processEval($item_key);
		if (!empty($item_value)) {
			$html["process"] = 1;
			$html["value"] = "On";
			$html["preview"] = "hidden";
			$html['attributes.']["value"] = 1;
		} else {
			$html["process"] = "0";
			$html['value'] = 0;
			$html["preview"] = "visible";
			$html['attributes.']["value"] = 1;
		}
		$html["label"] = $TCA["label"];
		$html["help"] = $TCA["helptext"];
		$html["key"] = $item_key;
		$html["name"] = $item_key;
		$html["element"] = "checkboxRow";
		$html["config."] = $TCA['config'];
		$this->html[$item_key] = $html;
	}

	function _getLL($str) {
		/*
		$config=$this->controller->configurations->getArrayCopy();
	 	print($str);
		$string = @explode(':',$str);
		$path = $string[1].':'.$string[2];
		$translator = tx_div::makeInstance('tx_lib_translator');
		$translator = new tx_lib_translator;
		$key = $config[$this->controller->action];
		//debug($key);
		$translator->setPathToLanguageFile($key); 
		//echo $key;
		
		$translator-> _loadLocalLang();
 		if($translator->LOCAL_LANG['default'][$string[3]]) return $translator->LOCAL_LANG['default'][$string[3]];
	    else {
	    	$translator = tx_div::makeInstance('tx_lib_translator');
	    	$translator->setPathToLanguageFile($path); 
	    	$translator-> _loadLocalLang();
	    	if($string[3]) return $translator->LOCAL_LANG['default'][$string[3]]; //TODO : locallang !!!!!
           else return $str;
		}
		*/
		return $str;
	}
	
	function _getLLXX($localLangKey,$key=false) { //TODO: abspecken action und table unwichtig
		$string = @explode(':',$localLangKey);
		$config = $this->controller->configurations->getArrayCopy();
		$LLL[] = $localLangKey;
		if (!$LLKey) {
			foreach($LLL as $num=>$str) {
				$action = $this->panelAction;
				$string = @explode(':',$str);
				$path = $string[1] . ':' . $string[2];
				if (!is_object($this->extTranslator)) {
					$this->extTranslator = tx_div::makeInstance('tx_lib_translator');
				} else {
					$this->extTranslator->LOCAL_LANG_loaded=false;
				}
				if ($string[0] == "LLL" && !$LLKey) {
					$this->extTranslator->setPathToLanguageFile($path);
					if (!file_exists($this->extTranslator->getPathToLanguageFile())) {
						$type = explode(".",$string[2]);
						if ($type[1] == "php") {
							$path = str_replace("php","xml",$path);
						}
						$this->extTranslator->setPathToLanguageFile($path);
					}
					if (file_exists($this->extTranslator->getPathToLanguageFile())) {
						$this->extTranslator-> _loadLocalLang();
						if (!empty($this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$table.".".$action.".".$key.".".$string[3]])) {
							$translated = $this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$table.".".$action.".".$key.".".$string[3]];
						} elseif (!empty($this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$table.".".$key.".".$string[3]])) {
							$translated = $this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$table.".".$key.".".$string[3]];
						} elseif (!empty($this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$table.".".$key])) {
							$translated = $this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$table.".".$key];
						} elseif (!empty($this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$string[3]])) {
							$translated = $this->extTranslator->LOCAL_LANG[$this->extTranslator->LLkey][$string[3]];
						} elseif ($this->extTranslator->LOCAL_LANG['default'][$string[3]]) {
							$translated =  $this->extTranslator->LOCAL_LANG['default'][$string[3]];
						} else {
							if ($string[3] == "pages.doktype") {
								$translated = "Pagetype";
							} elseif($string[3] == "pages.title") {
								$translated =  "Title";
							}
						}
					}
				}
			}
		}
		//t3lib_div::debug($translated,$item_key);
		if (!empty($translated)) {
			return $translated;
		} else {
			return $localLangKey;
		}
	}

	//suche ob existiert
	function _checkUnique($item_key,$TCA) {
		$item_value = $this->controller->parameters->get($item_key); 
		$where = "$item_key=\"$item_value\"";
		$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery($item_key,$TCA['config']['table'],$where);
		if ($query) {
			$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
		}
		if ($result[$item_key] == $item_value) {
			return false;
		} else {
			return true;
		}
	}

	//suche nach pid ob existiert
	function _checkUniqueInPid($item_key,$TCA,$pid)  {
		$item_value = $this->controller->parameters->get($item_key); 
		$where = "$item_key=\"$item_value\"";
		$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery($item_key,$TCA['config']['table'],$where);
		if ($query) {
			return false;
		} else {
			return true;
		}
	}

	function _checkBackValues()  {
		$GET = t3lib_div::_GET();
		if (is_array($GET)) {
			if (isset($GET['id'])) {
				unset($GET['id']);
			}
			unset($GET[$this->getDesignator()]);
			if (is_array($GET)) {
				foreach($GET as $key=>$val) {
					if (!is_array($val)) {
						$this->backValues['_GET'][$key]=$val;
					} else {
						foreach ($val as $k=>$v) {
							$this->backValues['_GET'][$key][$k] = $v;
						}
					}
				}
			}
		}
		$POST = t3lib_div::_POST(); 
		unset($POST[$this->getDesignator()]);
		if (is_array($POST)) {
			foreach ($POST as $key=>$val) {
				$this->backValues['_POST'][$key]=$val;
			}
		}
	}

	function _processModifications($html,$item_key) {
		//debug($html,"pre");
		$setup = $this->controller->configurations->getArrayCopy();
		//debug($setup[$this->controller->action."."]["storage."][$this->panelTable."."][$item_key."."]);
		if (is_array($setup["storage."]['modifications.'][$this->panelTable."."][$item_key."."])) {
			foreach ($setup["storage."]['modifications.'][$this->panelTable."."][$item_key."."] as $key=>$val) {
				///debug($key,"erster key");
				if (!is_array($val)) {
					$html[str_replace(".","",$key)] = $val;
				} else {
					foreach ($val as $key2=>$val2) {
						if (!is_array($val2)) {
							//debug($key2,"zeiter key");
							$html[str_replace(".","",$key)][str_replace(".","",$key2)] = $val2;
						} else {
							foreach ($val2 as $key3=>$val3) {
								if (!is_array($val3)) {
									//debug($key3,"driteer  key");
									$html[str_replace(".","",$key)][str_replace(".","",$key2)][str_replace(".","",$key3)] = $val3;
								}
							}
						}
					}
				}
			}
		}
		if (is_array($setup["storage."]['overwrite.'][$this->panelTable."."][$item_key."."])) {
			$html = array();
			foreach ($setup["storage."]['overwrite.'][$this->panelTable."."][$item_key."."] as $key=>$val)
		///debug($key,"erster key");
			if (!is_array($val)) {
				$html[str_replace(".","",$key)] = str_replace(".","",$val);
			} else {
				foreach($val as $key2=>$val2) {
					if (!is_array($val2)) {
						//debug($key2,"zeiter key");
						$html[str_replace(".","",$key)][str_replace(".","",$key2)]=str_replace(".","",$val2);
					} else {
						foreach ($val2 as $key3=>$val3) {
							if (!is_array($val3)) {
								//debug($key3,"driteer  key");
								$html[str_replace(".","",$key)][str_replace(".","",$key2)][str_replace(".","",$key3)] = str_replace(".","",$val3);
							}
						}
					}
				}
			}
		}
		return $html;
	}


	//////////////////////////////////////////////////////////////////////
	///////////////////////////////DB PROCESSING//////////////////////////
	//////////////////////////////////////////////////////////////////////	


	function getDataMM($uid,$key) {
		$config = $this->html[$key]['config.'];
		$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid_foreign",$config['MM'],$config['MM'].".uid_local=".$uid);
		if ($query) {
			$i = 0;
			$size = $GLOBALS['TYPO3_DB']->sql_affected_rows($query);
			for ($i = 0; $i < $size; $i++) {
				$GLOBALS['TYPO3_DB']->sql_data_seek($query,$i); //TODO Bug!!
				$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
				$mm[$i] = $result;
			}
		}
		//t3lib_div::debug($mm,$item_key);
		if (is_array($mm)) {
			$i = 0;
			$where = "1=1";
			foreach ($mm as $uid=>$uid_foreign) {
				if ($i == 0) {
					$where .= " AND ";
				} else {
					$where .= " OR ";
				}
				$where .= "uid=" . $uid_foreign['uid_foreign'];
				$i++;
			}
			$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*",$config['foreign_table'],$where);
			if ($query) {
				$i = 0;
				$size = $GLOBALS['TYPO3_DB']->sql_affected_rows($query);
				for ($i = 0; $i < $size; $i++) {
					$GLOBALS['TYPO3_DB']->sql_data_seek($query,$i); 
					$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
					$uid = $result['uid'];
					unset($result['tstamp']);
					unset($result['crdate']);
					unset($result['pid']);
					unset($result['deleted']);
					unset($result['hidden']);
					unset($result['uid']);
					unset($result['cruser_id']);
					$dataMM[$uid] = $result;
				}
			}
			if ($dataMM) {
				return $dataMM;
			}
		}
	}


	///////////////////REDIRECT//////////////////////////
	function _nextStep() { //TODO: realurl !
		$this->redirect =  tx_div::makeInstance('tx_lib_link');
		$this->preNextStep(); 
		if (empty($this->redirect->designatorString)) {
			$this->redirect->designator($this->getDesignator());
		}
		if (empty($this->redirect->destination)) {
			$this->redirect->destination($GLOBALS['TSFE']->id);
		}
		$this->redirect->parameters = array();
		$url = $this->redirect->makeUrl();
		if (count($this->backValues['_GET']) >= 1) {
			foreach ($this->backValues['_GET'] as $key=>$val) {
				//$data=array();
				if(is_array($val)) {
					$prefix = "&" . $key;
					foreach( $val as $k=>$v) {
						$url .= $prefix . "[" . $k . "]=" . $v;
					}
				} else {
					$url .= "&" . $key . "=" . $val;
				}
			}
		} else {
			$url = "index.php?id=" . $GLOBALS['TSFE']->id . "";
		}

		if (!$this->postNextStep()) {
			//echo $url."&ajax=1&ajaxTarget=crud-tabs-form";
			//session_write_close();
			//header('Location: ' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $url."?refresh=1&ajax=1&ajaxTarget=crud-tabs-form");
			//header('Redirect: 1 url= ' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $url);
			//exit();
		}
	}

	function deleteMarker($key,$data) {
		$str = explode("{{{",$data);
		if (strlen($str[1]) > 1) {
			$new = $str[0];
			$str2 = explode("}}}",$str[1]);
			$new .= $str2[1];
			$marker = '<div style="display:none;">{{{'.$str2[0].'}}}</div>';
			$marker = str_replace(strtoupper($this->panelTable),"###TABLE###",$marker);
			$marker = str_replace($this->panelRecord,"###RECORD###",$marker);
			$marker = str_replace(strtoupper($this->panelAction),"###ACTION###",$marker);
			$new = str_replace('{{{'.$str2[0]."}}}",$marker,$data);
			return $new;
		} else {
			return $data;
		}
	}

	function printAsBackLink() {
		
	}
	
	function processLLL() {
		
	}

	function __destruct() {
		
	}

	function getCacheHash() {
		$config = $this->controller->configurations->getArrayCopy();
		$string = $config['setup.']['marker'].$config['storage.']['action'].$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		return $string;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/models/class.tx_crud_models_common.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/models/class.tx_crud_models_common.php']);
}
?>