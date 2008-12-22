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

require_once(t3lib_extMgm::extPath('div') ."../../../typo3/sysext/cms/tslib/class.tslib_pibase.php");
require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');
tx_div::load('tx_lib_controller');
require_once(t3lib_extMgm::extPath('crud') . 'controllers/class.tx_crud__marker_controller.php');
require_once(t3lib_extMgm::extPath('crud') . 'library/class.tx_crud__cache.php');
class tx_crud__parser{
	var $newContent;
	var $extras;

	/**
	 * [Put your description here]
	 */




	/**
	 * [Put your description here]
	 */
	function contentPostProc_output(&$params, &$reference) {
	$start = microtime(true);
		$this->parse($params['pObj']->content);
	
		if (strlen($this->newContent) > 3) {
			$params['pObj']->content = $this->newContent;
		}
		$stop = microtime(true);
		//echo  "<br />CRUD complete time:".round($stop-$start,3);
	}
	function setup() {
//		$start = microtime(true);
		$marker = $this->marker;
		//echo $marker;
		$hash = md5("pluginSetup".$marker);
		$marker = explode("~",$marker);
		$setup = explode(".",$marker[3]);
		$hash = md5("pluginSetup-".$marker[3].$GLOBALS['TSFE']->config['config']['sys_language_uid']);
		$typoscript = tx_crud__cache::get($hash);
		if (!is_array($typoscript)) {
			define(PATH_t3lib,"t3lib/");
			require_once (PATH_t3lib . 'class.t3lib_page.php');
			require_once (PATH_t3lib . 'class.t3lib_tstemplate.php');
			require_once (PATH_t3lib . 'class.t3lib_tsparser_ext.php');
			$sysPageObj = tx_div::makeInstance('t3lib_pageSelect');
			$rootLine = $sysPageObj->getRootLine($GLOBALS['TSFE']->id);
			$TSObj = tx_div::makeInstance('t3lib_tsparser_ext');
			$TSObj->tt_track = 0;
			$TSObj->init();
			$TSObj->runThroughTemplates($rootLine);
			$TSObj->generateConfig();
			$typoscript = $TSObj->setup[strtolower($setup[0])."."][strtolower($setup[1]).'.'];
			tx_crud__cache::write($hash,$typoscript);
//			echo "no cache";
		}
		$setup = $typoscript['configurations.'][strtolower($marker[0])."Action."];
		if(!is_array($setup['setup.'])) die("CRUD Parser has no Typscript found for ".implode("~",$marker)."! Please create Typoscript: ". $marker[3]);
		$pars = t3lib_div::_GP($setup['setup.']['extension']);
		if(strlen($pars["action"]) >= 3 && $pars['action'] != strtolower($marker[0])) {
			$marker[0] = $pars['action'];
			$setup = $typoscript['configurations.'][strtolower($marker[0])."Action."];
		}
		$checkNodeType = explode(".",$marker[2]);
		if ($checkNodeType[0] == "PARS") {
			$marker[2] = $pars[strtolower($checkNodeType[1])];
		}
		if ($pars['retrieve']) {
			$setup['storage.']['nodes'] = $pars['retrieve'];
		} else {
			$setup['storage.']['nodes']=$marker[2];
		}
		$setup['storage.']['nameSpace'] = strtolower($marker[1]);
		if(strlen($marker[4])>1) $setup['storage.']['fields']=$marker[4];
		if (strlen($marker[5]) > 1) {
			$defaults = explode(",",$marker[5]);
			if (is_array($defaults)) {
				foreach ($defaults as $default) {
					$singleDefault_exploded = explode("=",$default);
					$setup['storage.']['defaultQuery.'][strtolower($marker[1])."."][$singleDefault_exploded[0]] = $singleDefault_exploded[1];
				}
			}
		}
		$setup_ok = $setup;
		if (is_array($setup['storage.']['virtual.'])) {
			foreach ($setup['storage.']['virtual.'] as $key=>$val) {
				if ($key != strtolower($marker[1]) . ".") {
					unset($setup_ok['storage.']['virtual.'][$key]);
				}
			}
		}
		if (is_array($setup['storage.']['modifications.'])) {
			foreach($setup['storage.']['modifications.'] as $key=>$val) {
				if ($key != strtolower($marker[1]) . ".") {
					unset($setup_ok['storage.']['modifications.'][$key]);
				}
			}
		}
//		$stop = microtime(true);
		//echo "<br />CRUD typoscript time:".round($stop-$start,3);
		return $setup_ok;
	}

