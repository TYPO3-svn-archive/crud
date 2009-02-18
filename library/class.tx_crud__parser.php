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
 * typo3 hook for all cached page to parse all markers on the page and replace with the controller action contents
 * sets the typoscript configurations,params,models and views for the controller
 *
 * @author Frank Thelemann <f.thelemann@yellowmed.com>
 * @package TYPO3
 * @subpackage tx_crud
 */



final class tx_crud__parser{
	var $newContent;
	var $extras;
	
	// -------------------------------------------------------------------------------------
	// typo3 hook function for contentPostProc_output
	// -------------------------------------------------------------------------------------
	
	/**
	 * hook for replacing all markers on a page with controller content and class factory for models and views
	 * 
	 * @param 	obejct	$params	
	 * @param 	string	$item_key	the key of the setup
	 * @return  array	mm data array
	 */	
	function contentPostProc_output(&$params, &$reference) {
		include_once(t3lib_extMgm::extPath('crud') . 'library/class.tx_crud__div.php');
		if(isset($_REQUEST['ajax']) && isset($_REQUEST['aID'])) {
			$html=explode("{{{",$params['pObj']->content);
			foreach($html as $key=>$val) {
				$needle = '}}}';
				$marker =  substr("$val",0,strpos($val,$needle)+strlen($needle));
				$marker = str_replace('}}}','',$marker);
				$test = explode("~",$marker);
				if (count($test) >= 3) {
					$str = '{{{' . $marker . '}}}';
					if(tx_crud__div::getActionID("",$str)==$_REQUEST['aID']) {
						$this->parse($str);
						echo $this->markerArray[$str];
						die();
					}
				}
			}
		}
		else{
			$this->parse($params['pObj']->content);
			$originalMarker = $this->markerArray;
			$this->markerArray=array();
			if(strlen($this->newContent) > 3) {
				$params['pObj']->content = $this->newContent;
				if (strlen($this->newContent) > 3 && (strtolower($this->actionID)=="retrieve") ) {
				$this->newContent=false;
					$this->parse($params['pObj']->content);
					$params['pObj']->content = $this->newContent;
				}
			}
		}
	}
	
