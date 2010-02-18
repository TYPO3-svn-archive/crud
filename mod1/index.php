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

// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');

$LANG->includeLLFile('EXT:crud/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]



/**
 * Module 'crud Manager' for the 'crud' extension.
 *
 * @author	Frank Thelemann <f.thelemann@yellowmed.com>
 * @package	TYPO3
 * @subpackage	tx_crud
 */
class  tx_crud_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 *
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();

		/*
		 if (t3lib_div::_GP("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
			}
			*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
		"function" => Array (
		"1" => $LANG->getLL("function1"),
		"2" => $LANG->getLL("function2"),
		)
		);
		parent::menuConfig();
	}

	// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	* Main function of the module. Write the content to $this->content
	*/
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

			// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="post">';

			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL) {
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content .= $this->doc->startPage($LANG->getLL("title"));
			$this->content .= $this->doc->header($LANG->getLL("title"));
			$this->content .= $this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content .= $this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content .= $this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{

		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 */
	function moduleContent()	{
		switch((string)$this->MOD_SETTINGS["function"])	{
			case 1:
				$content = $this->makeAclOptions();
				$this->content .= $this->doc->section("Message #1:",$content,0,1);
				break;
			case 2:
				$content = $this->deleteCache();
				$this->content .= $this->doc->section("Message #1:",$content,0,1);
				break;
		}
	}

	/**
	 * Generates the ACL Options
	 */
	function makeAclOptions() {
		if($_POST['table'] && $_POST['fields']) {
			return $this->_generator($_POST['table'],$_POST['fields']);
		}
		else { //TODO: localization
	 	$form = '<form action="" method="post">
				<table>
					<tr>
						<td>Tables:</td>
						<td><input type="text" name="table" /></td>
						<td>Fields:</td>
						<td><input type="text" name="fields" /></td>
						<td><input type="submit" name="submit" value="Generate crud Options" /></td>
					</tr>
				</table>
				</form>';
	 	return $form;
		}
	}

	function deleteCache() {
		if($_POST['cache']) {
			if($GLOBALS['TYPO3_DB']->sql_query("TRUNCATE tx_crud_cached")) {
				$form= "All Caches successfull deleted";
			}
			
		}
		$form .= '<form action="" method="post">
				<table>
					<tr>
						<td><input type="submit" name="cache" value="empty CRUD Caches" /></td>
					</tr>
				</table>
				</form>';
	 	return $form;
	}
	
	/**
	 * Edit the ACL Options
	 */
	function _generator($target,$value) {
		if ($value == "*") {
			$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*",$target,'');
			$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
			unset($result['uid']);
			unset($result['pid']);
			unset($result['tstamp']);
			unset($result['crdate']);
			unset($result['cruser_id']);
			unset($result['editlock']);
			unset($result['l18n_parent']);
			unset($result['sys_language_uid']);
			unset($result['l18n_diffsource']);
			unset($result['t3ver_oid']);
			unset($result['t3ver_id']);
			unset($result['t3ver_wsid']);
			unset($result['t3ver_label']);
			unset($result['t3ver_state']);
			unset($result['t3ver_stage']);
			unset($result['t3ver_count']);
			unset($result['t3ver_tstamp']);
			unset($result['t3_origuid']);
			foreach ($result as $k=>$v) {
				$fieldArray[$k] = $k;
			}
			$value = implode(',',$fieldArray);
 		}
		$fields = explode(",",$value);
		$table = $target;
		$target = "(".$target.")";
		$array['pid'] = $this->id;
		$array['tstamp'] = time();
		$array['crdate'] = time();
		$array['cruser_id'] = 0;
		$array['deleted'] = 0;
		$array['hidden'] = 0;
		$actions = array("CREATE","RETRIEVE","UPDATE");
		foreach($fields as $key=>$val) {
			if ($val == "deleted") {
				$insert = $array;
				$insert['title'] = "DELETED" . $target . " DELETE";
				$insert['action'] = 3;
				$insert['target'] = $table;
				$insert['value'] = "deleted";
				if(!$GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_crud_options",$insert)) {
					$errors[] = $val;
				}
			} elseif ($val == "hidden") {
				$insert = $array;
				$insert['title'] = "HIDDEN".$target." CREATE";
				$insert['action'] = 0;
				$insert['target'] = $table;
				$insert['value'] = "deleted";
				if (!$GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_crud_options",$insert)) {
					$errors[] = $val;
				}
				$insert = $array;
				$insert['title'] =  "HIDDEN".$target." RETRIEVE";
				$insert['action'] = 1;
				$insert['target'] = $table;
				$insert['value'] = "hidden";
				if (!$GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_crud_options",$insert)) {
					$errors[] = $val;
				}
				$insert = $array;
				$insert['title'] =  "HIDDEN".$target." UPDATE";
				$insert['action'] = 2;
				$insert['target'] = $table;
				$insert['value'] = "hidden";
				if(!$GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_crud_options",$insert)) {
					$errors[] = $val;
				}
			} else {
				$field = strtoupper($val);
				foreach ($actions as $x=>$action) {
					$action = $action;
					$insert = $array;
					$insert['title'] = $field.$target." ".$action;
					$insert['action'] = $x;
					$insert['target'] = $table;
					$insert['value'] = $val;
					if (!$GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_crud_options",$insert)) {
						$errors[] = $field."-".$action.$target;
					} else {
						$output="ok";
					}
				}
			}
			if ($errors) {
				$output = "<strong>Konnte diese Optionen nicht ertellen:</strong><br />";
				foreach ($errors as $key=>$val) {
					$output .= $val . "<br />";
				}
			} else {
				$output = "SUCCESS";
			}
		}
		//debug($fields);
		return $output;
	 }
	 
	 /**
	 * Edit the ACL Options
	 */
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/crud/mod1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_crud_module1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>