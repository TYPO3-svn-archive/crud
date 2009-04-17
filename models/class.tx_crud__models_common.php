<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Frank Thelemann
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
class tx_crud__models_common extends tx_lib_object {
	
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
	
	
	// -------------------------------------------------------------------------------------
	// SETUP MODEL 
	// -------------------------------------------------------------------------------------
	
	/**
	 * setup the model based on a controller reference
	 * 
	 * makes an reset to the model and setup based on the controller configurations all things automatical
	 * @param	object		$controller	the reference to the controller oject
	 * @return	void
	 */
	function setup(&$controller) {
		$start=microtime(true);
		$this->reset ();
		$this->controller = $controller;
		$pars = $controller->parameters->getArrayCopy ();
		$filter = $pars ['filter'];
		if (is_array ( $filter )) {
			$this->filter = $filter;
		}
		if ($pars ['page'] >= 1) {
			$this->page = $pars ['page'];
		}
		$config = $this->controller->configurations->getArrayCopy ();
		$storage = $config ['storage.'];
		$this->controller->parameters = new tx_lib_object ( $pars );
		$this->setStorageNameSpace ( $storage ['nameSpace'] );
		$this->setStorageNodes ( $storage ['nodes'] );
		$this->setStorageAction ( $storage ['action'] );
		$this->setStorageFields ( $storage ['fields'] );
		if ($config ['enable.'] ['rights'] == '0') {
			$this->setStorageAnonym ();
		} else {
			tx_crud__acl::setup ( $config );
			$this->rights = tx_crud__acl::getOptions ();
		}
		if ($config ['enable.'] ['logging'] == 1) {
			$this->logs = tx_crud__log::read ( $storage ['action'], $storage ['nodes'], $storage ['nameSpace'], $config ['logging.'] );
		}
		$hash = md5 ( $this->getCacheHash () . "-MODEL" );
		if ($config ['enable.'] ['caching'] == 1) {
			$this->cached=tx_crud__cache::get($hash);
		}
		//t3lib_div::debug($this->cached);
		$this->load ();
		
	
	}
	
	/**
	 * reset the controller for using again with an other setup configurations
	 * 
	 * @return	void
	 */
	function reset() {
		unset ( $this->controller );
		unset ( $this->parameters );
		unset ( $this->html );
		unset ( $this->items );
		unset ( $this->rights );
		unset ( $this->panelTable );
		unset ( $this->panelRecord );
		unset ( $this->panelAction );
		unset ( $this->lastQueryID );
		unset ( $this->fields );
		unset ( $this->cache );
		unset ( $this->cached );
		unset ( $this->data );
		unset ( $this->cType );
		unset ( $this->errors );
		unset ( $this->mode );
		unset ( $this->submit );
		unset ( $this->processData );
		unset ( $this->processDataMM );
		$this->start = 0;
		$this->limit = 5;
		unset ( $this->data );
		$this->count = 0;
		$this->page = 0;
		unset ( $this->types );
		unset ( $this->action );
		unset ( $this->submit );
		unset ( $this->backValues );
	}
	
	// -------------------------------------------------------------------------------------
	// SETTER
	// -------------------------------------------------------------------------------------
	
	/**
	 * set the db table for the model
	 * 
	 *
	 * @param	string		$namespace	the database table
	 * @return	void
	 */
	public function setStorageNameSpace($namespace) {
		$this->panelTable = $namespace;
	}
	
	/**
	 * set the pid for the model
	 * 
	 *
	 * @param	string		$nodes	the page id for all actions
	 * @return	void
	 */
	public function setStorageNodes($nodes) {
		$this->panelRecord = $nodes;
	}
	
	/**
	 * set the action for the model
	 * 
	 *
	 * @param	string		$action	the action for the model
	 * @return	void
	 */
	public function setStorageAction($action) {
		$this->panelAction = $action;
	}
	
	/**
	 * set the db fields for using in the model
	 * 
	 *
	 * @param	string		$fields	commaseparated db fields
	 * @return	void
	 */
	public function setStorageFields($fields) {
		$this->fields = $fields;
	}
	
	/**
	 * set the action with no rights
	 * 
	 * @return	void
	 */
	public function setStorageAnonym() {
		$fields = explode ( ",", $this->fields );
		if (! isset ( $this->panelAction ) || ! isset ( $this->panelTable )) {
			die ( "call setStorageAnonym required a setting up panelTable and panelAction" );
		}
		foreach ( $fields as $key ) {
			$this->rights [$this->panelTable] [$this->panelAction] [$key] = $key;
		}
	}
	
	/**
	 * set the rights for the model
	 * 
	 * @param 	array 	$acls	array with the rights based on class.crud__acl
	 * @return	void
	 * @see		class.tx_crud__acl.php
	 */
	public function setStorageAcls($acls) {
		$this->rights = $acls;
	}
	
	/**
	 * set a function for pre/post processing
	 * 
	 * @param 	string 	$call	the name of the model function
	 * @param 	object	$obj	the reference to the object with the pre/post function
	 * @return	void
	 */
	public function setStorageFunction($call, &$obj) {
		$this->functions [$call] = &$obj;
	}
	
	/**
	 * set manual an error
	 * 
	 * @param 	string 	$key	the field name in the setup
	 * @param 	string	$error	the reference to the object with the pre/post function
	 * @return	void
	 */
	public function setError($key, $error) {
		$this->errors [$key] = $error;
	}
	
	/**
	 * set the submit state 
	 * 
	 * normaly called automatical by load() but you can use it manual too with the 2 params
	 * you can use for the mode ICON,PROCESS,EDIT,HIDE
	 * 
	 * @param 	string 	$submit	the submit state from a form
	 * @param 	string	$mode	the mode to set
	 * @return	void
	 */
	function setSubmit($submit=false,$mode=false) {
		if($submit && $mode) {
			$this->submit=$submit;
			$this->mode=$mode;
			$this->postSetSubmit ();
			return true;
		}
		$this->preSetSubmit ();
		$config = $this->controller->configurations->getArrayCopy ();
		if ($this->controller->parameters->get ( "form" ) == tx_crud__div::getActionID ( $config )) {
			if ($this->controller->parameters->get ( "process" ) == "preview") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($this->controller->parameters->get ( "process" ) == "create") {
				$this->submit = false;
				$this->mode = "EDIT";
			} elseif ($this->controller->parameters->get ( "process" ) == "update") {
				if ($this->controller->parameters->get ( "icon" ) == "1") {
					$this->submit = false;
					$this->mode = "EDIT";
				} else {
					$this->submit = true;
					$this->mode = "EDIT";
				}
			} elseif ($this->controller->parameters->get ( "process" ) == "delete") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($this->controller->parameters->get ( "process" ) == "cancel") {
				$this->submit = false;
				$this->mode = "ICON";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		} else {
			if ($this->controller->parameters->get ( "form" ) && ! $this->controller->parameters->get ( "cancel" )) {
				$this->submit = false;
				$this->mode = "HIDE";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		}
		if (is_array ( $this->cType ) && $this->mode == "PROCESS" || $this->mode == "PREVIEW" )
			foreach ( $this->cType as $name => $val ) {
				$pars=$this->controller->parameters->getArrayCopy();
				if (isset($pars[$name]) && ! $this->controller->parameters->get ( "submit" )) {
					$this->mode = "EDIT";
				}
			}
		if ($this->hasUpload)
			$this->mode = "EDIT";
		$this->postSetSubmit ();
	}

	/**
	 * set the model mode wich means the state like PROCESS,EDIT,QUERY_ERROR etc
	 * 
	 * normaly called automatical by load() but you can use it manual too with the optional param
	 * 
	 * @param 	string	$mode	the own mode to set
	 * @return	void
	 */
	function setMode($mode=false) {
		if($mode) {
			$this->mode=$mode;
			return true;
		}
		$this->preSetMode ();
		if ($this->mode == "PROCESS" && is_array ( $this->errors ) || $this->controller->parameters->get ( "remove" )) {
			$this->mode = "EDIT";
		}
		$this->_checkNode ();
		$this->postSetMode ();
	}
	
	/**
	 * set the extension key
	 * 
	 * normaly called automatical by load() but you can use it manual too with the optional param
	 * 
	 * @param 	string 	$prefixId 	the extension key
	 * @return  void
	 */ 
	function setPrefixId($prefixId) {
		$this->prefixId = $prefixId;
	}
	
	/**
	 * set some own field for the setup
	 * 
	 * @param 	string 	$key 	the name for the setup key
	 * @param 	array	$TCA	the complete tca entry for the field
	 * @return  void
	 */ 
	function setVirtualFields($key, $TCA) {
		$this->html [$key] = $TCA;
	}
	
	// -------------------------------------------------------------------------------------
	// GETTER
	// -------------------------------------------------------------------------------------
	
	/**
	 * get an error from a setup key or get al errors
	 * 
	 * leave the key empty for all errors
	 * 
	 * @param 	string 	$key 	the name for the setup key for the error
	 * @return  string	the error if exist
	 */
	public function getError($key = false) {
		if ($key) {
			return $this->errors [$key];
		} else {
			return $this->errors;
		}
	}
	
	/**
	 * get all actual used storage fields
	 * 
	 * @return  string	comma separated fields of the storage/table
	 */
	public function getStorageFields() {
		if (is_array ( $this->rights )) {
			$fields = implode ( ",", $this->rights [strtolower ( $this->getStorageNameSpace () )] [strtolower ( $this->getStorageAction () )] );
		} else {
			$fields = $this->fields;
		}
		
		$fields = explode ( ",", $fields );
		$config = $this->controller->configurations->getArrayCopy ();
		if (is_array ( $config ['storage.'] ['virtual.'] [$this->panelTable . "."] )) {
			foreach ( $fields as $val ) {
				$new_fields [$val] = $val;
			}
			$fields = $new_fields;
			foreach ( $config ['storage.'] ['virtual.'] [$this->panelTable . "."] as $key => $val ) {
				$key = str_replace ( ".", "", $key );
				if ($fields [$key]) {
					unset ( $fields [$key] );
				}
				$key .= "_again";
				if ($fields [$key]) {
					unset ( $fields [$key] );
				}
			}
		}
		if (is_array ( $fields )) {
			return implode ( ",", $fields );
		}
	}
	
	/**
	 * get the actual mode
	 * 
	 * @return  string	$mode the mode of the model
	 */
	public function getStorageMode() {
		return $this->mode;
	}
	
	/**
	 * get the pid for the storage
	 * 
	 * @return  integer	the pid wich is actual used
	 */
	public function getStorageNodes() {
		return $this->panelRecord;
	}
	
	/**
	 * get the complete setup
	 * 
	 * @return  array	the setup array
	 */
	public function getStorageSetup() {
		return $this->html;
	}
	
	/**
	 * get the action of the model
	 * 
	 * @return  string	the model action
	 */
	public function getStorageAction() {
		return $this->panelAction;
	}
	
	/**
	 * get the table name of the model
	 * 
	 * @return  string	the db table name
	 */
	public function getStorageNameSpace() {
		return $this->panelTable;
	}
	
	/**
	 * get the params to go back before the action started
	 * 
	 * @return  array	the get/post params array
	 */
	public function getBackValues() { 
		if (is_array ( $this->backValues )) {
			return $this->backValues;
		} else {
			return false;
		}
	}
	
	/**
	 * get the tca from the table and the fields
	 * 
	 * @return  array	the get/post params array
	 */
	public function getTCA() {
		$array = false;
		if (is_array ( $this->html )) {
			$array = array ();
			foreach ( $this->html as $key => $val ) {
				foreach ( $val as $row => $value ) {
					$form [$row] = $value;
				}
			}
			return $form;
		} else {
			return false;
		}
	}
	
	/**
	 * get a translated a string bye the complete locallang key with the path.
	 *
	 * @param	string		$localLangKey 	the complete key. Example LLL:EXT:crud/locallang.xml:key
	 * @param	boolean		$force	if set the string will return alos if not successfull translated
	 * @return	string
	 */
	function getLL($localLangKey,$force=false) {
		$string = @explode(':',$localLangKey);
		$config = $this->controller->configurations->getArrayCopy();
		$hash=md5($string[1].$string[2]);
		if($string[0]=="LLL") {
			if(!is_array($this->LL[$hash])) {
				$localLang=tx_crud__cache::get($hash);
				$this->LL[$hash]=$localLang;
			}
			else $localLang=$this->LL[$hash];
			$LLL[] = $localLangKey;
			if (!is_array($localLang)) {
		
				foreach($LLL as $num=>$str) {
					$action = $this->get("action");
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
							$this->extTranslator->_loadLocalLang();
							if(is_array($this->extTranslator->LOCAL_LANG)) {
								tx_crud__cache::write($hash,$this->extTranslator->LOCAL_LANG);
								$localLang=$this->extTranslator->LOCAL_LANG;
								$this->LL[$hash]=$localLang;
							}
						}
					}
				}
			}
			if(is_array($localLang)) {
				$language=trim($GLOBALS['TSFE']->config['config']['language']);
				if (strlen($localLang[$language][$string[3]])>=5) {
					$translated =  $localLang["default"][$string[3]];
				}
				elseif ($localLang["default"][$string[3]]) {
					$translated =  $localLang['default'][$string[3]];
				}
				else {
					if ($string[3] == "pages.doktype") {
						$translated = "Pagetype";
					} elseif($string[3] == "pages.title") {
						$translated =  "Title";
					}
				}
			}
		}
		if (!empty($translated)) {
			return $translated;
		} else {
			if($force) return $localLangKey;
			else return false;
		}
	}
	