	function setup() {
		$marker = $this->marker;
		$hash = md5("pluginSetup".$marker);
		$marker = explode("~",$marker);
		$setup = explode(".",$marker[3]);
		$hash = md5("pluginSetup-".$marker[3]);	
		$cached = tx_crud__cache::get($hash);
		$this->pageConfig=$cached['config'];
		if(is_array($cached['typoscript'])) {
			$typoscript=$cached['typoscript'];
		}
		$cache['config']=$cached['config'];
		if (!is_array($typoscript)) {
			define(PATH_t3lib,"t3lib/");
			require_once(PATH_t3lib . 'class.t3lib_page.php');
			require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
			require_once (PATH_t3lib . 'class.t3lib_tsparser_ext.php');
			$sysPageObj = tx_div::makeInstance('t3lib_pageSelect');
			$rootLine = $sysPageObj->getRootLine($GLOBALS['TSFE']->id);
			$TSObj = tx_div::makeInstance('t3lib_tsparser_ext');
			$TSObj->tt_track = 0;
			$TSObj->init();
			$TSObj->runThroughTemplates($rootLine);
			$TSObj->generateConfig();
			$cache['typoscript'] = $TSObj->setup[strtolower($setup[0])."."][strtolower($setup[1]).'.'];
			$cache['typoscript']['setup.']['baseURL']=$cache['config']['baseURL'];
			$cache['config'] = $TSObj->setup['config.'];
			tx_crud__cache::write($hash,$cache);
			$typoscript=$cache['typoscript'];
			$this->pageConfig=$cache['config'];
		}
		$setup = $typoscript['configurations.'][strtolower($marker[0])."Action."];
		if(!is_array($setup['setup.'])) $this->newContent="CRUD Parser has no Typscript found for ".implode("~",$marker)."! Please create Typoscript: ". $marker[3];
		$pars=tx_crud__div::_GP($setup['setup.']['extension']);
		if(strlen($pars["action"]) >= 3 && $pars['action'] != strtolower($marker[0])) {
			$marker[0] = $pars['action'];
			$setup = $typoscript['configurations.'][strtolower($marker[0])."Action."];
		}
		$this->actionID=$setup['storage.']['action'];
		$_checkNodeType = explode(".",$marker[2]);
		if ($_checkNodeType[0] == "PARS") {
			$marker[2] = $pars[strtolower($_checkNodeType[1])];
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
					$sisngleDefault_exploded = explode("=",$default);
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
		$setup_ok['setup.']['baseURL']=$cache['config']['baseURL'];
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
				$pars=tx_crud__div::_GP($setup['setup.']['extension']);
				session_start();
				if($pars['track'] && strtoupper($pars['action'])!="RETRIEVE") {
	                $hash=md5($_REQUEST['PHPSESSID']."-".$setup['setup.']['marker']."-".$GLOBALS['TSFE']->id);
	            	$params=$_SESSION[$hash];
	                if(is_array($params)) foreach($params as $name=>$values) $pars[$name]=$values;
	            }
				if(strlen($pars['search'])>2 && !is_array($pars['search'])) {
					$search=explode(" (",$pars['search']);
					$pars['search']=urldecode($search[0]);
				}
				if ($mode == "ICON" && $icon  && $setup['icons.']['useParserIcons']=='1') {
					$replace[$str] = tx_crud__div::printActionLink($setup);;
				} elseif ($mode == "HIDE" && $setup['hideIfNotActive']!="1") {
					$replace[$str] = "";
				} else {
					if (is_array($setup)) {
						if (!is_object($this->obj[$setup['controller.']['className']])) {
							require_once($setup['controller.']['classPath']);
							$this->obj[$setup['controller.']['className']]=new $setup['controller.']['className'];
						}
						if (!is_object($this->obj[$setup['storage.']['className']])) {
							require_once($setup['storage.']['classPath']);
							$this->obj[$setup['storage.']['className']]= new  $setup['storage.']['className'];
						}
						if (!is_object($this->obj[$setup['view.']['className']])) {
							require_once($setup['view.']['classPath']);
							$this->obj[$setup['view.']['className']] = new $setup['view.']['className'];
						}
						$this->obj[$setup['controller.']['className']]->configurations = new tx_lib_object($setup);
						$this->obj[$setup['controller.']['className']]->parameters = new tx_lib_object($pars);
						$this->obj[$setup['controller.']['className']]->defaultDesignator = strtolower($setup['setup.']['extension']);
						if(isset($_REQUEST['q'])) $pars['action']="autocomplete";
						if (strlen($pars["action"]) >= 3 && $pars['action']!=strtolower($marker[0])) {
							$action = strtolower($pars['action']) . "Action";
						} else {
							$action = strtolower($test[0]) . "Action";
						}
						$this->obj[$setup['controller.']['className']]->action = $action;
						$this->obj[$setup['storage.']['className']]->setup($this->obj[$setup['controller.']['className']]);
						$this->obj[$setup['view.']['className']]->setup($this->obj[$setup['controller.']['className']]);
						$this->obj[$setup['controller.']['className']]->model=$this->obj[$setup['storage.']['className']];
						$this->obj[$setup['controller.']['className']]->view=$this->obj[$setup['view.']['className']];
						$replace[$str] = $this->obj[$setup['controller.']['className']]->$action();
						$this->obj[$setup['storage.']['className']]->destruct($this->obj[$setup['controller.']['className']]);
						$this->obj[$setup['view.']['className']]->destruct($this->obj[$setup['controller.']['className']]);
						if (is_array($this->obj[$setup['controller.']['className']]->headerData)) {
							$headerData[] = $this->obj[$setup['controller.']['className']]->headerData;
						}
						if (is_array($this->obj[$setup['controller.']['className']]->footerData)) {
							$footerData[] = $this->obj[$setup['controller.']['className']]->footerData;
						}
						if ($marker == 'DELETE') {
							$extras[$marker]=$action[1];
						}
					}
				}
			}
		}
		if (is_array($replace) && !isset($_REQUEST['ajax'])) {
			foreach($replace as $key=>$val) {
				$content = str_replace($key,$val,$content);
			}
		}
		$this->markerArray=$replace;
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
		$headerData=$headerData;
		if (is_array($headerData)) {
			foreach ($headerData as $k=>$v) {
				$v = array_reverse($v);
				foreach ($v as $what=>$include) {
					//t3lib_div::debug($include);
					if (is_array($include)) {
						$replace = TRUE;
						foreach ($include as $key=>$value) {
							//echo $value;
							$additionalHeader .= "" . $value . "\n\t";
						}
					}
				}
			}
		}
		if ($js) {
			$additionalHeader .= $js;
		}
		$replace=true;
		if(strlen($this->pageConfig['baseURL'])<3) {
			$url="http://".$_SERVER['SERVER_ADDR'];
			$script=explode("index.php",$_SERVER['SCRIPT_NAME']);
			$this->pageConfig['baseURL']=$url.$script[0];
		}
		$additionalHeader.='
<script type="text/javascript">
function getBaseurl(){
	return "'.$this->pageConfig['baseURL'].'";
}
</script>';
		if ($replace) {
			$content = str_replace('</head>',$additionalHeader."</head>",$content);
		}
		return $content;
	}
	
	function makeFooter($headerData,$content) {
		$headerData=$headerData;
		if (is_array($headerData)) {
			foreach ($headerData as $k=>$v) {
				$v = array_reverse($v);
				foreach ($v as $what=>$include) {
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
			$content = str_replace('</body>',$additionalHeader."</body>",$content);
		}
		return $content;
	}
	
	function setSubmit($setup) {
		$pars=tx_crud__div::_GP($setup['setup.']['extension']);
		if ($pars["form"] == tx_crud__div::getActionID($setup)) {
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
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/crud/library/class.crud_parser.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/crud/library/class.crud_parser.php"]);
}
?>