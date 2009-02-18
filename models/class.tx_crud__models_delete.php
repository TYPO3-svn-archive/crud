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
class tx_crud__models_delete extends tx_crud__models_common{

	// -------------------------------------------------------------------------------------
	// database create queries
	// -------------------------------------------------------------------------------------
	
	/**
	 * overwrite of the query call in common
	 * 
	 * @return  void
	 */	
	function processQuery() {
		$this->deleteQuery();
	} 
	
	/** 
	 * makes the delete query
	 * 
	 * @return  void
	 */	
	protected function deleteQuery() {
		$this->preQuery(); 
		$where = 'uid=' . $this->panelRecord;
		$table = strtolower($this->panelTable);
		if ($this->mode == 'PROCESS') {
			$query = $GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
		}
		if (!$query) {
			$this->mode = 'QUERY_ERROR';
		}
		$config = $this->controller->configurations->getArrayCopy();
		if ($config['enable.']['logging']==1) {
			tx_crud__log::write($config['storage.']['action'], $this->panelRecord, $config['storage.']['nameSpace'],$config['logging.']);
		}
		$this->postQuery(); 
		$config['view.']['mode'] = $this->mode;
		$config['view.']['errors'] = $this->errors;
		$config['view.']['setup'] = $this->html;
		$config['view.']['data'] = $this->data;
		$this->controller->configurations = new tx_lib_object($config);
		$this->_nextStep();
	}
		
	/** 
	 * get an value for an delete form
	 * 
	 * @params	$item_key	the key of the form
	 * @return  string	the get/post value from the form field
	 */	
	function _getValue($item_key)  {
		if (isset($this->parameters[$item_key])) {
			return $this->parameters[$item_key];
		}
	}
	
	// -------------------------------------------------------------------------------------
	// overwrite non used function at deleting
	// -------------------------------------------------------------------------------------
	
	/** 
	 * dummy to overwrite the getValue, because at a delete are not values to get
	 * 
	 * @params	$item_key	the key of the form
	 * @return  string	the get/post value from the form field
	 */
	function getData() {}
	 
	/** 
	 * dummy to overwrite the setupValues, because at a delete are not values to set
	 * 
	 * @params	$item_key	the key of the form
	 * @return  string	the get/post value from the form field
	 */
	function setupValues() {}
	
}

?>