	// -------------------------------------------------------------------------------------
	// POST/PRE-PROCESS DUMMIES FOR YOUR OWN MODELS
	// -------------------------------------------------------------------------------------
	
	/**
	 * dummy for manipulate the model in your own model before the processQuery function processed 
	 * 
	 * @return  void
	 */
	public function preQuery() {}
	
	/**
	 * dummy for manipulate the model in your own model aftere the processQuery function processed 
	 * 
	 * @return  void
	 */
	public function postQuery() {}

	/**
	 * dummy for manipulate the model in your own model before the setMode function processed 
	 * 
	 * @return  void
	 */
	public function preSetMode() {}
	
	/**
	 * dummy for manipulate the model in your own model before the setMode function processed 
	 * 
	 * @return  void
	 */
	public function postSetMode() {}

	/**
	 * dummy for manipulate the model in your own model before the setSubmit function processed 
	 * 
	 * @return  void
	 */
	public function preSetSubmit() {}

	/**
	 * dummy for manipulate the model in your own model after the setSubmit function processed 
	 * 
	 * @return  void
	 */	
	protected function postSetSubmit() {}
	
	/**
	 * dummy for manipulate the model in your own model before the nextStep function processed 
	 * 
	 * @return  void
	 */	
	public function preNextStep() {}
	
	/**
	 * dummy for manipulate the model in your own model after the nextStep function processed 
	 * 
	 * @return  void
	 */	
	public function postNextStep() {}
	
	/**
	 * dummy for manipulate the model in your own model before the processEval function processed 
	 * 
	 * @param 	string	$item_key	the key of the setup entry
	 * @return  void
	 */	
	public function preProcessEval($item_key) {}
	
	/**
	 * dummy for manipulate the model in your own model after the processEval function processed 
	 * 
	 * @param 	string	$item_key	the key of the setup entry
	 * @return  void
	 */	
	public function postProcessEval($item_key) {}
	
	/**
	 * dummy for manipulate the model in your own model before the process* any fieldtype function processed 
	 * 
	 * @param 	string	$item_key	the key of the setup entry
	 * @return  void
	 */	
	public function preProcessField($item_key) {}
	
	/**
	 * dummy for manipulate the model in your own model after the process* any fieldtype function processed 
	 * 
	 * @param 	string	$item_key	the key of the setup entry
	 * @return  void
	 */		
	public function postProcessField($item_key) {}
	
	/**
	 * dummy for manipulate the model in your own model before the load function processed 
	 * 
	 * @return  void
	 */	
	public function preLoad() {}
	
	/**
	 * dummy for manipulate the model in your own model after the load function processed 
	 * 
	 * @return  void
	 */		
	public function postLoad() {}
	
	// -------------------------------------------------------------------------------------
	// LOADER
	// -------------------------------------------------------------------------------------
	
	/**
	 * the loader of the model, called normally by setup
	 * 
	 * checks for an cache, if empty  the tca and setup will be rendered and written to the cache and the configurations
	 * sets the mode and submit states and furthermore the options and values for the setup. 
	 * 
	 * @return  void
	 */	
	
	protected final function load() {
		$this->preLoad();
		$this->prefixId = $this->getDesignator ();
		$table = strtolower ( $this->panelTable );
		$action = $this->panelAction;
		$rights = $this->rights;
		$allowedFields = explode ( ",", $this->getStorageFields () );
		foreach ( $allowedFields as $key => $val ) {
			$allowedParameters [$val] = $val;
		}
		$pars = $this->controller->parameters->getArrayCopy ();
		foreach ( $pars as $key => $val ) {
			if (! $allowedParameters [$key]) {
				$deniedParameters [$key] = $key;
			}
		}
		if (is_array ( $deniedParameters )) {
			foreach ( $deniedParameters as $key => $val ) {
				unset ( $pars [$key] );
				$unsetParameters = true;
			}
		}
		$conf = $this->controller->configurations->getArrayCopy ();
		if (is_array ( $conf ['storage.'] [$table . '.'] )) {
			$this->modify = $conf ['storage.'] [$table . '.'];
		}
		$config = $this->controller->configurations->getArrayCopy ();
		$back_id = $GLOBALS ['TSFE']->id;
		$this->_checkBackValues ();
		if ($fields = @implode ( ',', $rights [strtolower ( $table )] [strtolower ( $action )] )) {
			$start=microtime(true);
			//t3lib_div::debug($config);
			$cache = $this->cached ['TCA'];
			if (is_array ( $cache )) {
				$items = $cache ['TCA'];
				//t3lib_div::debug($cache);
				$this->TCA = $items;
				$this->bigTCA = $cache ['CTRL'];
				$this->divider = $cache ['DIVIDER'];
				if ($cache ['CTYPE'])
					$this->cType = $cache ['CTYPE'];
				if ($cache ['TYPES'])
					$this->types = $cache ['TYPES'];
				if ($cache ['TITLE'])
					$this->recordTitle = $cache ['TITLE'];
			} else {
				$cache ['TITLE'] = $this->TCA ['ctrl'] ['title'];
			}
			if (is_array ( $this->cType ) && (strtolower($this->panelAction)=="create"  || strtolower($this->panelAction)=="update")) $reloadTCA = true;
			if ($reloadTCA) {
				$this->cached = false;
				$items = false;
				$cache = false;
				$this->bigTCA = false;
				$this->divider = false;
			}
			
			if (! is_array ( $items ) || $reloadTCA) {	
				$items = $this->_processTCA ( $table, $fields );
			}
			$config = $this->controller->configurations->getArrayCopy ();
		    //t3lib_div::debug($items);
			$this->table = $table;
			$this->items = $items;
			$this->setSubmit ();
			if ($config ['enable.'] ['caching'] && is_array($this->cached ['HTML'])) $this->html = $this->cached ['HTML'];
			if (!is_array($this->html )) {
				$this->_processAll ();
				if ($config ['enable.'] ['caching'])
					$this->cache ['HTML'] = $this->html;
			}
			$data = $this->getData ();
			//t3lib_div::debug($data);
			$config = $this->controller->configurations->getArrayCopy ();
			//t3lib_div::debug($config);
			//t3lib_div::debug($this->html);
			//die();
			$this->_processOptions ();
			$this->setupValues ();
			$this->setMode ();
			$config = $this->controller->configurations->getArrayCopy ();
			$config ['view.'] ['mode'] = $this->mode;
			$config ['view.'] ['errors'] = $this->errors;
			$config ['view.'] ['setup'] = $this->html;
			$config ['view.'] ['title'] = $this->recordTitle;
			$config ['view.'] ['backValues'] = $this->backValues;
			if (isset ( $_GET['q'] )) {
				$this->data = $this->sortAutocomplete ( $data );
			} else
				$this->data = $data;
			$config ['view.'] ['data'] = $data;
			if (is_array ( $this->logs )) {
				$config ['view.'] ['logs'] = $this->logs;
			}
			if ($this->hasUpload || is_array ( $_POST [$this->getDesignator ()] ['remove'] )) {
				$this->mode = "EDIT";
				$config ['view.'] ['mode'] = $this->mode;
			}
			if(!isset($_REQUEST['q']) && isset($config['getExistingValues.'])) {
			
				$config['view.']['existingValues']= $this->getStaticValues();
				//t3lib_div::debug($config['view.']['existingValues']);
			}

			$this->controller->configurations = new tx_lib_object ( $config );
			if ($this->mode == 'PROCESS') {
				$this->processQuery ();
				$config = $this->controller->configurations->getArrayCopy ();
				$config ['view.'] ['mode'] = $this->mode;
				if(is_array($this->additionalData)) $config ['view.'] ['additionalData'] = $this->additionalData;
				if ($config ['enable.'] ['histories'] == 1 && is_array ( $this->histories )) $config ['view.'] ['histories'] = $this->histories;
				$config ['view.'] ['data'] = $data;
				$this->controller->configurations = new tx_lib_object ( $config );
				$this->_nextStep ();
			}
		} else {
			$this->mode = "NO_RIGHTS";
			$config = $this->controller->configurations->getArrayCopy ();
			$config ['view.'] ['mode'] = $this->mode;
			$config ['view.'] ['setup'] = $this->html;
			$config ['view.'] ['title'] = $this->recordTitle;
			$config ['view.'] ['backValues'] = $this->backValues;
			$this->controller->configurations = new tx_lib_object ( $config );
		
		}
		$this->postLoad();
	}
	