	function parse($content) {
		$html = explode('{{{',$content);
		$replace = array();
		foreach($html as $key=>$val) {
			
			$needle = '}}}';
			$marker =  substr("$val",0,strpos($val,$needle)+strlen($needle));
			$marker = str_replace('}}}','',$marker);
			$test = explode("~",$marker);
			$str = '{{{' . $marker . '}}}';
			if (count($test) >= 3) {
				$this->marker = $marker;
				$setup = $this->setup();
				$setup['setup.']['marker'] = $str;
				$mode = $this->setSubmit($setup);
				if ($test[0] == "CREATE" || $test[0] == "UPDATE" || $test[0] == "DELETE") {
					$icon = true;
				}
				$pars = t3lib_div::_GP($setup['setup.']['extension']);
				session_start();
				//echo $test[0];
				if($pars['track'] && strtoupper($pars['action'])!="RETRIEVE") {
					$hash=md5($_REQUEST['PHPSESSID']."-".$setup['setup.']['marker']."-".$GLOBALS['TSFE']->id);
					$params=$_SESSION[$hash];
					//t3lib_div::debug($params,"session pars");
					if(is_array($params)) foreach($params as $name=>$values) $pars[$name]=$values;
					//else $pars=$params;
				}
				if ($mode == "ICON" && $icon) {
					$replace[$str] = $this->printActionLink($setup);
				} elseif ($mode == "HIDE") {
					$replace[$str] = "";
				} else {
					if (is_array($setup)) {
						
						if (!is_object($controller)) {
							require_once($setup['controller.']['classPath']);
							$controller = new $setup['controller.']['className'];
						}
						$controller->configurations = new tx_lib_object($setup);
						$controller->parameters = new tx_lib_object($pars);
						$controller->defaultDesignator = strtolower($setup['setup.']['extension']);
						if (strlen($pars["action"]) >= 3 && $pars['action']!=strtolower($marker[0])) {
							$action = strtolower($pars['action']) . "Action";
						} else {
							$action = strtolower($test[0]) . "Action";
						}
					
						$controller->action = $action;
						$start = microtime(true);
						$replace[$str] = $controller->$action();
						$stop = microtime(true);
						//echo "<br />CRUD action time:".round($stop-$start,3);
						if (is_array($controller->headerData)) {
							$headerData[] = $controller->headerData;
						}
						if (is_array($controller->footerData)) {
							$footerData[] = $controller->footerData;
						}
						if ($marker == 'DELETE') {
							$extras[$marker]=$action[1];
						}
						
					}
				}
			}
		}
		
		if (is_array($replace)) {
			foreach($replace as $key=>$val) $content = str_replace($key,$val,$content);
		}
		if (is_array($headerData)) {
			$content = $this->makeHeader($headerData,$content);
		}
		if (is_array($footerData)) {
			$content = $this->makeFooter($footerData,$content);
		}
		if (is_array($replace) || is_array($headerData) || is_array($footerData)) {
			$this->newContent = $content;
		}
	}

	function makeHeader($headerData,$content) {
		if (is_array($headerData[0]['document.ready'])) {
			$documentReady = $headerData[0]['document.ready'];
			$js = "\n" . '<script type="text/javascript">' . "\n\t" . '$(document).ready(function(){' . "\n\t";
			foreach($documentReady as $key=>$val) {
				$js .= "\n\t" . $val;
			}
			$js .= "\n\t" . '});' . "\n\t" . "</script>\n";
			unset($headerData[0]['document.ready']);
		}
		//$crudJS['domscript']=$headerData['domscript'];
		//t3lib_div::debug($headerData);
		//unset($headerData['domscript']);
		if (is_array($headerData)) {
			foreach ($headerData as $k=>$v) {
				$v = array_reverse($v);
				foreach ($v as $what=>$include) {
			//		$include = array_reverse($include);
					if (is_array($include)) {
						$replace = TRUE;
						foreach ($include as $key=>$value) {
							$additionalHeader .= "" . $value . "\n\t";
						}
					}
				}
			}
		}
		
		if ($js) {
			$additionalHeader .= $js;
		}
		if ($replace) {
			$content = str_replace('</head>',$additionalHeader."</head>",$content);
		}
		return $content;
	}
	
