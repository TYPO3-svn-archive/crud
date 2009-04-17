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
	 * @param 	object	$params	the pObj 
	 * @param 	string	$reference	the reference
	 * @return  string	the parsed html pages
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
				if (count($test) >= 2) {
					$str = '{{{' . $marker . '}}}';
					//echo $marker;
					if(tx_crud__div::getActionID("",$str)==$_REQUEST['aID'] || tx_crud__div::getActionID("",$str)==$_REQUEST['xID']) {
						if(isset($_REQUEST['mID'])) {
							$str=$_REQUEST['mID'];
							$this->parse($str);
							echo $this->markerArray[$str];
							die();
						}
						$this->parse($str);
						$content=$this->markerArray[$str];
						$this->parse($content);
						if(is_array($this->markerArray)) foreach($this->markerArray as $marker=>$html) {
							$content=str_replace($marker,$html,$content);
						}
						echo $content;
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
				if (strlen($this->newContent) > 3 && (strtolower($this->actionID)=="retrieve"  || strtolower($this->actionID)=="browse")) {
				$this->newContent=false;
					$this->parse($params['pObj']->content);
					$params['pObj']->content = $this->newContent;
				}
			}
			if(is_array($this->headerData)) $params['pObj']->content=$this->makeHeader($this->headerData,$params['pObj']->content);
			if(is_array($this->footerData)) $params['pObj']->content=$this->makeFooter($this->footerData,$params['pObj']->content);
		}
	}
	
	/**
	 * get and cached the typoscript configuation for an action
	 * 
	 * @return  array	typoscript
	 */	
	function setup() {
		$marker = $this->marker;
		$hash = md5("pluginSetup".$marker);
		$marker = explode("~",$marker);
		//$setup = explode(".",$marker[3]);
		if(count($marker)==2) {
				$setup=explode(".",$marker[1]);
		}
		else $setup = explode(".",$marker[3]);
		//$hash = md5("pluginSetup-".$marker[3]);	
		//$cached = tx_crud__cache::get($hash);
		$this->pageConfig=$cached['config'];
		if(is_array($cached['typoscript'])) {
			$typoscript=$cached['typoscript'];
		}
		//t3lib_div::Debug($setup);
		$cache['config']=$cached['config'];
		if (!is_array($typoscript)) {
			define(PATH_t3lib,"t3lib/");
			//echo "generiere typscript";
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
			//t3lib_div::debug($marker);
			if(strtolower($marker[0])=="plugin") {
				$ts=explode(".",$marker[1]);
				$cache['typoscript'] = $TSObj->setup[strtolower($ts[0])."."][strtolower($ts[1]).'.'];
			}
			else $cache['typoscript'] = $TSObj->setup[strtolower($setup[0])."."][strtolower($setup[1]).'.'];
			
			$cache['typoscript']['configurations.']['setup.']['baseURL']=$cache['config']['baseURL'];
			$cache['config'] = $TSObj->setup['config.'];
			tx_crud__cache::write($hash,$cache);
			$typoscript=$cache['typoscript'];
			$this->pageConfig=$cache['config'];
		}
		//t3lib_div::debug($typoscript);
		
		if(isset($_REQUEST['q'])) $setup = $typoscript['configurations.']["autocompleteAction."];
		else $setup = $typoscript['configurations.'][$marker[0]."Action."];
		//$pars=tx_crud__div::_GP($setup['setup.']['extension']);
		if(strtolower($marker[0])=="plugin") return $setup;
		///t3lib_div::debug($typoscript);
		if(!is_array($setup['setup.'])) $this->newContent="CRUD Parser has no Typscript found for ".implode("~",$marker)."! Please create Typoscript: ". $marker[3];
		$pars=tx_crud__div::_GP($setup['setup.']['extension']);
		///t3lib_div::debug($pars);
		if(strlen($pars["action"]) >= 3 && $pars['action'] != $marker[0]  && $setup['setup.']['freezeAction']!='1') {
			$marker[0] = $pars['action'];
			$setup = $typoscript['configurations.'][$marker[0]."Action."];
			//t3lib_div::debug($setup);
		}
		//t3lib_div::debug($setup);
		$this->actionID=$setup['storage.']['action'];
		$_checkNodeType = explode(".",$marker[2]);
		if ($_checkNodeType[0] == "PARS") {
			$marker[2] = $pars[strtolower($_checkNodeType[1])];
		}
		if (isset($pars['retrieve'])) {
			$setup['storage.']['nodes'] = $pars['retrieve'];
			//t3lib_div::debug($setup);
		} 
		elseif(isset($marker[2])) $setup['storage.']['nodes']=$marker[2];
		
		if(strlen($setup['storage.']['nameSpace'])<=3) $setup['storage.']['nameSpace']= strtolower($marker[1]);
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
		$setup_ok['setup.']['baseURL']=$cache['config']['baseURL'];
		///t3lib_div::Debug($setup_ok);
		//die();
		return $setup_ok;
	}

	/**
	 * search and replace content marker with the contoller action
	 * 
	 * @param	$content	the content with the markers
	 * @return  void
	 */
	function parse($content) {
		$html = explode('{{{',$content);
		$replace = array();
		foreach($html as $key=>$val) {
			$needle = '}}}';
			$marker =  substr("$val",0,strpos($val,$needle)+strlen($needle));
			$marker = str_replace('}}}','',$marker);
			$test = explode("~",$marker);
			$str = '{{{' . $marker . '}}}';
			if (count($test) >= 2 || strtolower($test[0])=="plugin"){
				$this->marker = $marker;
				$setup = $this->setup();
				$setup['setup.']['marker'] = $str;
				$mode = $this->setSubmit($setup);
				if ($test[0] == "create" || $test[0] == "update" || $test[0] == "delete") {
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
						if (isset($setup['controller.']['className']) && !is_object($this->obj[$setup['controller.']['className']])) {
							require_once($setup['controller.']['classPath']);
							$this->obj[$setup['controller.']['className']]=new $setup['controller.']['className'];
						}
						if (isset($setup['storage.']['className']) && !is_object($this->obj[$setup['storage.']['className']])) {
							require_once($setup['storage.']['classPath']);
							$this->obj[$setup['storage.']['className']]= new  $setup['storage.']['className'];
						}
						if (isset($setup['view.']['className']) && !is_object($this->obj[$setup['view.']['className']])) {
							require_once($setup['view.']['classPath']);
							$this->obj[$setup['view.']['className']] = new $setup['view.']['className'];
						}
						if (isset($setup['controller.']['className'])) {
							//if(strtolower($test[0]) != "plugin") 
							$this->obj[$setup['controller.']['className']]->configurations = new tx_lib_object($setup);
							//else $this->obj[$setup['controller.']['className']]->configurations = new tx_lib_object($setup['view.']);
						//	t3lib_div::debug($pars,'ss');
							$this->obj[$setup['controller.']['className']]->parameters = new tx_lib_object($pars);
							$this->obj[$setup['controller.']['className']]->defaultDesignator = strtolower($setup['setup.']['extension']);
							if(isset($_REQUEST['q'])) $pars['action']="autocomplete";
							if (strlen($pars["action"]) >= 3 && $pars['action']!=strtolower($marker[0]) && !isset($setup['setup.']['freezeAction'])) {
								$action = $pars['action'] . "Action";
							} 
							else {
							$action = $test[0] . "Action";
							}
							$this->obj[$setup['controller.']['className']]->action = $action;
						}
						//t3lib_div::debug($_REQUEST);
						if(strtolower($test[0]) != "plugin" && !isset($_REQUEST['q'])) 
						{
							$this->obj [$setup ['storage.'] ['className']]->setup ( $this->obj [$setup ['controller.'] ['className']] );
							$this->obj [$setup ['view.'] ['className']]->setup ( $this->obj [$setup ['controller.'] ['className']] );
							$this->obj [$setup ['controller.'] ['className']]->model = $this->obj [$setup ['storage.'] ['className']];
							$this->obj [$setup ['controller.'] ['className']]->view = $this->obj [$setup ['view.'] ['className']];
							$replace [$str] = $this->obj [$setup ['controller.'] ['className']]->$action ();
							if(!isset($_REQUEST['q'])) {
							$this->obj [$setup ['storage.'] ['className']]->destruct ( $this->obj [$setup ['controller.'] ['className']] );
							$this->obj [$setup ['view.'] ['className']]->destruct ( $this->obj [$setup ['controller.'] ['className']] );
							if (is_array ( $this->obj [$setup ['controller.'] ['className']]->headerData )) {
								$headerData [] = $this->obj [$setup ['controller.'] ['className']]->headerData;
							}
							if (is_array ( $this->obj [$setup ['controller.'] ['className']]->footerData )) {
								$footerData [] = $this->obj [$setup ['controller.'] ['className']]->footerData;
							}
							if ($marker == 'DELETE') {
								$extras [$marker] = $action [1];
							}
							}
						}
						else {
							if(isset($_REQUEST['q'])) $action="autocomplete";
							
							if (isset($setup['controller.']['className']))  {
								if(isset($_REQUEST['q'])) {
									$action="autocompleteAction";
									$this->obj [$setup ['storage.'] ['className']]->setup ( $this->obj [$setup ['controller.'] ['className']] );
									$this->obj [$setup ['controller.'] ['className']]->model = $this->obj [$setup ['storage.'] ['className']];
								}
								else $action = strtolower($test[0]) . "Action";
								$replace [$str] = $this->obj [$setup ['controller.'] ['className']]->$action ();
							}
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
			foreach($headerData[0] as $key=>$array) {
				foreach($array as $name=>$val) {
					$this->headerData[$key][$name]=$val;
				}
			}
		}
		if (is_array($footerData)) {
			foreach($footerData as $key=>$array) {
				foreach($array as $name=>$val) {
					foreach($val as $k=>$string) $this->footerData[$key][$name][$k]=$string;
				}
			}
		}
		if (is_array($replace) || is_array($headerData) || is_array($footerData)) {
			$this->newContent = $content;
		}
	}

	/**
	 * write header data like css or javscripts to the page header
	 * 
	 * @param	array $headerData	the data for the header
	 * @param 	string	$content	the html page
	 * @return  string the htmlpage with the added header data
	 */
	function makeHeader($headerData,$content) {
		$headerData=$headerData;
		if (is_array($headerData)) {
			foreach ($headerData as $k=>$include) {
				if (is_array($include)) {
					$replace = TRUE;
					//t3lib_div::debug($include, $k);		
					/*if($k == 'libraries') {			
						$controllScript=fopen(md5($include).".txt",'r');//wohin?
						fseek($mainScript,0);
					}*/
					$lineCount=0;
					$writeScript=false;
					foreach ($include as $key=>$value) {
						if($k=='libraries') {
							/*$pathToScript=substr($value,strpos($value,'src="')+5, -11);
							$javaScript=file_get_contents($pathToScript);
							// datei noch nich da
							if(!is_file(md5($pathToScript.strlen($javascript)).'.js')) {
								$jsFile=fopen(md5($pathToScript.strlen($javascript)).".js",'w+');//wohin?
								fseek($jsFile,0);//safety
								$jsPack=t3lib_div::minifyJavaScript($javaScript, $error);
								if(strlen($jspack)!=fwrite($jsFile,$jspack))
									error;
								$line[$lineCount]=gets($controllScript);
								if($line[$lineCount]!='// '.$pathToScript.'('.strlen($javaScript).')') {
									$writeScript=true;			
									//				 	
									if(substr($line[$lineCount],$pathToScript)
								}	
								else {
									
								}		
							}
							else {
								$jsPack=file_get_contents(md5($pathToScript.strlen($javascript)).'.js');
							}
							

*/
						}
						//else
						$additionalHeader .= "" . $value . "\n\t";
					}
					//if($k == 'libraries') {			
					//	$additionalHeader .= '<script type="text/javascript" src="'.md5($include).'.js"></script>';//wohin?
					//	fclose($mainScript);
					//}
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
	
	/**
	 * write footer data like css or javscripts to the page footer
	 * 
	 * @param	array $headerData	the data for the header
	 * @param 	string	$content	the html page
	 * @return  string the htmlpage with the added footer data
	 */
	function makeFooter($headerData,$content) {
		$headerData=$headerData;
		if (is_array($headerData)) {
			foreach ($headerData as $k=>$include) {
				if (is_array($include)) {
					$replace = TRUE;
					foreach ($include as $key=>$value) {
						$additionalHeader .= "" . $value . "\n\t";
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
	
	/**
	 * set submit state 
	 * 
	 * @param	array $setup	the configuration setup
	 * @return  string the mode
	 */
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

?>