	// -------------------------------------------------------------------------------------
	// TCA PROCESSING
	// -------------------------------------------------------------------------------------

	/**
	 * processing the tca for the table and fields and looks for modifications and virtual fields defined by typoscript setup
	 * 
	 * @param 	string $table	the db table name
	 * @param 	string	$fields	commaseparated fields of the table
	 * @return  void
	 */	
	protected function _processTCA($table, $fields) {
		$setup = $this->controller->configurations->getArrayCopy ();
		$extList = explode ( ",", $GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extList'] );
		$fields = explode ( ',', $fields );
		foreach ( $fields as $f ) {
			$nFields [$f] = $f;
		}
		$fields = $nFields;
		foreach ( $fields as $val ) {
			if (substr ( $val, 0, 3 ) == "tx_") {
				$parts = explode ( "_", $val );
				if ($parts [1]) {
					$extraTCA [$parts [1]] = $parts [1];
				}
			}
		}
		if (is_array ( $extraTCA )) {
			foreach ( $extList as $listKey ) {
				$listName = str_replace ( "_", "", $listKey );
				if ($extraTCA [$listName]) {
					$loadExtraTCA [] = $listKey;
					}
				}
			}
			if ($table == "pages") {
				require_once (t3lib_extMgm::extPath ( 'crud' ) . 'library/pages_tca.php');
			}
			if (is_array ( $loadExtraTCA )) {
				tx_div::loadTcaAdditions ( $loadExtraTCA );
			}
			t3lib_div::loadTCA ( $table );
			$this->TCA = $GLOBALS ["TCA"] [$table];
			//t3lib_div::debug($this->TCA);
			if (! is_array ( $this->TCA )) die ( "Could not load TCA from table:" . $table );
			$this->bigTCA = $GLOBALS ["TCA"] [$table];
			$this->recordTitle = $this->TCA ['ctrl'] ['title'];
			if ($GLOBALS ["TCA"] [$table] ['ctrl'] ['type']) {
				$control = $GLOBALS ["TCA"] [$table] ['ctrl'] ['type'];
				$cType [$control] = $GLOBALS ["TCA"] [$table] ['columns'] [$control] ['config'] ['default'];
				if ($this->controller->parameters->get ( $control )) {
					$cType [$control] = $this->controller->parameters->get ( $control );
				}
				$this->cType = $cType;
				$this->types = $cType [$control];
				$cFields = explode ( ",", $GLOBALS ['TCA'] [$table] ['types'] [$this->types] ['showitem'] );
				foreach ( $cFields as $key ) {
					$name = explode ( ";", $key );
					$name [0] = str_replace ( " ", "", $name [0] );
					if ($fields [$name [0]]) {
						$newFields [$name [0]] = $name [0];
					}
					if (! empty ( $GLOBALS ['TCA'] [$table] ['palettes'] [$name [2]] ['showitem'] ) || $name [0] == '--palette--') {
						$extraFields = explode ( ",", $GLOBALS ['TCA'] [$table] ['palettes'] [$name [2]] ['showitem'] );
						if (is_array ( $extraFields )) {
							foreach ( $extraFields as $e => $extra ) {
								$extra = str_replace ( " ", "", $extra );
								if ($fields [$extra]) {
									$newFields [$extra] = $extra;
								}
							}
						}
					}
				}
			}
			if (is_array ( $newFields )) {
				foreach ( $fields as $key => $val ) {
					if (! $newFields [$key]) {
						if ($key != "starttime" && $key != "endtime" && $key != "fe_group") {
							unset ( $fields [$key] );
						}
					}
				}
			}
			$TCA = $GLOBALS ["TCA"] [$table] ['columns'];
			
			if ($setup ["storage."] ['virtual.'] [strtolower ( $this->panelTable ) . "."]) {
				foreach ( $setup ["storage."] ['virtual.'] [strtolower ( $this->panelTable ) . "."] as $key => $val ) {
					if (is_array ( $val )) {
						foreach ( $val as $key2 => $val2 ) {
							if (! is_array ( $val2 )) {
								if ($key2 != "label") {
									$TCA [str_replace ( ".", "", $key )] [str_replace ( ".", "", $key2 )] = str_replace ( ".", "", $val2 );
								} else {
									$TCA [str_replace ( ".", "", $key )] [$key2] = $val2;
								}
							} else {
								foreach ( $val2 as $key3 => $val3 ) {
									if (! is_array ( $val3 )) {
										$TCA [str_replace ( ".", "", $key )] [str_replace ( ".", "", $key2 )] [str_replace ( ".", "", $key3 )] = str_replace ( ".", "", $val3 );
									}
								}
							}
						}
					}
				}
			}
			
			$TCA ['deleted'] = array ('delete' => 1 );
			$rights = $this->rights;
			foreach ( $TCA as $key => $val ) $TCA[trim($key)]=$val;
			foreach ( $fields as $key => $val ) $fields[trim($key)]=$val;
			foreach ( $TCA as $key => $val ) {
					if(!isset($fields[$key]))unset($TCA[$key]);
			}
			$newTCA=$TCA;
			
			$setup = $this->controller->configurations->getArrayCopy ();
			if (strtoupper ( $this->panelAction ) != 'DELETE') {
				unset ( $newTCA ['deleted'] );
			}
			if (is_array ( $GLOBALS ["TCA"] [$table] ['columns'] ['hidden'] ) && $rights [strtolower ( $table )] [strtoupper ( $this->panelAction )] ['hidden']) {
				$newTCA ['hidden'] ['config'] ['type'] = "hidden";
			}
			if (! is_array ( $cType )) {
				$this->types = 0;
			} else {
				$this->types = $cType [$control];
			}
			$tryTCA=$newTCA;
			foreach($tryTCA as $key=>$config) {
				if (is_array ( $setup ["storage."] ["modifications."] [strtolower ( $this->panelTable ) . "."] [$key.'.'] )) {
					foreach($setup ["storage."] ["modifications."] [strtolower ( $this->panelTable ) . "."] [$key.'.'] as $name=>$value) {
						$name=str_replace(".","",$name);
						if(is_array($value)) foreach($value as $name2=>$value2) {
							$name2=str_replace(".","",$name2);
							if($value2=="unset") {
								unset($newTCA[$key][$name][$name2]);
							}
							else $newTCA[$key][$name][$name2]=$value2;
						}
						else {
							if(trim($value)=="unset") unset($newTCA[$key][$name]);
							else $newTCA[$key][$name]=$value;
						}
					}
				}
			}
			if (strtolower($this->panelAction)=="update" || strtolower($this->panelAction)=="create") {
				if(is_array ( $setup ["storage."] ["modifications."] [strtolower ( $this->panelTable ) . "."] ['divider.'] )) {
					$divider = $setup ["storage."] ["modifications."] [strtolower ( $this->panelTable ) . "."] ['divider.'];
					$orgDivider = $divider;
					//print_r($divider);
					foreach ( $divider as $key => $val ) {
						$name = str_replace ( ".", "", $key );
						foreach ( $val as $breaker => $values ) {
						$fFields = explode ( ",", $values );
						$sectionFields = false;
						foreach ( $fFields as $fName ) {
							if ($newTCA [$fName]) {
								$sectionFields [$fName] = $fName;
								$sectionTCA [$fName] = $newTCA [$fName];
							}
						}
						if (is_array ( $sectionFields )) {
							$this->divider [$name] [$breaker] = $sectionFields;
						}
					}
				}
				$newTCA = $sectionTCA;
				
			} 
			
			else {
				$tabs = explode ( "--div--", $GLOBALS ['TCA'] [$table] ['types'] [$this->types] ['showitem'] );
				if (sizeof ( $tabs >= 1 )) {
					$i = 0;
					foreach ( $tabs as $key => $val ) {
						$values = explode ( ",", $val );
						foreach ( $values as $k => $v ) {
							$v = str_replace ( " ", "", $v );
							$v = explode ( ";", $v );
							$v = $v [0];
							if (is_array ( $newTCA [$v] )) {
								if ($i == 0) {
									$title = "General";
								} elseif (strlen ( $values [0] ) > 2) {
									$title = $values [0];
								} else {
									$title = "%%%more%%% " . $i; 
								}
								$title = str_replace ( ";", "", $title );
								$this->divider [$title] ["emptySection"] [$v] = $v;
							}
						}
						$i ++;
					}
				}
			}
			}
		$this->cType = $cType;
		$this->types = $cType [$control];
		$cache ['TCA'] = $newTCA;
		$cache ['CTRL'] = $this->bigTCA ['ctrl'];
		$this->bigTCA = $cache ['CTRL'];
		$cache ['DIVIDER'] = $this->divider;
		$cache ['TYPES'] = $this->type;
		$cache ['CTYPE'] = $this->cType;
		$cache ['TITLE'] = $this->recordTitle;
		$this->cache ['TCA'] = $cache;
		//t3lib_div::debug($newTCA);
		return $newTCA;
	}
	
	/**
	 * processing based on a rendered tca set by processTCA the setup for all fields in the model
	 * 
	 * @return  void
	 */	
	private function _processAll() {
		$items = $this->items;
		$pars = $this->parameters;
		if ($pars ['submit']) {
			unset ( $pars ['submit'] );
		}
		if ($pars ['cancel']) {
			unset ( $pars ['cancel'] );
		}
		if ($pars ['preview']) {
			unset ( $pars ['preview'] );
		}
		if ($pars ['action']) {
			unset ( $pars ['action'] );
		}
		if ($pars ['table']) {
			unset ( $pars ['table'] );
		}
		if ($pars ['record']) {
			unset ( $pars ['record'] );
		}
		if (is_array ( $items )) {
			foreach ( $items as $key => $val ) {
				if ($val ['config'] ['type'] == 'input') {
					$this->_processInput ( $key );
				} elseif ($val ['config'] ['type'] == 'hidden') {
					$this->_processHidden ( $key );
				} elseif ($val ['config'] ['type'] == 'text') {
					$this->_processText ( $key );
				} elseif ($val ['config'] ['type'] == 'select') {
					$this->_processSelect ( $key );
				} elseif ($val ['config'] ['type'] == 'check') {
					$this->_processCheck ( $key );
				} elseif ($val ['config'] ['type'] == 'radio') {
					$this->_processRadio ( $key );
				} elseif ($val ['config'] ['type'] == 'group' && $val ['config'] ['internal_type'] == "file") {
					$this->_processFiles ( $key );
				} elseif ($val ['config'] ['type'] == 'group' && $val ['config'] ['internal_type'] != "file") {
					$this->_processGroup ( $key );
				} elseif ($key == 'deleted') {
					$this->_processDeleted ( $key );
				}
			}
		}
		if ($this->submit && is_array ( $pars )) {
			foreach ( $pars as $key => $val )
				$values .= $val;
		}
		if (strlen ( $values ) < 1) {
			$this->lasterror = "empty";
		}
		if ($this->lasterror) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * processing the modifications for an fields wich defined by the typoscript setup
	 * 
	 * @param 	array	$html	the initial setup for the setup entry
	 * @param 	string	$item_key	the key of the setup
	 * @return  void
	 */	
	private function _processModifications($html, $item_key) {
		$setup = $this->controller->configurations->getArrayCopy ();
		if (is_array ( $setup ["storage."] ['modifications.'] [$this->panelTable . "."] [$item_key . "."] )) {
			foreach ( $setup ["storage."] ['modifications.'] [$this->panelTable . "."] [$item_key . "."] as $key => $val ) {
				if (! is_array ( $val )) {
					$html [str_replace ( ".", "", $key )] = $val;
				} else {
					foreach ( $val as $key2 => $val2 ) {
						if (! is_array ( $val2 )) {
							$html [str_replace ( ".", "", $key )] [str_replace ( ".", "", $key2 )] = $val2;
						} else {
							foreach ( $val2 as $key3 => $val3 ) {
								if (! is_array ( $val3 )) {
									$html [str_replace ( ".", "", $key )] [str_replace ( ".", "", $key2 )] [str_replace ( ".", "", $key3 )] = $val3;
								}
							}
						}
					}
				}
			}
		}
		return $html;
	}
		
	// -------------------------------------------------------------------------------------
	// RENDERING OF TCA FIELDS
	// -------------------------------------------------------------------------------------
	
	/**
	 * processing tca type text 
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */	
	private function _processText($item_key) {
		$pars = $this->parameters;
		$conf = $this->configurations;
		$TCA = $this->items [$item_key];
		if (! isset ( $TCA ['config'] ['type'] )) {
			$TCA ['config'] = $GLOBALS ["TCA"] [$this->table] ["columns"] [$item_key] ['config'];
		}
		$item = $this->items [$item_key];
		$html = $this->_processEval ( $item_key );
		$html ["config."] = $TCA ['config'];
		$html ["search"] = 1;
		if (is_array ( $this->divider )) {
			$html ['divider'] = $this->_processDivider ( $item_key );
		}
		$html ["key"] = $item_key;
		if (strstr ( $TCA ['defaultExtras'], "richtext" ) || is_array ( $TCA ['config'] ['wizards'] ['RTE'] )) {
			$html ["element"] = "rteRow";
		} else {
			$html ["element"] = "textareaRow";
		}
		$html ["attributes."] = $this->_processAttributes ( $TCA ['config'] );
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
		return $html;
	}
	
	/**
	 * processing tca type uploads/files
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */	
	private function _processFiles($item_key) {
		$TCA = $this->items [$item_key];
		if (! isset ( $TCA ['config'] ['type'] )) {
			$TCA = $GLOBALS ["TCA"] [$this->table] ["columns"] [$item_key];
		}
		$item = $this->items [$item_key];
		;
		$html = $this->_processEval ( $item_key );
		$html ["key"] = $item_key;
		$html ["attributes."] = $this->_processAttributes ( $TCA ['config'] );
		$html ["attributes."] ['name'] = $this->prefixId . "[" . $item_key . "]";
		$html ['config.'] = $TCA ['config'];
		if (! empty ( $html ['help'] )) {
			$html ["attributes."] ['title'] = $html ['help'];
		}
		if (is_array ( $this->divider )) {
			$html ['divider'] = $this->_processDivider ( $item_key );
		}
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
	}
	
	/**
	 * processing tca type input
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */	
	private function _processInput($item_key) {
		$this->preProcessField ( $item_key );
		$TCA = $this->items [$item_key];
		if (! isset ( $TCA ['config'] ['type'] )) {
			$TCA = $GLOBALS ["TCA"] [$this->table] ["columns"] [$item_key];
		}
		$item = $TCA;
		if (! $item_value && $TCA ['config'] ['eval'] == 'datetime') {
			if ($TCA ['config'] ['default'] > 1) {
				$item_value = $TCA ['config'] ['default'];
			}
		}
		$html ["label"] = $TCA ["label"];
		$html ["help"] = $TCA ["help"];
		$eval = explode ( ",", $TCA ['config'] ['eval'] );
		$html ["key"] = $item_key;
		$this->_processDivider ( $item_key );
		if (in_array ( "password", $eval )) {
			$html ["element"] = "passwordRow";
		} elseif (in_array ( "datetime", $eval )) {
			$html ["element"] = "dateTimeRow";
		} else {
			$html ["search"] = 1;
			$html ["element"] = "inputRow";
		}
		$html ["config."] = $TCA ['config'];
		$html ["attributes."] = $this->_processAttributes ( $TCA ['config'] );
		if (! empty ( $html ['help'] )) {
			$html ["attributes."] ['title'] = $html ['help'];
		}
		if (in_array ( "twice", $eval ) && $this->mode = "EDIT") {
			$second_value = $this->_getValue ( $item_key . "_again" );
			$html2 ["label"] = str_replace ( $item_key, $item_key . "_again", $html ['label'] );
			$html2 ["help"] = $html ["help"];
			$html2 ["key"] = $item_key . "_again";
			$html2 ["element"] = "inputRow";
			$html2 ["attributes."] = $this->_processAttributes ( $TCA ['config'] );
		}
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
		if (is_array ( $html2 )) {
			$this->html [$item_key . "_again"] = $html2;
			$this->html [$item_key . "_again"] ['divider'] = $this->html [$item_key] ['divider'];
			$this->html [$item_key . "_again"] ['section'] = $this->html [$item_key] ['section'];
			if (in_array ( "password", $eval )) {
				$this->html [$item_key . "_again"] ['element'] = 'passwordRow';
			}
			if (in_array ( "required", $eval )) {
				$this->html [$item_key . "_again"] ['required'] = '1';
			}
			if ($this->errors [$item_key . "_again"]) {
				$this->html [$item_key . "_again"] ['error'] = $this->errors [$item_key . "_again"];
			}
		}
		$this->postProcessField ( $item_key );
		return $html;
	}
	
	/**
	 * processing tca type select
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */	
	private function _processSelect($item_key) {
		$TCA = $this->items [$item_key];
		$item = $TCA;
		$html = array ();
		$html ["label"] = $TCA ["label"];
		if ($TCA ['config'] ['maxitems'] > 1) {
			$multiple = 1;
			$html ["element"] = "multiselectRow";
		} else {
			$html ["element"] = "selectRow";
		}
		
		$html ["key"] = $item_key;
		$html ["attributes."] = $this->_processAttributes ( $TCA ['config'] );
		if (isset ( $this->cType [$item_key] )) {
			$html ['reload'] = 1;
		}
		$html ["config."] = $TCA ["config"];
		if (is_array ( $this->divider )) {
			$html ['divider'] = $this->_processDivider ( $item_key );
		}
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
		return $html;
	}
	
	/**
	 * processing tca type check
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */
	private function _processCheck($item_key) {
		$TCA = $this->items [$item_key];
		$pars = $this->controller->parameters->get ( $item_key );
		if (! isset ( $TCA ['config'] ['type'] )) {
			$TCA = $GLOBALS ["TCA"] [$this->table] ["columns"] [$item_key];
		}
		$item =  $this->items [$item_key];
		$item_value = $this->_getValue ( $item_key );
		$html = $this->_processEval ( $item_key );
		$html ["config."] = $TCA ["config"];
		$html ["key"] = $item_key;
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
		return $html;
	}
	
	/**
	 * processing tca type radio
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */
	private  function _processRadio($item_key) {
		$TCA =  $this->items [$item_key];
		if (! isset ( $TCA ['config'] ['type'] )) {
			$TCA = $GLOBALS ["TCA"] [$this->table] ["columns"] [$item_key];
		}
		$item  = $this->items [$item_key];
		$html ["label"] = $TCA ["label"];
		$html ["config."] = $TCA ['config'];
		$items = $TCA ['config'] ['items'];
		for($i = 0; $i < sizeof ( $items ); $i ++) {
			$options [$items [$i] [1]] = $items [$i] [0];
		}
		$html ["attributes."] ['name'] = $this->prefixId . "[" . $item_key . "]";
		$html ["attributes."] ["options."] = $options;
		$html ["options."] = $options;
		if (is_array ( $this->divider )) {
			$html ['divider'] = $this->_processDivider ( $item_key );
		}
		$html ["key"] = $item_key;
		$html ["element"] = "radio";
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
		return $html;
	}
	
	/**
	 * processing tca type group
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */
	private function _processGroup($item_key) {
		$TCA = $this->items [$item_key];
		$item = $TCA;
		$html = $this->_processEval ( $item_key );
		if ($TCA ['config'] ['maxitems'] > 1) {
			$multiple = 1;
			$html ["element"] = "multiselectRow";
		} else {
			$html ["element"] = "selectRow";
		}
		$html ["key"] = $item_key;
		$html ["attributes."] = $this->_processAttributes ( $TCA ['config'] );
		$html ["config."] = $TCA ["config"];
		if (is_array ( $this->divider )) {
			$html ['divider'] = $this->_processDivider ( $item_key );
		}
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
		return $html;
	}
	
	/**
	 * processing tca type hidden
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */
	private function _processHidden($item_key) {
		$TCA = $this->TCA ["columns"] ['hidden'];
		$item = $this->items [$item_key];
		$item_value = $this->_getValue ( $item_key );
		$html = $this->_processEval ( $item_key );
		if (! empty ( $item_value )) {
			$html ["process"] = 1;
			$html ["value"] = "On";
			$html ["preview"] = "hidden";
			$html ['attributes.'] ["value"] = 1;
		} else {
			$html ["process"] = "0";
			$html ['value'] = 0;
			$html ["preview"] = "visible";
			$html ['attributes.'] ["value"] = 1;
		}
		$html ["label"] = $TCA ["label"];
		$html ["key"] = $item_key;
		$html ["name"] = $item_key;
		$html ["element"] = "checkboxRow";
		$html ["config."] = $TCA ['config'];
		$this->html [$item_key] = $html;
	}
	
	/**
	 * processing tca type deleted
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array 	the rendered entry without options and values
	 */
	private function _processDeleted($item_key) {
		$conf = $this->configurations;
		$pars = $this->parameters;
		$html ["hidden"] .= '<input type="hidden" value="' . $uid . '" name="' . $this->prefixId . "[" . $item_key . "]" . '" />' . "\n\t";
		$html ["preview"] .= $val;
		$html ["label"] = 'Delete';
		$html ["field"] = 'hm';
		$html ["name"] = $item_key;
		$html ["config."] = $TCA ['config'];
		$html ["value"] = 1;
		$this->html [$item_key] = $html;
		$this->_processDivider ( $item_key );
		return $html;
	}
	
	/**
	 * processing tca config to html attributes
	 * 
	 * @param 	array	$item_key	the setup entry
	 * @return  array 	an array with the attributes
	 */
	private function _processAttributes($config) {
		$array = array ();
		if (isset ( $config ['size'] )) {
			$array ['size'] = $config ['size'];
		}
		if (isset ( $config ['max'] )) {
			$array ['maxlength'] = $config ['max'];
		}
		if (isset ( $config ['max_size'] )) {
			$array ['maxlength'] = $config ['max_size'];
		}
		if (isset ( $config ['cols'] )) {
			$array ['cols'] = $config ['cols'];
		}
		if (isset ( $config ['rows'] )) {
			$array ['rows'] = $config ['rows'];
		}
		return $array;
	}
	
	/**
	 * processing the the divider and tabs for an entry
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  void
	 */
	private function _processDivider($item_key) {
		if (is_array ( $this->divider )) {
			foreach ( $this->divider as $key => $val ) {
				foreach ( $val as $key2 => $val2 ) {
					if ($val2 [$item_key]) {
						$this->html [$item_key] ['divider'] = $key;
						$this->html [$item_key] ['section'] = $key2;
						$ok = true;
					}
				}
			}
		}
		if (! $ok) {
			$this->html [$item_key] ['divider'] = "General";
			$this->html [$item_key] ['section'] = "emptySection";
		}
	}
		
	// -------------------------------------------------------------------------------------
	// PROCESSING ALL TCA ITEMS/RELATIONS
	// -------------------------------------------------------------------------------------
	
	/**
	 * processing the options and relations
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  void
	 */
	private function _processOptions() {
		$setup = $this->controller->configurations->getArrayCopy ();
		$maxOptions = $setup ['caching.'] ['options'];
		//t3lib_div::debug($this->cached);
		if (is_array ( $this->html ))
			foreach ( $this->html as $item_key => $item ) {
				$options = array ();
				
				
				if (! is_array ( $item ['options.'] ) and ($item ['element'] == "selectRow" or $item ['element'] == "multiselectRow")) {
					//t3lib_div::debug($item['options.'],$item_key);
					$TCA = $this->items [$item_key];
					if (! isset ( $TCA ['config'] ['type'] )) {
						$TCA = $GLOBALS ["TCA"] [$this->panelTable] ["columns"] [$item_key];
					}
					if ($TCA ['config'] ['allowed']) {
						$allowed = $this->_processAllowed ( $item_key );
						$options = $allowed ['data'];
						$sorting = $allowed ['sorting'];
						if ($setup ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset']) {
							$what = explode ( ",", $setup ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset'] );
							foreach ( $what as $key ) {
								if ($options [$key]) {
									unset ( $options [$key] );
								}
							}
						}
						if (is_array ( $this->cache ['HTML'] [$item_key] ))
							$this->cache ['HTML'] [$item_key] ["options."] = $options;
						$this->html [$item_key] ["options."] = $options;
						if (is_array ( $sorting )) {
							$this->html [$item_key] ["sorting."] = $sorting;
							if (is_array ( $this->cache ['HTML'] [$item_key] ))
								$this->cache ['HTML'] [$item_key] ["sorting."] = $sorting;
						}
					} elseif ($TCA ['config'] ['foreign_table']) {
						$options = $this->_processForeignTable ( $item_key );
						$setup = $this->controller->configurations->getArrayCopy ();
						if ($setup ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset']) {
							$what = explode ( ",", $setup [$this->controller->action . "."] ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset'] );
							foreach ( $what as $key ) {
								if ($options [$key]) {
									unset ( $options [$key] );
								}
							}
						}
						
						if (count ( $options ) >= $maxOptions)
							$this->dontCacheOptions [$item_key] = 1;
						if (is_array ( $this->cache ['HTML'] [$item_key] ))
							$this->cache ['HTML'] [$item_key] ["options."] = $options;
						$this->html [$item_key] ["options."] = $options;
					} elseif ($TCA ['config'] ['itemsProcFunc'] || $TCA ['config'] ['userFunc']) {
						$procOptions = $this->_processItemsProcFunc ( $item_key );
						if (is_array ( $procOptions )) {
							foreach ( $procOptions as $key => $val ) {
								$options [$val [1]] = $val [0];
							}
							if ($setup ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset']) {
								$what = explode ( ",", $setup ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset'] );
								foreach ( $what as $key ) {
									if ($options [$key]) {
										unset ( $options [$key] );
									}
								}
							}
							if (count ( $options ) >= $maxOptions)
								$this->dontCacheOptions [$item_key] = 1;
							if (is_array ( $this->cache ['HTML'] [$item_key] ))
								$this->cache ['HTML'] [$item_key] ["options."] = $options;
							$this->html [$item_key] ["options."] = $options;
						}
					} else {
						$items = $TCA ['config'] ['items'];
						$options = array ();
						for($i = 0; $i < sizeof ( $items ); $i ++) {
							$options [$items [$i] [1]] = $items [$i] [0];
						}
						if ($setup ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset']) {
							$what = explode ( ",", $setup ["storage."] ['modifications.'] [strtolower ( $this->panelTable ) . "."] [$item_key . "."] ['unset'] );
							foreach ( $what as $key ) {
								if ($options [$key]) {
									unset ( $options [$key] );
								}
							}
						}
						//if (count ( $options ) >= $maxOptions)
						
					}
					if (is_array ( $this->cache ['HTML'] [$item_key] )) $this->cache ['HTML'] [$item_key] ["options."] = $options;
					$this->html [$item_key] ["options."] = $options;
					//t3lib_div::Debug($options,$item_key);
				}
				//	$this->dontCacheOptions [$item_key] = 1;
						
				
			}
	}
	
	
	/**
	 * processing the options from typeselect or group with foreign_table config
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array	the options array
	 */
	private function _processForeignTable($item_key) {
		$setup = $this->controller->configurations->getArrayCopy ();
		$TCA = $this->_processModifications ( $this->items [$item_key], $item_key );
		if (! isset ( $TCA ['config'] ['type'] )) {
			$TCA = $GLOBALS ["TCA"] [$this->table] ["columns"] [$item_key];
		}
		$table = $TCA ['config'] ['foreign_table'];
		if($table!=$this->panelTable) t3lib_div::loadTCA ( $table );
		$orgTCA = $GLOBALS ["TCA"] [$table];
		if (isset ( $GLOBALS ["TCA"] [$table] ['ctrl'] ['delete'] )) {
			$where = $table . "." . $GLOBALS ["TCA"] [$table] ['ctrl'] ['delete'] . "=0 ";
		}
		if ($GLOBALS ["TCA"] [$table] ['colums'] ['hidden']) {
			$where = ' AND hidden=0 ';
		}
		if (isset ( $TCA ['config'] ['foreign_table_where'] )) {
			if (strpos ( $TCA ['config'] ['foreign_table_where'], "###CURRENT_PID###" )) {
				$where .= str_replace ( "###CURRENT_PID###", $GLOBALS ['TSFE']->id, $TCA ['config'] ['foreign_table_where'] );
			} elseif (strpos ( $TCA ['config'] ['foreign_table_where'], "###STORAGE_PID###" )) {
				$where .= str_replace ( "###STORAGE_PID###", $GLOBALS ['TSFE']->rootLine [0] ['storage_pid'], $TCA ['config'] ['foreign_table_where'] );
			} elseif (strpos ( $TCA ['config'] ['foreign_table_where'], "###SITEROOT###" )) {
				$where .= str_replace ( "###SITEROOT###", 0, $TCA ['config'] ['foreign_table_where'] );
			} else {
				//$where .= $TCA['config']['foreign_table_where'];
			}
		}
		if (strlen ( $TCA ['config'] ['field'] ) > 3) {
			$field = $TCA ['config'] ['field'];
		} else {
			$field = $GLOBALS ["TCA"] [$table] ['ctrl'] ['label'];
		}
		if(is_array($setup['storage.']['relations.'][$item_key."."][$table."."])) $relation = $setup['storage.']['relations.'][$item_key."."][$table."."];
		else $relation=false;
		if($relation['title']) $field=$relation['title'];
		$what = $table . ".uid" . ',' . $table . "." . $field;
		if($relation['fields']) {
			$extraFields=explode(",",$relation['fields']);
			foreach($extraFields as $extraField) $what.= ",".$table.".".$extraField;
		}
		if ($orgTCA ['columns'] ['fe_group']) {
			$where .= " AND (NOT $table.fe_group";
			if ($GLOBALS ['TSFE']->fe_user->user ['usergroup']) {
				$fegroups = explode ( ",", $GLOBALS ['TSFE']->fe_user->user ['usergroup'] );
				foreach ( $fegroups as $groupid ) {
					$where .= " OR $table.fe_group IN ($groupid)";
				}
			}
			$where .= ")";
		}
		$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $what, $table, $where );
		$size = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $res );
		for($i = 0; $i < $size; $i ++) {
			$GLOBALS ['TYPO3_DB']->sql_data_seek ( $res, $i );
			$row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res );
			if(isset($extraFields)) {
					foreach($extraFields as $extraField) {
						if($row[$extraField]) $this->html[$item_key]['additionalData'][$extraField]=$row[$extraField];
					}
				}
			if ($TCA ['config'] ['MM'])
				$data [$table . "__" . $row ['uid']] = $row [$field];
			else
				$data [$row ['uid']] = $row [$field];
		}
		//t3lib_div::debug($data);
		return $data;
	}
	
	/**
	 * processing the options from type group with allowed config
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array	the options array
	 */
	function _processAllowed($item_key, $key = 'uid') {
	
		$setup = $this->controller->configurations->getArrayCopy ();
		$config = $setup ["storage."] [$this->panelTable . "."] [$item_key . "."];
		$TCA = $this->_processModifications ( $this->items [$item_key], $item_key );
		if (! isset ( $TCA ['config'] ['type'] )) {
			$TCA = $GLOBALS ["TCA"] [$this->panelTable] ["columns"] [$item_key];
		}
		$allowed = $TCA ['config'] ['allowed'];
		$tables = explode ( ",", $allowed );
		foreach ( $tables as $table ) {
			if (strlen ( $field = $TCA ['config'] ['field'] ) > 3) {
				$field = $TCA ['config'] ['field'];
			} else {
				$field = 'title';
			}
			if(is_array($setup['storage.']['relations.'][$item_key."."][$table."."])) $relation = $setup['storage.']['relations.'][$item_key."."][$table."."];
			else $relation=false;
			if($relation['title']) $field=$relation['title'];
			$what = 'uid,pid,' . $field;
			if ($key != 'uid' && $key != $field) {
				$what .= ',' . $key;
			}
			if($relation['fields']) {
				$extraFields=explode(",",$relation['fields']);
				foreach($extraFields as $extraField) $what.= ",".$extraField;
			}
			$orgTCA = $GLOBALS ["TCA"] [$table];
			if ($GLOBALS ["TCA"] [$table] ['ctrl'] ['delete']) {
				$where = " ".$GLOBALS ["TCA"] [$table] ['ctrl'] ['delete'] . '=0';
			}
			if ($GLOBALS ["TCA"] [$table] ['colums'] ['hidden']) {
				$where .= ' AND hidden=0';
			}
			if ($config [$table . "."] ['denyPids']) {
				$denyPids = explode ( ",", $config [$table . "."] ['denyPids'] );
			} else {
				$denyPids = array ();
			}
			if ($config [$table . "."] ['denyUids']) {
				$denyUids = explode ( ",", $config [$table . "."] ['denyUids'] );
				foreach ( $denyUids as $denyUid ) {
					$where .= " AND uid !=" . $denyUid;
				}
			}
			if ($GLOBALS ["TCA"] [$table] ['ctrl'] ['sorting']) {
				$sorting = $GLOBALS ["TCA"] [$table] ['ctrl'] ['sorting'];
			} elseif (strtolower ( $table ) == "pages") {
				$sorting = "sorting";
			} elseif ($GLOBALS ["TCA"] [$table] ['ctrl'] ['default_sortby']) {
				$sorting = explode ( " ", $GLOBALS ["TCA"] [$table] ['ctrl'] ['default_sortby'] );
				$sorting = $sorting [2];
			} else {
				$sorting = "tstamp";
			}
			if ($orgTCA ['columns'] ['fe_group']) {
				$where .= " AND NOT $table.fe_group";
				if ($GLOBALS ['TSFE']->fe_user->user ['usergroup']) {
					$fegroups = explode ( ",", $GLOBALS ['TSFE']->fe_user->user ['usergroup'] );
					foreach ( $fegroups as $groupid ) {
						$where .= " OR $table.fe_group IN ($groupid)";
					}
				}
			}
			if ($orgTCA ['columns'] ['subgroup']) {
				if ($GLOBALS ['TSFE']->fe_user->user ['usergroup']) {
					$fegroups = explode ( ",", $GLOBALS ['TSFE']->fe_user->user ['usergroup'] );
					$plus = "AND";
					foreach ( $fegroups as $groupid ) {
						$where .= " $plus $table.uid=$groupid OR subgroup IN($groupid) ";
						$plus = "OR";
					}
				}
			}
			if(strtoupper($this->panelAction)=="BROWSE") {
				$where =  $this->getFilterWhere();
			}
			$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $what, $table, $where, "", $sorting );
			$size = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $res );
			$pageTree = array ();
			for($i = 0; $i < $size; $i ++) {
				$GLOBALS ['TYPO3_DB']->sql_data_seek ( $res, $i );
				$row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res );
				if(isset($extraFields)) {
					foreach($extraFields as $extraField) {
						if($row[$extraField]) $this->html[$item_key]['additionalData'][$extraField]=$row[$extraField];
					}
				}
				//t3lib_div::debug($row);
				if ($TCA ['config'] ['MM'])
					$data [$table . "__" . $row [$key]] = $row [$field];
				elseif (sizeof ( $tables ) > 1)
					$data [$table . "_" . $row [$key]] = $row [$field];
				else
					$data [$row [$key]] = $row [$field];
				$newData [$table] [$row ["pid"]] [$row ["uid"]] = $row;
			}
		}
		foreach ( $tables as $table ) {
			$rootPid = $config [$table . '.'] ['rootPid'];
			if (empty ( $rootPid )) {
				$rootPid = "0";
			}
			$start = $newData [$table] [$rootPid];
			if (! is_array ( $start )) {
				$start = $newData [$table];
			}
			if (is_array ( $start )) {
				foreach ( $start as $pid => $entry ) {
					if (is_array ( $newData [$table] [$pid] ) && ! in_array ( $pid, $denyPids )) {
						//second level";
						foreach ( $newData [$table] [$pid] as $pid2 => $entry2 ) {
							if ($pid2 != $pid && is_array ( $newData [$table] [$pid2] ) && ! in_array ( $pid2, $denyPids )) {
								foreach ( $newData [$table] [$pid2] as $pid3 => $entry3 ) {
									//third level";
									if ($pid != $pid2 && is_array ( $newData [$table] [$pid3] ) && ! in_array ( $pid3, $denyPids )) {
										foreach ( $newData [$table] [$pid3] as $pid4 => $entry4 ) {
											//forth level
											if ($pid3 != $pid2 && is_array ( $newData [$table] [$pid4] ) && ! in_array ( $pid4, $denyPids )) {
												foreach ( $newData [$table] [$pid4] as $pid5 => $entry5 ) {
													if ($pid5 != $pid4 && $pid5 != $pid3) {
														$pageTree [$table] [$pid] [$pid2] [$pid3] [$pid4] [$pid5] = $pid5;
													}
												}
											} elseif ($pid4 != $pid3 && $pid4 != $pid2) {
												$pageTree [$table] [$pid] [$pid2] [$pid3] [$pid4] = $pid4;
											}
										}
									} elseif ($pid3 != $pid2 && $pid3 != $pid) {
										$pageTree [$table] [$pid] [$pid2] [$pid3] = $pid3;
									}
								}
							} elseif ($pid2 != $pid) {
								$pageTree [$table] [$pid] [$pid2] = $pid2;
							}
						}
					} elseif (is_numeric ( $newData [$table] [$pid] ) && ! in_array ( $pid, $denyPids )) {
						$pageTree [$table] [$pid] = $pid;
					}
				}
			}
		}
		//t3lib_div::debug($data);
		return array ("data" => $data, "sorting" => $pageTree );
	}
	
	/**
	 * processing the options from type group with itemsProcFunction config
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array	the options array
	 */
	function _processItemsProcFunc($item_key) {
		$params = $this->_processModifications ( $this->items [$item_key], $item_key );
		if ($params ['config'] ['MM'])
			return $this->_processAllowed ( $item_key );
		if (strlen ( $params ['config'] ['itemsProcFunc'] ) > 1)
			$className = explode ( "->", $params ['config'] ['itemsProcFunc'] );
		else
			$className = explode ( "->", $params ['config'] ['userFunc'] );
		$extList = explode ( ",", $GLOBALS ['TSFE']->TYPO3_CONF_VARS ['EXT'] ['extList'] );
		$extKey = explode ( "tx_", $className [0] );
		$extKey = explode ( "_", $extKey [1] );
		$extKey = $extKey [0];
		foreach ( $extList as $key => $ext ) {
			$name = str_replace ( "_", "", $ext );
			if ($name == $extKey) {
				$extKey = $ext;
				break;
			}
		}
		require_once (t3lib_extMgm::extPath ( $extKey ) . 'class.' . $className [0] . '.php');
		require_once ("t3lib/class.t3lib_befunc.php");
		$class = new $className [0] ( );
		foreach ( $params ['config'] as $key => $val ) {
			$class->$key = $val;
		}
		$items = $class->$className [1] ( &$params, &$this );
		return $params ['items'];
	}
	
	// -------------------------------------------------------------------------------------
	// ERRORS AND TCA EVALUTION
	// -------------------------------------------------------------------------------------
	
	/**
	 * evalu
	 * 
	 * @param 	string	$item_key	the key of the setup
	 * @return  array	the options array
	 */
	function _processEval($item_key) {
		$this->preProcessEval ( $item_key );
		$TCA = $this->_processModifications ( $this->items [$item_key], $item_key );
		$string = explode ( ":", $TCA ['label'] );
		$evalTCA = @explode ( ",", $TCA ["config"] ["eval"] );
		$item = $TCA;
		$html = array ();
		$config = $this->controller->configurations->getArrayCopy ();
		$html ["label"] = $TCA ["label"];
		$html ["help"] = $TCA ["help"];
		$item_value = $this->_getValue ( $item_key );
		if (! empty ( $item_value ) && ! is_array ( $item_value ) && $TCA ['config'] ['internal_type'] == "file") {
			$file_value = explode ( ",", $item_value );
			$item_value = array ();
			foreach ( $file_value as $key => $val ) {
				$item_value [$key] = $TCA ['config'] ['uploadfolder'] . "/" . $file_value [$key];
			}
		}
		if (is_array ( $evalTCA )) {
			foreach ( $evalTCA as $k => $v ) {
				if (! empty ( $v )) {
					$eval [$v] = true;
				}
			}
		}
		$min = 1;
		$max = 1024;
		if ($TCA ['config'] ['min'] > 1) {
			$min = $TCA ['config'] ['min'];
		}
		if ($TCA ['config'] ['max'] > 1) {
			$max = $TCA ['config'] ['max'];
		}
		if ($this->controller->parameters->get ( $item_key ) && $TCA ['config'] ['wizards'] ['link']) {
			$url = explode ( "http://", $item_value );
			if (! isset ( $url [1] )) {
				$item_value = "http://" . $item_value;
			}
			if (! @file_get_contents ( $item_value )) {
				$this->errors [$item_key] = "error_link";
			}
		}
		if ($eval ['date'] && $this->submit) {
			if (! $TCA ['config'] ['splitter']) {
				$TCA ['config'] ['splitter'] = "."; //TODO: set default date splitter/format in TS
			}
			if (! $TCA ['config'] ['format']) {
				$TCA ['config'] ['format'] = "dd.mm.yyyy"; //TODO: Ist das sinnvoll?
			}
			$split = $TCA ['config'] ['splitter'];
			if (strlen ( $TCA ['config'] ['format'] ) >= 2) {
				$format = explode ( $split, $TCA ['config'] ['format'] );
			}
			if ($this->controller->parameters->get ( $item_key )) {
				$time = explode ( $split, $this->controller->parameters->get ( $item_key ) );
				for($i = 0; $i < count ( $format ); $i ++) {
					$date [$format [$i]] = $time [$i];
				}
				if ($item_value && sizeof ( $time ) == 3 && is_numeric ( $date ['dd'] ) && $date ['dd'] >= 1 && $date ['dd'] <= 31 && is_numeric ( $date ['mm'] ) && $date ['mm'] >= 1 && $date ['mm'] <= 12 && is_numeric ( $date ['yyyy'] ) && $date ['yyyy'] >= 1000 && $date ['yyyy'] <= 4000) {
					$postStamp = mktime ( 0, 0, 0, $date ['mm'], $date ['dd'], $date ['yyyy'] );
					if (! empty ( $TCA ['config'] ['range'] ['lower'] ) && $postStamp <= ($TCA ['config'] ['range'] ['lower'])) {
						$this->errors [$item_key] = "error_date_lower";
					}
					if (! empty ( $TCA ['config'] ['range'] ['upper'] ) && $postStamp >= $TCA ['config'] ['range'] ['upper']) {
						$this->errors [$item_key] = "error_date_upper";
					}
				} else {
					$this->errors [$item_key] = "error_date";
				}
			}
			if ($postStamp) {
				$html ['process'] = $postStamp;
			} else
				$html ['process'] = $item_value;
		
		} elseif ($eval ['date'] && ! $this->submit && ! empty ( $item_value ))
			$html ['process'] = $item_value;
		if ($eval ['datetime'] && $this->submit) {
			$pars = $this->controller->parameters->getArrayCopy ();
			$item_value = $pars [$item_key];
			if (! $TCA ['config'] ['splitter']) {
				$TCA ['config'] ['splitter'] = "."; //TODO: set default date splitter/format in TS
			}
			if (! $TCA ['config'] ['format']) {
				$TCA ['config'] ['format'] = "dd.mm.yyyy";
			}
			$split = $TCA ['config'] ['splitter'];
			if (strlen ( $item_value ['time'] ) > 1) {
				$datetime = explode ( ":", $item_value ['time'] );
				if (strlen ( $datetime [0] ) <= 0 || strlen ( $datetime [1] ) <= 0 || $datetime [0] < 0 || $datetime [0] > 23 || $datetime [1] < 0 || $datetime [1] > 60) {
					$this->errors [$item_key] = "error_datetime";
					$datetime [0] = 0;
					$datetime [1] = 0;
				}
			}
			if (strlen ( $TCA ['config'] ['format'] ) >= 2) {
				$format = explode ( $split, $TCA ['config'] ['format'] );
			}
			if (strlen ( $item_value ['date'] ) > 1) {
				$time = explode ( $split, $item_value ['date'] );
				for($i = 0; $i < count ( $format ); $i ++) {
					$date [$format [$i]] = $time [$i];
				}
				if ($item_value ['date'] && sizeof ( $time ) == 3 && is_numeric ( $date ['dd'] ) && $date ['dd'] >= 1 && $date ['dd'] <= 31 && is_numeric ( $date ['mm'] ) && $date ['mm'] >= 1 && $date ['mm'] <= 12 && is_numeric ( $date ['yyyy'] ) && $date ['yyyy'] >= 1000 && $date ['yyyy'] <= 4000) {
					$postStamp = mktime ( $datetime [0], $datetime [1], 0, $date ['mm'], $date ['dd'], $date ['yyyy'] );
					if (! empty ( $TCA ['config'] ['range'] ['lower'] ) && $postStamp <= ($TCA ['config'] ['range'] ['lower'])) {
						$this->errors [$item_key] = "error_date_lower";
					}
					if (! empty ( $TCA ['config'] ['range'] ['upper'] ) && $postStamp >= $TCA ['config'] ['range'] ['upper']) {
						$this->errors [$item_key] = "error_date_upper";
					}
				} else {
					$this->errors [$item_key] = "error_datetime";
				}
			}
			if ($postStamp) {
				$html ['process'] = $postStamp;
			}
		} elseif ($eval ['datetime'] && ! $this->submit && ! empty ( $item_value )) {
			$html ['process'] = $item_value;
		}
		$item_value = $this->_getValue ( $item_key );
		if (! is_array ( $item_value ) && strlen ( $item_value ) >= 3) {
			$item_value = explode ( ",", $item_value );
		}
		if (is_array ( $item_value ) && $TCA ['config'] ['internal_type'] == "file") {
			if (strtoupper ( $this->panelAction ) == "UPDATE")
				foreach ( $item_value as $k => $v ) {
					$v_exploded = explode ( "/", $v );
					if (! $v_exploded [1])
						$item_value [$k] = $TCA ['config'] ['uploadfolder'] . "/" . $v;
				}
			foreach ( $item_value as $key => $file ) {
				$remove = $this->controller->parameters->get ( 'remove' );
				if (is_array ( $remove ) && strlen ( $remove [$item_key] ) >= 1 && $remove [$item_key] == $key) {
					if (@file_get_contents ( $item_value [$key] ) && @unlink ( $item_value [$key] )) { //FIXME: delete erst beim processQuery! bug:wenn ein upload geloescht wird muss db aktualseirt werden,wenn kein submit
						unset ( $html ['value'] );
						unset ( $this->html [$item_key] ['value'] );
						unset ( $html ['process'] );
						unset ( $this->html [$item_key] ['process'] );
						unset ( $html ['preview'] );
						unset ( $this->html [$item_key] ['preview'] );
					} else {
						unset ( $html ['value'] );
						unset ( $this->html [$item_key] ['value'] );
						unset ( $html ['process'] );
						unset ( $this->html [$item_key] ['process'] );
						unset ( $html ['preview'] );
						unset ( $this->html [$item_key] ['preview'] );
						$this->errors [$item_key] = "error_unlink";
					}
				
				} else {
					if (! @file_get_contents ( $item_value [$key] )) {
						unset ( $html ['value'] );
						unset ( $this->html [$item_key] ['value'] );
						unset ( $html ['process'] );
						unset ( $this->html [$item_key] ['process'] );
						unset ( $html ['preview'] );
						unset ( $this->html [$item_key] ['preview'] );
						$error = "error_link";
					} else {
						
						$html ['value'] [$key] = $item_value [$key];
						$valData = explode ( "/", $item_value [$key] );
						$html ['process'] [$key] = $valData [count ( $valData ) - 1];
						$html ['preview'] [$key] = $item_value [$key];
					}
				}
			}
		}
		$pars = $this->controller->parameters->getArrayCopy ();
		if (is_array ( $_FILES [$this->prefixId] ['name'] [$item_key] )) {
			foreach ( $_FILES [$this->prefixId] ['name'] [$item_key] as $key => $file ) {
				if ($this->submit && $TCA ['config'] ['internal_type'] == "file" && is_uploaded_file ( $_FILES [$this->prefixId] ['tmp_name'] [$item_key] [$key] )) {
					$allowed = explode ( ",", $TCA ['config'] ['allowed'] );
					$allowed_types = "(" . implode ( "|", $allowed ) . ")";
					if (strlen ( $allowed [0] ) <= 0 || preg_match ( "/\." . $allowed_types . "$/i", $_FILES [$this->prefixId] ["name"] [$item_key] [$key] )) {
						if ($_FILES [$this->prefixId] ["size"] [$item_key] [$key] <= ($TCA ['config'] ['max_size'] * 1024)) {
							$path = $TCA ['config'] ['uploadfolder'] . "/" . $_FILES [$this->prefixId] ["name"] [$item_key] [$key];
							$fName = $_FILES [$this->prefixId] ["name"] [$item_key] [$key];
							if (is_readable ( $path )) {
								$fileName = explode ( ".", $_FILES [$this->prefixId] ["name"] [$item_key] [$key] );
								$micro = explode ( " ", microtime () );
								$microtime = substr ( $micro [0], 4, 4 );
								$fileName [0] = $fileName [0] . "_" . $microtime;
								$newName = $fileName [0] . "." . $fileName [count ( $fileName ) - 1];
								$path = $TCA ['config'] ['uploadfolder'] . "/" . $newName;
								$fName = $newName;
							}
							if (move_uploaded_file ( $_FILES [$this->prefixId] ["tmp_name"] [$item_key] [$key], $path )) {
								$html ['value'] [$key] = $path;
								$html ['process'] [$key] = $fName;
								$item_value [$key] = $fName;
								$this->mode = "EDIT";
								$this->hasUpload = true;
							} else {
								$this->errors [$item_key] = "error_filecopy";
							}
						} else {
							$this->errors [$item_key] = "error_filesize";
						}
					} else {
						$this->errors [$item_key] = "error_filetype";
					}
				}
			}
		}
		$error = false;
		if ($eval ['required'] || $this->modify [$item_key . '.'] ['required']) {
			$html ['required'] = 1;
			if ($this->submit && strlen ( $item_value ) < 1) {
				$this->errors [$item_key] = "error_required";
			}
		}
		if (! $this->errors [$item_key] && $this->submit && isset ( $TCA ['config'] ['minitems'] ) && sizeof ( $item_value ) < $TCA ['config'] ['minitems']) {
			$this->errors [$item_key] = "error_minitems";
		}
		if (! $this->errors [$item_key] && $this->submit && isset ( $TCA ['config'] ['maxitems'] ) && sizeof ( $item_value ) > $TCA ['config'] ['maxitems']) {
			$this->errors [$item_key] = "error_maxitems";
		}
		if ($TCA ['config'] ['type'] == "group" && $this->submit) {
			if (! $this->errors [$item_key] && isset ( $TCA ['config'] ['minitems'] ) && sizeof ( $item_value ) < $TCA ['config'] ['minitems']) {
				$this->errors [$item_key] = "error_minitems";
			}
			if (! $this->errors [$item_key] && isset ( $TCA ['config'] ['maxitems'] ) && sizeof ( $item_value ) > $TCA ['config'] ['maxitems']) {
				$this->errors [$item_key] = "error_maxitems";
			}
		}
		if (isset ( $TCA ['config'] ['min'] )) {
			$min = $TCA ['config'] ['min'];
			if (strlen ( $item_value ) < $min) {
				$this->errors [$item_key] = "error_min";
			}
		}
		if (isset ( $TCA ['config'] ['max'] )) {
			$min = $TCA ['config'] ['max'];
			if (strlen ( $item_value ) > $max) {
				$this->errors [$item_key] = "error_max";
			}
		}
		
		if ($eval ['email'] && strlen ( $item_value ) >= 1 && ! t3lib_div::validEmail ( $item_value )) {
			$this->errors [$item_key] = "error_email";
		}
		if ($eval ['unique'] && strlen ( $item_value ) >= $min && ! $this->_checkUnique ( $item_key, $TCA )) {
			$this->errors [$item_key] = "error_unique";
		}
		if ($eval ['captcha'] && strlen ( $item_value ) >= 1 && $this->submit) {
			if (t3lib_extMgm::isLoaded ( 'captcha' )) {
				$captchaStr = $_SESSION ['tx_captcha_string'];
				$_SESSION ['tx_captcha_string'] = '';
			} else {
				$this->errors [$item_key] = "error_captcha_ext";
			}
			if ($captchaStr != $item_value)
				$this->errors [$item_key] = "error_captcha";
		}
		if (! empty ( $item_value ) && $eval ['int'] && $this->submit && ! is_numeric ( $item_value )) {
			$this->errors [$item_key] = "error_integer";
		}
		if ($eval ['twice'] && strlen ( $item_value ) >= $min && strlen ( $second_value ) >= $min) {
			if ($item_value != $second_value) {
				$this->errors [$item_key] = "error_twice";
				$this->errors [$item_key . "_again"] = "error_twice";
			}
		}
		if ($eval ['twice'] && $this->mode = "EDIT" && $this->submit) {
			$second_value = $this->_getValue ( $item_key . "_again" );
			if ($eval ['required']) {
				if (strlen ( $second_value ) < 1) {
					$this->errors [$item_key . "_again"] = "error_required";
				}
			}
			if (strlen ( $second_value ) >= 1 && strlen ( $second_value ) < $min || strlen ( $item_value ) > $max) {
				$this->errors [$item_key . "_again"] = "error_leng";
			}
			if ($eval ['email'] && strlen ( $second_value ) >= 1 && ! t3lib_div::validEmail ( $second_value )) {
				$this->errors [$item_key . "_again"] = "error_email";
			}
			if ($eval ['unique'] && strlen ( $item_value ) >= $min && ! $this->_checkUnique ( $item_key, $TCA )) {
				$this->errors [$item_key . "_again"] = "error_unique";
			}
			if ($eval ['int'] && $this->submit && ! is_numeric ( $second_value )) {
				$this->errors [$item_key . "_again"] = "error_integer";
			}
			if ($eval ['twice'] && strlen ( $item_value ) >= $min && strlen ( $second_value ) >= $min) {
				if ($item_value != $second_value) {
					$this->errors [$item_key . "_again"] = "error_twice";
					$this->errors [$item_key] = "error_twice";
				}
			}
		}
		if ($eval ['required'] && $this->submit && ! $item_value) {
			$this->errors [$item_key] = "error_required";
		}
		if ($this->errors [$item_key]) {
			$this->lasterror = $this->errors [$item_key];
			$html ["error"] = $this->errors [$item_key];
		}
		$this->postProcessEval ( $item_key );
		return $html;
	}
	
	function _checkUnique($item_key, $TCA) {
		$item_value = $this->controller->parameters->get ( $item_key );
		$where = "$item_key=\"$item_value\"";
		$query = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $item_key, $TCA ['config'] ['table'], $where );
		if ($query) {
			$result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
		}
		if ($result [$item_key] == $item_value) {
			return false;
		} else {
			return true;
		}
	}
	
	function _checkUniqueInPid($item_key, $TCA, $pid) {
		$item_value = $this->controller->parameters->get ( $item_key );
		$where = "$item_key=\"$item_value\"";
		$query = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $item_key, $TCA ['config'] ['table'], $where );
		if ($query) {
			return false;
		} else {
			return true;
		}
	}
	

	function _checkNode() {
		$where = 'uid=' . $this->panelRecord;
		$table = strtolower ( $this->panelTable );
		$query = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( "uid", $table, $where );
		if (! $query) {
			$this->mode = 'NOT_EXIST';
		}
	}
	
	function _checkBackValues() {
		$GET = t3lib_div::_GET ();
		if (is_array ( $GET )) {
			if (isset ( $GET ['id'] )) {
				unset ( $GET ['id'] );
			}
			unset ( $GET [$this->getDesignator ()] );
			if (is_array ( $GET )) {
				foreach ( $GET as $key => $val ) {
					if (! is_array ( $val )) {
						$this->backValues ['_GET'] [$key] = $val;
					} else {
						foreach ( $val as $k => $v ) {
							$this->backValues ['_GET'] [$key] [$k] = $v;
						}
					}
				}
			}
		}
		$POST = t3lib_div::_POST ();
		unset ( $POST [$this->getDesignator ()] );
		if (is_array ( $POST )) {
			foreach ( $POST as $key => $val ) {
				$this->backValues ['_POST'] [$key] = $val;
			}
		}
	}
	
	// -------------------------------------------------------------------------------------
	// Database MM table helper
	// -------------------------------------------------------------------------------------
	
	/**
	 * returns the mm realtions/data from db field
	 * 
	 * @param 	integer	$uid	the uid of element with the mm realtions
	 * @param 	string	$item_key	the key of the setup
	 * @return  array	mm data array
	 */	
	function getDataMM($uid, $key) {
		$config = $this->html [$key] ['config.'];
		if (isset ( $config ['foreign_table'] ))
			$tables = $config ['foreign_table'];
		else $tables = $config ['allowed'];
		$tables_exploded = explode ( ",", $tables );
		$setup =$this->controller->configurations->getArrayCopy();
		//if(is_array($setup['storage.']['relations.'][$key."."])) $relation = $setup['storage.']['relations.'][$key."."];
		foreach ( $tables_exploded as $table ) {
			if (isset ( $config ['foreign_table'] ))
				$tableNames = "";
			else
				$tableNames = ' AND tablenames="' . $table . '"';
			if(is_array($setup['storage.']['relations.'][$key."."][$table."."])) $relation = $setup['storage.']['relations.'][$key."."][$table."."];
			else $relation=false;
			if (! is_array ( $this->cache ['MM'] [$table] )) {
				t3lib_div::loadTCA ( $table );
				if (! is_array ( $this->cached ['MM'] [$table] ))
					$noCache = true;
				else
					$noCache = false;
				//$this->cache ['MM'] [$table] = $GLOBALS ["TCA"] [$table] ['ctrl'];
			}
			if ($noCache)
				$TCA = $this->cache ['MM'] [$table];
			elseif(!$relation);
				$TCA = $this->cached ['MM'] [$table];
			if (strlen ( $TCA ['label'] ) > 3) {
				$field = $TCA ['label'];
			} else {
				$field = 'title';
			}
			if($relation['title']) $field=$relation['title'];
			$what=$table . "." . $field . "," . $table . ".uid";
			$query = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ($what.",".$config ['MM'].".uid_foreign", $this->panelTable, $config ['MM'], $table, " AND " . $config ['MM'] . ".uid_local=" . $uid . $tableNames ); //$where=false;
			if ($query) {
				$size = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $query );
				for($i = 0; $i < $size; $i ++) {
					$GLOBALS ['TYPO3_DB']->sql_data_seek ( $query, $i );
					$result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $query );
					$mm [$table . "__" . $result ['uid_foreign']] = $result [$field];
				}
			}
			
		}
		if(is_array($setup['storage.']['relations.'][$key."."])) { 
			foreach($mm as $uid=>$val) {
				$record=explode("__",$uid);
				$extra[$record[0]][$record[1]]=$record[1];	
			}
			if(is_array($extra)) foreach($extra as $table=>$uids) {
				if(isset($setup['storage.']['relations.'][$key."."][$table."."]['fields'])) {
					$where="";
					//$fields = $setup['storage.']['relations.'][$key."."][$table."."]['fields'];
					foreach($uids as $uid) {
						$where.=$OR."uid=".$uid;
						$OR=" OR ";
					}
					$fields=$setup['storage.']['relations.'][$key."."][$table."."]['fields'];
					$extraQuery=$GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,pid,".$fields,$table,$where);
					if($extraQuery) {
						$size = $GLOBALS ['TYPO3_DB']->sql_affected_rows ( $extraQuery );
						for($i = 0; $i < $size; $i ++) {
							$GLOBALS ['TYPO3_DB']->sql_data_seek ( $extraQuery, $i );
							$result = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $extraQuery );
							$this->additionalData[$key][$table."__".$result['uid']]=$result;
						}
					}
				}
			}
		}	
		return $mm;
	}
	
	// -------------------------------------------------------------------------------------
	// Some Helper
	// -------------------------------------------------------------------------------------
	
	/**
	 * dummy for itemsProcFunction, because in crud is no instance of a pObj
	 * 
	 * @param 	string	$key	the key of the setup
	 * @return  array	options array
	 */	
	function sL($key) {
		return $key;
	}
		
	/**
	 * the next step after a succeccfull update,delete or create
	 * 
	 * based on $this->backValues you can define a redirect to another url or an other action
	 * 
	 * @return	void
	 */	
	function _nextStep() { 
		$this->preNextStep(); 
		/*
		require_once(PATH_site.'typo3/sysext/cms/tslib/class.tslib_content.php');
		$this->redirect =  tx_div::makeInstance('tx_lib_link');
		
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
		*/
		if (! $this->postNextStep ()) {
		//session_write_close();
		//header('Location: ' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $url."?refresh=1&ajax=1&ajaxTarget=crud-tabs-form");
		//header('Redirect: 1 url= ' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $url);
		//exit();
		}
	}
	
	/**
	 * returns the hash for the model cache
	 * 
	 * @return	string the hash
	 */	
	function getCacheHash() {
		$config = $this->controller->configurations->getArrayCopy ();
		$string = $config ['setup.'] ['marker'] . $config ['storage.'] ['action'] . $GLOBALS ['TSFE']->config ['config'] ['sys_language_uid'] . $GLOBALS ['TSFE']->fe_user->user ['usergroup'];
		return $string;
	}
	
	/**
	 * writes the model cache, normally called by the parser class automatical
	 * 
	 * @return	void
	 */	
	function destruct() {
		$config = $this->controller->configurations->getArrayCopy ();
		$hash = md5 ( $this->getCacheHash () . "-MODEL" );
		if (is_array ( $this->cache ) && $config ['enable.'] ['caching'] == 1) {
			//t3lib_div::debug($this->cache,"neuer chache");
			if (is_array ( $this->dontCacheOptions ))
				foreach ( $this->dontCacheOptions as $item_key => $item ) {
					unset ( $this->cache ['HTML'] [$item_key] ['options.'] );
					unset ( $this->cache ['HTML'] [$item_key] ['attributes.'] ['options.'] );
				}
			//t3lib_div::debug($this->cache);
			if (is_array ( $this->cached )) {
				if (is_array ( $this->cached )) foreach ( $this->cached as $key => $val ) if (is_array ( $val )) foreach($val as $k=>$v)$this->cache [$key][$k] = $v;
				tx_crud__cache::write ( $hash, $this->cache );
			}
		///t3lib_div::debug($this->cache,"schreibe chache komplett");
			tx_crud__cache::write ( $hash, $this->cache );
		}
	}
	
	

}

?>