	function makeFooter($footerData,$content) {
		if (is_array($footerData[0]['document.ready'])) {
			$documentReady = $footerData[0]['document.ready'];
			$js = "\n" . '<script type="text/javascript">' . "\n\t" . '$(document).ready(function(){' . "\n\t";
			foreach($documentReady as $key=>$val) {
				$js .= "\n\t" . $val;
			}
			$js .= "\n\t" . '});' . "\n\t" . "</script>\n";
			unset($footerData[0]['document.ready']);
		}
		if (is_array($footerData)) {
			foreach ($footerData as $k=>$v) {
				foreach ($v as $what=>$include) {
					if (is_array($include)) {
						$replace = TRUE;
						foreach ($include as $key=>$value) {
							$additionalFooter .= "" . $value . "\n\t";
						}
					}
				}
			}
		}
		if ($js) {
			$additionalFooter .= $js;
		}
		if ($replace) {
			$content = str_replace('</body>',$additionalFooter."</body>",$content);
		}
		return $content;
	}
	
	function setSubmit($setup) {
		$pars = t3lib_div::_GP($setup['setup.']['extension']);
		if ($pars["form"] == $this->getActionID($setup)) {
			if ($pars["process"] == "preview") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($pars["process"] == "create") {
				$this->submit = false;
				$this->mode = "EDIT";
			} elseif ($pars["process"] == "update") {
				$this->submit = true;
				$this->mode = "EDIT";
			} elseif ($pars["process"]=="delete") {
				$this->submit = true;
				$this->mode = "PROCESS";
			} elseif ($pars["process"]=="cancel") {
				$this->submit = false;
				$this->mode = "ICON";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		} else {
			if ($pars["form"] && !$pars["cancel"]) {
				$this->submit = false;
				$this->mode = "HIDE";
			} else {
				$this->submit = false;
				$this->mode = "ICON";
			}
		}
		return $this->mode;
	}
	
	
	function getActionID($setup) {
		//echo $setup['storage.']['nodes'];
		return md5($setup['setup.']['secret'] . $setup['storage.']['nameSpace'] . $setup['storage.']['nodes'] . $setup['storage.']['fields']);
	}
    
	function printActionLink($setup) {
		$form = '<div class="crud-icon">' . "\n\t" . '<form action="" method="post"><div>';
		$action = $setup['storage.']['action'];
		$image = 'typo3conf/ext/crud/resources/images/icons/' . $action . '.gif';
		$form .= '<input type="hidden" name="ajaxTarget" value="crud-icon" />' . "\n\t";
		$form .= '<input type="hidden" name="' . $setup['setup.']['extension'] . '[form]" value="' . $this->getActionID($setup) . '" />' . "\n\t";
		$form .= '<input type="hidden" name="' . $setup['setup.']['extension'] . '[icon]" value="1" />' . "\n\t";
		$form .= '<input type="hidden" name="' . $setup['setup.']['extension'] . '[process]" value="' . strtolower($action) . '" />' . "\n\t";
		$form .= '<input type="image"  name="' . $setup['setup.']['extension'] . '[submit]" value="Submit" src="' . $image . '" />' . "</div>\n\t</form>\n</div>\n"; //TODO: Localization
		return $form;
	}
	
	function getAjaxTarget($config,$function) {
		if ($config['view.']['ajaxTargets.'][$function]) {
			return $config['view.']['ajaxTargets.'][$function];
		} else {
			return $config['view.']['ajaxTargets.']["default"];
		}
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/crud/library/class.crud_parser.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/crud/library/class.crud_parser.php"]);
}
?>